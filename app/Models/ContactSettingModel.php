<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ContactSettingModel extends Model
{
    protected $table            = 'contact_settings';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',

        'organization_name',

        'address',

        'email',
        'alternate_email',

        'phone',
        'alternate_phone',

        'whatsapp',

        'google_map_url',

        'facebook_url',
        'twitter_url',
        'linkedin_url',
        'instagram_url',
        'youtube_url',

        'working_hours',

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
        'organization_name' => [
            'label' => 'Organization Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'address' => [
            'label' => 'Address',
            'rules' => 'permit_empty',
        ],

        'email' => [
            'label' => 'Email',
            'rules' => 'required|valid_email|max_length[255]',
        ],

        'alternate_email' => [
            'label' => 'Alternate Email',
            'rules' => 'permit_empty|valid_email|max_length[255]',
        ],

        'phone' => [
            'label' => 'Phone',
            'rules' => 'permit_empty|max_length[50]',
        ],

        'alternate_phone' => [
            'label' => 'Alternate Phone',
            'rules' => 'permit_empty|max_length[50]',
        ],

        'whatsapp' => [
            'label' => 'WhatsApp',
            'rules' => 'permit_empty|max_length[50]',
        ],

        'google_map_url' => [
            'label' => 'Google Map URL',
            'rules' => 'permit_empty|valid_url|max_length[500]',
        ],

        'facebook_url' => [
            'label' => 'Facebook URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'twitter_url' => [
            'label' => 'Twitter URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'linkedin_url' => [
            'label' => 'LinkedIn URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'instagram_url' => [
            'label' => 'Instagram URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'youtube_url' => [
            'label' => 'YouTube URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'working_hours' => [
            'label' => 'Working Hours',
            'rules' => 'permit_empty',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[active,inactive]',
        ],
    ];

    protected $validationMessages = [
        'organization_name' => [
            'required'   => 'Organization name is required.',
            'min_length' => 'Organization name must contain at least 2 characters.',
            'max_length' => 'Organization name cannot exceed 255 characters.',
        ],

        'email' => [
            'required'    => 'Email address is required.',
            'valid_email' => 'Please enter a valid email address.',
        ],

        'alternate_email' => [
            'valid_email' => 'Please enter a valid alternate email address.',
        ],

        'google_map_url' => [
            'valid_url' => 'Please enter a valid Google Map URL.',
        ],

        'facebook_url' => [
            'valid_url' => 'Please enter a valid Facebook URL.',
        ],

        'twitter_url' => [
            'valid_url' => 'Please enter a valid Twitter URL.',
        ],

        'linkedin_url' => [
            'valid_url' => 'Please enter a valid LinkedIn URL.',
        ],

        'instagram_url' => [
            'valid_url' => 'Please enter a valid Instagram URL.',
        ],

        'youtube_url' => [
            'valid_url' => 'Please enter a valid YouTube URL.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid contact setting status.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
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
     * Get Active Contact Settings
     */
    public function getActiveSettings(): ?array
    {
        return $this->active()
            ->orderBy('id', 'DESC')
            ->first();
    }
}