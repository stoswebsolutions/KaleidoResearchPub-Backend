<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptPublicationModel extends Model
{
    protected $table = 'manuscript_publications';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'page_start',

        'page_end',

        'volume_number',

        'issue_number',

        'published_by',

        'frequency',

        'published_date',

        'doi',

        'article_url',

        'published_pdf',

        'status',

        'created_by',

        'updated_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'page_start' => 'integer',

        'page_end' => 'integer',

        'created_by' => '?integer',

        'updated_by' => '?integer',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [

        'manuscript_id' => [
            'label' => 'Manuscript',
            'rules' => 'required|integer',
        ],

        'page_start' => [
            'label' => 'Page Start',
            'rules' => 'required|integer|greater_than[0]',
        ],

        'page_end' => [
            'label' => 'Page End',
            'rules' => 'required|integer|greater_than[0]',
        ],

        'volume_number' => [
            'label' => 'Volume Number',
            'rules' => 'required|max_length[50]',
        ],

        'issue_number' => [
            'label' => 'Issue Number',
            'rules' => 'required|max_length[50]',
        ],

        'published_by' => [
            'label' => 'Published By',
            'rules' => 'required|max_length[255]',
        ],

        'frequency' => [
            'label' => 'Frequency',
            'rules' => 'required|in_list[monthly,quarterly,half_yearly,yearly]',
        ],

        'published_date' => [
            'label' => 'Published Date',
            'rules' => 'permit_empty|valid_date',
        ],

        'doi' => [
            'label' => 'DOI',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'article_url' => [
            'label' => 'Article URL',
            'rules' => 'permit_empty|valid_url_strict',
        ],

        'published_pdf' => [
            'label' => 'Published PDF',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[published,draft,archived]',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'page_start' => [
            'required' =>
                'Starting page is required.',
        ],

        'page_end' => [
            'required' =>
                'Ending page is required.',
        ],

        'volume_number' => [
            'required' =>
                'Volume number is required.',
        ],

        'issue_number' => [
            'required' =>
                'Issue number is required.',
        ],

        'published_by' => [
            'required' =>
                'Published by is required.',
        ],

        'frequency' => [
            'required' =>
                'Frequency is required.',
            'in_list' =>
                'Invalid frequency selected.',
        ],

        'article_url' => [
            'valid_url_strict' =>
                'Please provide a valid article URL.',
        ],

        'status' => [
            'required' =>
                'Status is required.',
            'in_list' =>
                'Invalid publication status selected.',
        ],
    ];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
    ];

    /**
     * Generate UUID.
     */
    protected function generateUuid(
        array $data
    ): array {

        if (
            empty(
                $data['data']['uuid']
            )
        ) {

            $data['data']['uuid']
                = generate_uuid();
        }

        return $data;
    }

    /**
     * Find By UUID.
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
     * Get Publication By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): ?array {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )->first();
    }

    /**
     * Check Publication Exists.
     */
    public function publicationExists(
        int $manuscriptId
    ): bool {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )->countAllResults() > 0;
    }

    /**
     * Find By DOI.
     */
    public function findByDoi(
        string $doi
    ): ?array {

        return $this->where(
            'doi',
            $doi
        )->first();
    }

    /**
     * Get Published Articles.
     */
    public function getPublishedArticles(): array
    {
        return $this->where(
            'status',
            'published'
        )
        ->orderBy(
            'published_date',
            'DESC'
        )
        ->findAll();
    }

    /**
     * Get Volume Articles.
     */
    public function getByVolume(
        string $volumeNumber
    ): array {

        return $this->where(
            'volume_number',
            $volumeNumber
        )
        ->where(
            'status',
            'published'
        )
        ->findAll();
    }

    /**
     * Get Issue Articles.
     */
    public function getByIssue(
        string $volumeNumber,
        string $issueNumber
    ): array {

        return $this->where(
            'volume_number',
            $volumeNumber
        )
        ->where(
            'issue_number',
            $issueNumber
        )
        ->where(
            'status',
            'published'
        )
        ->findAll();
    }

    /**
     * Publication Statistics.
     */
    public function getStatistics(): array
    {
        return [

            'total' => $this
                ->countAllResults(),

            'published' => $this
                ->where(
                    'status',
                    'published'
                )
                ->countAllResults(),

            'draft' => $this
                ->where(
                    'status',
                    'draft'
                )
                ->countAllResults(),

            'archived' => $this
                ->where(
                    'status',
                    'archived'
                )
                ->countAllResults(),
        ];
    }
}