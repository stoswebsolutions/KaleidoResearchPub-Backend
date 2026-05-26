<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class JournalModel extends Model
{
    protected $table            = 'journals';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'title',
        'short_title',
        'slug',
        'thumbnail',
        'description',
        'aims_scope',
        'issn_print',
        'issn_online',
        'doi_prefix',
        'impact_factor',
        'frequency',
        'publication_type',
        'subject_area',
        'peer_review_type',
        'is_indexed',
        'year_started',
        'website_url',
        'contact_email',
        'contact_phone',
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

        'short_title' => [
            'label' => 'Short Title',
            'rules' => 'permit_empty|max_length[100]',
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => 'permit_empty|max_length[255]|is_unique[journals.slug,id,{id}]',
        ],

        'thumbnail' => [
            'label' => 'Thumbnail',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty',
        ],

        'aims_scope' => [
            'label' => 'Aims & Scope',
            'rules' => 'permit_empty',
        ],

        'issn_print' => [
            'label' => 'Print ISSN',
            'rules' => 'permit_empty|max_length[20]',
        ],

        'issn_online' => [
            'label' => 'Online ISSN',
            'rules' => 'permit_empty|max_length[20]',
        ],

        'doi_prefix' => [
            'label' => 'DOI Prefix',
            'rules' => 'permit_empty|max_length[100]',
        ],

        'impact_factor' => [
            'label' => 'Impact Factor',
            'rules' => 'permit_empty|decimal',
        ],

        'frequency' => [
            'label' => 'Frequency',
            'rules' => 'permit_empty|in_list[annual,biannual,quarterly,monthly,continuous]',
        ],

        'publication_type' => [
            'label' => 'Publication Type',
            'rules' => 'permit_empty|in_list[online,print,hybrid]',
        ],

        'subject_area' => [
            'label' => 'Subject Area',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'peer_review_type' => [
            'label' => 'Peer Review Type',
            'rules' => 'permit_empty|in_list[single_blind,double_blind,open_review,editor_review]',
        ],

        'is_indexed' => [
            'label' => 'Indexed Status',
            'rules' => 'required|in_list[0,1]',
        ],

        'year_started' => [
            'label' => 'Year Started',
            'rules' => 'permit_empty|integer|greater_than[1900]',
        ],

        'website_url' => [
            'label' => 'Website URL',
            'rules' => 'permit_empty|valid_url|max_length[255]',
        ],

        'contact_email' => [
            'label' => 'Contact Email',
            'rules' => 'permit_empty|valid_email|max_length[191]',
        ],

        'contact_phone' => [
            'label' => 'Contact Phone',
            'rules' => 'permit_empty|max_length[30]',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[draft,active,inactive,archived]',
        ],
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Journal title is required.',
            'min_length' => 'Journal title must contain at least 2 characters.',
            'max_length' => 'Journal title cannot exceed 255 characters.',
        ],

        'slug' => [
            'is_unique' => 'Journal slug already exists.',
        ],

        'impact_factor' => [
            'decimal' => 'Impact factor must be a valid decimal value.',
        ],

        'frequency' => [
            'in_list' => 'Invalid journal frequency selected.',
        ],

        'publication_type' => [
            'in_list' => 'Invalid publication type selected.',
        ],

        'peer_review_type' => [
            'in_list' => 'Invalid peer review type selected.',
        ],

        'is_indexed' => [
            'in_list' => 'Invalid indexing status.',
        ],

        'website_url' => [
            'valid_url' => 'Please provide a valid website URL.',
        ],

        'contact_email' => [
            'valid_email' => 'Please provide a valid email address.',
        ],

        'status' => [
            'in_list' => 'Invalid journal status.',
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
            'status',
            'active'
        );
    }

    /**
     * Ordered Records
     */
    public function ordered(): self
    {
        return $this->orderBy(
            'title',
            'ASC'
        );
    }

    /**
     * Indexed Journals
     */
    public function indexed(): self
    {
        return $this->where(
            'is_indexed',
            1
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