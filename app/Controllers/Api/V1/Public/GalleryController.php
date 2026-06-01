<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

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

            $referenceId = (int) 
            (
                $this->request->getGet(
                    'reference_id'
                ) ?? 0
            );

            $builder =
                $this->galleryModel
                    ->where(
                        'status',
                        'active'
                    );

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

            if ($referenceId > 0) {

                $builder->where(
                    'reference_id',
                    $referenceId
                );
            }

            $records =
                $builder
                    ->orderBy(
                        'sort_order',
                        'ASC'
                    )
                    ->orderBy(
                        'created_at',
                        'DESC'
                    )
                    ->paginate(
                        $perPage
                    );

            foreach (
                $records
                as &$record
            ) {

                $record['media_url'] =
                    base_url(
                        $record['media_path']
                    );

                $record['thumbnail_url'] =
                    ! empty(
                        $record['thumbnail_path']
                    )
                    ? base_url(
                        $record['thumbnail_path']
                    )
                    : null;
            }

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
    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $gallery =
                $this->galleryModel
                    ->where(
                        'uuid',
                        (string) $id
                    )
                    ->where(
                        'status',
                        'active'
                    )
                    ->first();

            if (! $gallery) {

                return $this->notFoundResponse(
                    'Gallery record not found.'
                );
            }

            $gallery['media_url'] =
                base_url(
                    $gallery['media_path']
                );

            $gallery['thumbnail_url'] =
                ! empty(
                    $gallery['thumbnail_path']
                )
                ? base_url(
                    $gallery['thumbnail_path']
                )
                : null;

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
}