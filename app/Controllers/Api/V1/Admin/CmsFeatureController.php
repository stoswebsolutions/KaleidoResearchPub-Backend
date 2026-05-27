<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

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
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->cmsFeatureModel = new CmsFeatureModel();
    }

        /**
     * GET /cms-features
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

            $builder = $this->cmsFeatureModel
                ->select([
                    'uuid',
                    'type',
                    'title',
                    'slug',
                    'short_description',
                    'icon',
                    'image',
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
                        'short_description',
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
     * GET /cms-features/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $cmsFeature = $this->cmsFeatureModel
                ->findByUuid(
                    (string) $id
                );

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

        /**
     * POST /cms-features
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
                'type' => trim(
                    (string) (
                        $payload['type']
                        ?? ''
                    )
                ),

                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? ''
                    )
                ),

                'short_description' => trim(
                    (string) (
                        $payload['short_description']
                        ?? ''
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
                ),

                'icon' => trim(
                    (string) (
                        $payload['icon']
                        ?? ''
                    )
                ),

                'image' => trim(
                    (string) (
                        $payload['image']
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
                ! $this->cmsFeatureModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->cmsFeatureModel->errors()
                );
            }

            $cmsFeature = $this->cmsFeatureModel
                ->find(
                    $this->cmsFeatureModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'CMS feature created successfully.',
                $cmsFeature,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create CMS feature.'
            );
        }
    }

        /**
     * PUT /cms-features/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $cmsFeature = $this->cmsFeatureModel
                ->findByUuid(
                    (string) $id
                );

            if (! $cmsFeature) {
                return $this->notFoundResponse(
                    'CMS feature not found.'
                );
            }

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'type' => trim(
                    (string) (
                        $payload['type']
                        ?? $cmsFeature['type']
                    )
                ),

                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? $cmsFeature['title']
                    )
                ),

                'short_description' => trim(
                    (string) (
                        $payload['short_description']
                        ?? (
                            $cmsFeature['short_description']
                            ?? ''
                        )
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? (
                            $cmsFeature['description']
                            ?? ''
                        )
                    )
                ),

                'icon' => trim(
                    (string) (
                        $payload['icon']
                        ?? (
                            $cmsFeature['icon']
                            ?? ''
                        )
                    )
                ),

                'image' => trim(
                    (string) (
                        $payload['image']
                        ?? (
                            $cmsFeature['image']
                            ?? ''
                        )
                    )
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $cmsFeature['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $cmsFeature['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->cmsFeatureModel->update(
                    $cmsFeature['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->cmsFeatureModel->errors()
                );
            }

            return $this->successResponse(
                'CMS feature updated successfully.',
                $this->cmsFeatureModel->find(
                    $cmsFeature['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update CMS feature.'
            );
        }
    }

    /**
     * DELETE /cms-features/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $cmsFeature = $this->cmsFeatureModel
                ->findByUuid(
                    (string) $id
                );

            if (! $cmsFeature) {
                return $this->notFoundResponse(
                    'CMS feature not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->cmsFeatureModel->update(
                $cmsFeature['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->cmsFeatureModel->delete(
                $cmsFeature['id']
            );

            return $this->successResponse(
                'CMS feature deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete CMS feature.'
            );
        }
    }
}