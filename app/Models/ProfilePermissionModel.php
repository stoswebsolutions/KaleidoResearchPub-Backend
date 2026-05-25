<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProfilePermissionModel extends Model
{
    protected $table            = 'profile_permissions';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'profile_id',
        'permission_id',
        'created_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'            => 'integer',
        'profile_id'    => 'integer',
        'permission_id' => 'integer',
        'created_by'    => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'profile_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],

        'permission_id' => [
            'rules' => 'required|integer|greater_than[0]',
        ],
    ];

    protected $validationMessages = [
        'profile_id' => [
            'required'     => 'Profile is required.',
            'greater_than' => 'Invalid profile selected.',
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