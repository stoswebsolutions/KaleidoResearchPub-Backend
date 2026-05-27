<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionPlanModel extends Model
{
    protected $table            = 'subscription_plans';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',
        'plan_name',
        'slug',
        'amount',
        'currency',
        'duration_days',
        'description',
        'features',
        'download_limit',
        'paper_submission_limit',
        'is_featured',
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
        'plan_name' => [
            'label' => 'Plan Name',
            'rules' => 'required|min_length[2]|max_length[150]',
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => 'permit_empty|max_length[180]|is_unique[subscription_plans.slug,id,{id}]',
        ],

        'amount' => [
            'label' => 'Amount',
            'rules' => 'required|decimal|greater_than_equal_to[0]',
        ],

        'currency' => [
            'label' => 'Currency',
            'rules' => 'required|max_length[10]',
        ],

        'duration_days' => [
            'label' => 'Duration',
            'rules' => 'required|integer|greater_than[0]',
        ],

        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty',
        ],

        'features' => [
            'label' => 'Features',
            'rules' => 'permit_empty',
        ],

        'download_limit' => [
            'label' => 'Download Limit',
            'rules' => 'required|integer',
        ],

        'paper_submission_limit' => [
            'label' => 'Paper Submission Limit',
            'rules' => 'required|integer',
        ],

        'is_featured' => [
            'label' => 'Featured Plan',
            'rules' => 'required|in_list[0,1]',
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
        'plan_name' => [
            'required'   => 'Plan name is required.',
            'min_length' => 'Plan name must contain at least 2 characters.',
            'max_length' => 'Plan name cannot exceed 150 characters.',
        ],

        'slug' => [
            'is_unique' => 'Plan slug already exists.',
        ],

        'amount' => [
            'required'              => 'Amount is required.',
            'decimal'               => 'Amount must be a valid decimal value.',
            'greater_than_equal_to' => 'Amount cannot be negative.',
        ],

        'currency' => [
            'required' => 'Currency is required.',
        ],

        'duration_days' => [
            'required'     => 'Duration is required.',
            'integer'      => 'Duration must be a valid number.',
            'greater_than' => 'Duration must be greater than zero.',
        ],

        'download_limit' => [
            'required' => 'Download limit is required.',
            'integer'  => 'Download limit must be a valid number.',
        ],

        'paper_submission_limit' => [
            'required' => 'Paper submission limit is required.',
            'integer'  => 'Paper submission limit must be a valid number.',
        ],

        'is_featured' => [
            'in_list' => 'Invalid featured status.',
        ],

        'sort_order' => [
            'integer' => 'Sort order must be a valid number.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid plan status.',
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
            ! empty($data['data']['plan_name'])
            && empty($data['data']['slug'])
        ) {
            $data['data']['slug'] = generate_slug(
                $data['data']['plan_name']
            );
        }

        return $data;
    }

    /**
     * Active Plans
     */
    public function active(): self
    {
        return $this->where(
            $this->table . '.status',
            'active'
        );
    }

    /**
     * Featured Plans
     */
    public function featured(): self
    {
        return $this->where(
            $this->table . '.is_featured',
            1
        );
    }

    /**
     * Ordered Plans
     */
    public function ordered(): self
    {
        return $this->orderBy(
            $this->table . '.sort_order',
            'ASC'
        )->orderBy(
            $this->table . '.plan_name',
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
     * Get Features Array
     */
    public function getFeatures(
        string|null $features
    ): array {
        if (empty($features)) {
            return [];
        }

        $decoded = json_decode(
            $features,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }
}