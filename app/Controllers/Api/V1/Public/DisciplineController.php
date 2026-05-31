<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\DisciplineModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class DisciplineController extends BaseApiController
{
    protected DisciplineModel $disciplineModel;

    protected array $allowedSortFields = [
        'title',
        'sort_order',
        'created_at',
    ];

    public function __construct()
    {
        $this->disciplineModel = new DisciplineModel();
    }

    /**
     * GET /public/disciplines
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

            $parentUuid = trim(
                (string) (
                    $this->request->getGet('parent_uuid')
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

            $builder = $this->disciplineModel
                ->where(
                    'disciplines.status',
                    'active'
                )
                ->select([
                    'disciplines.id',
                    'disciplines.uuid',
                    'disciplines.title',
                    'disciplines.slug',
                    'disciplines.description',
                    'disciplines.parent_id',
                    'parent.uuid AS parent_uuid',
                    'parent.title AS parent_title',
                    'disciplines.sort_order',
                ])
                ->join(
                    'disciplines parent',
                    'parent.id = disciplines.parent_id',
                    'left'
                );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'disciplines.title',
                        $search
                    )
                    ->orLike(
                        'disciplines.slug',
                        $search
                    )
                    ->orLike(
                        'disciplines.description',
                        $search
                    )
                    ->groupEnd();
            }

            if ($parentUuid !== '') {

                $parent = $this->disciplineModel
                    ->findByUuid(
                        $parentUuid
                    );

                if (! $parent) {
                    return $this->notFoundResponse(
                        'Parent discipline not found.'
                    );
                }

                $builder->where(
                    'disciplines.parent_id',
                    $parent['id']
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Disciplines fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->disciplineModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->disciplineModel
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
                'Unable to fetch disciplines.'
            );
        }
    }

    /**
     * GET /public/disciplines/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $discipline = $this->disciplineModel
                ->where(
                    'disciplines.status',
                    'active'
                )
                ->select([
                    'disciplines.id',
                    'disciplines.uuid',
                    'disciplines.title',
                    'disciplines.slug',
                    'disciplines.description',
                    'disciplines.parent_id',
                    'parent.uuid AS parent_uuid',
                    'parent.title AS parent_title',
                    'disciplines.sort_order',
                    'disciplines.created_at',
                    'disciplines.updated_at',
                ])
                ->join(
                    'disciplines parent',
                    'parent.id = disciplines.parent_id',
                    'left'
                )
                ->where(
                    'disciplines.uuid',
                    (string) $id
                )
                ->first();

            if (! $discipline) {
                return $this->notFoundResponse(
                    'Discipline not found.'
                );
            }

            $children = $this->disciplineModel
                ->where(
                    'disciplines.status',
                    'active'
                )
                ->select([
                    'uuid',
                    'title',
                    'slug',
                    'sort_order',
                ])
                ->where(
                    'parent_id',
                    $discipline['id']
                )
                ->orderBy(
                    'sort_order',
                    'ASC'
                )
                ->findAll();

            $discipline['children'] = $children;

            return $this->successResponse(
                'Discipline fetched successfully.',
                $discipline
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch discipline.'
            );
        }
    }
}