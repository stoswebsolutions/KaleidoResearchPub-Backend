<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CmsFeatureModel extends Model
{
    protected $table            = 'cms_features';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'type',
        'title',
        'slug',
        'short_description',
        'description',
        'icon',
        'image',
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
        'type' => [
            'label' => 'Type',
            'rules' => 'required|in_list[whychooseus,guidelines,instructions,homepage,journal,author,reviewer,membership,about,general]',
        ],

        'title' => [
            'label' => 'Title',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => 'permit_empty|max_length[255]|is_unique[cms_features.slug,id,{id}]',
        ],

        'short_description' => [
            'label' => 'Short Description',
            'rules' => 'permit_empty',
        ],

        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty',
        ],

        'icon' => [
            'label' => 'Icon',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'image' => [
            'label' => 'Image',
            'rules' => 'permit_empty|max_length[255]',
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
        'type' => [
            'required' => 'Feature type is required.',
            'in_list'  => 'Invalid feature type selected.',
        ],

        'title' => [
            'required'   => 'Title is required.',
            'min_length' => 'Title must contain at least 2 characters.',
            'max_length' => 'Title cannot exceed 255 characters.',
        ],

        'slug' => [
            'is_unique' => 'Feature slug already exists.',
        ],

        'icon' => [
            'max_length' => 'Icon value cannot exceed 255 characters.',
        ],

        'image' => [
            'max_length' => 'Image value cannot exceed 255 characters.',
        ],

        'sort_order' => [
            'integer' => 'Sort order must be a valid number.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid feature status.',
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
    protected function generateUuid(
        array $data
    ): array {
        if (
            empty(
                $data['data']['uuid']
            )
        ) {
            $data['data']['uuid'] = generate_uuid();
        }

        return $data;
    }

    /**
     * Auto Generate Slug
     */
    protected function generateSlug(
        array $data
    ): array {
        if (! isset($data['data'])) {
            return $data;
        }

        if (
            ! empty(
                $data['data']['title']
            )
            && empty(
                $data['data']['slug']
            )
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
     * Filter By Type
     */
    public function byType(
        string $type
    ): self {
        return $this->where(
            $this->table . '.type',
            $type
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