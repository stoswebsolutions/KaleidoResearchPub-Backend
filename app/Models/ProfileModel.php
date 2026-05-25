<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProfileModel extends Model
{
    protected $table            = 'profiles';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'role_id',
        'full_name',
        'email',
        'phone',
        'password_hash',
        'profile_image',
        'status',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'                    => 'integer',
        'role_id'               => 'integer',
        'created_by'            => '?integer',
        'updated_by'            => '?integer',
        'deleted_by'            => '?integer',
        'failed_login_attempts' => 'integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'uuid' => [
            'rules' => 'permit_empty|max_length[36]|is_unique[profiles.uuid,id,{id}]',
        ],

        'role_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],

        'full_name' => [
            'rules' => 'required|min_length[3]|max_length[150]',
        ],

        'email' => [
            'rules' => 'required|valid_email|max_length[191]|is_unique[profiles.email,id,{id}]',
        ],

        'phone' => [
            'rules' => 'permit_empty|max_length[20]|is_unique[profiles.phone,id,{id}]',
        ],

        'password_hash' => [
            'rules' => 'required|max_length[255]',
        ],

        'status' => [
            'rules' => 'required|in_list[active,inactive,suspended,blocked]',
        ],
    ];

    protected $validationMessages = [
        'uuid' => [
            'is_unique' => 'UUID already exists.',
        ],

        'role_id' => [
            'required' => 'Role is required.',
        ],

        'full_name' => [
            'required'   => 'Full name is required.',
            'min_length' => 'Full name must contain at least 3 characters.',
        ],

        'email' => [
            'required'    => 'Email address is required.',
            'valid_email' => 'Please enter a valid email address.',
            'is_unique'   => 'Email address already exists.',
        ],

        'phone' => [
            'is_unique' => 'Phone number already exists.',
        ],

        'password_hash' => [
            'required' => 'Password hash is required.',
        ],

        'status' => [
            'in_list' => 'Invalid profile status.',
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