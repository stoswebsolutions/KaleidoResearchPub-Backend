<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

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
        'created_at',
    ];

    public function __construct()
    {
        $this->academicPartnerModel = new AcademicPartnerModel();
    }

    /**
     * GET /public/academic-partners
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
                ->active()
                ->select([
                    'id',
                    'uuid',
                    'name',
                    'slug',
                    'logo',
                    'partner_type',
                    'description',
                    'website_url',
                    'email',
                    'phone',
                    'contact_person',
                    'sort_order',
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
                        'description',
                        $search
                    )
                    ->groupEnd();
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
     * GET /public/academic-partners/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $academicPartner = $this->academicPartnerModel
                ->active()
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

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
}