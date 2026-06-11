<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CmsPageModel extends Model
{
    protected $table            = 'cms_pages';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'page_key',
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'image',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        
        'id' => [
            'rules' => 'permit_empty|integer',
        ],

        'page_key' => [
            'label' => 'Page Key',
            'rules' => 'required|max_length[100]|is_unique[cms_pages.page_key,id,{id}]',
        ],

        'title' => [
            'label' => 'Title',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => 'permit_empty|max_length[255]|is_unique[cms_pages.slug,id,{id}]',
        ],

        'content' => [
            'label' => 'Content',
            'rules' => 'permit_empty',
        ],

        'meta_title' => [
            'label' => 'Meta Title',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'meta_keywords' => [
            'label' => 'Meta Keywords',
            'rules' => 'permit_empty',
        ],

        'meta_description' => [
            'label' => 'Meta Description',
            'rules' => 'permit_empty',
        ],

        'image' => [
            'label' => 'Banner Image',
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
        'page_key' => [
            'required'  => 'Page key is required.',
            'is_unique' => 'Page key already exists.',
        ],

        'title' => [
            'required'   => 'Title is required.',
            'min_length' => 'Title must contain at least 2 characters.',
            'max_length' => 'Title cannot exceed 255 characters.',
        ],

        'slug' => [
            'is_unique' => 'Page slug already exists.',
        ],

        'meta_title' => [
            'max_length' => 'Meta title cannot exceed 255 characters.',
        ],

        'image' => [
            'max_length' => 'Banner image path cannot exceed 255 characters.',
        ],

        'sort_order' => [
            'integer' => 'Sort order must be a valid number.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid page status.',
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

    /**
     * Find By Page Key
     */
    public function findByPageKey(
        string $pageKey
    ): ?array {
        return $this->where(
            'page_key',
            $pageKey
        )->first();
    }
}