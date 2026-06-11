<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class RolePermissionController extends BaseApiController
{
    protected RoleModel $roleModel;

    protected PermissionModel $permissionModel;

    protected RolePermissionModel $rolePermissionModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->rolePermissionModel = new RolePermissionModel();
    }

    /**
     * GET /api/v1/admin/roles/{role_uuid}/permissions
     */
    public function index($roleUuid = null): ResponseInterface
    {
        try {

            $role = $this->roleModel
                ->where('uuid', (string) $roleUuid)
                ->first();

            if (! $role) {
                return $this->notFoundResponse(
                    'Role not found.'
                );
            }

            $permissions = db_connect()
                ->table('role_permissions rp')
                ->select([
                    'p.uuid',
                    'p.name',
                    'p.slug',
                    'p.description',
                    'p.status',
                ])
                ->join(
                    'permissions p',
                    'p.id = rp.permission_id'
                )
                ->where(
                    'rp.role_id',
                    $role['id']
                )
                ->orderBy('p.name', 'ASC')
                ->get()
                ->getResultArray();

            return $this->successResponse(
                'Role permissions fetched successfully.',
                [
                    'role' => [
                        'uuid' => $role['uuid'],
                        'name' => $role['name'],
                        'slug' => $role['slug'],
                    ],
                    'permissions' => $permissions,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch role permissions.'
            );
        }
    }

    /**
     * POST /api/v1/admin/roles/{role_uuid}/permissions
     */
    public function assign($roleUuid = null): ResponseInterface
    {
        try {

            $role = $this->roleModel
                ->where('uuid', (string) $roleUuid)
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

            $permissionUuids =
                $payload['permission_uuids']
                ?? [];

            if (
                ! is_array($permissionUuids)
                || empty($permissionUuids)
            ) {
                return $this->validationResponse([
                    'permission_uuids' =>
                        'Please provide at least one permission UUID.',
                ]);
            }

            $permissions = $this->permissionModel
                ->select('id, uuid')
                ->whereIn(
                    'uuid',
                    $permissionUuids
                )
                ->findAll();

            if (
                count($permissions)
                !== count($permissionUuids)
            ) {
                return $this->validationResponse([
                    'permission_uuids' =>
                        'One or more permission UUIDs are invalid.',
                ]);
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $db = db_connect();

            $db->transStart();

            /**
             * Remove existing permissions.
             */
            $this->rolePermissionModel
                ->where(
                    'role_id',
                    $role['id']
                )
                ->delete();

            /**
             * Insert selected permissions.
             */
            foreach ($permissions as $permission) {

                $this->rolePermissionModel->insert([
                    'role_id'       => $role['id'],
                    'permission_id' => $permission['id'],
                    'created_by'    => $user['id'],
                ]);
            }

            $db->transComplete();

            if (! $db->transStatus()) {

                return $this->serverErrorResponse(
                    'Failed to assign permissions.'
                );
            }

            $assignedPermissions = db_connect()
                ->table('role_permissions rp')
                ->select([
                    'p.uuid',
                    'p.name',
                    'p.slug',
                ])
                ->join(
                    'permissions p',
                    'p.id = rp.permission_id'
                )
                ->where(
                    'rp.role_id',
                    $role['id']
                )
                ->orderBy('p.name', 'ASC')
                ->get()
                ->getResultArray();

            return $this->successResponse(
                'Role permissions assigned successfully.',
                [
                    'role' => [
                        'uuid' => $role['uuid'],
                        'name' => $role['name'],
                        'slug' => $role['slug'],
                    ],
                    'permissions' => $assignedPermissions,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to assign role permissions.'
            );
        }
    }

    /**
     * GET /api/v1/admin/role-permissions/matrix
     */
    public function matrix(): ResponseInterface
    {
        try {

            $roles =
                $this->roleModel
                    ->where(
                        'status',
                        'active'
                    )
                    ->orderBy(
                        'name',
                        'ASC'
                    )
                    ->findAll();

            $permissions =
                $this->permissionModel
                    ->where(
                        'status',
                        'active'
                    )
                    ->orderBy(
                        'name',
                        'ASC'
                    )
                    ->findAll();

            $rolePermissions =
                $this->rolePermissionModel
                    ->select(
                        'role_id, permission_id'
                    )
                    ->findAll();

            /**
             * Build lookup.
             */
            $assigned = [];

            foreach (
                $rolePermissions
                as $rolePermission
            ) {

                $assigned[
                    $rolePermission['role_id']
                ][
                    $rolePermission['permission_id']
                ] = true;
            }

            $result = [];

            foreach (
                $roles
                as $role
            ) {

                $roleData = [

                    'uuid' =>
                        $role['uuid'],

                    'name' =>
                        $role['name'],

                    'slug' =>
                        $role['slug'],

                    'permissions' => [],
                ];

                foreach (
                    $permissions
                    as $permission
                ) {

                    $roleData['permissions'][] = [

                        'uuid' =>
                            $permission['uuid'],

                        'module' =>
                            $permission['module'],

                        'name' =>
                            $permission['name'],

                        'slug' =>
                            $permission['slug'],

                        'assigned' =>
                            isset(
                                $assigned[
                                    $role['id']
                                ][
                                    $permission['id']
                                ]
                            ),
                    ];
                }

                $result[] =
                    $roleData;
            }

            return $this->successResponse(
                'Role permission matrix fetched successfully.',
                [
                    'roles' =>
                        $result,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch role permission matrix.'
            );
        }
    }
}