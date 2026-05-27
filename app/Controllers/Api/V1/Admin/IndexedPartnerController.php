<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

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
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->indexedPartnerModel = new IndexedPartnerModel();
    }

        /**
     * GET /indexed-partners
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
                ->select([
                    'uuid',
                    'title',
                    'slug',
                    'logo',
                    'website_url',
                    'sort_order',
                    'status',
                    'created_at',
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

            if ($status !== '') {

                $builder->where(
                    'status',
                    $status
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
     * GET /indexed-partners/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $indexedPartner = $this->indexedPartnerModel
                ->findByUuid(
                    (string) $id
                );

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

        /**
     * POST /indexed-partners
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

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? ''
                    )
                ),

                'logo' => trim(
                    (string) (
                        $payload['logo']
                        ?? ''
                    )
                ),

                'website_url' => trim(
                    (string) (
                        $payload['website_url']
                        ?? ''
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
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
                ! $this->indexedPartnerModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->indexedPartnerModel->errors()
                );
            }

            $indexedPartner = $this->indexedPartnerModel
                ->find(
                    $this->indexedPartnerModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Indexed partner created successfully.',
                $indexedPartner,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create indexed partner.'
            );
        }
    }

        /**
     * PUT /indexed-partners/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $indexedPartner = $this->indexedPartnerModel
                ->findByUuid(
                    (string) $id
                );

            if (! $indexedPartner) {
                return $this->notFoundResponse(
                    'Indexed partner not found.'
                );
            }

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? $indexedPartner['title']
                    )
                ),

                'logo' => trim(
                    (string) (
                        $payload['logo']
                        ?? (
                            $indexedPartner['logo']
                            ?? ''
                        )
                    )
                ),

                'website_url' => trim(
                    (string) (
                        $payload['website_url']
                        ?? (
                            $indexedPartner['website_url']
                            ?? ''
                        )
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? (
                            $indexedPartner['description']
                            ?? ''
                        )
                    )
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $indexedPartner['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $indexedPartner['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->indexedPartnerModel->update(
                    $indexedPartner['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->indexedPartnerModel->errors()
                );
            }

            return $this->successResponse(
                'Indexed partner updated successfully.',
                $this->indexedPartnerModel->find(
                    $indexedPartner['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update indexed partner.'
            );
        }
    }

    /**
     * DELETE /indexed-partners/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $indexedPartner = $this->indexedPartnerModel
                ->findByUuid(
                    (string) $id
                );

            if (! $indexedPartner) {
                return $this->notFoundResponse(
                    'Indexed partner not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->indexedPartnerModel->update(
                $indexedPartner['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->indexedPartnerModel->delete(
                $indexedPartner['id']
            );

            return $this->successResponse(
                'Indexed partner deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete indexed partner.'
            );
        }
    }
}