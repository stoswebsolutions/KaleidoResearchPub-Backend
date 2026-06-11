<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\EditorProfileModel;
use App\Models\JournalEditorModel;
use App\Models\JournalModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class JournalEditorController extends BaseApiController
{
    protected JournalEditorModel $journalEditorModel;

    protected JournalModel $journalModel;

    protected EditorProfileModel $editorProfileModel;

    public function __construct()
    {
        $this->journalEditorModel =
            new JournalEditorModel();

        $this->journalModel =
            new JournalModel();

        $this->editorProfileModel =
            new EditorProfileModel();
    }

    /**
     * GET /api/v1/admin/journal-editors
     */
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

            $journalUuid = trim(
                (string) (
                    $this->request->getGet(
                        'journal_uuid'
                    ) ?? ''
                )
            );

            $editorUuid = trim(
                (string) (
                    $this->request->getGet(
                        'editor_uuid'
                    ) ?? ''
                )
            );

            $editorRole = trim(
                (string) (
                    $this->request->getGet(
                        'editor_role'
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

            $builder = db_connect()
                ->table(
                    'journal_editors je'
                )
                ->select([
                    'je.*',

                    'j.uuid AS journal_uuid',
                    'j.title AS journal_title',

                    'ep.uuid AS editor_uuid',
                    'ep.full_name',
                    'ep.designation',
                    'ep.organization_name',
                ])
                ->join(
                    'journals j',
                    'j.id = je.journal_id'
                )
                ->join(
                    'editor_profiles ep',
                    'ep.id = je.editor_profile_id'
                );

            if (
                $journalUuid !== ''
            ) {

                $builder->where(
                    'j.uuid',
                    $journalUuid
                );
            }

            if (
                $editorUuid !== ''
            ) {

                $builder->where(
                    'ep.uuid',
                    $editorUuid
                );
            }

            if (
                $editorRole !== ''
            ) {

                $builder->where(
                    'je.editor_role',
                    $editorRole
                );
            }

            if (
                $status !== ''
            ) {

                $builder->where(
                    'je.status',
                    $status
                );
            }

            $records = $builder
                ->orderBy(
                    'je.sort_order',
                    'ASC'
                )
                ->orderBy(
                    'ep.full_name',
                    'ASC'
                )
                ->get(
                    $perPage,
                    ($page - 1)
                    * $perPage
                )
                ->getResultArray();

            return $this->successResponse(
                'Journal editor assignments fetched successfully.',
                [
                    'items' => $records,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch journal editor assignments.'
            );
        }
    }

    /**
     * POST /api/v1/admin/journal-editors
     */
    public function create(): ResponseInterface
    {
        try {

            $payload =
                $this->getRequestData();

            $journalUuid = trim(
                (string) (
                    $payload['journal_uuid']
                    ?? ''
                )
            );

            $editorUuid = trim(
                (string) (
                    $payload['editor_profile_uuid']
                    ?? ''
                )
            );

            $journal =
                $this->journalModel
                    ->findByUuid(
                        $journalUuid
                    );

            if (! $journal) {

                return $this->validationResponse([
                    'journal_uuid' =>
                        'Journal not found.',
                ]);
            }

            $editorProfile =
                $this->editorProfileModel
                    ->findByUuid(
                        $editorUuid
                    );

            if (! $editorProfile) {

                return $this->validationResponse([
                    'editor_profile_uuid' =>
                        'Editor profile not found.',
                ]);
            }

            /**
             * Prevent Duplicate Assignment
             */
            if (
                $this->journalEditorModel
                    ->assignmentExists(
                        (int)
                        $journal['id'],
                        (int)
                        $editorProfile['id']
                    )
            ) {

                return $this->errorResponse(
                    'Editor already assigned to this journal.'
                );
            }

            $authUser =
                service('authUser');

            $user =
                $authUser->profile;

            $data = [

                'journal_id' =>
                    $journal['id'],

                'editor_profile_id' =>
                    $editorProfile['id'],

                'editor_role' => trim(
                    (string) (
                        $payload['editor_role']
                        ?? 'editorial_board_member'
                    )
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

                'created_by' =>
                    $user['id'],
            ];

            if (
                ! $this->journalEditorModel
                    ->insert(
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->journalEditorModel
                        ->errors()
                );
            }

            $assignment =
                $this->journalEditorModel
                    ->find(
                        $this->journalEditorModel
                            ->getInsertID()
                    );

            return $this->successResponse(
                'Journal editor assigned successfully.',
                $assignment,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to assign editor to journal.'
            );
        }
    }

    /**
     * GET /api/v1/admin/journal-editors/{uuid}
     */
    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $assignment =
                db_connect()
                    ->table(
                        'journal_editors je'
                    )
                    ->select([
                        'je.*',

                        'j.uuid AS journal_uuid',
                        'j.title AS journal_title',
                        'j.short_title',

                        'ep.uuid AS editor_profile_uuid',
                        'ep.full_name',
                        'ep.designation',
                        'ep.organization_name',
                        'ep.country',
                        'ep.profile_image',
                        'ep.profile_slug',
                    ])
                    ->join(
                        'journals j',
                        'j.id = je.journal_id'
                    )
                    ->join(
                        'editor_profiles ep',
                        'ep.id = je.editor_profile_id'
                    )
                    ->where(
                        'je.uuid',
                        (string) $id
                    )
                    ->get()
                    ->getRowArray();

            if (! $assignment) {

                return $this->notFoundResponse(
                    'Journal editor assignment not found.'
                );
            }

            return $this->successResponse(
                'Journal editor assignment fetched successfully.',
                $assignment
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch journal editor assignment.'
            );
        }
    }

    /**
     * PUT /api/v1/admin/journal-editors/{uuid}
     */
    public function update(
        $id = null
    ): ResponseInterface
    {
        try {

            $assignment =
                $this->journalEditorModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $assignment) {

                return $this->notFoundResponse(
                    'Journal editor assignment not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $journalUuid = trim(
                (string) (
                    $payload['journal_uuid']
                    ?? ''
                )
            );

            $editorUuid = trim(
                (string) (
                    $payload['editor_profile_uuid']
                    ?? ''
                )
            );

            $journalId =
                $assignment['journal_id'];

            $editorProfileId =
                $assignment['editor_profile_id'];

            /**
             * Journal Change
             */
            if ($journalUuid !== '') {

                $journal =
                    $this->journalModel
                        ->findByUuid(
                            $journalUuid
                        );

                if (! $journal) {

                    return $this->validationResponse([
                        'journal_uuid' =>
                            'Journal not found.',
                    ]);
                }

                $journalId =
                    $journal['id'];
            }

            /**
             * Editor Change
             */
            if ($editorUuid !== '') {

                $editorProfile =
                    $this->editorProfileModel
                        ->findByUuid(
                            $editorUuid
                        );

                if (! $editorProfile) {

                    return $this->validationResponse([
                        'editor_profile_uuid' =>
                            'Editor profile not found.',
                    ]);
                }

                $editorProfileId =
                    $editorProfile['id'];
            }

            /**
             * Duplicate Check
             */
            $duplicate =
                $this->journalEditorModel
                    ->where(
                        'journal_id',
                        $journalId
                    )
                    ->where(
                        'editor_profile_id',
                        $editorProfileId
                    )
                    ->where(
                        'id !=',
                        $assignment['id']
                    )
                    ->first();

            if ($duplicate) {

                return $this->errorResponse(
                    'Editor is already assigned to this journal.'
                );
            }

            $authUser =
                service('authUser');

            $user =
                $authUser->profile;

            $data = [

                'journal_id' =>
                    $journalId,

                'editor_profile_id' =>
                    $editorProfileId,

                'editor_role' => trim(
                    (string) (
                        $payload['editor_role']
                        ?? $assignment['editor_role']
                    )
                ),

                'sort_order' => (int) (
                    $payload['sort_order']
                    ?? $assignment['sort_order']
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $assignment['status']
                    )
                ),

                'updated_by' =>
                    $user['id'],
            ];

            if (
                ! $this->journalEditorModel
                    ->update(
                        $assignment['id'],
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->journalEditorModel
                        ->errors()
                );
            }

            return $this->successResponse(
                'Journal editor assignment updated successfully.',
                $this->journalEditorModel
                    ->find(
                        $assignment['id']
                    )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update journal editor assignment.'
            );
        }
    }

    /**
     * DELETE /api/v1/admin/journal-editors/{uuid}
     */
    public function delete(
        $id = null
    ): ResponseInterface
    {
        try {

            $assignment =
                $this->journalEditorModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $assignment) {

                return $this->notFoundResponse(
                    'Journal editor assignment not found.'
                );
            }

            $authUser =
                service('authUser');

            $user =
                $authUser->profile;

            /**
             * Audit Update
             */
            $this->journalEditorModel
                ->update(
                    $assignment['id'],
                    [
                        'deleted_by' =>
                            $user['id'],
                    ]
                );

            if (
                ! $this->journalEditorModel
                    ->delete(
                        $assignment['id']
                    )
            ) {

                return $this->errorResponse(
                    'Unable to delete journal editor assignment.'
                );
            }

            return $this->successResponse(
                'Journal editor assignment deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete journal editor assignment.'
            );
        }
    }

}