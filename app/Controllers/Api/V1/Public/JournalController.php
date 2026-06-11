<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\JournalModel;
use App\Models\JournalEditorModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class JournalController extends BaseApiController
{
    protected JournalModel $journalModel;
    protected JournalEditorModel $journalEditorModel;

    protected array $allowedSortFields = [
        'title',
        'impact_factor',
        'year_started',
        'created_at',
    ];

    public function __construct()
    {
        $this->journalModel = new JournalModel();

        $this->journalEditorModel = new JournalEditorModel();
    }

    /**
     * GET /public/journals
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

            $subjectArea = trim(
                (string) (
                    $this->request->getGet('subject_area')
                    ?? ''
                )
            );

            $frequency = trim(
                (string) (
                    $this->request->getGet('frequency')
                    ?? ''
                )
            );

            $publicationType = trim(
                (string) (
                    $this->request->getGet('publication_type')
                    ?? ''
                )
            );

            $isIndexed = $this->request->getGet(
                'is_indexed'
            );

            $sortBy = (string) (
                $this->request->getGet('sort_by')
                ?? 'title'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet('sort_direction')
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
                $sortBy = 'title';
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

            $builder = $this->journalModel
                ->active()
                ->select([
                    'id',
                    'uuid',
                    'title',
                    'short_title',
                    'slug',
                    'thumbnail',
                    'description',
                    'issn_print',
                    'issn_online',
                    'impact_factor',
                    'frequency',
                    'publication_type',
                    'subject_area',
                    'is_indexed',
                    'year_started',
                    'website_url',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'title',
                        $search
                    )
                    ->orLike(
                        'short_title',
                        $search
                    )
                    ->orLike(
                        'issn_print',
                        $search
                    )
                    ->orLike(
                        'issn_online',
                        $search
                    )
                    ->orLike(
                        'subject_area',
                        $search
                    )
                    ->groupEnd();
            }

            if ($subjectArea !== '') {

                $builder->where(
                    'subject_area',
                    $subjectArea
                );
            }

            if ($frequency !== '') {

                $builder->where(
                    'frequency',
                    $frequency
                );
            }

            if ($publicationType !== '') {

                $builder->where(
                    'publication_type',
                    $publicationType
                );
            }

            if (
                $isIndexed !== null
                && $isIndexed !== ''
            ) {
                $builder->where(
                    'is_indexed',
                    (int) $isIndexed
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Journals fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->journalModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->journalModel
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
                'Unable to fetch journals.'
            );
        }
    }

    /**
     * GET /public/journals/{uuid}
     */
    public function show( $id = null ): ResponseInterface
    {
        try {

            $journal = $this->journalModel
                ->active()
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $journal) {
                return $this->notFoundResponse(
                    'Journal not found.'
                );
            }

            /**
             * Editorial Board
             */
            $editorialBoard =
                db_connect()
                    ->table(
                        'journal_editors je'
                    )
                    ->select([
                        'je.editor_role',

                        'ep.uuid',
                        'ep.full_name',
                        'ep.designation',
                        'ep.organization_name',
                        'ep.country',
                        'ep.profile_image',
                        'ep.profile_slug',
                        'ep.orcid_id',
                    ])
                    ->join(
                        'editor_profiles ep',
                        'ep.id = je.editor_profile_id'
                    )
                    ->where(
                        'je.journal_id',
                        $journal['id']
                    )
                    ->where(
                        'je.status',
                        'active'
                    )
                    ->where(
                        'ep.status',
                        'active'
                    )
                    ->orderBy(
                        'je.sort_order',
                        'ASC'
                    )
                    ->orderBy(
                        'ep.full_name',
                        'ASC'
                    )
                    ->get()
                    ->getResultArray();

            $groupedEditors = [

                'editor_in_chief' => [],

                'editor' => [],

                'managing_editor' => [],

                'associate_editor' => [],

                'editorial_board_member' => [],

                'review_editor' => [],

                'guest_editor' => [],
            ];

            foreach (
                $editorialBoard
                as $editor
            ) {

                $role =
                    $editor['editor_role'];

                unset(
                    $editor['editor_role']
                );

                $groupedEditors[
                    $role
                ][] = $editor;
            }

            return $this->successResponse(
                'Journal fetched successfully.',
                [

                    'journal' =>
                        $journal,

                    'editorial_board' =>
                        $groupedEditors,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch journal.'
            );
        }
    }

}