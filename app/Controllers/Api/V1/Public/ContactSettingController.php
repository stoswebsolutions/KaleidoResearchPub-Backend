<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ContactSettingModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ContactSettingController extends BaseApiController
{
    protected ContactSettingModel $contactSettingModel;

    protected array $allowedSortFields = [
        'organization_name',
        'created_at',
    ];

    public function __construct()
    {
        $this->contactSettingModel = new ContactSettingModel();
    }

    /**
     * GET /public/contact-settings
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
                ->active()
                ->select([
                    'uuid',
                    'organization_name',
                    'address',
                    'email',
                    'alternate_email',
                    'phone',
                    'alternate_phone',
                    'whatsapp',
                    'google_map_url',
                    'facebook_url',
                    'twitter_url',
                    'linkedin_url',
                    'instagram_url',
                    'youtube_url',
                    'working_hours',
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
                        'phone',
                        $search
                    )
                    ->groupEnd();
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
     * GET /public/contact-settings/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $contactSetting = $this->contactSettingModel
                ->active()
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

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
     * GET /public/contact-settings/active
     */
    public function active(): ResponseInterface
    {
        try {

            $contactSetting = $this->contactSettingModel
                ->getActiveSettings();

            if (! $contactSetting) {
                return $this->notFoundResponse(
                    'Active contact setting not found.'
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
}