<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class EditorProfileModel extends Model
{
    protected $table            = 'editor_profiles';

    protected $primaryKey       = 'id';

    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;

    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',

        'profile_id',

        'editor_type',

        'full_name',

        'designation',
        'department',
        'organization_name',
        'country',

        'qualification',

        'specialization',
        'research_interests',

        'experience_years',

        'bio',

        'profile_image',

        'profile_slug',

        'orcid_id',

        'google_scholar_url',
        'scopus_author_url',
        'researchgate_url',
        'linkedin_url',
        'personal_website_url',

        'sort_order',

        'is_featured',

        'status',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',

        'profile_id' => '?integer',

        'experience_years' => 'integer',

        'sort_order' => 'integer',

        'is_featured' => 'integer',

        'created_by' => '?integer',

        'updated_by' => '?integer',

        'deleted_by' => '?integer',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        
        'id' => [
            'rules' => 'permit_empty|integer',
        ],

        'profile_id' => [
            'label' => 'Profile',
            'rules' => 'permit_empty|integer',
        ],

        'editor_type' => [
            'label' => 'Editor Type',
            'rules' => 'required|in_list[editor_in_chief,associate_editor,managing_editor,guest_editor,editorial_board,advisor,reviewer_board]',
        ],

        'full_name' => [
            'label' => 'Full Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'designation' => [
            'label' => 'Designation',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'department' => [
            'label' => 'Department',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'organization_name' => [
            'label' => 'Organization Name',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'country' => [
            'label' => 'Country',
            'rules' => 'permit_empty|max_length[100]',
        ],

        'qualification' => [
            'label' => 'Qualification',
            'rules' => 'permit_empty',
        ],

        'specialization' => [
            'label' => 'Specialization',
            'rules' => 'permit_empty',
        ],

        'research_interests' => [
            'label' => 'Research Interests',
            'rules' => 'permit_empty',
        ],

        'experience_years' => [
            'label' => 'Experience Years',
            'rules' => 'permit_empty|integer|greater_than_equal_to[0]',
        ],

        'bio' => [
            'label' => 'Bio',
            'rules' => 'permit_empty',
        ],

        'profile_image' => [
            'label' => 'Profile Image',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'profile_slug' => [
            'label' => 'Profile Slug',
            'rules' => 'permit_empty|min_length[2]|max_length[255]|is_unique[editor_profiles.profile_slug,id,{id}]',
        ],

        'orcid_id' => [
            'label' => 'ORCID ID',
            'rules' => 'permit_empty|max_length[50]',
        ],

        'google_scholar_url' => [
            'label' => 'Google Scholar URL',
            'rules' => 'permit_empty|valid_url',
        ],

        'scopus_author_url' => [
            'label' => 'Scopus Author URL',
            'rules' => 'permit_empty|valid_url',
        ],

        'researchgate_url' => [
            'label' => 'ResearchGate URL',
            'rules' => 'permit_empty|valid_url',
        ],

        'linkedin_url' => [
            'label' => 'LinkedIn URL',
            'rules' => 'permit_empty|valid_url',
        ],

        'personal_website_url' => [
            'label' => 'Personal Website URL',
            'rules' => 'permit_empty|valid_url',
        ],

        'sort_order' => [
            'label' => 'Sort Order',
            'rules' => 'permit_empty|integer',
        ],

        'is_featured' => [
            'label' => 'Featured Status',
            'rules' => 'permit_empty|in_list[0,1]',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[active,inactive]',
        ],
    ];

    protected $validationMessages = [

        'editor_type' => [
            'required' => 'Editor type is required.',
            'in_list'  => 'Invalid editor type selected.',
        ],

        'full_name' => [
            'required'   => 'Full name is required.',
            'min_length' => 'Full name must contain at least 2 characters.',
            'max_length' => 'Full name cannot exceed 255 characters.',
        ],

        'profile_slug' => [
            'is_unique'  => 'Profile slug already exists.',
            'max_length' => 'Profile slug cannot exceed 255 characters.',
        ],

        'google_scholar_url' => [
            'valid_url' => 'Please enter a valid Google Scholar URL.',
        ],

        'scopus_author_url' => [
            'valid_url' => 'Please enter a valid Scopus Author URL.',
        ],

        'researchgate_url' => [
            'valid_url' => 'Please enter a valid ResearchGate URL.',
        ],

        'linkedin_url' => [
            'valid_url' => 'Please enter a valid LinkedIn URL.',
        ],

        'personal_website_url' => [
            'valid_url' => 'Please enter a valid personal website URL.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid status selected.',
        ],
    ];

    protected $skipValidation = false;

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
     * Generate UUID
     */
    protected function generateUuid(
        array $data
    ): array {

        if (
            empty($data['data']['uuid'])
        ) {
            $data['data']['uuid'] = generate_uuid();
        }

        return $data;
    }

    /**
     * Generate Profile Slug
     */
    protected function generateSlug(
        array $data
    ): array {

        if (
            empty($data['data']['profile_slug'])
            &&
            ! empty($data['data']['full_name'])
        ) {

            $slug = url_title(
                $data['data']['full_name'],
                '-',
                true
            );

            $originalSlug = $slug;

            $counter = 1;

            while (
                $this->where(
                    'profile_slug',
                    $slug
                )->countAllResults() > 0
            ) {

                $slug = $originalSlug . '-' . $counter;

                $counter++;
            }

            $data['data']['profile_slug'] = $slug;
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
     * Featured Records
     */
    public function featured(): self
    {
        return $this->where(
            'is_featured',
            1
        );
    }

    /**
     * Filter By Editor Type
     */
    public function byEditorType(
        string $editorType
    ): self {

        return $this->where(
            'editor_type',
            $editorType
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
     * Find By Slug
     */
    public function findBySlug(
        string $slug
    ): ?array {

        return $this->where(
            'profile_slug',
            $slug
        )->first();
    }
}