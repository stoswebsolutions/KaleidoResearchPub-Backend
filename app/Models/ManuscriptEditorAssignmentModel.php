<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptEditorAssignmentModel extends Model
{
    protected $table = 'manuscript_editor_assignments';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'editor_profile_id',

        'editor_role',

        'assigned_at',

        'status',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'editor_profile_id' => 'integer',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [

        'manuscript_id' => [
            'label' => 'Manuscript',
            'rules' => 'required|integer',
        ],

        'editor_profile_id' => [
            'label' => 'Editor Profile',
            'rules' => 'required|integer',
        ],

        'editor_role' => [
            'label' => 'Editor Role',
            'rules' => 'required|in_list[editor_in_chief,editor]',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[assigned,accepted,declined,completed]',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'editor_profile_id' => [
            'required' =>
                'Editor profile is required.',
        ],

        'editor_role' => [
            'required' =>
                'Editor role is required.',
            'in_list' =>
                'Invalid editor role selected.',
        ],

        'status' => [
            'required' =>
                'Status is required.',
            'in_list' =>
                'Invalid status selected.',
        ],
    ];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
    ];

    /**
     * Generate UUID.
     */
    protected function generateUuid(
        array $data
    ): array {

        if (
            empty(
                $data['data']['uuid']
            )
        ) {

            $data['data']['uuid'] =
                generate_uuid();
        }

        return $data;
    }

    /**
     * Find By UUID.
     */
    public function findByUuid(
        string $uuid
    ): ?array {

        return $this->where(
            'uuid',
            $uuid
        )->first();
    }

    /**
     * Get Assignments By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): array {

        return $this->select([
                'manuscript_editor_assignments.*',
                'editor_profiles.full_name',
                'editor_profiles.designation',
                'editor_profiles.organization_name',
            ])
            ->join(
                'editor_profiles',
                'editor_profiles.id = manuscript_editor_assignments.editor_profile_id',
                'left'
            )
            ->where(
                'manuscript_editor_assignments.manuscript_id',
                $manuscriptId
            )
            ->orderBy(
                'editor_role',
                'DESC'
            )
            ->findAll();
    }

    /**
     * Get Chief Editor Assignment.
     */
    public function getChiefEditor(
        int $manuscriptId
    ): ?array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'editor_role',
                'editor_in_chief'
            )
            ->first();
    }

    /**
     * Get Editor Assignments.
     */
    public function getEditors(
        int $manuscriptId
    ): array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'editor_role',
                'editor'
            )
            ->findAll();
    }

    /**
     * Check Assignment Exists.
     */
    public function assignmentExists(
        int $manuscriptId,
        int $editorProfileId
    ): bool {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'editor_profile_id',
                $editorProfileId
            )
            ->countAllResults() > 0;
    }

    /**
     * Get Assigned Manuscripts.
     */
    public function getAssignedManuscripts(
        int $editorProfileId
    ): array {

        return $this->where(
                'editor_profile_id',
                $editorProfileId
            )
            ->whereIn(
                'status',
                [
                    'assigned',
                    'accepted',
                ]
            )
            ->findAll();
    }
}