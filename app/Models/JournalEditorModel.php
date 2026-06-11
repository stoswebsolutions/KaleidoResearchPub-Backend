<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class JournalEditorModel extends Model
{
    protected $table            = 'journal_editors';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'journal_id',
        'editor_profile_id',
        'editor_role',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [

        'journal_id' => [
            'label' => 'Journal',
            'rules' => 'required|integer',
        ],

        'editor_profile_id' => [
            'label' => 'Editor Profile',
            'rules' => 'required|integer',
        ],

        'editor_role' => [
            'label' => 'Editor Role',
            'rules' =>
                'required|in_list[editor_in_chief,editor,managing_editor,associate_editor,editorial_board_member,review_editor,guest_editor]',
        ],

        'sort_order' => [
            'label' => 'Sort Order',
            'rules' => 'permit_empty|integer',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[active,inactive]',
        ],
    ];

    protected $validationMessages = [

        'journal_id' => [
            'required' => 'Journal is required.',
        ],

        'editor_profile_id' => [
            'required' =>
                'Editor profile is required.',
        ],

        'editor_role' => [
            'required' =>
                'Editor role is required.',
            'in_list' =>
                'Invalid editor role.',
        ],

        'sort_order' => [
            'integer' =>
                'Sort order must be numeric.',
        ],

        'status' => [
            'required' =>
                'Status is required.',
            'in_list' =>
                'Invalid status.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
    ];

    /**
     * Auto Generate UUID
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
     * Active Records
     */
    public function active(): self
    {
        return $this->where(
            $this->table . '.status',
            'active'
        );
    }

    /**
     * Ordered Records
     */
    public function ordered(): self
    {
        return $this->orderBy(
            $this->table . '.sort_order',
            'ASC'
        )->orderBy(
            $this->table . '.id',
            'ASC'
        );
    }

    /**
     * Find By UUID
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
     * Find By Journal
     */
    public function findByJournal(
        int $journalId
    ): array {

        return $this->where(
            'journal_id',
            $journalId
        )
        ->ordered()
        ->findAll();
    }

    /**
     * Find By Editor
     */
    public function findByEditor(
        int $editorProfileId
    ): array {

        return $this->where(
            'editor_profile_id',
            $editorProfileId
        )
        ->ordered()
        ->findAll();
    }

    /**
     * Find By Journal And Editor
     */
    public function findByJournalAndEditor(
        int $journalId,
        int $editorProfileId
    ): ?array {

        return $this->where(
            'journal_id',
            $journalId
        )
        ->where(
            'editor_profile_id',
            $editorProfileId
        )
        ->first();
    }

    /**
     * Assignment Exists
     */
    public function assignmentExists(
        int $journalId,
        int $editorProfileId
    ): bool {

        return $this->where(
            'journal_id',
            $journalId
        )
        ->where(
            'editor_profile_id',
            $editorProfileId
        )
        ->countAllResults() > 0;
    }
}