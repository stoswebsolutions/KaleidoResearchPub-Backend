<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\CmsFeatureModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class CmsFeatureController extends BaseApiController
{
    protected CmsFeatureModel $cmsFeatureModel;

    protected array $allowedSortFields = [
        'type',
        'title',
        'sort_order',
        'created_at',
    ];

    public function __construct()
    {
        $this->cmsFeatureModel = new CmsFeatureModel();
    }

    /**
     * GET /public/cms-features
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

            $type = trim(
                (string) (
                    $this->request->getGet('type')
                    ?? ''
                )
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

            $builder = $this->cmsFeatureModel
                ->active()
                ->select([
                    'id',
                    'uuid',
                    'type',
                    'title',
                    'slug',
                    'short_description',
                    'description',
                    'icon',
                    'image',
                    'sort_order',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'title',
                        $search
                    )
                    ->orLike(
                        'slug',
                        $search
                    )
                    ->orLike(
                        'short_description',
                        $search
                    )
                    ->orLike(
                        'description',
                        $search
                    )
                    ->groupEnd();
            }

            if ($type !== '') {

                $builder->where(
                    'type',
                    $type
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
                'CMS features fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->cmsFeatureModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->cmsFeatureModel
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
                'Unable to fetch CMS features.'
            );
        }
    }

    /**
     * GET /public/cms-features/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $cmsFeature = $this->cmsFeatureModel
                ->active()
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $cmsFeature) {
                return $this->notFoundResponse(
                    'CMS feature not found.'
                );
            }

            return $this->successResponse(
                'CMS feature fetched successfully.',
                $cmsFeature
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch CMS feature.'
            );
        }
    }
}