<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\AuthorProfileModel;
use App\Models\ProfileModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AuthorProfileController extends BaseApiController
{
    protected AuthorProfileModel $authorProfileModel;

    protected ProfileModel $profileModel;

    protected array $allowedSortFields = [
        'full_name',
        'author_type',
        'organization_name',
        'country',
        'publication_count',
        'citation_count',
        'h_index',
        'sort_order',
        'is_featured',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->authorProfileModel = new AuthorProfileModel();

        $this->profileModel = new ProfileModel();
    }

        /**
     * GET /author-profiles
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

            $authorType = trim(
                (string) (
                    $this->request->getGet('author_type')
                    ?? ''
                )
            );

            $status = trim(
                (string) (
                    $this->request->getGet('status')
                    ?? ''
                )
            );

            $featured = $this->request->getGet(
                'featured'
            );

            $profileUuid = trim(
                (string) (
                    $this->request->getGet('profile_uuid')
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

            $builder = $this->authorProfileModel
                ->select([
                    'author_profiles.uuid',

                    'author_profiles.author_type',

                    'author_profiles.full_name',

                    'author_profiles.designation',

                    'author_profiles.organization_name',

                    'author_profiles.country',

                    'author_profiles.profile_image',

                    'author_profiles.profile_slug',

                    'author_profiles.publication_count',

                    'author_profiles.citation_count',

                    'author_profiles.h_index',

                    'author_profiles.is_featured',

                    'author_profiles.sort_order',

                    'author_profiles.status',

                    'author_profiles.created_at',

                    'profiles.uuid AS profile_uuid',

                    'profiles.email AS profile_email',
                ])
                ->join(
                    'profiles',
                    'profiles.id = author_profiles.profile_id',
                    'left'
                );

            $builder = $this->applyOwnershipFilter(
                $builder,
                'author_profiles'
            );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'author_profiles.full_name',
                        $search
                    )
                    ->orLike(
                        'author_profiles.designation',
                        $search
                    )
                    ->orLike(
                        'author_profiles.organization_name',
                        $search
                    )
                    ->orLike(
                        'author_profiles.country',
                        $search
                    )
                    ->orLike(
                        'author_profiles.specialization',
                        $search
                    )
                    ->groupEnd();
            }

            if ($authorType !== '') {

                $builder->where(
                    'author_profiles.author_type',
                    $authorType
                );
            }

            if ($status !== '') {

                $builder->where(
                    'author_profiles.status',
                    $status
                );
            }

            if (
                $featured !== null
                && $featured !== ''
            ) {

                $builder->where(
                    'author_profiles.is_featured',
                    (int) $featured
                );
            }

            if ($profileUuid !== '') {

                $builder->where(
                    'profiles.uuid',
                    $profileUuid
                );
            }

            $records = $builder
                ->orderBy(
                    'author_profiles.' . $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Author profiles fetched successfully.',
                [
                    'items' => $records,

                    'pagination' => [
                        'current_page' => $page,

                        'per_page' => $perPage,

                        'total' => $this->authorProfileModel
                            ->pager
                            ->getTotal(),

                        'last_page' => $this->authorProfileModel
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
                'Unable to fetch author profiles.'
            );
        }
    }

        /**
     * GET /author-profiles/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $authorProfile = $this->authorProfileModel
                ->select([
                    'author_profiles.id',

                    'author_profiles.uuid',

                    'author_profiles.profile_id',

                    'author_profiles.author_type',

                    'author_profiles.full_name',

                    'author_profiles.designation',

                    'author_profiles.organization_name',

                    'author_profiles.department',

                    'author_profiles.qualification',

                    'author_profiles.specialization',

                    'author_profiles.research_interests',

                    'author_profiles.experience_years',

                    'author_profiles.bio',

                    'author_profiles.profile_image',

                    'author_profiles.profile_slug',

                    'author_profiles.orcid_id',

                    'author_profiles.google_scholar_url',

                    'author_profiles.scopus_author_url',

                    'author_profiles.researchgate_url',

                    'author_profiles.linkedin_url',

                    'author_profiles.personal_website_url',

                    'author_profiles.country',

                    'author_profiles.state',

                    'author_profiles.city',

                    'author_profiles.zipcode',

                    'author_profiles.address',

                    'author_profiles.publication_count',

                    'author_profiles.citation_count',

                    'author_profiles.h_index',

                    'author_profiles.is_featured',

                    'author_profiles.sort_order',

                    'author_profiles.status',

                    'author_profiles.created_by',

                    'author_profiles.updated_by',

                    'author_profiles.created_at',

                    'author_profiles.updated_at',

                    'profiles.uuid AS profile_uuid',

                    'profiles.full_name AS profile_name',

                    'profiles.email AS profile_email',

                    'profiles.phone AS profile_phone',
                ])
                ->join(
                    'profiles',
                    'profiles.id = author_profiles.profile_id',
                    'left'
                )
                ->where(
                    'author_profiles.uuid',
                    (string) $id
                )
                ->first();

            if (! $authorProfile) {

                return $this->notFoundResponse(
                    'Author profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $authorProfile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {

                return $ownershipCheck;
            }

            return $this->successResponse(
                'Author profile fetched successfully.',
                $authorProfile
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch author profile.'
            );
        }
    }
        /**
     * POST /author-profiles
     */
    public function create(): ResponseInterface
    {
        try {

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $profile = null;

            if (
                ! empty($payload['profile_uuid'])
            ) {

                $profile = $this->profileModel
                    ->where(
                        'uuid',
                        (string) $payload['profile_uuid']
                    )
                    ->first();

                if (! $profile) {

                    return $this->validationResponse([
                        'profile_uuid' =>
                            'Invalid profile selected.',
                    ]);
                }
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [

                'profile_id' => $profile['id'] ?? null,

                'author_type' => trim(
                    (string) (
                        $payload['author_type']
                        ?? ''
                    )
                ),

                'full_name' => trim(
                    (string) (
                        $payload['full_name']
                        ?? ''
                    )
                ),

                'designation' => trim(
                    (string) (
                        $payload['designation']
                        ?? ''
                    )
                ),

                'organization_name' => trim(
                    (string) (
                        $payload['organization_name']
                        ?? ''
                    )
                ),

                'department' => trim(
                    (string) (
                        $payload['department']
                        ?? ''
                    )
                ),

                'qualification' => trim(
                    (string) (
                        $payload['qualification']
                        ?? ''
                    )
                ),

                'specialization' => trim(
                    (string) (
                        $payload['specialization']
                        ?? ''
                    )
                ),

                'research_interests' => trim(
                    (string) (
                        $payload['research_interests']
                        ?? ''
                    )
                ),

                'experience_years' => (int) (
                    $payload['experience_years']
                    ?? 0
                ),

                'bio' => trim(
                    (string) (
                        $payload['bio']
                        ?? ''
                    )
                ),

                'profile_image' => trim(
                    (string) (
                        $payload['profile_image']
                        ?? ''
                    )
                ),

                'profile_slug' => trim(
                    (string) (
                        $payload['profile_slug']
                        ?? ''
                    )
                ),

                'orcid_id' => trim(
                    (string) (
                        $payload['orcid_id']
                        ?? ''
                    )
                ),

                'google_scholar_url' => trim(
                    (string) (
                        $payload['google_scholar_url']
                        ?? ''
                    )
                ),

                'scopus_author_url' => trim(
                    (string) (
                        $payload['scopus_author_url']
                        ?? ''
                    )
                ),

                'researchgate_url' => trim(
                    (string) (
                        $payload['researchgate_url']
                        ?? ''
                    )
                ),

                'linkedin_url' => trim(
                    (string) (
                        $payload['linkedin_url']
                        ?? ''
                    )
                ),

                'personal_website_url' => trim(
                    (string) (
                        $payload['personal_website_url']
                        ?? ''
                    )
                ),

                'country' => trim(
                    (string) (
                        $payload['country']
                        ?? ''
                    )
                ),

                'state' => trim(
                    (string) (
                        $payload['state']
                        ?? ''
                    )
                ),

                'city' => trim(
                    (string) (
                        $payload['city']
                        ?? ''
                    )
                ),

                'zipcode' => trim(
                    (string) (
                        $payload['zipcode']
                        ?? ''
                    )
                ),

                'address' => trim(
                    (string) (
                        $payload['address']
                        ?? ''
                    )
                ),

                'publication_count' => (int) (
                    $payload['publication_count']
                    ?? 0
                ),

                'citation_count' => (int) (
                    $payload['citation_count']
                    ?? 0
                ),

                'h_index' => (int) (
                    $payload['h_index']
                    ?? 0
                ),

                'is_featured' => (int) (
                    $payload['is_featured']
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

            if (
                ! $this->authorProfileModel->insert(
                    $data
                )
            ) {

                return $this->validationResponse(
                    $this->authorProfileModel->errors()
                );
            }

            $authorProfile = $this->authorProfileModel
                ->find(
                    $this->authorProfileModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Author profile created successfully.',
                $authorProfile,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create author profile.'
            );
        }
    }
        /**
     * PUT /author-profiles/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $authorProfile = $this->authorProfileModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $authorProfile) {

                return $this->notFoundResponse(
                    'Author profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $authorProfile
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

            $profile = null;

            if (
                ! empty($payload['profile_uuid'])
            ) {

                $profile = $this->profileModel
                    ->where(
                        'uuid',
                        (string) $payload['profile_uuid']
                    )
                    ->first();

                if (! $profile) {

                    return $this->validationResponse([
                        'profile_uuid' =>
                            'Invalid profile selected.',
                    ]);
                }
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [

                'profile_id' => $profile['id']
                    ?? $authorProfile['profile_id'],

                'author_type' => trim(
                    (string) (
                        $payload['author_type']
                        ?? $authorProfile['author_type']
                    )
                ),

                'full_name' => trim(
                    (string) (
                        $payload['full_name']
                        ?? $authorProfile['full_name']
                    )
                ),

                'designation' => trim(
                    (string) (
                        $payload['designation']
                        ?? (
                            $authorProfile['designation']
                            ?? ''
                        )
                    )
                ),

                'organization_name' => trim(
                    (string) (
                        $payload['organization_name']
                        ?? (
                            $authorProfile['organization_name']
                            ?? ''
                        )
                    )
                ),

                'department' => trim(
                    (string) (
                        $payload['department']
                        ?? (
                            $authorProfile['department']
                            ?? ''
                        )
                    )
                ),

                'qualification' => trim(
                    (string) (
                        $payload['qualification']
                        ?? (
                            $authorProfile['qualification']
                            ?? ''
                        )
                    )
                ),

                'specialization' => trim(
                    (string) (
                        $payload['specialization']
                        ?? (
                            $authorProfile['specialization']
                            ?? ''
                        )
                    )
                ),

                'research_interests' => trim(
                    (string) (
                        $payload['research_interests']
                        ?? (
                            $authorProfile['research_interests']
                            ?? ''
                        )
                    )
                ),

                'experience_years' => (int) (
                    $payload['experience_years']
                    ?? $authorProfile['experience_years']
                ),

                'bio' => trim(
                    (string) (
                        $payload['bio']
                        ?? (
                            $authorProfile['bio']
                            ?? ''
                        )
                    )
                ),

                'profile_image' => trim(
                    (string) (
                        $payload['profile_image']
                        ?? (
                            $authorProfile['profile_image']
                            ?? ''
                        )
                    )
                ),

                'profile_slug' => trim(
                    (string) (
                        $payload['profile_slug']
                        ?? $authorProfile['profile_slug']
                    )
                ),

                'orcid_id' => trim(
                    (string) (
                        $payload['orcid_id']
                        ?? (
                            $authorProfile['orcid_id']
                            ?? ''
                        )
                    )
                ),

                'google_scholar_url' => trim(
                    (string) (
                        $payload['google_scholar_url']
                        ?? (
                            $authorProfile['google_scholar_url']
                            ?? ''
                        )
                    )
                ),

                'scopus_author_url' => trim(
                    (string) (
                        $payload['scopus_author_url']
                        ?? (
                            $authorProfile['scopus_author_url']
                            ?? ''
                        )
                    )
                ),

                'researchgate_url' => trim(
                    (string) (
                        $payload['researchgate_url']
                        ?? (
                            $authorProfile['researchgate_url']
                            ?? ''
                        )
                    )
                ),

                'linkedin_url' => trim(
                    (string) (
                        $payload['linkedin_url']
                        ?? (
                            $authorProfile['linkedin_url']
                            ?? ''
                        )
                    )
                ),

                'personal_website_url' => trim(
                    (string) (
                        $payload['personal_website_url']
                        ?? (
                            $authorProfile['personal_website_url']
                            ?? ''
                        )
                    )
                ),

                'country' => trim(
                    (string) (
                        $payload['country']
                        ?? (
                            $authorProfile['country']
                            ?? ''
                        )
                    )
                ),

                'state' => trim(
                    (string) (
                        $payload['state']
                        ?? (
                            $authorProfile['state']
                            ?? ''
                        )
                    )
                ),

                'city' => trim(
                    (string) (
                        $payload['city']
                        ?? (
                            $authorProfile['city']
                            ?? ''
                        )
                    )
                ),

                'zipcode' => trim(
                    (string) (
                        $payload['zipcode']
                        ?? (
                            $authorProfile['zipcode']
                            ?? ''
                        )
                    )
                ),

                'address' => trim(
                    (string) (
                        $payload['address']
                        ?? (
                            $authorProfile['address']
                            ?? ''
                        )
                    )
                ),

                'publication_count' => (int) (
                    $payload['publication_count']
                    ?? $authorProfile['publication_count']
                ),

                'citation_count' => (int) (
                    $payload['citation_count']
                    ?? $authorProfile['citation_count']
                ),

                'h_index' => (int) (
                    $payload['h_index']
                    ?? $authorProfile['h_index']
                ),

                'is_featured' => (int) (
                    $payload['is_featured']
                    ?? $authorProfile['is_featured']
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $authorProfile['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $authorProfile['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->authorProfileModel->update(
                    $authorProfile['id'],
                    $data
                )
            ) {

                return $this->validationResponse(
                    $this->authorProfileModel->errors()
                );
            }

            return $this->successResponse(
                'Author profile updated successfully.',
                $this->authorProfileModel->find(
                    $authorProfile['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update author profile.'
            );
        }
    }

    /**
     * DELETE /author-profiles/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $authorProfile = $this->authorProfileModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $authorProfile) {

                return $this->notFoundResponse(
                    'Author profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $authorProfile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {

                return $ownershipCheck;
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->authorProfileModel->update(
                $authorProfile['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->authorProfileModel->delete(
                $authorProfile['id']
            );

            return $this->successResponse(
                'Author profile deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete author profile.'
            );
        }
    }
}