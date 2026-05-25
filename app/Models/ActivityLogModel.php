<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'profile_id',
        'module',
        'action',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'profile_id' => 'integer',
        'record_id'  => '?integer',
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'uuid' => [
            'rules' => 'permit_empty|max_length[36]|is_unique[activity_logs.uuid,id,{id}]',
        ],

        'profile_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],

        'module' => [
            'rules' => 'required|min_length[2]|max_length[100]',
        ],

        'action' => [
            'rules' => 'required|min_length[2]|max_length[100]',
        ],
    ];

    protected $validationMessages = [
        'uuid' => [
            'is_unique' => 'UUID already exists.',
        ],

        'profile_id' => [
            'required' => 'Profile is required.',
        ],

        'module' => [
            'required' => 'Module is required.',
        ],

        'action' => [
            'required' => 'Action is required.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data): array
    {
        if (empty($data['data']['uuid'])) {
            $data['data']['uuid'] = generate_uuid();
        }

        return $data;
    }
}