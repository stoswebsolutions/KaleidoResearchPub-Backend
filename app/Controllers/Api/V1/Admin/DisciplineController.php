<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\DisciplineModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class DisciplineController extends BaseApiController
{
    protected DisciplineModel $disciplineModel;

    protected array $allowedSortFields = [
        'title',
        'sort_order',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->disciplineModel = new DisciplineModel();
    }

        /**
     * GET /disciplines
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

            $parentId = $this->request->getGet(
                'parent_id'
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

            $builder = $this->disciplineModel
                ->select([
                    'disciplines.id',
                    'disciplines.uuid',
                    'disciplines.title',
                    'disciplines.slug',
                    'disciplines.description',
                    'disciplines.parent_id',
                    'parent.title AS parent_title',
                    'disciplines.sort_order',
                    'disciplines.status',
                    'disciplines.created_at',
                ])
                ->join(
                    'disciplines parent',
                    'parent.id = disciplines.parent_id',
                    'left'
                );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'disciplines.title',
                        $search
                    )
                    ->orLike(
                        'disciplines.slug',
                        $search
                    )
                    ->orLike(
                        'disciplines.description',
                        $search
                    )
                    ->groupEnd();
            }

            if ($status !== '') {

                $builder->where(
                    'disciplines.status',
                    $status
                );
            }

            if (
                $parentId !== null
                && $parentId !== ''
            ) {
                $builder->where(
                    'disciplines.parent_id',
                    (int) $parentId
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Disciplines fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->disciplineModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->disciplineModel
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
                'Unable to fetch disciplines.'
            );
        }
    }

        /**
     * GET /disciplines/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $discipline = $this->disciplineModel
                ->select([
                    'disciplines.id',
                    'disciplines.uuid',
                    'disciplines.title',
                    'disciplines.slug',
                    'disciplines.description',
                    'disciplines.parent_id',
                    'parent.uuid AS parent_uuid',
                    'parent.title AS parent_title',
                    'disciplines.sort_order',
                    'disciplines.status',
                    'disciplines.created_at',
                    'disciplines.updated_at',
                ])
                ->join(
                    'disciplines parent',
                    'parent.id = disciplines.parent_id',
                    'left'
                )
                ->where(
                    'disciplines.uuid',
                    (string) $id
                )
                ->first();

            if (! $discipline) {
                return $this->notFoundResponse(
                    'Discipline not found.'
                );
            }

            return $this->successResponse(
                'Discipline fetched successfully.',
                $discipline
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch discipline.'
            );
        }
    }

        /**
     * POST /disciplines
     */
    public function create(): ResponseInterface
    {
        try {

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $parentId = null;

            if (! empty($payload['parent_uuid'])) {

                $parent = $this->disciplineModel
                    ->findByUuid(
                        (string) $payload['parent_uuid']
                    );

                if (! $parent) {
                    return $this->errorResponse(
                        'Parent discipline not found.'
                    );
                }

                $parentId = (int) $parent['id'];
            }

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? ''
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
                ),

                'parent_id' => $parentId,

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
                ! $this->disciplineModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->disciplineModel->errors()
                );
            }

            $discipline = $this->disciplineModel
                ->find(
                    $this->disciplineModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Discipline created successfully.',
                $discipline,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create discipline.'
            );
        }
    }

        /**
     * PUT /disciplines/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $discipline = $this->disciplineModel
                ->findByUuid(
                    (string) $id
                );

            if (! $discipline) {
                return $this->notFoundResponse(
                    'Discipline not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $parentId = $discipline['parent_id'];

            if (
                array_key_exists(
                    'parent_uuid',
                    $payload
                )
            ) {

                if (
                    empty(
                        $payload['parent_uuid']
                    )
                ) {

                    $parentId = null;

                } else {

                    $parent = $this->disciplineModel
                        ->findByUuid(
                            (string) $payload['parent_uuid']
                        );

                    if (! $parent) {
                        return $this->errorResponse(
                            'Parent discipline not found.'
                        );
                    }

                    if (
                        (int) $parent['id']
                        === (int) $discipline['id']
                    ) {
                        return $this->errorResponse(
                            'A discipline cannot be its own parent.'
                        );
                    }

                    $parentId = (int) $parent['id'];
                }
            }

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? $discipline['title']
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? (
                            $discipline['description']
                            ?? ''
                        )
                    )
                ),

                'parent_id' => $parentId,

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $discipline['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $discipline['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->disciplineModel->update(
                    $discipline['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->disciplineModel->errors()
                );
            }

            return $this->successResponse(
                'Discipline updated successfully.',
                $this->disciplineModel->find(
                    $discipline['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update discipline.'
            );
        }
    }

    /**
     * DELETE /disciplines/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $discipline = $this->disciplineModel
                ->findByUuid(
                    (string) $id
                );

            if (! $discipline) {
                return $this->notFoundResponse(
                    'Discipline not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->disciplineModel->update(
                $discipline['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->disciplineModel->delete(
                $discipline['id']
            );

            return $this->successResponse(
                'Discipline deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete discipline.'
            );
        }
    }
}