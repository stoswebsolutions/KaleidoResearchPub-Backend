<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProfileSessionModel extends Model
{
    protected $table            = 'profile_sessions';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'profile_id',
        'refresh_token_hash',
        'device_type',
        'device_name',
        'browser',
        'platform',
        'user_agent',
        'ip_address',
        'login_method',
        'last_activity_at',
        'expires_at',
        'login_at',
        'logout_at',
        'revoked_at',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'profile_id' => 'integer',
        'is_active'  => 'boolean',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'uuid' => [
            'rules' => 'permit_empty|max_length[36]|is_unique[profile_sessions.uuid,id,{id}]',
        ],

        'profile_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],

        'refresh_token_hash' => [
            'rules' => 'required|max_length[255]',
        ],

        'login_method' => [
            'rules' => 'required|in_list[password,google,orcid,otp]',
        ],

        'is_active' => [
            'rules' => 'required|in_list[0,1]',
        ],
    ];

    protected $validationMessages = [
        'uuid' => [
            'is_unique' => 'UUID already exists.',
        ],

        'profile_id' => [
            'required' => 'Profile is required.',
        ],

        'refresh_token_hash' => [
            'required' => 'Refresh token hash is required.',
        ],

        'login_method' => [
            'in_list' => 'Invalid login method.',
        ],

        'is_active' => [
            'in_list' => 'Invalid session status.',
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