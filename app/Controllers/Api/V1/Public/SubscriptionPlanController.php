<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

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
        'created_at',
    ];

    public function __construct()
    {
        $this->subscriptionPlanModel = new SubscriptionPlanModel();
    }

    /**
     * GET /public/subscription-plans
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
                ->active()
                ->select([
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

            foreach ($records as &$record) {

                $record['features'] =
                    $this->subscriptionPlanModel
                        ->getFeatures(
                            $record['features']
                            ?? null
                        );
            }

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
     * GET /public/subscription-plans/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $subscriptionPlan = $this->subscriptionPlanModel
                ->active()
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $subscriptionPlan) {
                return $this->notFoundResponse(
                    'Subscription plan not found.'
                );
            }

            $subscriptionPlan['features'] =
                $this->subscriptionPlanModel
                    ->getFeatures(
                        $subscriptionPlan['features']
                        ?? null
                    );

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
}