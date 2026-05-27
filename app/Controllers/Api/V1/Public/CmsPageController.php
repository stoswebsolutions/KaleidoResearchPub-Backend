<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\CmsPageModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class CmsPageController extends BaseApiController
{
    protected CmsPageModel $cmsPageModel;

    protected array $allowedSortFields = [
        'page_key',
        'title',
        'sort_order',
        'created_at',
    ];

    public function __construct()
    {
        $this->cmsPageModel = new CmsPageModel();
    }

    /**
     * GET /public/cms-pages
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

            $builder = $this->cmsPageModel
                ->active()
                ->select([
                    'uuid',
                    'page_key',
                    'title',
                    'slug',
                    'meta_title',
                    'meta_description',
                    'banner_image',
                    'sort_order',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'page_key',
                        $search
                    )
                    ->orLike(
                        'title',
                        $search
                    )
                    ->orLike(
                        'slug',
                        $search
                    )
                    ->orLike(
                        'meta_title',
                        $search
                    )
                    ->groupEnd();
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
                'CMS pages fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->cmsPageModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->cmsPageModel
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
                'Unable to fetch CMS pages.'
            );
        }
    }

    /**
     * GET /public/cms-pages/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $cmsPage = $this->cmsPageModel
                ->active()
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $cmsPage) {
                return $this->notFoundResponse(
                    'CMS page not found.'
                );
            }

            return $this->successResponse(
                'CMS page fetched successfully.',
                $cmsPage
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch CMS page.'
            );
        }
    }

    /**
     * GET /public/cms-pages/page-key/{pageKey}
     */
    public function showByPageKey(
        string $pageKey
    ): ResponseInterface {
        try {

            $cmsPage = $this->cmsPageModel
                ->active()
                ->where(
                    'page_key',
                    $pageKey
                )
                ->first();

            if (! $cmsPage) {
                return $this->notFoundResponse(
                    'CMS page not found.'
                );
            }

            return $this->successResponse(
                'CMS page fetched successfully.',
                $cmsPage
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch CMS page.'
            );
        }
    }
}