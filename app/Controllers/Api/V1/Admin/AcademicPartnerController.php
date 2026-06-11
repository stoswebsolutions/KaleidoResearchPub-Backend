<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\AcademicPartnerModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AcademicPartnerController extends BaseApiController
{
    protected AcademicPartnerModel $academicPartnerModel;

    protected array $allowedSortFields = [
        'name',
        'partner_type',
        'sort_order',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->academicPartnerModel = new AcademicPartnerModel();
    }

        /**
     * GET /academic-partners
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

            $partnerType = trim(
                (string) (
                    $this->request->getGet('partner_type')
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

            $builder = $this->academicPartnerModel
                ->select([
                    'id',
                    'uuid',
                    'name',
                    'slug',
                    'logo',
                    'partner_type',
                    'website_url',
                    'email',
                    'phone',
                    'contact_person',
                    'sort_order',
                    'status',
                    'created_at',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'name',
                        $search
                    )
                    ->orLike(
                        'slug',
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

            if ($status !== '') {

                $builder->where(
                    'status',
                    $status
                );
            }

            if ($partnerType !== '') {

                $builder->where(
                    'partner_type',
                    $partnerType
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
                'Academic partners fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->academicPartnerModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->academicPartnerModel
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
                'Unable to fetch academic partners.'
            );
        }
    }

        /**
     * GET /academic-partners/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $academicPartner = $this->academicPartnerModel
                ->findByUuid(
                    (string) $id
                );

            if (! $academicPartner) {
                return $this->notFoundResponse(
                    'Academic partner not found.'
                );
            }

            return $this->successResponse(
                'Academic partner fetched successfully.',
                $academicPartner
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch academic partner.'
            );
        }
    }

        /**
     * POST /academic-partners
     */
    public function create(): ResponseInterface
    {
        try {

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'name' => trim(
                    (string) (
                        $payload['name']
                        ?? ''
                    )
                ),

                'partner_type' => trim(
                    (string) (
                        $payload['partner_type']
                        ?? ''
                    )
                ),

                'address' => trim(
                    (string) (
                        $payload['address']
                        ?? ''
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
                ),

                'website_url' => trim(
                    (string) (
                        $payload['website_url']
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

                'contact_person' => trim(
                    (string) (
                        $payload['contact_person']
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
            $data['logo'] =
                $this->uploadFile(
                    'logo',
                    'uploads/academic',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if (
                empty(
                    $data['logo']
                )
            ) {

                return $this->validationResponse([
                    'logo' =>
                        'Media file is required.'
                ]);
            }

            if (
                ! $this->academicPartnerModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->academicPartnerModel->errors()
                );
            }

            $academicPartner = $this->academicPartnerModel
                ->find(
                    $this->academicPartnerModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Academic partner created successfully.',
                $academicPartner,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create academic partner.'
            );
        }
    }

        /**
     * PUT /academic-partners/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $academicPartner = $this->academicPartnerModel
                ->findByUuid(
                    (string) $id
                );

            if (! $academicPartner) {
                return $this->notFoundResponse(
                    'Academic partner not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'id' => $academicPartner['id'],

                'name' => trim(
                    (string) (
                        $payload['name']
                        ?? $academicPartner['name']
                    )
                ),

                'partner_type' => trim(
                    (string) (
                        $payload['partner_type']
                        ?? $academicPartner['partner_type']
                    )
                ),

                'address' => trim(
                    (string) (
                        $payload['address']
                        ?? (
                            $academicPartner['address']
                            ?? ''
                        )
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? (
                            $academicPartner['description']
                            ?? ''
                        )
                    )
                ),

                'website_url' => trim(
                    (string) (
                        $payload['website_url']
                        ?? (
                            $academicPartner['website_url']
                            ?? ''
                        )
                    )
                ),

                'email' => trim(
                    (string) (
                        $payload['email']
                        ?? (
                            $academicPartner['email']
                            ?? ''
                        )
                    )
                ),

                'phone' => trim(
                    (string) (
                        $payload['phone']
                        ?? (
                            $academicPartner['phone']
                            ?? ''
                        )
                    )
                ),

                'contact_person' => trim(
                    (string) (
                        $payload['contact_person']
                        ?? (
                            $academicPartner['contact_person']
                            ?? ''
                        )
                    )
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $academicPartner['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $academicPartner['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            /**
             * Media Upload
             */
            $logo =
                $this->uploadFile(
                    'logo',
                    'uploads/academic',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if ($logo !== null) {

                $this->deleteFile(
                    $academicPartner['logo']
                );

                $data['logo'] =
                    $logo;
            }

            if (
                ! $this->academicPartnerModel->update(
                    $academicPartner['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->academicPartnerModel->errors()
                );
            }

            return $this->successResponse(
                'Academic partner updated successfully.',
                $this->academicPartnerModel->find(
                    $academicPartner['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update academic partner.'
            );
        }
    }

    /**
     * DELETE /academic-partners/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $academicPartner = $this->academicPartnerModel
                ->findByUuid(
                    (string) $id
                );

            if (! $academicPartner) {
                return $this->notFoundResponse(
                    'Academic partner not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->academicPartnerModel->update(
                $academicPartner['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->academicPartnerModel->delete(
                $academicPartner['id']
            );

            return $this->successResponse(
                'Academic partner deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete academic partner.'
            );
        }
    }
}