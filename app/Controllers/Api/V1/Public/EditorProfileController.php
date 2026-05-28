<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\EditorProfileModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class EditorProfileController extends BaseApiController
{
    protected EditorProfileModel $editorProfileModel;

    protected array $allowedSortFields = [
        'full_name',
        'editor_type',
        'sort_order',
        'created_at',
    ];

    public function __construct()
    {
        $this->editorProfileModel = new EditorProfileModel();
    }

    /**
     * GET /public/editor-profiles
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

            $editorType = trim(
                (string) (
                    $this->request->getGet('editor_type')
                    ?? ''
                )
            );

            $featured = $this->request->getGet(
                'featured'
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

            $builder = $this->editorProfileModel
                ->select([
                    'uuid',

                    'editor_type',

                    'full_name',

                    'designation',
                    'department',
                    'organization_name',
                    'country',

                    'qualification',

                    'specialization',
                    'research_interests',

                    'experience_years',

                    'bio',

                    'profile_image',

                    'profile_slug',

                    'orcid_id',

                    'google_scholar_url',
                    'scopus_author_url',
                    'researchgate_url',
                    'linkedin_url',
                    'personal_website_url',

                    'sort_order',

                    'is_featured',

                    'created_at',
                ])
                ->where(
                    'status',
                    'active'
                );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'full_name',
                        $search
                    )
                    ->orLike(
                        'designation',
                        $search
                    )
                    ->orLike(
                        'organization_name',
                        $search
                    )
                    ->orLike(
                        'country',
                        $search
                    )
                    ->orLike(
                        'specialization',
                        $search
                    )
                    ->groupEnd();
            }

            if ($editorType !== '') {

                $builder->where(
                    'editor_type',
                    $editorType
                );
            }

            if (
                $featured !== null
                && $featured !== ''
            ) {

                $builder->where(
                    'is_featured',
                    (int) $featured
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Editor profiles fetched successfully.',
                [
                    'items' => $records,

                    'pagination' => [
                        'current_page' => $page,

                        'per_page' => $perPage,

                        'total' => $this->editorProfileModel
                            ->pager
                            ->getTotal(),

                        'last_page' => $this->editorProfileModel
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
                'Unable to fetch editor profiles.'
            );
        }
    }

    /**
     * GET /public/editor-profiles/{slug}
     */
    public function show(
        $slug = null
    ): ResponseInterface {
        try {

            $editorProfile = $this->editorProfileModel
                ->select([
                    'uuid',

                    'editor_type',

                    'full_name',

                    'designation',
                    'department',
                    'organization_name',
                    'country',

                    'qualification',

                    'specialization',
                    'research_interests',

                    'experience_years',

                    'bio',

                    'profile_image',

                    'profile_slug',

                    'orcid_id',

                    'google_scholar_url',
                    'scopus_author_url',
                    'researchgate_url',
                    'linkedin_url',
                    'personal_website_url',

                    'sort_order',

                    'is_featured',

                    'created_at',
                ])
                ->where(
                    'status',
                    'active'
                )
                ->first();

            if (! $editorProfile) {

                return $this->notFoundResponse(
                    'Editor profile not found.'
                );
            }

            return $this->successResponse(
                'Editor profile fetched successfully.',
                $editorProfile
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch editor profile.'
            );
        }
    }
}