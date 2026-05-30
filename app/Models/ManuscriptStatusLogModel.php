<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptStatusLogModel extends Model
{
    protected $table = 'manuscript_status_logs';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'old_status',

        'new_status',

        'remarks',

        'changed_by',

        'is_system_generated',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'changed_by' => '?integer',

        'is_system_generated' => 'boolean',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected $deletedField = '';

    protected $validationRules = [

        'manuscript_id' => [
            'label' => 'Manuscript',
            'rules' => 'required|integer',
        ],

        'new_status' => [
            'label' => 'New Status',
            'rules' => 'required|max_length[50]',
        ],

        'old_status' => [
            'label' => 'Old Status',
            'rules' => 'permit_empty|max_length[50]',
        ],

        'remarks' => [
            'label' => 'Remarks',
            'rules' => 'permit_empty',
        ],

        'changed_by' => [
            'label' => 'Changed By',
            'rules' => 'permit_empty|integer',
        ],

        'is_system_generated' => [
            'label' => 'System Generated',
            'rules' => 'permit_empty|in_list[0,1]',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'new_status' => [
            'required' =>
                'New status is required.',
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

            $data['data']['uuid']
                = generate_uuid();
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
     * Get Timeline By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): array {

        return $this->select([
                'manuscript_status_logs.*',
                'profiles.full_name',
            ])
            ->join(
                'profiles',
                'profiles.id = manuscript_status_logs.changed_by',
                'left'
            )
            ->where(
                'manuscript_status_logs.manuscript_id',
                $manuscriptId
            )
            ->orderBy(
                'manuscript_status_logs.created_at',
                'ASC'
            )
            ->findAll();
    }

    /**
     * Add Status Log.
     */
    public function addLog(
        int $manuscriptId,
        ?string $oldStatus,
        string $newStatus,
        ?string $remarks = null,
        ?int $changedBy = null,
        bool $isSystemGenerated = false
    ): bool {

        return $this->insert([
            'manuscript_id'       => $manuscriptId,
            'old_status'          => $oldStatus,
            'new_status'          => $newStatus,
            'remarks'             => $remarks,
            'changed_by'          => $changedBy,
            'is_system_generated' => $isSystemGenerated ? 1 : 0,
        ]) !== false;
    }

    /**
     * Get Latest Status Log.
     */
    public function getLatestStatus(
        int $manuscriptId
    ): ?array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->orderBy(
                'created_at',
                'DESC'
            )
            ->first();
    }

    /**
     * Get Status Count.
     */
    public function getStatusCount(
        string $status
    ): int {

        return $this->where(
            'new_status',
            $status
        )->countAllResults();
    }

    /**
     * Get System Generated Logs.
     */
    public function getSystemLogs(
        int $manuscriptId
    ): array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'is_system_generated',
                1
            )
            ->orderBy(
                'created_at',
                'ASC'
            )
            ->findAll();
    }

    /**
     * Get Manual Logs.
     */
    public function getManualLogs(
        int $manuscriptId
    ): array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'is_system_generated',
                0
            )
            ->orderBy(
                'created_at',
                'ASC'
            )
            ->findAll();
    }
}