<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\EmailLogModel;
use App\Models\EmailSettingModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class EmailSettingController extends BaseApiController
{
    protected EmailSettingModel $emailSettingModel;

    protected array $allowedSortFields = [

        'from_name',

        'from_email',

        'mail_driver',

        'status',

        'created_at',
    ];

    public function __construct()
    {
        $this->emailSettingModel =
            new EmailSettingModel();
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

                $sortBy =
                    'created_at';
            }

            if (
                ! in_array(
                    $sortDirection,
                    ['asc', 'desc'],
                    true
                )
            ) {

                $sortDirection =
                    'desc';
            }

            $builder =
                $this->emailSettingModel;

            if ($search !== '') {

                $builder
                    ->groupStart()
                    ->like(
                        'from_name',
                        $search
                    )
                    ->orLike(
                        'from_email',
                        $search
                    )
                    ->orLike(
                        'smtp_host',
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
                'Email settings fetched successfully.',
                [

                    'items' =>
                        $records,

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $perPage,

                        'total' =>
                            $this->emailSettingModel
                                ->pager
                                ->getTotal(),

                        'last_page' =>
                            $this->emailSettingModel
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
                'Unable to fetch email settings.'
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

            if (
                (
                    (int) (
                        $payload['is_default']
                        ?? 0
                    )
                ) === 1
            ) {

                $this->emailSettingModel
                    ->builder()
                    ->set(
                        'is_default',
                        0
                    )
                    ->update();
            }

            $smtpPassword =
                trim(
                    (string) (
                        $payload['smtp_pass']
                        ?? ''
                    )
                );

            $data = [

                'mail_driver' => trim(
                    (string) (
                        $payload['mail_driver']
                        ?? 'smtp'
                    )
                ),

                'smtp_host' => trim(
                    (string) (
                        $payload['smtp_host']
                        ?? ''
                    )
                ),

                'smtp_port' => (
                    ! empty(
                        $payload['smtp_port']
                    )
                )
                ? (int)
                    $payload['smtp_port']
                : null,

                'smtp_user' => trim(
                    (string) (
                        $payload['smtp_user']
                        ?? ''
                    )
                ),

                'smtp_pass' => (
                    $smtpPassword !== ''
                )
                ? $smtpPassword
                : null,

                'smtp_crypto' => trim(
                    (string) (
                        $payload['smtp_crypto']
                        ?? ''
                    )
                ),

                'from_email' => trim(
                    (string) (
                        $payload['from_email']
                        ?? ''
                    )
                ),

                'from_name' => trim(
                    (string) (
                        $payload['from_name']
                        ?? ''
                    )
                ),

                'reply_to_email' => trim(
                    (string) (
                        $payload['reply_to_email']
                        ?? ''
                    )
                ),

                'reply_to_name' => trim(
                    (string) (
                        $payload['reply_to_name']
                        ?? ''
                    )
                ),

                'is_default' => (
                    (int) (
                        $payload['is_default']
                        ?? 0
                    )
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

            if (
                ! $this->emailSettingModel
                    ->insert(
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->emailSettingModel
                        ->errors()
                );
            }

            $emailSetting =
                $this->emailSettingModel
                    ->find(
                        $this->emailSettingModel
                            ->getInsertID()
                    );

            /**
             * Hide Password
             */
            unset(
                $emailSetting['smtp_pass']
            );

            return $this->successResponse(
                'Email setting created successfully.',
                $emailSetting,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create email setting.'
            );
        }
    }
    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailSetting =
                $this->emailSettingModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailSetting) {

                return $this->notFoundResponse(
                    'Email setting not found.'
                );
            }

            /**
             * Never expose SMTP Password.
             */
            unset(
                $emailSetting['smtp_pass']
            );

            return $this->successResponse(
                'Email setting fetched successfully.',
                $emailSetting
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch email setting.'
            );
        }
    }

    public function update(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailSetting =
                $this->emailSettingModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailSetting) {

                return $this->notFoundResponse(
                    'Email setting not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $authUser =
                service('authUser');

            /**
             * Default Configuration
             */
            if (
                (
                    (int) (
                        $payload['is_default']
                        ?? $emailSetting['is_default']
                    )
                ) === 1
            ) {

                $this->emailSettingModel
                    ->builder()
                    ->set(
                        'is_default',
                        0
                    )
                    ->update();
            }

            /**
             * Preserve Existing Password
             */
            $smtpPassword =
                $emailSetting['smtp_pass'];

            if (
                ! empty(
                    $payload['smtp_pass']
                )
            ) {

                $smtpPassword =
                trim(
                    (string)
                    $payload['smtp_pass']
                );
            }

            $data = [

                'mail_driver' => trim(
                    (string) (
                        $payload['mail_driver']
                        ?? $emailSetting['mail_driver']
                    )
                ),

                'smtp_host' => trim(
                    (string) (
                        $payload['smtp_host']
                        ?? $emailSetting['smtp_host']
                    )
                ),

                'smtp_port' => (
                    isset(
                        $payload['smtp_port']
                    )
                )
                ? (int)
                    $payload['smtp_port']
                : $emailSetting['smtp_port'],

                'smtp_user' => trim(
                    (string) (
                        $payload['smtp_user']
                        ?? $emailSetting['smtp_user']
                    )
                ),

                'smtp_pass' =>
                    $smtpPassword,

                'smtp_crypto' => trim(
                    (string) (
                        $payload['smtp_crypto']
                        ?? $emailSetting['smtp_crypto']
                    )
                ),

                'from_email' => trim(
                    (string) (
                        $payload['from_email']
                        ?? $emailSetting['from_email']
                    )
                ),

                'from_name' => trim(
                    (string) (
                        $payload['from_name']
                        ?? $emailSetting['from_name']
                    )
                ),

                'reply_to_email' => trim(
                    (string) (
                        $payload['reply_to_email']
                        ?? (
                            $emailSetting['reply_to_email']
                            ?? ''
                        )
                    )
                ),

                'reply_to_name' => trim(
                    (string) (
                        $payload['reply_to_name']
                        ?? (
                            $emailSetting['reply_to_name']
                            ?? ''
                        )
                    )
                ),

                'is_default' => (
                    (int) (
                        $payload['is_default']
                        ?? $emailSetting['is_default']
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $emailSetting['status']
                    )
                ),

                'updated_by' =>
                    $authUser->profileId,
            ];

            if (
                ! $this->emailSettingModel
                    ->update(
                        $emailSetting['id'],
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->emailSettingModel
                        ->errors()
                );
            }

            $updatedRecord =
                $this->emailSettingModel
                    ->find(
                        $emailSetting['id']
                    );

            /**
             * Never expose password.
             */
            unset(
                $updatedRecord['smtp_pass']
            );

            return $this->successResponse(
                'Email setting updated successfully.',
                $updatedRecord
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update email setting.'
            );
        }
    }

    public function delete(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailSetting =
                $this->emailSettingModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailSetting) {

                return $this->notFoundResponse(
                    'Email setting not found.'
                );
            }

            /**
             * Prevent deleting default SMTP.
             */
            if (
                (int)
                $emailSetting['is_default']
                === 1
            ) {

                return $this->errorResponse(
                    'Default email setting cannot be deleted.'
                );
            }

            $authUser =
                service('authUser');

            /**
             * Audit Update
             */
            $this->emailSettingModel
                ->update(
                    $emailSetting['id'],
                    [
                        'deleted_by' =>
                            $authUser->profileId,
                    ]
                );

            if (
                ! $this->emailSettingModel
                    ->delete(
                        $emailSetting['id']
                    )
            ) {

                return $this->errorResponse(
                    'Unable to delete email setting.'
                );
            }

            return $this->successResponse(
                'Email setting deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete email setting.'
            );
        }
    }

    public function setDefault(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailSetting =
                $this->emailSettingModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailSetting) {

                return $this->notFoundResponse(
                    'Email setting not found.'
                );
            }

            if (
                (int)
                $emailSetting['is_default']
                === 1
            ) {

                return $this->successResponse(
                    'Email setting is already default.'
                );
            }

            $authUser =
                service('authUser');

            /**
             * Reset Existing Defaults
             */
            $this->emailSettingModel
                ->builder()
                ->set(
                    'is_default',
                    0
                )
                ->update();

            /**
             * Set New Default
             */
            $this->emailSettingModel
                ->update(
                    $emailSetting['id'],
                    [
                        'is_default' => 1,

                        'updated_by' =>
                            $authUser->profileId,
                    ]
                );

            return $this->successResponse(
                'Default email setting updated successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update default email setting.'
            );
        }
    }
    public function testConnection(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailSetting =
                $this->emailSettingModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailSetting) {

                return $this->notFoundResponse(
                    'Email setting not found.'
                );
            }

            $config = [

                'protocol' =>
                    $emailSetting['mail_driver'],

                'SMTPHost' =>
                    $emailSetting['smtp_host'],

                'SMTPPort' =>
                    (int)
                    $emailSetting['smtp_port'],

                'SMTPUser' =>
                    $emailSetting['smtp_user'],

                'SMTPPass' =>
                    ! empty(
                        $emailSetting['smtp_pass']
                    )
                    ? $emailSetting['smtp_pass']
                    : '',

                'SMTPCrypto' =>
                    $emailSetting['smtp_crypto'],

                'mailType' =>
                    'html',

                'charset' =>
                    'UTF-8',

                'newline' =>
                    "\r\n",

                'CRLF' =>
                    "\r\n",
            ];

            $email =
                \Config\Services::email();

            $email->initialize(
                $config
            );

            $email->setFrom(
                $emailSetting['from_email'],
                $emailSetting['from_name']
            );

            $email->setTo(
                $emailSetting['from_email']
            );

            $email->setSubject(
                'SMTP Test Connection'
            );

            $email->setMessage(
                '<p>SMTP connection test successful.</p>'
            );

            if (
                ! $email->send()
            ) {

                return $this->errorResponse(
                    'SMTP connection failed.',
                    [
                        'debug' =>
                            $email->printDebugger(
                                [
                                    'headers',
                                    'subject',
                                    'body',
                                ]
                            ),
                    ]
                );
            }

            return $this->successResponse(
                'SMTP connection successful.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->errorResponse(
                'SMTP connection failed.',
                [
                    'error' =>
                        $e->getMessage(),
                ]
            );
        }
    }
}