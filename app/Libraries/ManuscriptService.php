<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\EditorProfileModel;
use App\Models\JournalModel;
use App\Models\ManuscriptEditorAssignmentModel;
use App\Models\ManuscriptModel;
use App\Models\ManuscriptSequenceModel;
use App\Models\ManuscriptStatusLogModel;
use RuntimeException;

class ManuscriptService
{
    protected ManuscriptModel $manuscriptModel;

    protected JournalModel $journalModel;

    protected EditorProfileModel $editorProfileModel;

    protected ManuscriptSequenceModel $sequenceModel;

    protected ManuscriptEditorAssignmentModel $assignmentModel;

    protected ManuscriptStatusLogModel $statusLogModel;

    public function __construct()
    {
        $this->manuscriptModel =
            new ManuscriptModel();

        $this->journalModel =
            new JournalModel();

        $this->editorProfileModel =
            new EditorProfileModel();

        $this->sequenceModel =
            new ManuscriptSequenceModel();

        $this->assignmentModel =
            new ManuscriptEditorAssignmentModel();

        $this->statusLogModel =
            new ManuscriptStatusLogModel();
    }

    /**
     * Generate Manuscript ID.
     *
     * Example:
     * IJCSR-2026-000001
     */
    public function generateManuscriptId(
        int $journalId
    ): string {

        $journal = $this->journalModel
            ->find($journalId);

        if (! $journal) {
            throw new RuntimeException(
                'Journal not found.'
            );
        }

        $year = (int) date('Y');

        $nextNumber =
            $this->sequenceModel
                ->getNextNumber(
                    $journalId,
                    $year
                );

        return sprintf(
            '%s-%s-%06d',
            strtoupper(
                (string) (
                    $journal['short_title']
                    ?? 'MAN'
                )
            ),
            $year,
            $nextNumber
        );
    }

    /**
     * Assign
     * 1 Chief Editor
     * 2 Editors
     */
    public function assignEditors(
        int $manuscriptId
    ): array {

        $chiefEditor =
            $this->editorProfileModel
                ->where(
                    'editor_type',
                    'editor_in_chief'
                )
                ->where(
                    'status',
                    'active'
                )
                ->orderBy(
                    'RAND()'
                )
                ->first();

        if (! $chiefEditor) {
            throw new RuntimeException(
                'No active chief editor available.'
            );
        }

        $editors =
            $this->editorProfileModel
                ->where(
                    'editor_type',
                    'editor'
                )
                ->where(
                    'status',
                    'active'
                )
                ->orderBy(
                    'RAND()'
                )
                ->findAll(2);

        if (
            count($editors) < 2
        ) {
            throw new RuntimeException(
                'Minimum two active editors required.'
            );
        }

        $assignedEditors = [];

        /**
         * Chief Editor
         */
        $this->assignmentModel->insert([
            'manuscript_id' =>
                $manuscriptId,

            'editor_profile_id' =>
                $chiefEditor['id'],

            'editor_role' =>
                'editor_in_chief',

            'assigned_at' =>
                date('Y-m-d H:i:s'),

            'status' =>
                'assigned',
        ]);

        $assignedEditors[] = [
            'id' =>
                $chiefEditor['id'],
            'role' =>
                'editor_in_chief',
        ];

        /**
         * Editors
         */
        foreach (
            $editors as $editor
        ) {

            $this->assignmentModel
                ->insert([
                    'manuscript_id' =>
                        $manuscriptId,

                    'editor_profile_id' =>
                        $editor['id'],

                    'editor_role' =>
                        'editor',

                    'assigned_at' =>
                        date(
                            'Y-m-d H:i:s'
                        ),

                    'status' =>
                        'assigned',
                ]);

            $assignedEditors[] = [
                'id' =>
                    $editor['id'],
                'role' =>
                    'editor',
            ];
        }

        return $assignedEditors;
    }
        /**
     * Create Status Log.
     */
    public function createStatusLog(
        int $manuscriptId,
        ?string $oldStatus,
        string $newStatus,
        ?string $remarks = null,
        ?int $changedBy = null,
        bool $isSystemGenerated = false
    ): bool {

        return $this->statusLogModel
            ->addLog(
                manuscriptId:
                    $manuscriptId,

                oldStatus:
                    $oldStatus,

                newStatus:
                    $newStatus,

                remarks:
                    $remarks,

                changedBy:
                    $changedBy,

                isSystemGenerated:
                    $isSystemGenerated
            );
    }

    /**
     * Update Manuscript Status.
     */
    public function updateStatus(
        int $manuscriptId,
        string $newStatus,
        ?string $remarks = null,
        ?int $changedBy = null,
        bool $isSystemGenerated = false
    ): bool {

        $manuscript =
            $this->manuscriptModel
                ->find(
                    $manuscriptId
                );

        if (! $manuscript) {
            return false;
        }

        $oldStatus =
            $manuscript[
                'current_status'
            ];

        $updated =
            $this->manuscriptModel
                ->update(
                    $manuscriptId,
                    [
                        'current_status' =>
                            $newStatus,
                    ]
                );

        if (! $updated) {
            return false;
        }

        $this->createStatusLog(
            manuscriptId:
                $manuscriptId,

            oldStatus:
                $oldStatus,

            newStatus:
                $newStatus,

            remarks:
                $remarks,

            changedBy:
                $changedBy,

            isSystemGenerated:
                $isSystemGenerated
        );

        return true;
    }

    /**
     * Get Timeline.
     */
    public function getTimeline(
        int $manuscriptId
    ): array {

        return $this->statusLogModel
            ->getByManuscript(
                $manuscriptId
            );
    }

    /**
     * Get Assigned Editors.
     */
    public function getAssignedEditors(
        int $manuscriptId
    ): array {

        return $this->assignmentModel
            ->getByManuscript(
                $manuscriptId
            );
    }

    /**
     * Get Chief Editor.
     */
    public function getChiefEditor(
        int $manuscriptId
    ): ?array {

        return $this->assignmentModel
            ->getChiefEditor(
                $manuscriptId
            );
    }
}