<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ProfileModel;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ProfileController extends BaseApiController
{
    protected ProfileModel $profileModel;

    protected RoleModel $roleModel;

    protected array $allowedSortFields = [
        'full_name',
        'email',
        'phone',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->profileModel = new ProfileModel();
        $this->roleModel    = new RoleModel();
    }

        /**
     * GET /profiles
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

            $status = trim(
                (string) (
                    $this->request->getGet('status')
                    ?? ''
                )
            );

            $roleUuid = trim(
                (string) (
                    $this->request->getGet('role_uuid')
                    ?? ''
                )
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

            $builder = $this->profileModel
                ->select([
                    'profiles.uuid',
                    'profiles.full_name',
                    'profiles.email',
                    'profiles.phone',
                    'profiles.status',
                    'profiles.last_login_at',
                    'profiles.created_at',
                    'roles.uuid AS role_uuid',
                    'roles.name AS role_name',
                ])
                ->join(
                    'roles',
                    'roles.id = profiles.role_id',
                    'left'
                );

            $builder = $this->applyOwnershipFilter(
                $builder,
                'profiles'
            );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'profiles.full_name',
                        $search
                    )
                    ->orLike(
                        'profiles.email',
                        $search
                    )
                    ->orLike(
                        'profiles.phone',
                        $search
                    )
                    ->groupEnd();
            }

            if ($status !== '') {

                $builder->where(
                    'profiles.status',
                    $status
                );
            }

            if ($roleUuid !== '') {

                $builder->where(
                    'roles.uuid',
                    $roleUuid
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Profiles fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->profileModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->profileModel
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
                'Unable to fetch profiles.'
            );
        }
    }

        /**
     * GET /profiles/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $profile = $this->profileModel
                ->select([
                    'profiles.uuid',
                    'profiles.full_name',
                    'profiles.email',
                    'profiles.phone',
                    'profiles.status',
                    'profiles.email_verified_at',
                    'profiles.phone_verified_at',
                    'profiles.last_login_at',
                    'profiles.last_login_ip',
                    'profiles.failed_login_attempts',
                    'profiles.locked_until',
                    'profiles.created_at',
                    'profiles.updated_at',
                    'roles.uuid AS role_uuid',
                    'roles.name AS role_name',
                    'roles.slug AS role_slug',
                ])
                ->join(
                    'roles',
                    'roles.id = profiles.role_id',
                    'left'
                )
                ->where(
                    'profiles.uuid',
                    (string) $id
                )
                ->first();

            if (! $profile) {
                return $this->notFoundResponse(
                    'Profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $profile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {
                return $ownershipCheck;
            }

            return $this->successResponse(
                'Profile fetched successfully.',
                $profile
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch profile.'
            );
        }
    }

        /**
     * POST /profiles
     */
    public function create(): ResponseInterface
    {
        try {

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $role = $this->roleModel
                ->where(
                    'uuid',
                    (string) (
                        $payload['role_uuid']
                        ?? ''
                    )
                )
                ->first();

            if (! $role) {
                return $this->validationResponse([
                    'role_uuid' => 'Invalid role selected.',
                ]);
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $temporaryPassword = bin2hex(
                random_bytes(6)
            );

            $data = [
                'role_id' => $role['id'],

                'full_name' => trim(
                    (string) (
                        $payload['full_name']
                        ?? ''
                    )
                ),

                'email' => trim(
                    (string) (
                        $payload['email']
                        ?? ''
                    )
                ),

                'phone' => trim(
                    (string) (
                        $payload['phone']
                        ?? ''
                    )
                ),

                'password_hash' => password_hash(
                    $temporaryPassword,
                    PASSWORD_DEFAULT
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
                ! $this->profileModel->insert($data)
            ) {
                return $this->validationResponse(
                    $this->profileModel->errors()
                );
            }

            $profile = $this->profileModel->find(
                $this->profileModel->getInsertID()
            );

            return $this->successResponse(
                'Profile created successfully.',
                [
                    'profile' => [
                        'uuid'      => $profile['uuid'],
                        'full_name' => $profile['full_name'],
                        'email'     => $profile['email'],
                    ],
                    'temporary_password' =>
                        $temporaryPassword,
                ],
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create profile.'
            );
        }
    }

        /**
     * PUT /profiles/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $profile = $this->profileModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $profile) {
                return $this->notFoundResponse(
                    'Profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $profile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {
                return $ownershipCheck;
            }

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $role = $this->roleModel
                ->where(
                    'uuid',
                    (string) (
                        $payload['role_uuid']
                        ?? ''
                    )
                )
                ->first();

            if (! $role) {
                return $this->validationResponse([
                    'role_uuid' => 'Invalid role selected.',
                ]);
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'role_id' => $role['id'],

                'full_name' => trim(
                    (string) (
                        $payload['full_name']
                        ?? $profile['full_name']
                    )
                ),

                'email' => trim(
                    (string) (
                        $payload['email']
                        ?? $profile['email']
                    )
                ),

                'phone' => trim(
                    (string) (
                        $payload['phone']
                        ?? $profile['phone']
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $profile['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->profileModel->update(
                    $profile['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->profileModel->errors()
                );
            }

            return $this->successResponse(
                'Profile updated successfully.',
                $this->profileModel->find(
                    $profile['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update profile.'
            );
        }
    }

    /**
     * DELETE /profiles/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $profile = $this->profileModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $profile) {
                return $this->notFoundResponse(
                    'Profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $profile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {
                return $ownershipCheck;
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            /**
             * Prevent self deletion.
             */
            if (
                (int) $profile['id']
                === (int) $user['id']
            ) {
                return $this->errorResponse(
                    'You cannot delete your own profile.'
                );
            }

            $this->profileModel->update(
                $profile['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->profileModel->delete(
                $profile['id']
            );

            return $this->successResponse(
                'Profile deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete profile.'
            );
        }
    }
}