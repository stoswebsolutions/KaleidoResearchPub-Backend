<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ContactSettingModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ContactSettingController extends BaseApiController
{
    protected ContactSettingModel $contactSettingModel;

    protected array $allowedSortFields = [
        'organization_name',
        'email',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->contactSettingModel = new ContactSettingModel();
    }

        /**
     * GET /contact-settings
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
                ?? 'created_at'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet(
                        'sort_direction'
                    )
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

            $builder = $this->contactSettingModel
                ->select([
                    'uuid',
                    'organization_name',
                    'email',
                    'alternate_email',
                    'phone',
                    'whatsapp',
                    'status',
                    'created_at',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'organization_name',
                        $search
                    )
                    ->orLike(
                        'email',
                        $search
                    )
                    ->orLike(
                        'alternate_email',
                        $search
                    )
                    ->orLike(
                        'phone',
                        $search
                    )
                    ->orLike(
                        'whatsapp',
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
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Contact settings fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->contactSettingModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->contactSettingModel
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
                'Unable to fetch contact settings.'
            );
        }
    }

        /**
     * GET /contact-settings/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $contactSetting = $this->contactSettingModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactSetting) {
                return $this->notFoundResponse(
                    'Contact setting not found.'
                );
            }

            return $this->successResponse(
                'Contact setting fetched successfully.',
                $contactSetting
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch contact setting.'
            );
        }
    }

        /**
     * POST /contact-settings
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
                'organization_name' => trim(
                    (string) (
                        $payload['organization_name']
                        ?? ''
                    )
                ),

                'address' => trim(
                    (string) (
                        $payload['address']
                        ?? ''
                    )
                ),

                'email' => trim(
                    (string) (
                        $payload['email']
                        ?? ''
                    )
                ),

                'alternate_email' => trim(
                    (string) (
                        $payload['alternate_email']
                        ?? ''
                    )
                ),

                'phone' => trim(
                    (string) (
                        $payload['phone']
                        ?? ''
                    )
                ),

                'alternate_phone' => trim(
                    (string) (
                        $payload['alternate_phone']
                        ?? ''
                    )
                ),

                'whatsapp' => trim(
                    (string) (
                        $payload['whatsapp']
                        ?? ''
                    )
                ),

                'google_map_url' => trim(
                    (string) (
                        $payload['google_map_url']
                        ?? ''
                    )
                ),

                'facebook_url' => trim(
                    (string) (
                        $payload['facebook_url']
                        ?? ''
                    )
                ),

                'twitter_url' => trim(
                    (string) (
                        $payload['twitter_url']
                        ?? ''
                    )
                ),

                'linkedin_url' => trim(
                    (string) (
                        $payload['linkedin_url']
                        ?? ''
                    )
                ),

                'instagram_url' => trim(
                    (string) (
                        $payload['instagram_url']
                        ?? ''
                    )
                ),

                'youtube_url' => trim(
                    (string) (
                        $payload['youtube_url']
                        ?? ''
                    )
                ),

                'working_hours' => trim(
                    (string) (
                        $payload['working_hours']
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
                ! $this->contactSettingModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->contactSettingModel->errors()
                );
            }

            $contactSetting = $this->contactSettingModel
                ->find(
                    $this->contactSettingModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Contact setting created successfully.',
                $contactSetting,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create contact setting.'
            );
        }
    }
    
        /**
     * PUT /contact-settings/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $contactSetting = $this->contactSettingModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactSetting) {
                return $this->notFoundResponse(
                    'Contact setting not found.'
                );
            }

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'organization_name' => trim(
                    (string) (
                        $payload['organization_name']
                        ?? $contactSetting['organization_name']
                    )
                ),

                'address' => trim(
                    (string) (
                        $payload['address']
                        ?? (
                            $contactSetting['address']
                            ?? ''
                        )
                    )
                ),

                'email' => trim(
                    (string) (
                        $payload['email']
                        ?? $contactSetting['email']
                    )
                ),

                'alternate_email' => trim(
                    (string) (
                        $payload['alternate_email']
                        ?? (
                            $contactSetting['alternate_email']
                            ?? ''
                        )
                    )
                ),

                'phone' => trim(
                    (string) (
                        $payload['phone']
                        ?? (
                            $contactSetting['phone']
                            ?? ''
                        )
                    )
                ),

                'alternate_phone' => trim(
                    (string) (
                        $payload['alternate_phone']
                        ?? (
                            $contactSetting['alternate_phone']
                            ?? ''
                        )
                    )
                ),

                'whatsapp' => trim(
                    (string) (
                        $payload['whatsapp']
                        ?? (
                            $contactSetting['whatsapp']
                            ?? ''
                        )
                    )
                ),

                'google_map_url' => trim(
                    (string) (
                        $payload['google_map_url']
                        ?? (
                            $contactSetting['google_map_url']
                            ?? ''
                        )
                    )
                ),

                'facebook_url' => trim(
                    (string) (
                        $payload['facebook_url']
                        ?? (
                            $contactSetting['facebook_url']
                            ?? ''
                        )
                    )
                ),

                'twitter_url' => trim(
                    (string) (
                        $payload['twitter_url']
                        ?? (
                            $contactSetting['twitter_url']
                            ?? ''
                        )
                    )
                ),

                'linkedin_url' => trim(
                    (string) (
                        $payload['linkedin_url']
                        ?? (
                            $contactSetting['linkedin_url']
                            ?? ''
                        )
                    )
                ),

                'instagram_url' => trim(
                    (string) (
                        $payload['instagram_url']
                        ?? (
                            $contactSetting['instagram_url']
                            ?? ''
                        )
                    )
                ),

                'youtube_url' => trim(
                    (string) (
                        $payload['youtube_url']
                        ?? (
                            $contactSetting['youtube_url']
                            ?? ''
                        )
                    )
                ),

                'working_hours' => trim(
                    (string) (
                        $payload['working_hours']
                        ?? (
                            $contactSetting['working_hours']
                            ?? ''
                        )
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $contactSetting['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->contactSettingModel->update(
                    $contactSetting['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->contactSettingModel->errors()
                );
            }

            return $this->successResponse(
                'Contact setting updated successfully.',
                $this->contactSettingModel->find(
                    $contactSetting['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update contact setting.'
            );
        }
    }

    /**
     * DELETE /contact-settings/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $contactSetting = $this->contactSettingModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactSetting) {
                return $this->notFoundResponse(
                    'Contact setting not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->contactSettingModel->update(
                $contactSetting['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->contactSettingModel->delete(
                $contactSetting['id']
            );

            return $this->successResponse(
                'Contact setting deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete contact setting.'
            );
        }
    }
}