<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

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
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->cmsPageModel = new CmsPageModel();
    }

        /**
     * GET /cms-pages
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

            $builder = $this->cmsPageModel
                ->select([
                    'id',
                    'uuid',
                    'page_key',
                    'title',
                    'slug',
                    'meta_title',
                    'sort_order',
                    'status',
                    'created_at',
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
     * GET /cms-pages/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $cmsPage = $this->cmsPageModel
                ->findByUuid(
                    (string) $id
                );

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
     * POST /cms-pages
     */
    public function create(): ResponseInterface
    {
        try {

            $payload = $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'page_key' => trim(
                    (string) (
                        $payload['page_key']
                        ?? ''
                    )
                ),

                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? ''
                    )
                ),

                'content' => trim(
                    (string) (
                        $payload['content']
                        ?? ''
                    )
                ),

                'meta_title' => trim(
                    (string) (
                        $payload['meta_title']
                        ?? ''
                    )
                ),

                'meta_keywords' => trim(
                    (string) (
                        $payload['meta_keywords']
                        ?? ''
                    )
                ),

                'meta_description' => trim(
                    (string) (
                        $payload['meta_description']
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

            /**
             * Media Upload
             */
            $data['image'] =
                $this->uploadFile(
                    'image',
                    'uploads/cms-pages',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if (
                empty(
                    $data['image']
                )
            ) {

                return $this->validationResponse([
                    'image' =>
                        'Media file is required.'
                ]);
            }

            if (
                ! $this->cmsPageModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->cmsPageModel->errors()
                );
            }

            $cmsPage = $this->cmsPageModel
                ->find(
                    $this->cmsPageModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'CMS page created successfully.',
                $cmsPage,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create CMS page.'
            );
        }
    }

        /**
     * PUT /cms-pages/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $cmsPage = $this->cmsPageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $cmsPage) {
                return $this->notFoundResponse(
                    'CMS page not found.'
                );
                
            }

            $payload = $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [

                'id' => $cmsPage['id'],

                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? $cmsPage['title']
                    )
                ),

                'content' => (string) (
                    $payload['content']
                    ?? (
                        $cmsPage['content']
                        ?? ''
                    )
                ),

                'meta_title' => trim(
                    (string) (
                        $payload['meta_title']
                        ?? (
                            $cmsPage['meta_title']
                            ?? ''
                        )
                    )
                ),

                'meta_keywords' => trim(
                    (string) (
                        $payload['meta_keywords']
                        ?? (
                            $cmsPage['meta_keywords']
                            ?? ''
                        )
                    )
                ),

                'meta_description' => trim(
                    (string) (
                        $payload['meta_description']
                        ?? (
                            $cmsPage['meta_description']
                            ?? ''
                        )
                    )
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $cmsPage['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $cmsPage['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            /**
             * Media Upload
             */
            $image =
                $this->uploadFile(
                    'image',
                    'uploads/cms-pages',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if ($image !== null) {

                $this->deleteFile(
                    $cmsPage['image']
                );

                $data['image'] =
                    $image;
            }

            if (
                ! $this->cmsPageModel->update(
                    $cmsPage['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->cmsPageModel->errors()
                );
            }

            return $this->successResponse(
                'CMS page updated successfully.',
                $this->cmsPageModel->find(
                    $cmsPage['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update CMS page.'
            );
        }
    }

    /**
     * DELETE /cms-pages/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $cmsPage = $this->cmsPageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $cmsPage) {
                return $this->notFoundResponse(
                    'CMS page not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->cmsPageModel->update(
                $cmsPage['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->cmsPageModel->delete(
                $cmsPage['id']
            );

            return $this->successResponse(
                'CMS page deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete CMS page.'
            );
        }
    }
}