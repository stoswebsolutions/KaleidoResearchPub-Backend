<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Libraries\ManuscriptService;
use App\Libraries\JwtLibrary;
use App\Models\ArticleTypeModel;
use App\Models\DisciplineModel;
use App\Models\JournalModel;
use App\Models\ManuscriptCoAuthorModel;
use App\Models\ManuscriptKeywordModel;
use App\Models\ManuscriptModel;
use App\Models\ManuscriptReferenceModel;
use App\Models\ManuscriptPaymentModel;
use App\Models\ManuscriptTrackingOtpModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ManuscriptController extends BaseApiController
{
    protected ManuscriptModel $manuscriptModel;

    protected ManuscriptCoAuthorModel $coAuthorModel;

    protected ManuscriptKeywordModel $keywordModel;

    protected ManuscriptReferenceModel $referenceModel;

    protected JournalModel $journalModel;

    protected ArticleTypeModel $articleTypeModel;

    protected DisciplineModel $disciplineModel;

    protected ManuscriptService $manuscriptService;

    protected ManuscriptTrackingOtpModel $trackingOtpModel;

    protected ManuscriptPaymentModel $paymentModel;

    protected JwtLibrary $jwtLibrary;

    public function __construct()
    {
        $this->manuscriptModel =
            new ManuscriptModel();

        $this->coAuthorModel =
            new ManuscriptCoAuthorModel();

        $this->keywordModel =
            new ManuscriptKeywordModel();

        $this->referenceModel =
            new ManuscriptReferenceModel();

        $this->journalModel =
            new JournalModel();

        $this->articleTypeModel =
            new ArticleTypeModel();

        $this->disciplineModel =
            new DisciplineModel();

        $this->manuscriptService =
            new ManuscriptService();

        $this->trackingOtpModel =
            new ManuscriptTrackingOtpModel();

        $this->paymentModel =
            new ManuscriptPaymentModel();
        
        $this->jwtLibrary          = new JwtLibrary();
    }

    /**
     * POST /api/v1/public/manuscripts
     */
    public function submit(): ResponseInterface
    {
        try {

            $journalId = (int)
                $this->request->getPost(
                    'journal_id'
                );

            $articleTypeId = (int)
                $this->request->getPost(
                    'article_type_id'
                );

            $disciplinaryId = (int)
                $this->request->getPost(
                    'disciplinary_id'
                );

            $journal = $this->journalModel
                ->where(
                    'id',
                    $journalId
                )
                ->where(
                    'status',
                    'active'
                )
                ->first();

            if (! $journal) {

                return $this->validationResponse([
                    'journal_id' =>
                        'Invalid journal selected.',
                ]);
            }

            $articleType =
                $this->articleTypeModel
                    ->where(
                        'id',
                        $articleTypeId
                    )
                    ->where(
                        'status',
                        1
                    )
                    ->first();

            if (! $articleType) {

                return $this->validationResponse([
                    'article_type_id' =>
                        'Invalid article type selected.',
                ]);
            }

            $disciplinary =
                $this->disciplineModel
                    ->where(
                        'id',
                        $disciplinaryId
                    )
                    ->where(
                        'status',
                        'active'
                    )
                    ->first();

            if (! $disciplinary) {

                return $this->validationResponse([
                    'disciplinary_id' =>
                        'Invalid disciplinary selected.',
                ]);
            }

            $paperFile =
                $this->request->getFile(
                    'paper_file'
                );

            $abstractFile =
                $this->request->getFile(
                    'abstract_file'
                );

            if (
                ! $paperFile ||
                ! $paperFile->isValid()
            ) {

                return $this->validationResponse([
                    'paper_file' =>
                        'Paper file is required.',
                ]);
            }

            $paperExtension =
                strtolower(
                    $paperFile->getExtension()
                );

            if (
                ! in_array(
                    $paperExtension,
                    ['doc', 'docx'],
                    true
                )
            ) {

                return $this->validationResponse([
                    'paper_file' =>
                        'Only DOC and DOCX files are allowed.',
                ]);
            }

            if (
                $abstractFile &&
                $abstractFile->isValid()
            ) {

                $abstractExtension =
                    strtolower(
                        $abstractFile->getExtension()
                    );

                if (
                    ! in_array(
                        $abstractExtension,
                        ['doc', 'docx'],
                        true
                    )
                ) {

                    return $this->validationResponse([
                        'abstract_file' =>
                            'Only DOC and DOCX files are allowed.',
                    ]);
                }
            }

            $db = db_connect();

            $db->transBegin();
                        $paperDirectory =
                FCPATH .
                'uploads/manuscripts/papers/';

            if (
                ! is_dir(
                    $paperDirectory
                )
            ) {
                mkdir(
                    $paperDirectory,
                    0755,
                    true
                );
            }

            $paperName =
                $paperFile->getRandomName();

            $paperFile->move(
                $paperDirectory,
                $paperName
            );

            $paperPath =
                'uploads/manuscripts/papers/'
                . $paperName;

            $abstractPath = null;

            if (
                $abstractFile &&
                $abstractFile->isValid()
            ) {

                $abstractDirectory =
                    FCPATH .
                    'uploads/manuscripts/abstracts/';

                if (
                    ! is_dir(
                        $abstractDirectory
                    )
                ) {
                    mkdir(
                        $abstractDirectory,
                        0755,
                        true
                    );
                }

                $abstractName =
                    $abstractFile
                        ->getRandomName();

                $abstractFile->move(
                    $abstractDirectory,
                    $abstractName
                );

                $abstractPath =
                    'uploads/manuscripts/abstracts/'
                    . $abstractName;
            }

            $manuscriptId =
                $this->manuscriptService
                    ->generateManuscriptId(
                        (int) $journal['id']
                    );

            $profileId = null;

            $submissionSource = 'guest';

            try {

                $jwtLibrary = new JwtLibrary();

                $token = $jwtLibrary->getBearerToken(
                    $this->request->getHeaderLine(
                        'Authorization'
                    )
                );

                if (! empty($token)) {

                    $payload =
                        $jwtLibrary->decode(
                            $token
                        );

                    if (
                        isset(
                            $payload->profile_id
                        )
                    ) {

                        $profileId =
                            (int)
                            $payload->profile_id;

                        $submissionSource =
                            'registered';
                    }
                }

            } catch (\Throwable $e) {

                /**
                 * Invalid token.
                 * Continue as guest.
                 */
            }

            $manuscriptData = [

                'manuscript_id' =>
                    $manuscriptId,

                'profile_id' =>
                    $profileId,

                'journal_id' =>
                    $journal['id'],

                'article_type_id' =>
                    $articleType['id'],

                'disciplinary_id' =>
                    $disciplinary['id'],

                'corresponding_author_name' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'corresponding_author_name'
                        )
                    ),

                'corresponding_author_email' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'corresponding_author_email'
                        )
                    ),

                'corresponding_author_phone' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'corresponding_author_phone'
                        )
                    ),

                'title' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'title'
                        )
                    ),

                'abstract' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'abstract'
                        )
                    ),

                'university_name' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'university_name'
                        )
                    ),

                'country' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'country'
                        )
                    ),

                'state' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'state'
                        )
                    ),

                'city' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'city'
                        )
                    ),

                'pincode' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'pincode'
                        )
                    ),

                'landmark' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'landmark'
                        )
                    ),

                'paper_file' =>
                    $paperPath,

                'abstract_file' =>
                    $abstractPath,

                'submission_source' =>
                    $submissionSource,

                'current_status' =>
                    'submitted',

                'final_decision' =>
                    'pending',

                'submitted_at' =>
                    date(
                        'Y-m-d H:i:s'
                    ),

                'created_by' =>
                    $profileId,
            ];

            if (
                ! $this->manuscriptModel
                    ->insert(
                        $manuscriptData
                    )
            ) {

                $db->transRollback();

                return $this
                    ->validationResponse(
                        $this
                            ->manuscriptModel
                            ->errors()
                    );
            }

            $manuscriptPrimaryId =
                (int) $this
                    ->manuscriptModel
                    ->getInsertID();
                                /**
             * Save Co Authors.
             */
            $coAuthorsJson =
                $this->request->getPost(
                    'co_authors'
                );

            if (
                ! empty(
                    $coAuthorsJson
                )
            ) {

                $coAuthors =
                    json_decode(
                        (string)
                        $coAuthorsJson,
                        true
                    );

                if (
                    is_array(
                        $coAuthors
                    )
                ) {

                    $sortOrder = 1;

                    foreach (
                        $coAuthors
                        as $coAuthor
                    ) {

                        if (
                            empty(
                                $coAuthor[
                                    'author_name'
                                ]
                            )
                        ) {
                            continue;
                        }

                        $this
                            ->coAuthorModel
                            ->insert([

                                'manuscript_id' =>
                                    $manuscriptPrimaryId,

                                'author_name' =>
                                    trim(
                                        (string)
                                        (
                                            $coAuthor[
                                                'author_name'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'email' =>
                                    trim(
                                        (string)
                                        (
                                            $coAuthor[
                                                'email'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'designation' =>
                                    trim(
                                        (string)
                                        (
                                            $coAuthor[
                                                'designation'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'university_name' =>
                                    trim(
                                        (string)
                                        (
                                            $coAuthor[
                                                'university_name'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'sort_order' =>
                                    $sortOrder,
                            ]);

                        $sortOrder++;
                    }
                }
            }

            /**
             * Save Keywords.
             */
            $keywordsJson =
                $this->request->getPost(
                    'keywords'
                );

            if (
                ! empty(
                    $keywordsJson
                )
            ) {

                $keywords =
                    json_decode(
                        (string)
                        $keywordsJson,
                        true
                    );

                if (
                    is_array(
                        $keywords
                    )
                ) {

                    foreach (
                        $keywords
                        as $keyword
                    ) {

                        $keyword =
                            trim(
                                (string)
                                $keyword
                            );

                        if (
                            $keyword === ''
                        ) {
                            continue;
                        }

                        $this
                            ->keywordModel
                            ->insert([

                                'manuscript_id' =>
                                    $manuscriptPrimaryId,

                                'keyword' =>
                                    $keyword,
                            ]);
                    }
                }
            }

            /**
             * Save References.
             */
            $referencesJson =
                $this->request->getPost(
                    'references'
                );

            if (
                ! empty(
                    $referencesJson
                )
            ) {

                $references =
                    json_decode(
                        (string)
                        $referencesJson,
                        true
                    );

                if (
                    is_array(
                        $references
                    )
                ) {

                    $sortOrder = 1;

                    foreach (
                        $references
                        as $reference
                    ) {

                        if (
                            empty(
                                $reference[
                                    'reference_title'
                                ]
                            )
                        ) {
                            continue;
                        }

                        $this
                            ->referenceModel
                            ->insert([

                                'manuscript_id' =>
                                    $manuscriptPrimaryId,

                                'reference_title' =>
                                    trim(
                                        (string)
                                        (
                                            $reference[
                                                'reference_title'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'reference_author' =>
                                    trim(
                                        (string)
                                        (
                                            $reference[
                                                'reference_author'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'reference_description' =>
                                    trim(
                                        (string)
                                        (
                                            $reference[
                                                'reference_description'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'reference_url' =>
                                    trim(
                                        (string)
                                        (
                                            $reference[
                                                'reference_url'
                                            ]
                                            ?? ''
                                        )
                                    ),

                                'sort_order' =>
                                    $sortOrder,
                            ]);

                        $sortOrder++;
                    }
                }
            }
                        /**
             * Assign
             * 1 Chief Editor
             * 2 Editors
             */
            $assignedEditors =
                $this->manuscriptService
                    ->assignEditors(
                        $manuscriptPrimaryId
                    );

            /**
             * Status:
             * Submitted
             */
            $this->manuscriptService
                ->createStatusLog(
                    manuscriptId:
                        $manuscriptPrimaryId,

                    oldStatus:
                        null,

                    newStatus:
                        'submitted',

                    remarks:
                        'Manuscript submitted successfully.',

                    changedBy:
                        $profileId,

                    isSystemGenerated:
                        true
                );

            /**
             * Status:
             * Assigned To Editors
             */
            $this->manuscriptService
                ->createStatusLog(
                    manuscriptId:
                        $manuscriptPrimaryId,

                    oldStatus:
                        'submitted',

                    newStatus:
                        'assigned_to_editors',

                    remarks:
                        'Chief editor and editors assigned automatically.',

                    changedBy:
                        null,

                    isSystemGenerated:
                        true
                );

            /**
             * Update Current Status.
             */
            $this->manuscriptModel
                ->update(
                    $manuscriptPrimaryId,
                    [
                        'current_status' =>
                            'assigned_to_editors',
                    ]
                );

            /**
             * Commit Transaction.
             */
            if (
                $db->transStatus()
                === false
            ) {

                $db->transRollback();

                return $this
                    ->serverErrorResponse(
                        'Unable to submit manuscript.'
                    );
            }

            $db->transCommit();

            $manuscript =
                $this->manuscriptModel
                    ->find(
                        $manuscriptPrimaryId
                    );

            $chiefEditor = null;

            $editors = [];

            foreach (
                $assignedEditors
                as $assignedEditor
            ) {

                if (
                    $assignedEditor['role']
                    === 'editor_in_chief'
                ) {

                    $chiefEditor =
                        $assignedEditor['id'];

                    continue;
                }

                $editors[] =
                    $assignedEditor['id'];
            }

            return $this->successResponse(
                'Manuscript submitted successfully.',
                [

                    'manuscript_uuid' =>
                        $manuscript['uuid'],

                    'manuscript_id' =>
                        $manuscript['manuscript_id'],

                    'title' =>
                        $manuscript['title'],

                    'status' =>
                        $manuscript['current_status'],

                    'tracking_email' =>
                        $manuscript[
                            'corresponding_author_email'
                        ],

                    'assigned_editors' => [

                        'chief_editor_id' =>
                            $chiefEditor,

                        'editor_ids' =>
                            $editors,
                    ],
                ],
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            if (
                isset($db)
            ) {
                $db->transRollback();
            }

            return $this->serverErrorResponse(
                'Unable to submit manuscript.'
            );
        }
    }

    /**
     * POST /api/v1/public/manuscripts/request-tracking-otp
     */
    public function requestTrackingOtp(): ResponseInterface
    {
        try {

            $data = $this->request->getJSON(true);

            $manuscriptId = trim(
                (string) ($data['manuscript_id'] ?? '')
            );

            $email = trim(
                (string) ($data['email'] ?? '')
            );

            $manuscript = $this->manuscriptModel
                ->where(
                    'manuscript_id',
                    $manuscriptId
                )
                ->where(
                    'corresponding_author_email',
                    $email
                )
                ->first();

            if (!$manuscript) {

                return $this->validationResponse([
                    'email' =>
                        'Invalid manuscript ID or email .',
                ]);
            }

            $this->trackingOtpModel
                ->expireOldOtps(
                    (int) $manuscript['id'],
                    $email
                );

            $otp = $this->trackingOtpModel
                ->generateOtp(
                    (int) $manuscript['id'],
                    $email,
                    (string) $this->request->getIPAddress(),
                    (string) $this->request->getUserAgent()
                );

            /**
             * TODO:
             * Send OTP Email.
             */

            return $this->successResponse(
                'OTP sent successfully.',
                [
                    'manuscript_id' =>
                        $manuscriptId,

                    /**
                     * Remove in production.
                     */
                    'otp' => $otp,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to send OTP.'
            );
        }
    }
    /**
     * POST /api/v1/public/manuscripts/verify-tracking-otp
     */
    public function verifyTrackingOtp(): ResponseInterface
    {
        try {

            $data = $this->request->getJSON(true);

            $manuscriptId = trim(
                (string) ($data['manuscript_id'] ?? '')
            );

            $email = trim(
                (string) ($data['email'] ?? '')
            );

            $otp = trim(
                (string) ($data['otp'] ?? '')
            );

            $manuscript = $this->manuscriptModel
                ->where(
                    'manuscript_id',
                    $manuscriptId
                )
                ->where(
                    'corresponding_author_email',
                    $email
                )
                ->first();

            if (!$manuscript) {

                return $this->validationResponse([
                    'email' =>
                        'Invalid manuscript ID or email.',
                ]);
            }

            $verified =
                $this->trackingOtpModel
                    ->verifyOtp(
                        (int) $manuscript['id'],
                        $email,
                        $otp
                    );

            if (! $verified) {

                return $this->errorResponse(
                    'Invalid or expired OTP.'
                );
            }

            return $this->successResponse(
                'OTP verified successfully.',
                [
                    'manuscript' =>
                        $manuscript,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to verify OTP.'
            );
        }
    }
    /**
     * POST /api/v1/public/manuscripts/upload-payment
     */
    public function uploadPayment(): ResponseInterface
    {
        try {

            $manuscriptUuid = trim(
                (string) $this->request->getPost(
                    'manuscript_uuid'
                )
            );

            $manuscript = $this->manuscriptModel
                ->where(
                    'uuid',
                    $manuscriptUuid
                )
                ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Manuscript not found.'
                );
            }

            if (
                $this->paymentModel
                    ->paymentExists(
                        (int) $manuscript['id']
                    )
            ) {

                return $this->errorResponse(
                    'Payment already submitted.'
                );
            }

            $paymentScreenshot =
                $this->request->getFile(
                    'payment_screenshot'
                );

            $authorSignature =
                $this->request->getFile(
                    'author_signature'
                );

            $authorIdProof =
                $this->request->getFile(
                    'author_id_proof'
                );

            if (
                ! $paymentScreenshot ||
                ! $paymentScreenshot->isValid()
            ) {

                return $this->validationResponse([
                    'payment_screenshot' =>
                        'Payment screenshot is required.',
                ]);
            }

            $paymentDirectory =
                FCPATH .
                'uploads/manuscripts/payments/';

            if (! is_dir($paymentDirectory)) {

                mkdir(
                    $paymentDirectory,
                    0755,
                    true
                );
            }

            $paymentScreenshotName =
                $paymentScreenshot
                    ->getRandomName();

            $paymentScreenshot->move(
                $paymentDirectory,
                $paymentScreenshotName
            );

            $signatureName = null;

            if (
                $authorSignature &&
                $authorSignature->isValid()
            ) {

                $signatureName =
                    $authorSignature
                        ->getRandomName();

                $authorSignature->move(
                    $paymentDirectory,
                    $signatureName
                );
            }

            $idProofName = null;

            if (
                $authorIdProof &&
                $authorIdProof->isValid()
            ) {

                $idProofName =
                    $authorIdProof
                        ->getRandomName();

                $authorIdProof->move(
                    $paymentDirectory,
                    $idProofName
                );
            }

            $this->paymentModel->insert([

                'manuscript_id' =>
                    $manuscript['id'],

                'payment_amount' =>
                    (float)
                    $this->request->getPost(
                        'payment_amount'
                    ),

                'payment_reference_no' =>
                    trim(
                        (string)
                        $this->request->getPost(
                            'payment_reference_no'
                        )
                    ),

                'payment_date' =>
                    $this->request->getPost(
                        'payment_date'
                    ),

                'payment_screenshot' =>
                    'uploads/manuscripts/payments/'
                    . $paymentScreenshotName,

                'author_signature' =>
                    $signatureName
                        ? 'uploads/manuscripts/payments/'
                        . $signatureName
                        : null,

                'author_id_proof' =>
                    $idProofName
                        ? 'uploads/manuscripts/payments/'
                        . $idProofName
                        : null,

                'payment_status' =>
                    'pending',
            ]);

            return $this->successResponse(
                'Payment submitted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to upload payment.'
            );
        }
    }

    /**
     * GET /public/manuscripts/published
     */
    public function published(): ResponseInterface
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

            $journalId = (int) (
                $this->request->getGet(
                    'journal_id'
                ) ?? 0
            );

            $year = trim(
                (string) (
                    $this->request->getGet(
                        'year'
                    ) ?? ''
                )
            );

            $volumeNumber = trim(
                (string) (
                    $this->request->getGet(
                        'volume_number'
                    ) ?? ''
                )
            );

            $issueNumber = trim(
                (string) (
                    $this->request->getGet(
                        'issue_number'
                    ) ?? ''
                )
            );

            $builder =
                $this->manuscriptModel
                    ->select([

                        'manuscripts.uuid',

                        'manuscripts.manuscript_id',

                        'manuscripts.title',

                        'manuscripts.doi',

                        'journals.title AS journal_title',

                        'manuscript_publications.volume_number',

                        'manuscript_publications.issue_number',

                        'manuscript_publications.published_date',

                        'manuscript_publications.article_url',

                        'manuscript_publications.published_pdf',
                    ])
                    ->join(
                        'manuscript_publications',
                        'manuscript_publications.manuscript_id = manuscripts.id',
                        'inner'
                    )
                    ->join(
                        'journals',
                        'journals.id = manuscripts.journal_id',
                        'left'
                    )
                    ->where(
                        'manuscripts.current_status',
                        'published'
                    )
                    ->where(
                        'manuscripts.final_decision',
                        'accepted'
                    );

            if ($search !== '') {

                $builder
                    ->groupStart()
                    ->like(
                        'manuscripts.manuscript_id',
                        $search
                    )
                    ->orLike(
                        'manuscripts.title',
                        $search
                    )
                    ->orLike(
                        'manuscripts.doi',
                        $search
                    )
                    ->groupEnd();
            }

            if ($journalId > 0) {

                $builder->where(
                    'manuscripts.journal_id',
                    $journalId
                );
            }

            if ($year !== '') {

                $builder->where(
                    'YEAR(manuscript_publications.published_date)',
                    $year
                );
            }

            if ($volumeNumber !== '') {

                $builder->where(
                    'manuscript_publications.volume_number',
                    $volumeNumber
                );
            }

            if ($issueNumber !== '') {

                $builder->where(
                    'manuscript_publications.issue_number',
                    $issueNumber
                );
            }

            $items = $builder
                ->orderBy(
                    'manuscript_publications.published_date',
                    'DESC'
                )
                ->paginate(
                    $perPage
                );

            return $this->successResponse(
                'Published manuscripts fetched successfully.',
                [

                    'items' =>
                        $items,

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
                'Unable to fetch published manuscripts.'
            );
        }
    }

    /**
     * GET /public/manuscripts/published/{manuscript_id}
     */
    public function publishedDetails(
        string $manuscriptId
    ): ResponseInterface
    {
        try {

            $manuscript =
                $this->manuscriptModel
                    ->select([

                        'manuscripts.*',

                        'journals.title AS journal_title',

                        'article_types.title AS article_type_title',

                        'disciplines.title AS disciplinary_title',

                        'manuscript_publications.volume_number',

                        'manuscript_publications.issue_number',

                        'manuscript_publications.published_date',

                        'manuscript_publications.article_url',

                        'manuscript_publications.published_pdf',
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
                    ->join(
                        'manuscript_publications',
                        'manuscript_publications.manuscript_id = manuscripts.id',
                        'inner'
                    )
                    ->where(
                        'manuscripts.manuscript_id',
                        $manuscriptId
                    )
                    ->where(
                        'manuscripts.current_status',
                        'published'
                    )
                    ->where(
                        'manuscripts.final_decision',
                        'accepted'
                    )
                    ->first();

            if (! $manuscript) {

                return $this->notFoundResponse(
                    'Published manuscript not found.'
                );
            }

            $keywords =
                $this->keywordModel
                    ->where(
                        'manuscript_id',
                        $manuscript['id']
                    )
                    ->findAll();

            $authors =
                $this->coAuthorModel
                    ->where(
                        'manuscript_id',
                        $manuscript['id']
                    )
                    ->findAll();

            return $this->successResponse(
                'Published manuscript fetched successfully.',
                [

                    'manuscript' =>
                        $manuscript,

                    'keywords' =>
                        $keywords,

                    'authors' =>
                        $authors,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch published manuscript.'
            );
        }
    }
}