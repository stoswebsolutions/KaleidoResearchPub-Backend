<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\SubscriptionPlanModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class SubscriptionPlanController extends BaseApiController
{
    protected SubscriptionPlanModel $subscriptionPlanModel;

    protected array $allowedSortFields = [
        'plan_name',
        'amount',
        'duration_days',
        'sort_order',
        'is_featured',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->subscriptionPlanModel = new SubscriptionPlanModel();
    }

        /**
     * GET /subscription-plans
     */
    public function index(): ResponseInterface
    {
        try {

            $page = max(
                1,
                (int) (
                    $this->request->getGet('page')
                    ?? 1
                )
            );

            $perPage = min(
                100,
                max(
                    1,
                    (int) (
                        $this->request->getGet('per_page')
                        ?? 20
                    )
                )
            );

            $search = trim(
                (string) (
                    $this->request->getGet('search')
                    ?? ''
                )
            );

            $status = trim(
                (string) (
                    $this->request->getGet('status')
                    ?? ''
                )
            );

            $featured = $this->request->getGet(
                'is_featured'
            );

            $sortBy = (string) (
                $this->request->getGet('sort_by')
                ?? 'sort_order'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet(
                        'sort_direction'
                    )
                    ?? 'asc'
                )
            );

            if (
                ! in_array(
                    $sortBy,
                    $this->allowedSortFields,
                    true
                )
            ) {
                $sortBy = 'sort_order';
            }

            if (
                ! in_array(
                    $sortDirection,
                    ['asc', 'desc'],
                    true
                )
            ) {
                $sortDirection = 'asc';
            }

            $builder = $this->subscriptionPlanModel
                ->select([
                    'uuid',
                    'plan_name',
                    'slug',
                    'amount',
                    'currency',
                    'duration_days',
                    'download_limit',
                    'paper_submission_limit',
                    'is_featured',
                    'sort_order',
                    'status',
                    'created_at',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'plan_name',
                        $search
                    )
                    ->orLike(
                        'slug',
                        $search
                    )
                    ->orLike(
                        'description',
                        $search
                    )
                    ->groupEnd();
            }

            if ($status !== '') {

                $builder->where(
                    'status',
                    $status
                );
            }

            if (
                $featured !== null
                && $featured !== ''
            ) {
                $builder->where(
                    'is_featured',
                    (int) $featured
                );
            }

            $records = $builder
                ->orderBy(
                    'sort_order',
                    'ASC'
                )
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Subscription plans fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->subscriptionPlanModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->subscriptionPlanModel
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
                'Unable to fetch subscription plans.'
            );
        }
    }

        /**
     * GET /subscription-plans/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $subscriptionPlan = $this->subscriptionPlanModel
                ->findByUuid(
                    (string) $id
                );

            if (! $subscriptionPlan) {
                return $this->notFoundResponse(
                    'Subscription plan not found.'
                );
            }

            if (
                ! empty(
                    $subscriptionPlan['features']
                )
            ) {
                $subscriptionPlan['features'] =
                    $this->subscriptionPlanModel
                        ->getFeatures(
                            $subscriptionPlan['features']
                        );
            } else {
                $subscriptionPlan['features'] = [];
            }

            return $this->successResponse(
                'Subscription plan fetched successfully.',
                $subscriptionPlan
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch subscription plan.'
            );
        }
    }

        /**
     * POST /subscription-plans
     */
    public function create(): ResponseInterface
    {
        try {

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $features = $payload['features'] ?? [];

            if (is_array($features)) {
                $features = json_encode(
                    $features,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );
            }

            $data = [
                'plan_name' => trim(
                    (string) (
                        $payload['plan_name']
                        ?? ''
                    )
                ),

                'amount' => (float) (
                        $payload['amount']
                        ?? 0
                ),

                'currency' => strtoupper(
                    trim(
                        (string) (
                            $payload['currency']
                            ?? 'INR'
                        )
                    )
                ),

                'duration_days' => (int) (
                        $payload['duration_days']
                        ?? 0
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
                ),

                'features' => $features,

                'download_limit' => (int) (
                        $payload['download_limit']
                        ?? 0
                ),

                'paper_submission_limit' => (int) (
                        $payload['paper_submission_limit']
                        ?? 0
                ),

                'is_featured' => (int) (
                        $payload['is_featured']
                        ?? 0
                ),

                'sort_order' => (int) (
                        $payload['sort_order']
                        ?? 0
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? 'active'
                    )
                ),

                'created_by' => $user['id'],
            ];

            if (
                ! $this->subscriptionPlanModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->subscriptionPlanModel->errors()
                );
            }

            $subscriptionPlan = $this->subscriptionPlanModel
                ->find(
                    $this->subscriptionPlanModel
                        ->getInsertID()
                );

            if (
                ! empty(
                    $subscriptionPlan['features']
                )
            ) {
                $subscriptionPlan['features'] =
                    $this->subscriptionPlanModel
                        ->getFeatures(
                            $subscriptionPlan['features']
                        );
            }

            return $this->successResponse(
                'Subscription plan created successfully.',
                $subscriptionPlan,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create subscription plan.'
            );
        }
    }

        /**
     * PUT /subscription-plans/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $subscriptionPlan = $this->subscriptionPlanModel
                ->findByUuid(
                    (string) $id
                );

            if (! $subscriptionPlan) {
                return $this->notFoundResponse(
                    'Subscription plan not found.'
                );
            }

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $features = $payload['features']
                ?? $subscriptionPlan['features'];

            if (is_array($features)) {
                $features = json_encode(
                    $features,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );
            }

            $data = [
                'plan_name' => trim(
                    (string) (
                        $payload['plan_name']
                        ?? $subscriptionPlan['plan_name']
                    )
                ),

                'amount' => (float) (
                        $payload['amount']
                        ?? $subscriptionPlan['amount']
                ),

                'currency' => strtoupper(
                    trim(
                        (string) (
                            $payload['currency']
                            ?? $subscriptionPlan['currency']
                        )
                    )
                ),

                'duration_days' => (int) (
                        $payload['duration_days']
                        ?? $subscriptionPlan['duration_days']
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? (
                            $subscriptionPlan['description']
                            ?? ''
                        )
                    )
                ),

                'features' => $features,

                'download_limit' => (int) (
                        $payload['download_limit']
                        ?? $subscriptionPlan['download_limit']
                ),

                'paper_submission_limit' => (int) (
                        $payload['paper_submission_limit']
                        ?? $subscriptionPlan['paper_submission_limit']
                ),

                'is_featured' => (int) (
                        $payload['is_featured']
                        ?? $subscriptionPlan['is_featured']
                ),

                'sort_order' => (int) (
                        $payload['sort_order']
                        ?? $subscriptionPlan['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $subscriptionPlan['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->subscriptionPlanModel->update(
                    $subscriptionPlan['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->subscriptionPlanModel->errors()
                );
            }

            $updatedPlan = $this->subscriptionPlanModel
                ->find(
                    $subscriptionPlan['id']
                );

            if (
                ! empty(
                    $updatedPlan['features']
                )
            ) {
                $updatedPlan['features'] =
                    $this->subscriptionPlanModel
                        ->getFeatures(
                            $updatedPlan['features']
                        );
            }

            return $this->successResponse(
                'Subscription plan updated successfully.',
                $updatedPlan
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update subscription plan.'
            );
        }
    }

    /**
     * DELETE /subscription-plans/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $subscriptionPlan = $this->subscriptionPlanModel
                ->findByUuid(
                    (string) $id
                );

            if (! $subscriptionPlan) {
                return $this->notFoundResponse(
                    'Subscription plan not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->subscriptionPlanModel->update(
                $subscriptionPlan['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->subscriptionPlanModel->delete(
                $subscriptionPlan['id']
            );

            return $this->successResponse(
                'Subscription plan deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete subscription plan.'
            );
        }
    }

}