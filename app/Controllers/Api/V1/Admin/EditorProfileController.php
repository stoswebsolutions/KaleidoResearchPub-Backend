<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\EditorProfileModel;
use App\Models\ProfileModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class EditorProfileController extends BaseApiController
{
    protected EditorProfileModel $editorProfileModel;

    protected ProfileModel $profileModel;

    protected array $allowedSortFields = [
        'full_name',
        'editor_type',
        'organization_name',
        'country',
        'sort_order',
        'is_featured',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->editorProfileModel = new EditorProfileModel();

        $this->profileModel = new ProfileModel();
    }

        /**
     * GET /editor-profiles
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

            $builder = $this->editorProfileModel
                ->select([
                    'editor_profiles.id',
                    'editor_profiles.uuid',
                    'editor_profiles.editor_type',
                    'editor_profiles.full_name',
                    'editor_profiles.designation',
                    'editor_profiles.organization_name',
                    'editor_profiles.country',
                    'editor_profiles.profile_image',
                    'editor_profiles.profile_slug',
                    'editor_profiles.is_featured',
                    'editor_profiles.sort_order',
                    'editor_profiles.status',
                    'editor_profiles.created_at',

                    'profiles.uuid AS profile_uuid',
                    'profiles.email AS profile_email',
                ])
                ->join(
                    'profiles',
                    'profiles.id = editor_profiles.profile_id',
                    'left'
                );

            $builder = $this->applyOwnershipFilter(
                $builder,
                'editor_profiles'
            );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'editor_profiles.full_name',
                        $search
                    )
                    ->orLike(
                        'editor_profiles.designation',
                        $search
                    )
                    ->orLike(
                        'editor_profiles.organization_name',
                        $search
                    )
                    ->orLike(
                        'editor_profiles.country',
                        $search
                    )
                    ->orLike(
                        'editor_profiles.specialization',
                        $search
                    )
                    ->groupEnd();
            }

            if ($editorType !== '') {

                $builder->where(
                    'editor_profiles.editor_type',
                    $editorType
                );
            }

            if ($status !== '') {

                $builder->where(
                    'editor_profiles.status',
                    $status
                );
            }

            if (
                $featured !== null
                && $featured !== ''
            ) {

                $builder->where(
                    'editor_profiles.is_featured',
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
                    'editor_profiles.' . $sortBy,
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
     * GET /editor-profiles/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $editorProfile = $this->editorProfileModel
                ->select([
                    'editor_profiles.id',

                    'editor_profiles.uuid',

                    'editor_profiles.profile_id',

                    'editor_profiles.editor_type',

                    'editor_profiles.full_name',

                    'editor_profiles.designation',
                    'editor_profiles.department',
                    'editor_profiles.organization_name',
                    'editor_profiles.country',

                    'editor_profiles.qualification',

                    'editor_profiles.specialization',
                    'editor_profiles.research_interests',

                    'editor_profiles.experience_years',

                    'editor_profiles.bio',

                    'editor_profiles.profile_image',

                    'editor_profiles.profile_slug',

                    'editor_profiles.orcid_id',

                    'editor_profiles.google_scholar_url',
                    'editor_profiles.scopus_author_url',
                    'editor_profiles.researchgate_url',
                    'editor_profiles.linkedin_url',
                    'editor_profiles.personal_website_url',

                    'editor_profiles.sort_order',

                    'editor_profiles.is_featured',

                    'editor_profiles.status',

                    'editor_profiles.created_by',
                    'editor_profiles.updated_by',

                    'editor_profiles.created_at',
                    'editor_profiles.updated_at',

                    'profiles.uuid AS profile_uuid',
                    'profiles.full_name AS profile_name',
                    'profiles.email AS profile_email',
                    'profiles.phone AS profile_phone',
                ])
                ->join(
                    'profiles',
                    'profiles.id = editor_profiles.profile_id',
                    'left'
                )
                ->where(
                    'editor_profiles.uuid',
                    (string) $id
                )
                ->first();

            if (! $editorProfile) {

                return $this->notFoundResponse(
                    'Editor profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $editorProfile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {
                return $ownershipCheck;
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

        /**
     * POST /editor-profiles
     */
    public function create(): ResponseInterface
    {
        try {

            $payload =
                $this->getRequestData();

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

                'editor_type' => trim(
                    (string) (
                        $payload['editor_type']
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

                'department' => trim(
                    (string) (
                        $payload['department']
                        ?? ''
                    )
                ),

                'organization_name' => trim(
                    (string) (
                        $payload['organization_name']
                        ?? ''
                    )
                ),

                'country' => trim(
                    (string) (
                        $payload['country']
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

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? 0
                ),

                'is_featured' => (int) (
                    $payload['is_featured']
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
            $data['profile_image'] =
                $this->uploadFile(
                    'profile_image',
                    'uploads/editor',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if (
                empty(
                    $data['profile_image']
                )
            ) {

                return $this->validationResponse([
                    'profile_image' =>
                        'Media file is required.'
                ]);
            }

            if (
                ! $this->editorProfileModel->insert(
                    $data
                )
            ) {

                return $this->validationResponse(
                    $this->editorProfileModel->errors()
                );
            }

            $editorProfile = $this->editorProfileModel
                ->find(
                    $this->editorProfileModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Editor profile created successfully.',
                $editorProfile,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create editor profile.'
            );
        }
    }

        /**
     * PUT /editor-profiles/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $editorProfile = $this->editorProfileModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $editorProfile) {

                return $this->notFoundResponse(
                    'Editor profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $editorProfile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {
                return $ownershipCheck;
            }

            $payload =
                $this->getRequestData();

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
                'id' => $editorProfile['id'],

                'profile_id' => $profile['id']
                    ?? $editorProfile['profile_id'],

                'editor_type' => trim(
                    (string) (
                        $payload['editor_type']
                        ?? $editorProfile['editor_type']
                    )
                ),

                'full_name' => trim(
                    (string) (
                        $payload['full_name']
                        ?? $editorProfile['full_name']
                    )
                ),

                'designation' => trim(
                    (string) (
                        $payload['designation']
                        ?? (
                            $editorProfile['designation']
                            ?? ''
                        )
                    )
                ),

                'department' => trim(
                    (string) (
                        $payload['department']
                        ?? (
                            $editorProfile['department']
                            ?? ''
                        )
                    )
                ),

                'organization_name' => trim(
                    (string) (
                        $payload['organization_name']
                        ?? (
                            $editorProfile['organization_name']
                            ?? ''
                        )
                    )
                ),

                'country' => trim(
                    (string) (
                        $payload['country']
                        ?? (
                            $editorProfile['country']
                            ?? ''
                        )
                    )
                ),

                'qualification' => trim(
                    (string) (
                        $payload['qualification']
                        ?? (
                            $editorProfile['qualification']
                            ?? ''
                        )
                    )
                ),

                'specialization' => trim(
                    (string) (
                        $payload['specialization']
                        ?? (
                            $editorProfile['specialization']
                            ?? ''
                        )
                    )
                ),

                'research_interests' => trim(
                    (string) (
                        $payload['research_interests']
                        ?? (
                            $editorProfile['research_interests']
                            ?? ''
                        )
                    )
                ),

                'experience_years' => (int) (
                    $payload['experience_years']
                    ?? $editorProfile['experience_years']
                ),

                'bio' => trim(
                    (string) (
                        $payload['bio']
                        ?? (
                            $editorProfile['bio']
                            ?? ''
                        )
                    )
                ),

                'profile_slug' => trim(
                    (string) (
                        $payload['profile_slug']
                        ?? $editorProfile['profile_slug']
                    )
                ),

                'orcid_id' => trim(
                    (string) (
                        $payload['orcid_id']
                        ?? (
                            $editorProfile['orcid_id']
                            ?? ''
                        )
                    )
                ),

                'google_scholar_url' => trim(
                    (string) (
                        $payload['google_scholar_url']
                        ?? (
                            $editorProfile['google_scholar_url']
                            ?? ''
                        )
                    )
                ),

                'scopus_author_url' => trim(
                    (string) (
                        $payload['scopus_author_url']
                        ?? (
                            $editorProfile['scopus_author_url']
                            ?? ''
                        )
                    )
                ),

                'researchgate_url' => trim(
                    (string) (
                        $payload['researchgate_url']
                        ?? (
                            $editorProfile['researchgate_url']
                            ?? ''
                        )
                    )
                ),

                'linkedin_url' => trim(
                    (string) (
                        $payload['linkedin_url']
                        ?? (
                            $editorProfile['linkedin_url']
                            ?? ''
                        )
                    )
                ),

                'personal_website_url' => trim(
                    (string) (
                        $payload['personal_website_url']
                        ?? (
                            $editorProfile['personal_website_url']
                            ?? ''
                        )
                    )
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $editorProfile['sort_order']
                ),

                'is_featured' => (int) (
                    $payload['is_featured']
                    ?? $editorProfile['is_featured']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $editorProfile['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            /**
             * Media Upload
             */
            $profile_image =
                $this->uploadFile(
                    'profile_image',
                    'uploads/editor',
                    [
                        'jpg',
                        'jpeg',
                        'png'
                    ],
                    10240
                );

            if ($profile_image !== null) {

                $this->deleteFile(
                    $editorProfile['profile_image']
                );

                $data['profile_image'] =
                    $profile_image;
            }

            if (
                ! $this->editorProfileModel->update(
                    $editorProfile['id'],
                    $data
                )
            ) {

                return $this->validationResponse(
                    $this->editorProfileModel->errors()
                );
            }

            return $this->successResponse(
                'Editor profile updated successfully.',
                $this->editorProfileModel->find(
                    $editorProfile['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update editor profile.'
            );
        }
    }

    /**
     * DELETE /editor-profiles/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $editorProfile = $this->editorProfileModel
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $editorProfile) {

                return $this->notFoundResponse(
                    'Editor profile not found.'
                );
            }

            $ownershipCheck = $this->validateOwnership(
                $editorProfile
            );

            if (
                $ownershipCheck
                instanceof ResponseInterface
            ) {
                return $ownershipCheck;
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->editorProfileModel->update(
                $editorProfile['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->editorProfileModel->delete(
                $editorProfile['id']
            );

            return $this->successResponse(
                'Editor profile deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete editor profile.'
            );
        }
    }
}