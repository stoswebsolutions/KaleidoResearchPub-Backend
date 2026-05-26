<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class DisciplineModel extends Model
{
    protected $table            = 'disciplines';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'title',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'title' => [
            'label' => 'Title',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => 'permit_empty|max_length[255]|is_unique[disciplines.slug,id,{id}]',
        ],

        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty',
        ],

        'parent_id' => [
            'label' => 'Parent Discipline',
            'rules' => 'permit_empty|integer|greater_than[0]',
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
        'title' => [
            'required'   => 'Discipline title is required.',
            'min_length' => 'Discipline title must contain at least 2 characters.',
            'max_length' => 'Discipline title cannot exceed 255 characters.',
        ],

        'slug' => [
            'is_unique' => 'Discipline slug already exists.',
        ],

        'parent_id' => [
            'integer'      => 'Invalid parent discipline.',
            'greater_than' => 'Invalid parent discipline.',
        ],

        'sort_order' => [
            'integer' => 'Sort order must be a valid number.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid discipline status.',
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

    /**
     * Auto Generate UUID
     */
    protected function generateUuid(array $data): array
    {
        if (empty($data['data']['uuid'])) {
            $data['data']['uuid'] = generate_uuid();
        }

        return $data;
    }

    /**
     * Auto Generate Slug
     */
    protected function generateSlug(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        if (
            ! empty($data['data']['title'])
            && empty($data['data']['slug'])
        ) {
            $data['data']['slug'] = generate_slug(
                $data['data']['title']
            );
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
            $this->table . '.title',
            'ASC'
        );
    }

    /**
     * Root Disciplines
     */
    public function rootDisciplines(): self
    {
        return $this->where(
            'parent_id',
            null
        );
    }

    /**
     * Child Disciplines
     */
    public function childrenOf(
        int $parentId
    ): self {
        return $this->where(
            'parent_id',
            $parentId
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
}