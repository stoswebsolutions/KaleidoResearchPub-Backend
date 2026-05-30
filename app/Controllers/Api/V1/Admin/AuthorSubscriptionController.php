<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\AuthorProfileModel;
use App\Models\AuthorSubscriptionModel;
use App\Models\SubscriptionPlanModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AuthorSubscriptionController extends BaseApiController
{
    protected AuthorSubscriptionModel $authorSubscriptionModel;

    protected SubscriptionPlanModel $subscriptionPlanModel;

    protected AuthorProfileModel $authorProfileModel;

    protected array $allowedSortFields = [

        'id',

        'payment_date',

        'start_date',

        'end_date',

        'amount',

        'status',

        'created_at',
    ];

    public function __construct()
    {
        $this->authorSubscriptionModel =
            new AuthorSubscriptionModel();

        $this->subscriptionPlanModel =
            new SubscriptionPlanModel();

        $this->authorProfileModel =
            new AuthorProfileModel();
    }

    /**
     * GET /author-subscriptions
     */
    public function index(): ResponseInterface
    {
        try {

            $page = max(
                1,
                (int) (
                    $this->request->getGet(
                        'page'
                    ) ?? 1
                )
            );

            $perPage = min(
                100,
                max(
                    1,
                    (int) (
                        $this->request->getGet(
                            'per_page'
                        ) ?? 20
                    )
                )
            );

            $search = trim(
                (string) (
                    $this->request->getGet(
                        'search'
                    ) ?? ''
                )
            );

            $status = trim(
                (string) (
                    $this->request->getGet(
                        'status'
                    ) ?? ''
                )
            );

            $sortBy = (string) (
                $this->request->getGet(
                    'sort_by'
                ) ?? 'created_at'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet(
                        'sort_direction'
                    ) ?? 'desc'
                )
            );

            if (
                ! in_array(
                    $sortBy,
                    $this->allowedSortFields,
                    true
                )
            ) {
                $sortBy = 'created_at';
            }

            if (
                ! in_array(
                    $sortDirection,
                    ['asc', 'desc'],
                    true
                )
            ) {
                $sortDirection = 'desc';
            }

            $builder =
                $this->authorSubscriptionModel
                    ->select([

                        'author_subscriptions.uuid',

                        'author_subscriptions.payment_reference_no',

                        'author_subscriptions.payment_date',

                        'author_subscriptions.start_date',

                        'author_subscriptions.end_date',

                        'author_subscriptions.amount',

                        'author_subscriptions.status',

                        'author_subscriptions.created_at',

                        'subscription_plans.plan_name',

                        'author_profiles.full_name',
                    ])
                    ->join(
                        'subscription_plans',
                        'subscription_plans.id = author_subscriptions.subscription_plan_id',
                        'left'
                    )
                    ->join(
                        'author_profiles',
                        'author_profiles.id = author_subscriptions.author_profile_id',
                        'left'
                    );

            if ($search !== '') {

                $builder
                    ->groupStart()
                    ->like(
                        'author_profiles.full_name',
                        $search
                    )
                    ->orLike(
                        'author_subscriptions.payment_reference_no',
                        $search
                    )
                    ->groupEnd();
            }

            if ($status !== '') {

                $builder->where(
                    'author_subscriptions.status',
                    $status
                );
            }

            $authUser = service(
                'authUser'
            );

            /**
             * Author
             */
            if (
                (int) $authUser->roleId === 6
            ) {

                $authorProfile =
                    $this->authorProfileModel
                        ->where(
                            'profile_id',
                            $authUser->profileId
                        )
                        ->first();

                if ($authorProfile) {

                    $builder->where(
                        'author_subscriptions.author_profile_id',
                        $authorProfile['id']
                    );

                } else {

                    $builder->where(
                        '1 = 0',
                        null,
                        false
                    );
                }
            }

            $items = $builder
                ->orderBy(
                    'author_subscriptions.' . $sortBy,
                    $sortDirection
                )
                ->paginate(
                    $perPage
                );

            return $this->successResponse(
                'Author subscriptions fetched successfully.',
                [

                    'items' =>
                        $items,

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $perPage,

                        'total' =>
                            $this->authorSubscriptionModel
                                ->pager
                                ->getTotal(),

                        'last_page' =>
                            $this->authorSubscriptionModel
                                ->pager
                                ->getPageCount(),
                    ],
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch subscriptions.'
            );
        }
    }
    /**
     * GET /author-subscriptions/{uuid}
     */
    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $subscription =
                $this->authorSubscriptionModel
                    ->select([

                        'author_subscriptions.*',

                        'subscription_plans.plan_name',
                        'subscription_plans.slug',
                        'subscription_plans.duration_days',
                        'subscription_plans.features',

                        'author_profiles.full_name',
                    ])
                    ->join(
                        'subscription_plans',
                        'subscription_plans.id = author_subscriptions.subscription_plan_id',
                        'left'
                    )
                    ->join(
                        'author_profiles',
                        'author_profiles.id = author_subscriptions.author_profile_id',
                        'left'
                    )
                    ->where(
                        'author_subscriptions.uuid',
                        (string) $id
                    )
                    ->first();

            if (! $subscription) {

                return $this->notFoundResponse(
                    'Subscription not found.'
                );
            }

            $authUser = service(
                'authUser'
            );

            /**
             * Author can view only own subscription.
             */
            if (
                (int) $authUser->roleId === 6
            ) {

                $authorProfile =
                    $this->authorProfileModel
                        ->where(
                            'profile_id',
                            $authUser->profileId
                        )
                        ->first();

                if (
                    ! $authorProfile
                    || (int) $subscription['author_profile_id']
                    !== (int) $authorProfile['id']
                ) {

                    return $this->forbiddenResponse(
                        'Access denied.'
                    );
                }
            }

            return $this->successResponse(
                'Subscription fetched successfully.',
                $subscription
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch subscription.'
            );
        }
    }
    /**
     * POST /author-subscriptions
     *
     * Author Purchase Subscription
     */
    public function create(): ResponseInterface
    {
        try {

            $authUser = service(
                'authUser'
            );

            /**
             * Author only.
             */
            if (
                (int) $authUser->roleId !== 6
            ) {

                return $this->forbiddenResponse(
                    'Only authors can purchase subscriptions.'
                );
            }

            $authorProfile =
                $this->authorProfileModel
                    ->where(
                        'profile_id',
                        $authUser->profileId
                    )
                    ->first();

            if (! $authorProfile) {

                return $this->notFoundResponse(
                    'Author profile not found.'
                );
            }

            $subscriptionPlanId = (int)
                $this->request->getPost(
                    'subscription_plan_id'
                );

            $plan =
                $this->subscriptionPlanModel
                    ->find(
                        $subscriptionPlanId
                    );

            if (! $plan) {

                return $this->validationResponse([
                    'subscription_plan_id' =>
                        'Invalid subscription plan.',
                ]);
            }

            /**
             * Active subscription check.
             */
            if (
                $this->authorSubscriptionModel
                    ->hasActiveSubscription(
                        (int) $authorProfile['id']
                    )
            ) {

                return $this->errorResponse(
                    'You already have an active subscription.'
                );
            }

            $paymentScreenshot =
                $this->request->getFile(
                    'payment_screenshot'
                );

            if (
                ! $paymentScreenshot
                || ! $paymentScreenshot->isValid()
            ) {

                return $this->validationResponse([
                    'payment_screenshot' =>
                        'Payment screenshot is required.',
                ]);
            }

            $directory =
                FCPATH .
                'uploads/subscriptions/payments/';

            if (
                ! is_dir(
                    $directory
                )
            ) {

                mkdir(
                    $directory,
                    0755,
                    true
                );
            }

            $fileName =
                $paymentScreenshot
                    ->getRandomName();

            $paymentScreenshot->move(
                $directory,
                $fileName
            );

            $insertData = [

                'author_profile_id' =>
                    $authorProfile['id'],

                'subscription_plan_id' =>
                    $plan['id'],

                'payment_reference_no' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'payment_reference_no'
                        )
                    ),

                'payment_date' =>
                    $this->request->getPost(
                        'payment_date'
                    ),

                'payment_screenshot' =>
                    'uploads/subscriptions/payments/'
                    . $fileName,

                /**
                 * Snapshot values.
                 */
                'amount' =>
                    $plan['amount'],

                'download_limit' =>
                    $plan['download_limit'],

                'submission_limit' =>
                    $plan['paper_submission_limit'],

                'download_used' =>
                    0,

                'submission_used' =>
                    0,

                'status' =>
                    'pending',

                'created_by' =>
                    $authUser->profileId,
            ];

            if (
                ! $this->authorSubscriptionModel
                    ->insert(
                        $insertData
                    )
            ) {

                return $this->validationResponse(
                    $this->authorSubscriptionModel
                        ->errors()
                );
            }

            return $this->successResponse(
                'Subscription request submitted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create subscription.'
            );
        }
    }
    /**
     * POST /author-subscriptions/{uuid}/approve
     */
    public function approve(
        $id = null
    ): ResponseInterface
    {
        try {

            $subscription =
                $this->authorSubscriptionModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $subscription) {

                return $this->notFoundResponse(
                    'Subscription not found.'
                );
            }

            if (
                $subscription['status']
                !== 'pending'
            ) {

                return $this->errorResponse(
                    'Only pending subscriptions can be approved.'
                );
            }

            $plan =
                $this->subscriptionPlanModel
                    ->find(
                        (int) $subscription['subscription_plan_id']
                    );

            if (! $plan) {

                return $this->notFoundResponse(
                    'Subscription plan not found.'
                );
            }

            $authUser = service(
                'authUser'
            );

            $startDate =
                $subscription['payment_date']
                ?: date('Y-m-d');

            $endDate =
                date(
                    'Y-m-d',
                    strtotime(
                        '+' .
                        (int) $plan['duration_days']
                        . ' days'
                    )
                );

            $updateData = [

                'start_date' =>
                    $startDate,

                'end_date' =>
                    $endDate,

                'amount' =>
                    $plan['amount'],

                'download_limit' =>
                    (int)
                    $plan['download_limit'],

                'submission_limit' =>
                    (int)
                    $plan['paper_submission_limit'],

                'approved_by' =>
                    $authUser->profileId,

                'approved_at' =>
                    date(
                        'Y-m-d H:i:s'
                    ),

                'status' =>
                    'active',

                'updated_by' =>
                    $authUser->profileId,
            ];

            $this->authorSubscriptionModel
                ->update(
                    $subscription['id'],
                    $updateData
                );

            return $this->successResponse(
                'Subscription approved successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to approve subscription.'
            );
        }
    }
    /**
     * POST /author-subscriptions/{uuid}/reject
     */
    public function reject(
        $id = null
    ): ResponseInterface
    {
        try {

            $subscription =
                $this->authorSubscriptionModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $subscription) {

                return $this->notFoundResponse(
                    'Subscription not found.'
                );
            }

            if (
                $subscription['status']
                !== 'pending'
            ) {

                return $this->errorResponse(
                    'Only pending subscriptions can be rejected.'
                );
            }

            $remarks = trim(
                (string)
                $this->request->getPost(
                    'remarks'
                )
            );

            if (
                empty(
                    $remarks
                )
            ) {

                return $this->validationResponse([
                    'remarks' =>
                        'Remarks are required.',
                ]);
            }

            $authUser = service(
                'authUser'
            );

            $this->authorSubscriptionModel
                ->update(
                    $subscription['id'],
                    [

                        'status' =>
                            'rejected',

                        'remarks' =>
                            $remarks,

                        'approved_by' =>
                            $authUser->profileId,

                        'approved_at' =>
                            date(
                                'Y-m-d H:i:s'
                            ),

                        'updated_by' =>
                            $authUser->profileId,
                    ]
                );

            return $this->successResponse(
                'Subscription rejected successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to reject subscription.'
            );
        }
    }
    /**
     * GET /my-subscriptions
     */
    public function mySubscriptions(): ResponseInterface
    {
        try {

            $authUser = service(
                'authUser'
            );

            if (
                (int) $authUser->roleId !== 6
            ) {

                return $this->forbiddenResponse(
                    'Only authors can access subscriptions.'
                );
            }

            $authorProfile =
                $this->authorProfileModel
                    ->where(
                        'profile_id',
                        $authUser->profileId
                    )
                    ->first();

            if (! $authorProfile) {

                return $this->notFoundResponse(
                    'Author profile not found.'
                );
            }

            $subscriptions =
                $this->authorSubscriptionModel
                    ->select([

                        'author_subscriptions.*',

                        'subscription_plans.plan_name',

                        'subscription_plans.duration_days',
                    ])
                    ->join(
                        'subscription_plans',
                        'subscription_plans.id = author_subscriptions.subscription_plan_id',
                        'left'
                    )
                    ->where(
                        'author_profile_id',
                        $authorProfile['id']
                    )
                    ->orderBy(
                        'id',
                        'DESC'
                    )
                    ->findAll();

            return $this->successResponse(
                'Subscriptions fetched successfully.',
                $subscriptions
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch subscriptions.'
            );
        }
    }
    /**
     * GET /my-active-subscription
     */
    public function myActiveSubscription(): ResponseInterface
    {
        try {

            $authUser = service(
                'authUser'
            );

            if (
                (int) $authUser->roleId !== 6
            ) {

                return $this->forbiddenResponse(
                    'Only authors can access subscriptions.'
                );
            }

            $authorProfile =
                $this->authorProfileModel
                    ->where(
                        'profile_id',
                        $authUser->profileId
                    )
                    ->first();

            if (! $authorProfile) {

                return $this->notFoundResponse(
                    'Author profile not found.'
                );
            }

            $subscription =
                $this->authorSubscriptionModel
                    ->select([

                        'author_subscriptions.*',

                        'subscription_plans.plan_name',

                        'subscription_plans.duration_days',

                        'subscription_plans.features',
                    ])
                    ->join(
                        'subscription_plans',
                        'subscription_plans.id = author_subscriptions.subscription_plan_id',
                        'left'
                    )
                    ->where(
                        'author_subscriptions.author_profile_id',
                        $authorProfile['id']
                    )
                    ->where(
                        'author_subscriptions.status',
                        'active'
                    )
                    ->where(
                        'author_subscriptions.end_date >=',
                        date('Y-m-d')
                    )
                    ->orderBy(
                        'author_subscriptions.id',
                        'DESC'
                    )
                    ->first();

            if (! $subscription) {

                return $this->notFoundResponse(
                    'No active subscription found.'
                );
            }

            return $this->successResponse(
                'Active subscription fetched successfully.',
                $subscription
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch active subscription.'
            );
        }
    }
}