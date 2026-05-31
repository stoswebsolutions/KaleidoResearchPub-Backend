<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\EmailTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class EmailTemplateController extends BaseApiController
{
    protected EmailTemplateModel $emailTemplateModel;

    protected array $allowedSortFields = [

        'template_name',

        'template_key',

        'status',

        'created_at',
    ];

    public function __construct()
    {
        $this->emailTemplateModel =
            new EmailTemplateModel();
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
                $this->emailTemplateModel;

            if ($search !== '') {

                $builder
                    ->groupStart()
                    ->like(
                        'template_name',
                        $search
                    )
                    ->orLike(
                        'template_key',
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
                'Email templates fetched successfully.',
                [
                    'items' =>
                        $records,

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $perPage,

                        'total' =>
                            $this->emailTemplateModel
                                ->pager
                                ->getTotal(),

                        'last_page' =>
                            $this->emailTemplateModel
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
                'Unable to fetch email templates.'
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

            $templateKey = trim(
                (string) (
                    $payload['template_key']
                    ?? ''
                )
            );

            $exists =
                $this->emailTemplateModel
                    ->where(
                        'template_key',
                        $templateKey
                    )
                    ->first();

            if ($exists) {

                return $this->validationResponse([
                    'template_key' =>
                        'Template key already exists.',
                ]);
            }

            $data = [

                'template_key' =>
                    $templateKey,

                'template_name' => trim(
                    (string) (
                        $payload['template_name']
                        ?? ''
                    )
                ),

                'subject' => trim(
                    (string) (
                        $payload['subject']
                        ?? ''
                    )
                ),

                'content' => (string) (
                    $payload['content']
                    ?? ''
                ),

                'variables' =>
                    isset(
                        $payload['variables']
                    )
                    ? json_encode(
                        $payload['variables']
                    )
                    : null,

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
                ! $this->emailTemplateModel
                    ->insert($data)
            ) {

                return $this->validationResponse(
                    $this->emailTemplateModel
                        ->errors()
                );
            }

            return $this->successResponse(
                'Email template created successfully.',
                $this->emailTemplateModel
                    ->find(
                        $this->emailTemplateModel
                            ->getInsertID()
                    ),
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create email template.'
            );
        }
    }

    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailTemplate =
                $this->emailTemplateModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailTemplate) {

                return $this->notFoundResponse(
                    'Email template not found.'
                );
            }

            /**
             * Decode Variables
             */
            $emailTemplate['variables'] =
                ! empty(
                    $emailTemplate['variables']
                )
                ? json_decode(
                    (string)
                    $emailTemplate['variables'],
                    true
                )
                : [];

            return $this->successResponse(
                'Email template fetched successfully.',
                $emailTemplate
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch email template.'
            );
        }
    }

    public function update(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailTemplate =
                $this->emailTemplateModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailTemplate) {

                return $this->notFoundResponse(
                    'Email template not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $authUser =
                service('authUser');

            $templateKey = trim(
                (string) (
                    $payload['template_key']
                    ?? $emailTemplate['template_key']
                )
            );

            /**
             * Duplicate Check
             */
            $exists =
                $this->emailTemplateModel
                    ->where(
                        'template_key',
                        $templateKey
                    )
                    ->where(
                        'id !=',
                        $emailTemplate['id']
                    )
                    ->first();

            if ($exists) {

                return $this->validationResponse([
                    'template_key' =>
                        'Template key already exists.',
                ]);
            }

            $data = [

                'template_key' =>
                    $templateKey,

                'template_name' => trim(
                    (string) (
                        $payload['template_name']
                        ?? $emailTemplate['template_name']
                    )
                ),

                'subject' => trim(
                    (string) (
                        $payload['subject']
                        ?? $emailTemplate['subject']
                    )
                ),

                'content' => (string) (
                    $payload['content']
                    ?? $emailTemplate['content']
                ),

                'variables' =>
                    isset(
                        $payload['variables']
                    )
                    ? json_encode(
                        $payload['variables']
                    )
                    : $emailTemplate['variables'],

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $emailTemplate['status']
                    )
                ),

                'updated_by' =>
                    $authUser->profileId,
            ];

            if (
                ! $this->emailTemplateModel
                    ->update(
                        $emailTemplate['id'],
                        $data
                    )
            ) {

                return $this->validationResponse(
                    $this->emailTemplateModel
                        ->errors()
                );
            }

            $updatedTemplate =
                $this->emailTemplateModel
                    ->find(
                        $emailTemplate['id']
                    );

            if (
                ! empty(
                    $updatedTemplate['variables']
                )
            ) {

                $updatedTemplate['variables'] =
                    json_decode(
                        (string)
                        $updatedTemplate['variables'],
                        true
                    );
            }

            return $this->successResponse(
                'Email template updated successfully.',
                $updatedTemplate
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update email template.'
            );
        }
    }

    public function delete(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailTemplate =
                $this->emailTemplateModel
                    ->findByUuid(
                        (string) $id
                    );

            if (! $emailTemplate) {

                return $this->notFoundResponse(
                    'Email template not found.'
                );
            }

            $authUser =
                service('authUser');

            /**
             * Audit Update
             */
            $this->emailTemplateModel
                ->update(
                    $emailTemplate['id'],
                    [
                        'deleted_by' =>
                            $authUser->profileId,
                    ]
                );

            if (
                ! $this->emailTemplateModel
                    ->delete(
                        $emailTemplate['id']
                    )
            ) {

                return $this->errorResponse(
                    'Unable to delete email template.'
                );
            }

            return $this->successResponse(
                'Email template deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete email template.'
            );
        }
    }
}