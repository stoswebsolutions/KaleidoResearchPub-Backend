<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\IndexedPartnerModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class IndexedPartnerController extends BaseApiController
{
    protected IndexedPartnerModel $indexedPartnerModel;

    protected array $allowedSortFields = [
        'title',
        'sort_order',
        'created_at',
    ];

    public function __construct()
    {
        $this->indexedPartnerModel = new IndexedPartnerModel();
    }

    /**
     * GET /public/indexed-partners
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

            $builder = $this->indexedPartnerModel
                ->active()
                ->select([
                    'uuid',
                    'title',
                    'slug',
                    'logo',
                    'website_url',
                    'description',
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
                        'description',
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
                'Indexed partners fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->indexedPartnerModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->indexedPartnerModel
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
                'Unable to fetch indexed partners.'
            );
        }
    }

    /**
     * GET /public/indexed-partners/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $indexedPartner = $this->indexedPartnerModel
                ->active()
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $indexedPartner) {
                return $this->notFoundResponse(
                    'Indexed partner not found.'
                );
            }

            return $this->successResponse(
                'Indexed partner fetched successfully.',
                $indexedPartner
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch indexed partner.'
            );
        }
    }
}