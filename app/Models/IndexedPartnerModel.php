<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class IndexedPartnerModel extends Model
{
    protected $table            = 'indexed_partners';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'title',
        'slug',
        'logo',
        'website_url',
        'description',
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
        
        'id' => [
            'rules' => 'permit_empty|integer',
        ],
        
        'title' => [
            'label' => 'Title',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => 'permit_empty|max_length[255]|is_unique[indexed_partners.slug,id,{id}]',
        ],

        'logo' => [
            'label' => 'Logo',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'website_url' => [
            'label' => 'Website URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty',
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
            'required'   => 'Title is required.',
            'min_length' => 'Title must contain at least 2 characters.',
            'max_length' => 'Title cannot exceed 255 characters.',
        ],

        'slug' => [
            'is_unique' => 'Indexed partner slug already exists.',
        ],

        'website_url' => [
            'valid_url' => 'Please enter a valid website URL.',
        ],

        'sort_order' => [
            'integer' => 'Sort order must be a valid number.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid indexed partner status.',
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
}