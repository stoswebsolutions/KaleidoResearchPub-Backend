<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AuthorSubscriptionModel extends Model
{
    protected $table = 'author_subscriptions';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'author_profile_id',

        'subscription_plan_id',

        'payment_reference_no',
        'payment_date',
        'payment_screenshot',

        'start_date',
        'end_date',

        'amount',

        'download_limit',
        'download_used',

        'submission_limit',
        'submission_used',

        'status',

        'approved_by',
        'approved_at',

        'remarks',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'author_profile_id' => 'integer',

        'subscription_plan_id' => 'integer',

        'amount' => 'float',

        'download_limit' => 'integer',
        'download_used' => 'integer',

        'submission_limit' => 'integer',
        'submission_used' => 'integer',

        'approved_by' => '?integer',

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

        'author_profile_id' => [
            'label' => 'Author Profile',
            'rules' => 'required|integer',
        ],

        'subscription_plan_id' => [
            'label' => 'Subscription Plan',
            'rules' => 'required|integer',
        ],

        'payment_reference_no' => [
            'label' => 'Payment Reference Number',
            'rules' => 'required|max_length[100]',
        ],

        'payment_date' => [
            'label' => 'Payment Date',
            'rules' => 'required|valid_date',
        ],

        'payment_screenshot' => [
            'label' => 'Payment Screenshot',
            'rules' => 'required|max_length[255]',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[pending,active,expired,cancelled,rejected]',
        ],
    ];

    protected $validationMessages = [

        'author_profile_id' => [
            'required' =>
                'Author profile is required.',
        ],

        'subscription_plan_id' => [
            'required' =>
                'Subscription plan is required.',
        ],

        'payment_reference_no' => [
            'required' =>
                'Payment reference number is required.',
        ],

        'payment_date' => [
            'required' =>
                'Payment date is required.',
        ],

        'payment_screenshot' => [
            'required' =>
                'Payment screenshot is required.',
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
     * Get Active Subscription.
     */
    public function getActiveSubscription(
        int $authorProfileId
    ): ?array {

        return $this->where(
            'author_profile_id',
            $authorProfileId
        )
            ->where(
                'status',
                'active'
            )
            ->where(
                'end_date >=',
                date('Y-m-d')
            )
            ->orderBy(
                'id',
                'DESC'
            )
            ->first();
    }

    /**
     * Check Active Subscription.
     */
    public function hasActiveSubscription(
        int $authorProfileId
    ): bool {

        return $this->where(
            'author_profile_id',
            $authorProfileId
        )
            ->where(
                'status',
                'active'
            )
            ->where(
                'end_date >=',
                date('Y-m-d')
            )
            ->countAllResults() > 0;
    }

    /**
     * Increment Download Usage.
     */
    public function incrementDownloadUsage(
        int $subscriptionId
    ): bool {

        return $this->set(
            'download_used',
            'download_used + 1',
            false
        )
            ->where(
                'id',
                $subscriptionId
            )
            ->update();
    }

    /**
     * Increment Submission Usage.
     */
    public function incrementSubmissionUsage(
        int $subscriptionId
    ): bool {

        return $this->set(
            'submission_used',
            'submission_used + 1',
            false
        )
            ->where(
                'id',
                $subscriptionId
            )
            ->update();
    }

    /**
     * Check Download Limit.
     */
    public function canDownload(
        int $subscriptionId
    ): bool {

        $subscription =
            $this->find(
                $subscriptionId
            );

        if (! $subscription) {
            return false;
        }

        return
            (int) $subscription['download_used']
            <
            (int) $subscription['download_limit'];
    }

    /**
     * Check Submission Limit.
     */
    public function canSubmitPaper(
        int $subscriptionId
    ): bool {

        $subscription =
            $this->find(
                $subscriptionId
            );

        if (! $subscription) {
            return false;
        }

        return
            (int) $subscription['submission_used']
            <
            (int) $subscription['submission_limit'];
    }
}