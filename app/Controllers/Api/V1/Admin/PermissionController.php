<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\PermissionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class PermissionController extends BaseApiController
{
    protected PermissionModel $permissionModel;

    protected array $allowedSortFields = [
        'module',
        'name',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
    }

    /**
     * GET /permissions
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
                    (int) ($this->request->getGet('per_page') ?? 20)
                )
            );

            $search = trim(
                (string) ($this->request->getGet('search') ?? '')
            );

            $status = trim(
                (string) ($this->request->getGet('status') ?? '')
            );

            $module = trim(
                (string) ($this->request->getGet('module') ?? '')
            );

            $sortBy = (string) (
                $this->request->getGet('sort_by')
                ?? 'created_at'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet('sort_direction')
                    ?? 'desc'
                )
            );

            if (
                ! in_array(
                    $sortBy,
                    $this->allowedSortFields,
                    true
                )
            ) {
                $sortBy = 'created_at';
            }

            if (
                ! in_array(
                    $sortDirection,
                    ['asc', 'desc'],
                    true
                )
            ) {
                $sortDirection = 'desc';
            }

            $builder = $this->permissionModel
                ->select([
                    'id',
                    'uuid',
                    'module',
                    'name',
                    'slug',
                    'description',
                    'status',
                    'created_at',
                ]);

            if ($search !== '') {
                $builder->groupStart()
                    ->like('module', $search)
                    ->orLike('name', $search)
                    ->orLike('slug', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
            }

            if ($status !== '') {
                $builder->where(
                    'status',
                    $status
                );
            }

            if ($module !== '') {
                $builder->where(
                    'module',
                    $module
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Permissions fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->permissionModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->permissionModel
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
                'Unable to fetch permissions.'
            );
        }
    }

    /**
     * GET /permissions/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $permission = $this->permissionModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $permission) {
                return $this->notFoundResponse(
                    'Permission not found.'
                );
            }

            return $this->successResponse(
                'Permission fetched successfully.',
                $permission
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch permission.'
            );
        }
    }

    /**
     * POST /permissions
     */
    public function create(): ResponseInterface
    {
        try {

            $payload = 
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'module' => trim(
                    (string) (
                        $payload['module']
                        ?? ''
                    )
                ),

                'name' => trim(
                    (string) (
                        $payload['name']
                        ?? ''
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
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
                ! $this->permissionModel->insert($data)
            ) {
                return $this->validationResponse(
                    $this->permissionModel->errors()
                );
            }

            $permission = $this->permissionModel
                ->find(
                    $this->permissionModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Permission created successfully.',
                $permission,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create permission.'
            );
        }
    }

    /**
     * PUT /permissions/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $permission = $this->permissionModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $permission) {
                return $this->notFoundResponse(
                    'Permission not found.'
                );
            }

            $payload = 
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'id' => $permission['id'],

                'module' => trim(
                    (string) (
                        $payload['module']
                        ?? $permission['module']
                    )
                ),

                'name' => trim(
                    (string) (
                        $payload['name']
                        ?? $permission['name']
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? $permission['description']
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $permission['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->permissionModel->update(
                    $permission['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->permissionModel->errors()
                );
            }

            return $this->successResponse(
                'Permission updated successfully.',
                $this->permissionModel->find(
                    $permission['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update permission.'
            );
        }
    }

    /**
     * DELETE /permissions/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $permission = $this->permissionModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $permission) {
                return $this->notFoundResponse(
                    'Permission not found.'
                );
            }

            $assigned = db_connect()
                ->table('role_permissions')
                ->where(
                    'permission_id',
                    $permission['id']
                )
                ->countAllResults();

            if ($assigned > 0) {
                return $this->errorResponse(
                    'Permission is assigned to one or more roles.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->permissionModel->update(
                $permission['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->permissionModel->delete(
                $permission['id']
            );

            return $this->successResponse(
                'Permission deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete permission.'
            );
        }
    }
}