<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\EmailLogModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class EmailLogController extends BaseApiController
{
    protected EmailLogModel $emailLogModel;

    protected array $allowedSortFields = [

        'recipient_email',

        'status',

        'sent_at',

        'created_at',
    ];

    public function __construct()
    {
        $this->emailLogModel =
            new EmailLogModel();
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
                $this->emailLogModel
                    ->select([
                        'email_logs.*',
                        'email_templates.template_key',
                        'email_templates.template_name',
                    ])
                    ->join(
                        'email_templates',
                        'email_templates.id = email_logs.email_template_id',
                        'left'
                    );

            if ($search !== '') {

                $builder
                    ->groupStart()
                    ->like(
                        'recipient_email',
                        $search
                    )
                    ->orLike(
                        'subject',
                        $search
                    )
                    ->orLike(
                        'email_templates.template_name',
                        $search
                    )
                    ->groupEnd();
            }

            if ($status !== '') {

                $builder->where(
                    'email_logs.status',
                    $status
                );
            }

            $records =
                $builder
                    ->orderBy(
                        'email_logs.'
                        . $sortBy,
                        $sortDirection
                    )
                    ->paginate(
                        $perPage
                    );

            return $this->successResponse(
                'Email logs fetched successfully.',
                [

                    'items' =>
                        $records,

                    'pagination' => [

                        'current_page' =>
                            $page,

                        'per_page' =>
                            $perPage,

                        'total' =>
                            $this->emailLogModel
                                ->pager
                                ->getTotal(),

                        'last_page' =>
                            $this->emailLogModel
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
                'Unable to fetch email logs.'
            );
        }
    }

    public function show(
        $id = null
    ): ResponseInterface
    {
        try {

            $emailLog =
                $this->emailLogModel
                    ->select([
                        'email_logs.*',
                        'email_templates.template_key',
                        'email_templates.template_name',
                    ])
                    ->join(
                        'email_templates',
                        'email_templates.id = email_logs.email_template_id',
                        'left'
                    )
                    ->where(
                        'email_logs.uuid',
                        (string) $id
                    )
                    ->first();

            if (! $emailLog) {

                return $this->notFoundResponse(
                    'Email log not found.'
                );
            }

            return $this->successResponse(
                'Email log fetched successfully.',
                $emailLog
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch email log.'
            );
        }
    }
    
}