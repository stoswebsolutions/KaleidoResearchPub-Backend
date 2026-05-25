<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'name',
        'slug',
        'description',
        'status',
        'is_system',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'         => 'integer',
        'is_system'  => 'boolean',
        'created_by' => '?integer',
        'updated_by' => '?integer',
        'deleted_by' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'uuid' => [
            'rules' => 'permit_empty|max_length[36]|is_unique[roles.uuid,id,{id}]',
        ],

        'name' => [
            'rules' => 'required|min_length[2]|max_length[100]|is_unique[roles.name,id,{id}]',
        ],

        'slug' => [
            'rules' => 'permit_empty|min_length[2]|max_length[120]|alpha_dash|is_unique[roles.slug,id,{id}]',
        ],

        'status' => [
            'rules' => 'required|in_list[active,inactive]',
        ],

        'is_system' => [
            'rules' => 'required|in_list[0,1]',
        ],
    ];

    protected $validationMessages = [
        'uuid' => [
            'is_unique' => 'UUID already exists.',
        ],

        'name' => [
            'required'  => 'Role name is required.',
            'is_unique' => 'Role name already exists.',
        ],

        'slug' => [
            'is_unique'  => 'Role slug already exists.',
            'alpha_dash' => 'Role slug may contain only letters, numbers, underscores and dashes.',
        ],

        'status' => [
            'in_list' => 'Invalid role status.',
        ],

        'is_system' => [
            'in_list' => 'Invalid system role flag.',
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