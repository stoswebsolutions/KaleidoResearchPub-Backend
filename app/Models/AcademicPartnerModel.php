<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AcademicPartnerModel extends Model
{
    protected $table            = 'academic_partners';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'name',
        'slug',
        'logo',
        'partner_type',
        'address',
        'description',
        'website_url',
        'email',
        'phone',
        'contact_person',
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
        'name' => [
            'label' => 'Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => 'permit_empty|max_length[255]|is_unique[academic_partners.slug,id,{id}]',
        ],

        'logo' => [
            'label' => 'Logo',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'partner_type' => [
            'label' => 'Partner Type',
            'rules' => 'required|in_list[university,college,research_institute,association,society,industry_partner,government_body,other]',
        ],

        'address' => [
            'label' => 'Address',
            'rules' => 'permit_empty',
        ],

        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty',
        ],

        'website_url' => [
            'label' => 'Website URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'email' => [
            'label' => 'Email',
            'rules' => 'permit_empty|valid_email|max_length[191]',
        ],

        'phone' => [
            'label' => 'Phone',
            'rules' => 'permit_empty|max_length[30]',
        ],

        'contact_person' => [
            'label' => 'Contact Person',
            'rules' => 'permit_empty|max_length[150]',
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
        'name' => [
            'required'   => 'Academic partner name is required.',
            'min_length' => 'Academic partner name must contain at least 2 characters.',
            'max_length' => 'Academic partner name cannot exceed 255 characters.',
        ],

        'slug' => [
            'is_unique' => 'Academic partner slug already exists.',
        ],

        'partner_type' => [
            'required' => 'Partner type is required.',
            'in_list'  => 'Invalid partner type selected.',
        ],

        'website_url' => [
            'valid_url' => 'Please enter a valid website URL.',
        ],

        'email' => [
            'valid_email' => 'Please enter a valid email address.',
        ],

        'sort_order' => [
            'integer' => 'Sort order must be a valid number.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid academic partner status.',
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
            ! empty($data['data']['name'])
            && empty($data['data']['slug'])
        ) {
            $data['data']['slug'] = generate_slug(
                $data['data']['name']
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
            $this->table . '.name',
            'ASC'
        );
    }

    /**
     * Filter By Partner Type
     */
    public function byPartnerType(
        string $partnerType
    ): self {
        return $this->where(
            $this->table . '.partner_type',
            $partnerType
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