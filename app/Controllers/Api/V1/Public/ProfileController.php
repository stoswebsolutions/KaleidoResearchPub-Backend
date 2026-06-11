<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ProfileModel;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ProfileController extends BaseApiController
{
    protected ProfileModel $profileModel;

    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->profileModel = new ProfileModel();

        $this->roleModel = new RoleModel();
    }

    /**
     * POST /public/profiles/register
     */
    public function register(): ResponseInterface
    {
        try {

            $payload = 
                $this->getRequestData();

            /**
             * Get Default Author Role
             */
            $role = $this->roleModel
                ->where(
                    'slug',
                    'author'
                )
                ->first();

            if (! $role) {

                return $this->serverErrorResponse(
                    'Default author role not configured.'
                );
            }

            $password = (string) (
                $payload['password']
                ?? ''
            );

            $confirmPassword = (string) (
                $payload['confirm_password']
                ?? ''
            );

            if (
                $password !== $confirmPassword
            ) {

                return $this->validationResponse([
                    'confirm_password' =>
                        'Password confirmation does not match.',
                ]);
            }

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
                    $password,
                    PASSWORD_DEFAULT
                ),

                /**
                 * Default Status
                 */
                'status' => 'active',
            ];

            if (
                ! $this->profileModel->insert(
                    $data
                )
            ) {

                return $this->validationResponse(
                    $this->profileModel->errors()
                );
            }

            $profile = $this->profileModel->find(
                $this->profileModel->getInsertID()
            );

            return $this->successResponse(
                'Registration completed successfully.',
                [
                    'profile' => [
                        'uuid' => $profile['uuid'],

                        'full_name' => $profile['full_name'],

                        'email' => $profile['email'],

                        'phone' => $profile['phone'],

                        'status' => $profile['status'],
                    ],
                ],
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to complete registration.'
            );
        }
    }

    /**
     * GET /public/profiles/{uuid}
     */
    public function show(
        $id = null
    ): ResponseInterface {
        try {

            $profile = $this->profileModel
                ->select([
                    'profiles.uuid',

                    'profiles.full_name',

                    'profiles.created_at',

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
                ->where(
                    'profiles.status',
                    'active'
                )
                ->first();

            if (! $profile) {

                return $this->notFoundResponse(
                    'Profile not found.'
                );
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
}