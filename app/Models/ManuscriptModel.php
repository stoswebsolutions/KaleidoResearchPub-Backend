<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptModel extends Model
{
    protected $table = 'manuscripts';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

    'uuid',

    'manuscript_id',

    'profile_id',

    'journal_id',
    'article_type_id',
    'disciplinary_id',

    'corresponding_author_name',
    'corresponding_author_email',
    'corresponding_author_phone',

    'title',
    'abstract',

    'university_name',

    'country',
    'state',
    'city',
    'pincode',
    'landmark',

    'paper_file',
    'abstract_file',

    'submission_source',

    'current_status',

    'final_decision',

    'decision_remarks',

    'rejection_reason',

    'decision_by',

    'decision_at',

    'revision_round',

    'submitted_at',

    'doi',

    'created_by',
    'updated_by',
    'deleted_by',
];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'profile_id' => '?integer',

        'journal_id' => 'integer',

        'article_type_id' => 'integer',

        'disciplinary_id' => 'integer',

        'decision_by' => '?integer',

        'revision_round' => 'integer',

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

        'journal_id' => [
            'label' => 'Journal',
            'rules' => 'required|integer',
        ],

        'article_type_id' => [
            'label' => 'Article Type',
            'rules' => 'required|integer',
        ],

        'disciplinary_id' => [
            'label' => 'Disciplinary',
            'rules' => 'required|integer',
        ],

        'corresponding_author_name' => [
            'label' => 'Corresponding Author Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'corresponding_author_email' => [
            'label' => 'Corresponding Author Email',
            'rules' => 'required|valid_email|max_length[255]',
        ],

        'corresponding_author_phone' => [
            'label' => 'Corresponding Author Phone',
            'rules' => 'permit_empty|max_length[20]',
        ],

        'title' => [
            'label' => 'Title',
            'rules' => 'required|min_length[10]',
        ],

        'abstract' => [
            'label' => 'Abstract',
            'rules' => 'required|min_length[50]',
        ],

        'university_name' => [
            'label' => 'University Name',
            'rules' => 'required|max_length[255]',
        ],

        'country' => [
            'label' => 'Country',
            'rules' => 'required|max_length[100]',
        ],

        'state' => [
            'label' => 'State',
            'rules' => 'permit_empty|max_length[100]',
        ],

        'city' => [
            'label' => 'City',
            'rules' => 'permit_empty|max_length[100]',
        ],

        'pincode' => [
            'label' => 'Pincode',
            'rules' => 'permit_empty|max_length[20]',
        ],

        'paper_file' => [
            'label' => 'Paper File',
            'rules' => 'required|max_length[255]',
        ],

        'abstract_file' => [
            'label' => 'Abstract File',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'submission_source' => [
            'label' => 'Submission Source',
            'rules' => 'required|in_list[guest,registered,admin]',
        ],

        'current_status' => [
            'label' => 'Current Status',
            'rules' => 'required',
        ],

        'final_decision' => [
            'label' => 'Final Decision',
            'rules' => 'required|in_list[pending,accepted,rejected,minor_revision,major_revision]',
        ],

        'decision_remarks' => [
            'label' => 'Decision Remarks',
            'rules' => 'permit_empty',
        ],

        'decision_by' => [
            'label' => 'Decision By',
            'rules' => 'permit_empty|integer',
        ],

        'decision_at' => [
            'label' => 'Decision Date',
            'rules' => 'permit_empty',
        ],

        'rejection_reason' => [
            'label' => 'Rejection Reason',
            'rules' => 'permit_empty',
        ],

        'revision_round' => [
            'label' => 'Revision Round',
            'rules' => 'permit_empty|integer',
        ],

        'submitted_at' => [
            'label' => 'Submitted At',
            'rules' => 'permit_empty',
        ],

        'doi' => [
            'label' => 'DOI',
            'rules' => 'permit_empty|max_length[255]',
        ],
    ];

    protected $validationMessages = [

        'journal_id' => [
            'required' =>
                'Journal is required.',
        ],

        'article_type_id' => [
            'required' =>
                'Article type is required.',
        ],

        'disciplinary_id' => [
            'required' =>
                'Disciplinary is required.',
        ],

        'corresponding_author_name' => [
            'required' =>
                'Corresponding author name is required.',
        ],

        'corresponding_author_email' => [
            'required' =>
                'Corresponding author email is required.',
            'valid_email' =>
                'Please provide a valid email address.',
        ],

        'title' => [
            'required' =>
                'Title is required.',
        ],

        'abstract' => [
            'required' =>
                'Abstract is required.',
        ],

        'paper_file' => [
            'required' =>
                'Paper file is required.',
        ],

        'revision_round' => [
            'integer' =>
                'Revision round must be a valid number.',
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

            $data['data']['uuid'] =
                generate_uuid();
        }

        return $data;
    }

    /**
     * Active Manuscripts.
     */
    public function active(): self
    {
        return $this->where(
            'deleted_at',
            null
        );
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
     * Find By Manuscript ID.
     */
    public function findByManuscriptId(
        string $manuscriptId
    ): ?array {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )->first();
    }
}