<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\GalleryModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class GalleryController extends BaseApiController
{
    protected GalleryModel $galleryModel;

    protected array $allowedSortFields = [

        'title',

        'media_date',

        'sort_order',

        'created_at',
    ];
    public function __construct()
    {
        $this->galleryModel =
            new GalleryModel();
    }
    public function index(): ResponseInterface
    {
        try {

            $page = max(
                1,
                (int) (
                    $this->request->getGet(
                        'page'
                    ) ?? 1
                )
            );

            $perPage = min(
                100,
                max(
                    1,
                    (int) (
                        $this->request->getGet(
                            'per_page'
                        ) ?? 20
                    )
                )
            );

            $search = trim(
                (string) (
                    $this->request->getGet(
                        'search'
                    ) ?? ''
                )
            );

            $status = trim(
                (string) (
                    $this->request->getGet(
                        'status'
                    ) ?? ''
                )
            );

            $mediaType = trim(
                (string) (
                    $this->request->getGet(
                        'media_type'
                    ) ?? ''
                )
            );

            $referenceType = trim(
                (string) (
                    $this->request->getGet(
                        'reference_type'
                    ) ?? ''
                )
            );

            $sortBy = (string) (
                $this->request->getGet(
                    'sort_by'
                ) ?? 'sort_order'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet(
                        'sort_direction'
                    ) ?? 'asc'
                )
            );

            if (
                ! in_array(
                    $sortBy,
                    $this->allowedSortFields,
                    true
                )
            ) {

                $sortBy =
                    'sort_order';
            }

            if (
                ! in_array(
                    $sortDirection,
                    ['asc', 'desc'],
                    true
                )
            ) {

                $sortDirection =
                    'asc';
            }

            $builder =
                $this->galleryModel;

            if ($search !== '') {

                $builder
                    ->groupStart()
                    ->like(
                        'title',
                        $search
                    )
                    ->orLike(
                        'description',
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

            if ($mediaType !== '') {

                $builder->where(
                    'media_type',
                    $mediaType
                );
            }

            if ($referenceType !== '') {

                $builder->where(
                    'reference_type',
                    $referenceType
                );
            }

            $records =
                $builder
                    ->orderBy(
                        $sortBy,
                        $sortDirection
                    )
                    ->paginate(
                        $perPage
                    );

            return $this->successResponse(
                'Gallery records fetched successfully.',
                [

                    'items' =>
                        $records,

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $perPage,

                        'total' =>
                            $this->galleryModel
                                ->pager
                                ->getTotal(),

                        'last_page' =>
                            $this->galleryModel
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
                'Unable to fetch gallery records.'
            );
        }
    }
    public function create(): ResponseInterface
    {
        try {

            $payload =
                $this->getRequestData();

            $authUser =
                service('authUser');

            $data = [

                'reference_type' => trim(
                    (string) (
                        $payload['reference_type']
                        ?? ''
                    )
                ),

                'reference_id' => (
                    ! empty(
                        $payload['reference_id']
                    )
                )
                ? (int)
                    $payload['reference_id']
                : null,

                'media_type' => trim(
                    (string) (
                        $payload['media_type']
                        ?? ''
                    )
                ),

                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? ''
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
                ),

                'media_date' => (
                    ! empty(
                        $payload['media_date']
                    )
                )
                ? (string)
                    $payload['media_date']
                : null,

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

                'created_by' =>
                    $authUser->profileId,
            ];

            /**
             * Media Upload
             */
            $data['media_path'] =
                $this->uploadFile(
                    'media_file',
                    'uploads/gallery',
                    [
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'webp',
                        'mp4',
                        'avi',
                        'mov',
                        'pdf',
                        'doc',
                        'docx'
                    ],
                    10240
                );

            /**
             * Thumbnail Upload
             */
            $data['thumbnail_path'] =
                $this->uploadFile(
                    'thumbnail_file',
                    'uploads/gallery/thumbnails',
                    [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp'
                    ]
                );

            if (
                empty(
                    $data['media_path']
                )
            ) {

                return $this->validationResponse([
                    'media_file' =>
                        'Media file is required.'
                ]);
            }

            if (
                ! $this->galleryModel
                    ->insert(
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->galleryModel
                        ->errors()
                );
            }

            $gallery =
                $this->galleryModel
                    ->find(
                        $this->galleryModel
                            ->getInsertID()
                    );

            return $this->successResponse(
                'Gallery record created successfully.',
                $gallery,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create gallery record.'
            );
        }
    }
    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $gallery =
                $this->galleryModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $gallery) {

                return $this->notFoundResponse(
                    'Gallery record not found.'
                );
            }

            return $this->successResponse(
                'Gallery record fetched successfully.',
                $gallery
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch gallery record.'
            );
        }
    }
    public function update(
        $id = null
    ): ResponseInterface
    {
        try {

            $gallery =
                $this->galleryModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $gallery) {

                return $this->notFoundResponse(
                    'Gallery record not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $authUser =
                service('authUser');

            $data = [

                'reference_type' => trim(
                    (string) (
                        $payload['reference_type']
                        ?? $gallery['reference_type']
                    )
                ),

                'reference_id' => isset(
                    $payload['reference_id']
                )
                    ? (
                        ! empty(
                            $payload['reference_id']
                        )
                        ? (int)
                            $payload['reference_id']
                        : null
                    )
                    : $gallery['reference_id'],

                'media_type' => trim(
                    (string) (
                        $payload['media_type']
                        ?? $gallery['media_type']
                    )
                ),

                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? $gallery['title']
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? (
                            $gallery['description']
                            ?? ''
                        )
                    )
                ),

                'media_date' => (
                    $payload['media_date']
                    ?? $gallery['media_date']
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $gallery['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $gallery['status']
                    )
                ),

                'updated_by' =>
                    $authUser->profileId,
            ];

            /**
             * Replace Media File
             */
            $mediaPath =
                $this->uploadFile(
                    'media_file',
                    'uploads/gallery',
                    [
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'webp',
                        'mp4',
                        'avi',
                        'mov',
                        'pdf',
                        'doc',
                        'docx'
                    ],
                    10240
                );

            if ($mediaPath !== null) {

                $this->deleteFile(
                    $gallery['media_path']
                );

                $data['media_path'] =
                    $mediaPath;
            }

            /**
             * Replace Thumbnail
             */
            $thumbnailPath =
                $this->uploadFile(
                    'thumbnail_file',
                    'uploads/gallery/thumbnails',
                    [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp'
                    ]
                );

            if (
                $thumbnailPath !== null
            ) {

                $this->deleteFile(
                    $gallery['thumbnail_path']
                );

                $data['thumbnail_path'] =
                    $thumbnailPath;
            }

            if (
                ! $this->galleryModel
                    ->update(
                        $gallery['id'],
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->galleryModel
                        ->errors()
                );
            }

            return $this->successResponse(
                'Gallery record updated successfully.',
                $this->galleryModel
                    ->find(
                        $gallery['id']
                    )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update gallery record.'
            );
        }
    }
    public function delete(
        $id = null
    ): ResponseInterface
    {
        try {

            $gallery =
                $this->galleryModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $gallery) {

                return $this->notFoundResponse(
                    'Gallery record not found.'
                );
            }

            $authUser =
                service('authUser');

            /**
             * Audit Update
             */
            $this->galleryModel
                ->update(
                    $gallery['id'],
                    [
                        'deleted_by' =>
                            $authUser->profileId,
                    ]
                );

            /**
             * Delete Media File
             */
            if (
                ! empty(
                    $gallery['media_path']
                )
            ) {

                $this->deleteFile(
                    $gallery['media_path']
                );
            }

            /**
             * Delete Thumbnail
             */
            if (
                ! empty(
                    $gallery['thumbnail_path']
                )
            ) {

                $this->deleteFile(
                    $gallery['thumbnail_path']
                );
            }

            if (
                ! $this->galleryModel
                    ->delete(
                        $gallery['id']
                    )
            ) {

                return $this->errorResponse(
                    'Unable to delete gallery record.'
                );
            }

            return $this->successResponse(
                'Gallery record deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete gallery record.'
            );
        }
    }
}