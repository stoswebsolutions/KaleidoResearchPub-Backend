<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptReviewModel extends Model
{
    protected $table = 'manuscript_reviews';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'editor_profile_id',

        'review_recommendation',

        'comments',

        'review_file',

        'reviewed_at',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'editor_profile_id' => 'integer',
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

        'editor_profile_id' => [
            'label' => 'Editor Profile',
            'rules' => 'required|integer',
        ],

        'review_recommendation' => [
            'label' => 'Review Recommendation',
            'rules' => 'required|in_list[accepted,rejected,minor_revision,major_revision]',
        ],

        'comments' => [
            'label' => 'Comments',
            'rules' => 'required|min_length[10]',
        ],

        'review_file' => [
            'label' => 'Review File',
            'rules' => 'permit_empty|max_length[255]',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'editor_profile_id' => [
            'required' =>
                'Editor profile is required.',
        ],

        'review_recommendation' => [
            'required' =>
                'Review recommendation is required.',

            'in_list' =>
                'Invalid review recommendation.',
        ],

        'comments' => [
            'required' =>
                'Review comments are required.',

            'min_length' =>
                'Review comments must contain at least 10 characters.',
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
     * Get Review By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): ?array {

        return $this->select([
                'manuscript_reviews.*',
                'editor_profiles.full_name',
                'editor_profiles.designation',
            ])
            ->join(
                'editor_profiles',
                'editor_profiles.id = manuscript_reviews.editor_profile_id',
                'left'
            )
            ->where(
                'manuscript_reviews.manuscript_id',
                $manuscriptId
            )
            ->first();
    }

    /**
     * Get Reviews By Editor.
     */
    public function getByEditor(
        int $editorProfileId
    ): array {

        return $this->where(
            'editor_profile_id',
            $editorProfileId
        )
        ->orderBy(
            'reviewed_at',
            'DESC'
        )
        ->findAll();
    }

    /**
     * Check Review Exists.
     */
    public function reviewExists(
        int $manuscriptId,
        int $editorProfileId
    ): bool {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'editor_profile_id',
                $editorProfileId
            )
            ->countAllResults() > 0;
    }

    /**
     * Get Recommendation Counts.
     */
    public function getRecommendationStats(): array
    {
        return [

            'accepted' => $this
                ->where(
                    'review_recommendation',
                    'accepted'
                )
                ->countAllResults(),

            'rejected' => $this
                ->where(
                    'review_recommendation',
                    'rejected'
                )
                ->countAllResults(),

            'minor_revision' => $this
                ->where(
                    'review_recommendation',
                    'minor_revision'
                )
                ->countAllResults(),

            'major_revision' => $this
                ->where(
                    'review_recommendation',
                    'major_revision'
                )
                ->countAllResults(),
        ];
    }
}