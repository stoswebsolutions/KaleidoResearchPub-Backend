<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

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
        'created_at',
    ];

    public function __construct()
    {
        $this->accountDetailsModel =
            new AccountDetailsModel();
    }

    /**
     * GET /public/account-details
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

                    'created_at',
                ])
                ->where(
                    'status',
                    'active'
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
     * GET /public/account-details/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $accountDetails = $this->accountDetailsModel
                ->select([

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

                    'created_at',
                ])
                ->where(
                    'uuid',
                    (string) $id
                )
                ->where(
                    'status',
                    'active'
                )
                ->first();

            if (! $accountDetails) {

                return $this->notFoundResponse(
                    'Account details not found.'
                );
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
}