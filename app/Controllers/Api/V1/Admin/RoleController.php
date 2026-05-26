<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class RoleController extends BaseApiController
{
    protected RoleModel $roleModel;

    protected array $allowedSortFields = [
        'name',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    /**
     * GET /roles
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

            $status = $this->request->getGet('status');

            $sortBy = (string) (
                $this->request->getGet('sort_by') ?? 'created_at'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet('sort_direction') ?? 'desc'
                )
            );

            if (! in_array($sortBy, $this->allowedSortFields, true)) {
                $sortBy = 'created_at';
            }

            if (! in_array($sortDirection, ['asc', 'desc'], true)) {
                $sortDirection = 'desc';
            }

            $builder = $this->roleModel
                ->select([
                    'uuid',
                    'name',
                    'slug',
                    'description',
                    'status',
                    'is_system',
                    'created_at',
                ]);

            if ($search !== '') {
                $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
            }

            if ($status !== null && $status !== '') {
                $builder->where(
                    'status',
                    (int) $status
                );
            }

            $records = $builder
                ->orderBy($sortBy, $sortDirection)
                ->paginate($perPage);

            return $this->successResponse(
                'Roles fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->roleModel->pager->getTotal(),
                        'last_page'    => $this->roleModel->pager->getPageCount(),
                    ],
                ]
            );

        } catch (Throwable $e) {

            log_message('error', $e->getMessage());

            return $this->serverErrorResponse(
                'Unable to fetch roles.'
            );
        }
    }

    /**
     * GET /roles/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $role = $this->roleModel
                ->where('uuid', (string) $id)
                ->first();

            if (! $role) {
                return $this->notFoundResponse(
                    'Role not found.'
                );
            }

            return $this->successResponse(
                'Role fetched successfully.',
                [
                    'uuid'        => $role['uuid'],
                    'name'        => $role['name'],
                    'slug'        => $role['slug'],
                    'description' => $role['description'],
                    'status'      => $role['status'],
                    'is_system'   => $role['is_system'],
                    'created_at'  => $role['created_at'],
                    'updated_at'  => $role['updated_at'],
                ]
            );

        } catch (Throwable $e) {

            log_message('error', $e->getMessage());

            return $this->serverErrorResponse(
                'Unable to fetch role.'
            );
        }
    }

    /**
     * POST /roles
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
                'name'        => trim(
                    (string) ($payload['name'] ?? '')
                ),
                'description' => trim(
                    (string) ($payload['description'] ?? '')
                ),
                'status'        => trim(
                    (string) ($payload['status'] ?? 'active')
                ),
                'is_system'   => 0,
                'created_by'  => $user['id'],
            ];

            if (! $this->roleModel->insert($data)) {
                return $this->validationResponse(
                    $this->roleModel->errors()
                );
            }

            $role = $this->roleModel->find(
                $this->roleModel->getInsertID()
            );

            return $this->successResponse(
                'Role created successfully.',
                $role,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message('error', $e->getMessage());

            return $this->serverErrorResponse(
                'Unable to create role.'
            );
        }
    }

    /**
     * PUT /roles/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $role = $this->roleModel
                ->where('uuid', (string) $id)
                ->first();

            if (! $role) {
                return $this->notFoundResponse(
                    'Role not found.'
                );
            }

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'name'        => trim(
                    (string) (
                        $payload['name']
                        ?? $role['name']
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? $role['description']
                    )
                ),

                'status'      => (string) (
                    $payload['status']
                    ?? $role['status']
                ),

                'updated_by'  => $user['id'],
            ];

            if (! $this->roleModel->update(
                $role['id'],
                $data
            )) {
                return $this->validationResponse(
                    $this->roleModel->errors()
                );
            }

            return $this->successResponse(
                'Role updated successfully.',
                $this->roleModel->find(
                    $role['id']
                )
            );

        } catch (Throwable $e) {

            log_message('error', $e->getMessage());

            return $this->serverErrorResponse(
                'Unable to update role.'
            );
        }
    }

    /**
     * DELETE /roles/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $role = $this->roleModel
                ->where('uuid', (string) $id)
                ->first();

            if (! $role) {
                return $this->notFoundResponse(
                    'Role not found.'
                );
            }

            if ((int) $role['is_system'] === 1) {
                return $this->errorResponse(
                    'System roles cannot be deleted.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->roleModel->update(
                $role['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->roleModel->delete(
                $role['id']
            );

            return $this->successResponse(
                'Role deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message('error', $e->getMessage());

            return $this->serverErrorResponse(
                'Unable to delete role.'
            );
        }
    }
}