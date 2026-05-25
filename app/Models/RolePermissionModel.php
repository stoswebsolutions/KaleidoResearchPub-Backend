<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table            = 'role_permissions';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'role_id',
        'permission_id',
        'created_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'            => 'integer',
        'role_id'       => 'integer',
        'permission_id' => 'integer',
        'created_by'    => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'role_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],

        'permission_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],
    ];

    protected $validationMessages = [
        'role_id' => [
            'required'     => 'Role is required.',
            'greater_than' => 'Invalid role selected.',
        ],

        'permission_id' => [
            'required'     => 'Permission is required.',
            'greater_than' => 'Invalid permission selected.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = false;
}