<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table            = 'password_resets';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'profile_id',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'profile_id' => 'integer',
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'uuid' => [
            'rules' => 'permit_empty|max_length[36]|is_unique[password_resets.uuid,id,{id}]',
        ],

        'profile_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],

        'token_hash' => [
            'rules' => 'required|exact_length[64]',
        ],

        'expires_at' => [
            'rules' => 'required',
        ],
    ];

    protected $validationMessages = [
        'uuid' => [
            'is_unique' => 'UUID already exists.',
        ],

        'profile_id' => [
            'required' => 'Profile is required.',
        ],

        'token_hash' => [
            'required'     => 'Token hash is required.',
            'exact_length' => 'Invalid token hash length.',
        ],

        'expires_at' => [
            'required' => 'Expiry date is required.',
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