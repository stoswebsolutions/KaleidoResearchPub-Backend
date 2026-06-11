<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\AccountDetailsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AccountDetailsController extends BaseApiController
{
    protected AccountDetailsModel $accountDetailsModel;

    protected array $allowedSortFields = [
        'account_holder_name',
        'bank_name',
        'account_type',
        'is_primary',
        'sort_order',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->accountDetailsModel =
            new AccountDetailsModel();
    }
        /**
     * GET /account-details
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

            $accountType = trim(
                (string) (
                    $this->request->getGet('account_type')
                    ?? ''
                )
            );

            $status = trim(
                (string) (
                    $this->request->getGet('status')
                    ?? ''
                )
            );

            $isPrimary = $this->request->getGet(
                'is_primary'
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

            $builder = $this->accountDetailsModel
                ->select([
                    'id',

                    'uuid',

                    'account_holder_name',

                    'account_number',

                    'bank_name',

                    'branch_name',

                    'ifsc_code',

                    'account_type',

                    'upi_id',

                    'qr_code_image',

                    'is_primary',

                    'sort_order',

                    'status',

                    'created_at',
                ]);

            $builder = $this->applyOwnershipFilter(
                $builder,
                'account_details'
            );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'account_holder_name',
                        $search
                    )
                    ->orLike(
                        'bank_name',
                        $search
                    )
                    ->orLike(
                        'account_number',
                        $search
                    )
                    ->orLike(
                        'ifsc_code',
                        $search
                    )
                    ->orLike(
                        'upi_id',
                        $search
                    )
                    ->groupEnd();
            }

            if ($accountType !== '') {

                $builder->where(
                    'account_type',
                    $accountType
                );
            }

            if ($status !== '') {

                $builder->where(
                    'status',
                    $status
                );
            }

            if (
                $isPrimary !== null
                && $isPrimary !== ''
            ) {

                $builder->where(
                    'is_primary',
                    (int) $isPrimary
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Account details fetched successfully.',
                [
                    'items' => $records,

                    'pagination' => [
                        'current_page' => $page,

                        'per_page' => $perPage,

                        'total' => $this->accountDetailsModel
                            ->pager
                            ->getTotal(),

                        'last_page' => $this->accountDetailsModel
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
                'Unable to fetch account details.'
            );
        }
    }
        /**
     * GET /account-details/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $accountDetails = $this->accountDetailsModel
                ->select([
                    'id',

                    'uuid',

                    'account_holder_name',

                    'account_number',

                    'bank_name',

                    'branch_name',

                    'branch_address',

                    'ifsc_code',

                    'account_type',

                    'upi_id',

                    'qr_code_image',

                    'is_primary',

                    'sort_order',

                    'status',

                    'created_by',

                    'updated_by',

                    'created_at',

                    'updated_at',
                ])
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $accountDetails) {

                return $this->notFoundResponse(
                    'Account details not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $accountDetails
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {

                return $ownershipCheck;
            }

            return $this->successResponse(
                'Account details fetched successfully.',
                $accountDetails
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch account details.'
            );
        }
    }
        /**
     * POST /account-details
     */
    public function create(): ResponseInterface
    {
        try {

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [

                'account_holder_name' => trim(
                    (string) (
                        $payload['account_holder_name']
                        ?? ''
                    )
                ),

                'account_number' => trim(
                    (string) (
                        $payload['account_number']
                        ?? ''
                    )
                ),

                'bank_name' => trim(
                    (string) (
                        $payload['bank_name']
                        ?? ''
                    )
                ),

                'branch_name' => trim(
                    (string) (
                        $payload['branch_name']
                        ?? ''
                    )
                ),

                'branch_address' => trim(
                    (string) (
                        $payload['branch_address']
                        ?? ''
                    )
                ),

                'ifsc_code' => strtoupper(
                    trim(
                        (string) (
                            $payload['ifsc_code']
                            ?? ''
                        )
                    )
                ),

                'account_type' => trim(
                    (string) (
                        $payload['account_type']
                        ?? 'savings'
                    )
                ),

                'upi_id' => trim(
                    (string) (
                        $payload['upi_id']
                        ?? ''
                    )
                ),

                'is_primary' => (int) (
                    $payload['is_primary']
                    ?? 0
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
            $data['qr_code_image'] =
                $this->uploadFile(
                    'qr_code_image',
                    'uploads/account',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if (
                empty(
                    $data['qr_code_image']
                )
            ) {

                return $this->validationResponse([
                    'qr_code_image' =>
                        'Media file is required.'
                ]);
            }

            if (
                ! $this->accountDetailsModel->insert(
                    $data
                )
            ) {

                return $this->validationResponse(
                    $this->accountDetailsModel->errors()
                );
            }

            $accountDetails = $this->accountDetailsModel
                ->find(
                    $this->accountDetailsModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Account details created successfully.',
                $accountDetails,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create account details.'
            );
        }
    }
        /**
     * PUT /account-details/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $accountDetails = $this->accountDetailsModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $accountDetails) {

                return $this->notFoundResponse(
                    'Account details not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $accountDetails
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {

                return $ownershipCheck;
            }

            $payload =
                $this->getRequestData();

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'id' => $accountDetails['id'],

                'account_holder_name' => trim(
                    (string) (
                        $payload['account_holder_name']
                        ?? $accountDetails['account_holder_name']
                    )
                ),

                'account_number' => trim(
                    (string) (
                        $payload['account_number']
                        ?? $accountDetails['account_number']
                    )
                ),

                'bank_name' => trim(
                    (string) (
                        $payload['bank_name']
                        ?? $accountDetails['bank_name']
                    )
                ),

                'branch_name' => trim(
                    (string) (
                        $payload['branch_name']
                        ?? (
                            $accountDetails['branch_name']
                            ?? ''
                        )
                    )
                ),

                'branch_address' => trim(
                    (string) (
                        $payload['branch_address']
                        ?? (
                            $accountDetails['branch_address']
                            ?? ''
                        )
                    )
                ),

                'ifsc_code' => strtoupper(
                    trim(
                        (string) (
                            $payload['ifsc_code']
                            ?? $accountDetails['ifsc_code']
                        )
                    )
                ),

                'account_type' => trim(
                    (string) (
                        $payload['account_type']
                        ?? $accountDetails['account_type']
                    )
                ),

                'upi_id' => trim(
                    (string) (
                        $payload['upi_id']
                        ?? (
                            $accountDetails['upi_id']
                            ?? ''
                        )
                    )
                ),

                'is_primary' => (int) (
                    $payload['is_primary']
                    ?? $accountDetails['is_primary']
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $accountDetails['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $accountDetails['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            /**
             * Media Upload
             */
            $qr_code_image =
                $this->uploadFile(
                    'qr_code_image',
                    'uploads/account',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if ($qr_code_image !== null) {

                $this->deleteFile(
                    $accountDetails['qr_code_image']
                );

                $data['qr_code_image'] =
                    $qr_code_image;
            }

            if (
                ! $this->accountDetailsModel->update(
                    $accountDetails['id'],
                    $data
                )
            ) {

                return $this->validationResponse(
                    $this->accountDetailsModel->errors()
                );
            }

            return $this->successResponse(
                'Account details updated successfully.',
                $this->accountDetailsModel->find(
                    $accountDetails['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update account details.'
            );
        }
    }

    /**
     * DELETE /account-details/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $accountDetails = $this->accountDetailsModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $accountDetails) {

                return $this->notFoundResponse(
                    'Account details not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $accountDetails
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {

                return $ownershipCheck;
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->accountDetailsModel->update(
                $accountDetails['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->accountDetailsModel->delete(
                $accountDetails['id']
            );

            return $this->successResponse(
                'Account details deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete account details.'
            );
        }
    }
}