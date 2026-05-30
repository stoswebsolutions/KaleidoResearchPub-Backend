<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Libraries\ManuscriptService;
use App\Models\ManuscriptEditorAssignmentModel;
use App\Models\ManuscriptModel;
use App\Models\ManuscriptPaymentModel;
use App\Models\ManuscriptPublicationModel;
use App\Models\ManuscriptReviewModel;
use App\Models\ManuscriptStatusLogModel;
use App\Models\EditorProfileModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ManuscriptController extends BaseApiController
{
    protected ManuscriptModel $manuscriptModel;

    protected ManuscriptReviewModel $reviewModel;

    protected ManuscriptPaymentModel $paymentModel;

    protected ManuscriptPublicationModel $publicationModel;

    protected ManuscriptStatusLogModel $statusLogModel;

    protected ManuscriptEditorAssignmentModel $assignmentModel;

    protected EditorProfileModel $editorProfileModel;

    protected ManuscriptService $manuscriptService;

    protected array $allowedSortFields = [

        'manuscript_id',

        'title',

        'current_status',

        'final_decision',

        'submitted_at',

        'created_at',
    ];

    public function __construct()
    {
        $this->manuscriptModel =
            new ManuscriptModel();

        $this->reviewModel =
            new ManuscriptReviewModel();

        $this->paymentModel =
            new ManuscriptPaymentModel();

        $this->publicationModel =
            new ManuscriptPublicationModel();

        $this->statusLogModel =
            new ManuscriptStatusLogModel();

        $this->assignmentModel =
            new ManuscriptEditorAssignmentModel();

        $this->editorProfileModel =
            new EditorProfileModel();

        $this->manuscriptService =
            new ManuscriptService();
    }

    /**
     * GET /admin/manuscripts
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

            $decision = trim(
                (string) (
                    $this->request->getGet(
                        'decision'
                    ) ?? ''
                )
            );

            $journalId = (int) (
                $this->request->getGet(
                    'journal_id'
                ) ?? 0
            );

            $sortBy = (string) (
                $this->request->getGet(
                    'sort_by'
                ) ?? 'created_at'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet(
                        'sort_direction'
                    ) ?? 'desc'
                )
            );

            if (
                ! in_array(
                    $sortBy,
                    $this->allowedSortFields,
                    true
                )
            ) {
                $sortBy = 'created_at';
            }

            if (
                ! in_array(
                    $sortDirection,
                    ['asc', 'desc'],
                    true
                )
            ) {
                $sortDirection = 'desc';
            }

            $builder = $this->manuscriptModel
                ->select([
                    'manuscripts.uuid',
                    'manuscripts.manuscript_id',
                    'manuscripts.title',
                    'manuscripts.corresponding_author_name',
                    'manuscripts.corresponding_author_email',
                    'manuscripts.current_status',
                    'manuscripts.final_decision',
                    'manuscripts.submitted_at',
                    'journals.title AS journal_title',
                    'article_types.title AS article_type_title',
                ])
                ->join(
                    'journals',
                    'journals.id = manuscripts.journal_id',
                    'left'
                )
                ->join(
                    'article_types',
                    'article_types.id = manuscripts.article_type_id',
                    'left'
                );

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'manuscripts.manuscript_id',
                        $search
                    )
                    ->orLike(
                        'manuscripts.title',
                        $search
                    )
                    ->orLike(
                        'manuscripts.corresponding_author_name',
                        $search
                    )
                    ->orLike(
                        'manuscripts.corresponding_author_email',
                        $search
                    )
                    ->groupEnd();
            }

            if ($status !== '') {

                $builder->where(
                    'manuscripts.current_status',
                    $status
                );
            }

            if ($decision !== '') {

                $builder->where(
                    'manuscripts.final_decision',
                    $decision
                );
            }

            if ($journalId > 0) {

                $builder->where(
                    'manuscripts.journal_id',
                    $journalId
                );
            }

            $records = $builder
                ->orderBy(
                    'manuscripts.' . $sortBy,
                    $sortDirection
                )
                ->paginate(
                    $perPage
                );

            return $this->successResponse(
                'Manuscripts fetched successfully.',
                [
                    'items' => $records,

                    'pagination' => [
                        'current_page' =>
                            $page,

                        'per_page' =>
                            $perPage,

                        'total' =>
                            $this->manuscriptModel
                                ->pager
                                ->getTotal(),

                        'last_page' =>
                            $this->manuscriptModel
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
                'Unable to fetch manuscripts.'
            );
        }
    }

    /**
     * GET /admin/manuscripts/{uuid}
     */
    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $manuscript =
                $this->manuscriptModel
                    ->select([
                        'manuscripts.*',

                        'journals.uuid AS journal_uuid',
                        'journals.title AS journal_title',

                        'article_types.uuid AS article_type_uuid',
                        'article_types.title AS article_type_title',

                        'disciplines.uuid AS disciplinary_uuid',
                        'disciplines.title AS disciplinary_title',
                    ])
                    ->join(
                        'journals',
                        'journals.id = manuscripts.journal_id',
                        'left'
                    )
                    ->join(
                        'article_types',
                        'article_types.id = manuscripts.article_type_id',
                        'left'
                    )
                    ->join(
                        'disciplines',
                        'disciplines.id = manuscripts.disciplinary_id',
                        'left'
                    )
                    ->where(
                        'manuscripts.uuid',
                        (string) $id
                    )
                    ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Manuscript not found.'
                );
            }

            $assignedEditors =
                $this->assignmentModel
                    ->getByManuscript(
                        (int) $manuscript['id']
                    );

            $review =
                $this->reviewModel
                    ->getByManuscript(
                        (int) $manuscript['id']
                    );

            $payment =
                $this->paymentModel
                    ->getByManuscript(
                        (int) $manuscript['id']
                    );

            $publication =
                $this->publicationModel
                    ->getByManuscript(
                        (int) $manuscript['id']
                    );

            return $this->successResponse(
                'Manuscript fetched successfully.',
                [

                    'manuscript' =>
                        $manuscript,

                    'assigned_editors' =>
                        $assignedEditors,

                    'review' =>
                        $review,

                    'payment' =>
                        $payment,

                    'publication' =>
                        $publication,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch manuscript.'
            );
        }
    }
        /**
     * GET /admin/manuscripts/{uuid}/timeline
     */
    public function timeline(
        $id = null
    ): ResponseInterface
    {
        try {

            $manuscript =
                $this->manuscriptModel
                    ->where(
                        'uuid',
                        (string) $id
                    )
                    ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Manuscript not found.'
                );
            }

            $timeline =
                $this->statusLogModel
                    ->getByManuscript(
                        (int) $manuscript['id']
                    );

            return $this->successResponse(
                'Timeline fetched successfully.',
                [
                    'manuscript_id' =>
                        $manuscript['manuscript_id'],

                    'timeline' =>
                        $timeline,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch timeline.'
            );
        }
    }

    /**
     * POST /admin/manuscripts/{uuid}/review
     */
    public function submitReview(
        $id = null
    ): ResponseInterface
    {
        try {

            $manuscript =
                $this->manuscriptModel
                    ->where(
                        'uuid',
                        (string) $id
                    )
                    ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Manuscript not found.'
                );
            }

            $authUser = service(
                'authUser'
            );

            $editorProfile =
                $this->editorProfileModel
                    ->where(
                        'profile_id',
                        $authUser->profileId
                    )
                    ->first();

            if (! $editorProfile) {

                return $this->forbiddenResponse(
                    'Editor profile not found.'
                );
            }

            /**
             * Check editor assignment.
             */
            $assignment =
                $this->assignmentModel
                    ->where(
                        'manuscript_id',
                        $manuscript['id']
                    )
                    ->where(
                        'editor_profile_id',
                        $editorProfile['id']
                    )
                    ->first();

            if (! $assignment) {

                return $this->forbiddenResponse(
                    'You are not assigned to this manuscript.'
                );
            }

            if (
                ! in_array(
                    $assignment['editor_role'],
                    [
                        'editor',
                        'editor_in_chief',
                    ],
                    true
                )
            ) {

                return $this->forbiddenResponse(
                    'Only assigned editors can submit reviews.'
                );
            }

            if (
                $this->reviewModel
                    ->reviewExists(
                        (int) $manuscript['id'],
                        (int) $authUser->profileId
                    )
            ) {

                return $this->errorResponse(
                    'Review already submitted.'
                );
            }

            $recommendation = trim(
                (string)
                $this->request->getPost(
                    'review_recommendation'
                )
            );

            if (
                ! in_array(
                    $recommendation,
                    [
                        'accepted',
                        'rejected',
                        'minor_revision',
                        'major_revision',
                    ],
                    true
                )
            ) {

                return $this->validationResponse([
                    'review_recommendation' =>
                        'Invalid recommendation.',
                ]);
            }

            $reviewFile =
                $this->request->getFile(
                    'review_file'
                );

            $reviewPath = null;

            if (
                $reviewFile &&
                $reviewFile->isValid()
            ) {

                $directory =
                    FCPATH .
                    'uploads/manuscripts/reviews/';

                if (
                    ! is_dir(
                        $directory
                    )
                ) {

                    mkdir(
                        $directory,
                        0755,
                        true
                    );
                }

                $fileName =
                    $reviewFile
                        ->getRandomName();

                $reviewFile->move(
                    $directory,
                    $fileName
                );

                $reviewPath =
                    'uploads/manuscripts/reviews/'
                    . $fileName;
            }

            $this->reviewModel->insert([

                'manuscript_id' =>
                    $manuscript['id'],

                'editor_profile_id' =>
                    $editorProfile['id'],

                'review_recommendation' =>
                    $recommendation,

                'comments' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'comments'
                        )
                    ),

                'review_file' =>
                    $reviewPath,

                'reviewed_at' =>
                    date(
                        'Y-m-d H:i:s'
                    ),
            ]);

            /**
             * Mark assignment completed.
             */
            $this->assignmentModel
                ->update(
                    $assignment['id'],
                    [
                        'status' =>
                            'completed',
                    ]
                );

            /**
             * Update manuscript status.
             */
            $this->manuscriptService
                ->updateStatus(
                    manuscriptId:
                        (int)
                        $manuscript['id'],

                    newStatus:
                        'review_completed',

                    remarks:
                        'Editor review submitted.',

                    changedBy:
                        (int)
                        $authUser->profileId,

                    isSystemGenerated:
                        false
                );

            return $this->successResponse(
                'Review submitted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to submit review.'
            );
        }
    }
        /**
     * POST /admin/manuscripts/{uuid}/decision
     */
    public function decision(
        $id = null
    ): ResponseInterface
    {
        try {

            $manuscript =
                $this->manuscriptModel
                    ->where(
                        'uuid',
                        (string) $id
                    )
                    ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Manuscript not found.'
                );
            }

            $authUser = service(
                'authUser'
            );

            $editorProfile =
                $this->editorProfileModel
                    ->where(
                        'profile_id',
                        $authUser->profileId
                    )
                    ->first();

            if (! $editorProfile) {

                return $this->forbiddenResponse(
                    'Editor profile not found.'
                );
            }

            /**
             * Chief Editor Validation.
             */
            $assignment =
                $this->assignmentModel
                    ->where(
                        'manuscript_id',
                        $manuscript['id']
                    )
                    ->where(
                        'editor_profile_id',
                        $editorProfile['id']
                    )
                    ->where(
                        'editor_role',
                        'editor_in_chief'
                    )
                    ->first();

            if (! $assignment) {

                return $this->forbiddenResponse(
                    'Only assigned chief editor can make final decision.'
                );
            }

            $decision = trim(
                (string)
                $this->request->getPost(
                    'decision'
                )
            );

            if (
                ! in_array(
                    $decision,
                    [
                        'accepted',
                        'rejected',
                        'minor_revision',
                        'major_revision',
                    ],
                    true
                )
            ) {

                return $this->validationResponse([
                    'decision' =>
                        'Invalid decision.',
                ]);
            }

            $remarks = trim(
                (string)
                $this->request->getPost(
                    'remarks'
                )
            );

            $rejectionReason = trim(
                (string)
                $this->request->getPost(
                    'rejection_reason'
                )
            );

            if (
                $decision !== 'rejected'
            ) {

                $rejectionReason = null;
            }

            if (
                $decision === 'rejected'
                && empty($rejectionReason)
            ) {

                return $this->validationResponse([
                    'rejection_reason' =>
                        'Rejection reason is required.',
                ]);
            }

            $this->manuscriptModel
            ->update(
                $manuscript['id'],
                [

                    'final_decision' =>
                        $decision,

                    'decision_remarks' =>
                        $remarks,

                    'rejection_reason' =>
                        $rejectionReason,

                    'decision_by' =>
                        $authUser->profileId,

                    'decision_at' =>
                        date('Y-m-d H:i:s'),
                ]
            );

            /**
             * Status Mapping.
             */
            $statusMap = [

                'accepted' =>
                    'accepted',

                'rejected' =>
                    'rejected',

                'minor_revision' =>
                    'revision_requested',

                'major_revision' =>
                    'revision_requested',
            ];

            $this->manuscriptService
                ->updateStatus(
                    manuscriptId:
                        (int)
                        $manuscript['id'],

                    newStatus:
                        $statusMap[
                            $decision
                        ],

                    remarks:
                        $remarks,

                    changedBy:
                        (int)
                        $authUser->profileId,

                    isSystemGenerated:
                        false
                );

            /**
             * Mark Assignment Completed.
             */
            $this->assignmentModel
                ->update(
                    $assignment['id'],
                    [
                        'status' =>
                            'completed',
                    ]
                );

            return $this->successResponse(
                'Decision submitted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to submit decision.'
            );
        }
    }

    /**
     * POST /admin/manuscripts/{uuid}/verify-payment
     */
    public function verifyPayment(
        $id = null
    ): ResponseInterface
    {
        try {

            $manuscript =
                $this->manuscriptModel
                    ->where(
                        'uuid',
                        (string) $id
                    )
                    ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Manuscript not found.'
                );
            }

            $payment =
                $this->paymentModel
                    ->getByManuscript(
                        (int)
                        $manuscript['id']
                    );

            if (! $payment) {

                return $this->notFoundResponse(
                    'Payment not found.'
                );
            }

            $paymentStatus = trim(
                (string)
                $this->request->getPost(
                    'payment_status'
                )
            );

            if (
                ! in_array(
                    $paymentStatus,
                    [
                        'approved',
                        'rejected',
                    ],
                    true
                )
            ) {

                return $this->validationResponse([
                    'payment_status' =>
                        'Invalid payment status.',
                ]);
            }

            $remarks = trim(
                (string)
                $this->request->getPost(
                    'remarks'
                )
            );

            if (
                $paymentStatus === 'rejected'
                && $remarks === ''
            ) {

                return $this->validationResponse([
                    'remarks' =>
                        'Remarks are required when payment is rejected.',
                ]);
            }

            $authUser = service(
                'authUser'
            );

            $this->paymentModel
                ->verifyPayment(
                    (int)
                    $payment['id'],

                    (int)
                    $authUser->profileId,

                    $paymentStatus,

                    $remarks
                );

            if (
                $paymentStatus
                === 'approved'
            ) {

                $this->manuscriptService
                    ->updateStatus(
                        manuscriptId:
                            (int)
                            $manuscript['id'],

                        newStatus:
                            'payment_verified',

                        remarks:
                            'Payment verified.',

                        changedBy:
                            (int)
                            $authUser->profileId,

                        isSystemGenerated:
                            false
                    );
            }

            if (
                $paymentStatus
                === 'rejected'
            ) {

                $this->manuscriptService
                    ->updateStatus(
                        manuscriptId:
                            (int)
                            $manuscript['id'],

                        newStatus:
                            'payment_rejected',

                        remarks:
                            $remarks,

                        changedBy:
                            (int)
                            $authUser->profileId,

                        isSystemGenerated:
                            false
                    );
            }

            return $this->successResponse(
                'Payment verification completed successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to verify payment.'
            );
        }
    }
        /**
     * POST /admin/manuscripts/{uuid}/publish
     */
    public function publish(
        $id = null
    ): ResponseInterface
    {
        try {

            $manuscript =
                $this->manuscriptModel
                    ->where(
                        'uuid',
                        (string) $id
                    )
                    ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Manuscript not found.'
                );
            }

            if (
                $manuscript['final_decision']
                !== 'accepted'
            ) {

                return $this->errorResponse(
                    'Only accepted manuscripts can be published.'
                );
            }

            if (
                $this->publicationModel
                    ->publicationExists(
                        (int) $manuscript['id']
                    )
            ) {

                return $this->errorResponse(
                    'Publication already exists.'
                );
            }

            $authUser = service(
                'authUser'
            );

            $doi = trim(
                (string)
                $this->request->getPost(
                    'doi'
                )
            );

            if (
                $doi === ''
            ) {

                return $this->validationResponse([
                    'doi' =>
                        'DOI is required.',
                ]);
            }

            $frequency = trim(
                (string)
                $this->request->getPost(
                    'frequency'
                )
            );

            if (
                ! in_array(
                    $frequency,
                    [
                        'monthly',
                        'quarterly',
                        'yearly',
                    ],
                    true
                )
            ) {

                return $this->validationResponse([
                    'frequency' =>
                        'Invalid frequency.',
                ]);
            }

            $data = [

                'manuscript_id' =>
                    $manuscript['id'],

                'page_start' =>
                    (int)
                    $this->request->getPost(
                        'page_start'
                    ),

                'page_end' =>
                    (int)
                    $this->request->getPost(
                        'page_end'
                    ),

                'volume_number' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'volume_number'
                        )
                    ),

                'issue_number' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'issue_number'
                        )
                    ),

                'published_by' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'published_by'
                        )
                    ),

                'frequency' =>
                    $frequency,

                'published_date' =>
                    $this->request->getPost(
                        'published_date'
                    ),

                'doi' =>
                    $doi,

                'article_url' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'article_url'
                        )
                    ),

                'status' =>
                    'published',

                'created_by' =>
                    $authUser->profileId,
            ];

            $publishedPdf =
                $this->request->getFile(
                    'published_pdf'
                );

            if (
                $publishedPdf &&
                $publishedPdf->isValid()
            ) {

                $directory =
                    FCPATH .
                    'uploads/manuscripts/publications/';

                if (
                    ! is_dir(
                        $directory
                    )
                ) {

                    mkdir(
                        $directory,
                        0755,
                        true
                    );
                }

                $fileName =
                    $publishedPdf
                        ->getRandomName();

                $publishedPdf->move(
                    $directory,
                    $fileName
                );

                $data['published_pdf'] =
                    'uploads/manuscripts/publications/'
                    . $fileName;
            }

            if (
                ! $this->publicationModel
                    ->insert(
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->publicationModel
                        ->errors()
                );
            }

            $this->manuscriptModel
            ->update(
                $manuscript['id'],
                [
                    'doi' => $doi,
                ]
            );

            $this->manuscriptService
                ->updateStatus(
                    manuscriptId:
                        (int)
                        $manuscript['id'],

                    newStatus:
                        'published',

                    remarks:
                        'Manuscript published successfully.',

                    changedBy:
                        (int)
                        $authUser->profileId,

                    isSystemGenerated:
                        false
                );

            return $this->successResponse(
                'Manuscript published successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to publish manuscript.'
            );
        }
    }
}