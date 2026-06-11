<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ArticleTypeModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ArticleTypeController extends BaseApiController
{
    protected ArticleTypeModel $articleTypeModel;

    protected array $allowedSortFields = [
        'title',
        'code',
        'sort_order',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->articleTypeModel = new ArticleTypeModel();
    }

        /**
     * GET /article-types
     */
    public function index(): ResponseInterface
    {
        try {

            $page = max(
                1,
                (int) ($this->request->getGet('page') ?? 1)
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

            $status = $this->request->getGet('status');

            $sortBy = (string) (
                $this->request->getGet('sort_by')
                ?? 'sort_order'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet('sort_direction')
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

            $builder = $this->articleTypeModel
                ->select([
                    'uuid',
                    'title',
                    'code',
                    'slug',
                    'description',
                    'sort_order',
                    'status',
                    'created_at',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like('title', $search)
                    ->orLike('code', $search)
                    ->orLike('slug', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
            }

            if (
                $status !== null
                && $status !== ''
            ) {
                $builder->where(
                    'status',
                    (int) $status
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
                'Article types fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->articleTypeModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->articleTypeModel
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
                'Unable to fetch article types.'
            );
        }
    }

        /**
     * GET /article-types/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $articleType = $this->articleTypeModel
                ->findByUuid(
                    (string) $id
                );

            if (! $articleType) {
                return $this->notFoundResponse(
                    'Article type not found.'
                );
            }

            return $this->successResponse(
                'Article type fetched successfully.',
                $articleType
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch article type.'
            );
        }
    }

        /**
     * POST /article-types
     */
    public function create(): ResponseInterface
    {
        try {

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? ''
                    )
                ),

                'code' => trim(
                    (string) (
                        $payload['code']
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

                'status' => (int) (
                    $payload['status']
                    ?? 1
                ),

                'created_by' => $user['id'],
            ];

            if (
                ! $this->articleTypeModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->articleTypeModel->errors()
                );
            }

            $articleType = $this->articleTypeModel
                ->find(
                    $this->articleTypeModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Article type created successfully.',
                $articleType,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create article type.'
            );
        }
    }

        /**
     * PUT /article-types/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $articleType = $this->articleTypeModel
                ->findByUuid(
                    (string) $id
                );

            if (! $articleType) {
                return $this->notFoundResponse(
                    'Article type not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? $articleType['title']
                    )
                ),

                'code' => trim(
                    (string) (
                        $payload['code']
                        ?? $articleType['code']
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? $articleType['description']
                    )
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $articleType['sort_order']
                ),

                'status' => (int) (
                    $payload['status']
                    ?? $articleType['status']
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->articleTypeModel->update(
                    $articleType['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->articleTypeModel->errors()
                );
            }

            return $this->successResponse(
                'Article type updated successfully.',
                $this->articleTypeModel->find(
                    $articleType['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update article type.'
            );
        }
    }

    /**
     * DELETE /article-types/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $articleType = $this->articleTypeModel
                ->findByUuid(
                    (string) $id
                );

            if (! $articleType) {
                return $this->notFoundResponse(
                    'Article type not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->articleTypeModel->update(
                $articleType['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->articleTypeModel->delete(
                $articleType['id']
            );

            return $this->successResponse(
                'Article type deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete article type.'
            );
        }
    }
}