<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'module',
        'name',
        'slug',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'created_by' => '?integer',
        'updated_by' => '?integer',
        'deleted_by' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'uuid' => [
            'rules' => 'permit_empty|max_length[36]|is_unique[permissions.uuid,id,{id}]',
        ],

        'module' => [
            'rules' => 'required|min_length[2]|max_length[100]',
        ],

        'name' => [
            'rules' => 'required|min_length[2]|max_length[150]',
        ],

        'slug' => [
            'rules' => 'permit_empty|min_length[2]|max_length[150]|alpha_dash|is_unique[permissions.slug,id,{id}]',
        ],

        'status' => [
            'rules' => 'required|in_list[active,inactive]',
        ],
    ];

    protected $validationMessages = [
        
        'id' => [
            'rules' => 'permit_empty|integer',
        ],
        
        'uuid' => [
            'is_unique' => 'UUID already exists.',
        ],

        'module' => [
            'required' => 'Module name is required.',
        ],

        'name' => [
            'required' => 'Permission name is required.',
        ],

        'slug' => [
            'is_unique'  => 'Permission slug already exists.',
            'alpha_dash' => 'Permission slug may contain only letters, numbers, underscores and dashes.',
        ],

        'status' => [
            'in_list' => 'Invalid permission status.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
        'generateSlug',
        ];

    protected $beforeUpdate = [
        'generateSlug',
    ];

    protected function generateUuid(array $data): array
    {
        if (empty($data['data']['uuid'])) {
            $data['data']['uuid'] = generate_uuid();
        }

        return $data;
    }

    protected function generateSlug(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        if (
            ! empty($data['data']['name'])
            && empty($data['data']['slug'])
        ) {
            $data['data']['slug'] = generate_slug(
                $data['data']['name']
            );
        }

        return $data;
    }
}