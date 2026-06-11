<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ContactMessageModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ContactMessageController extends BaseApiController
{
    protected ContactMessageModel $contactMessageModel;

    protected array $allowedSortFields = [
        'full_name',
        'email',
        'message_type',
        'status',
        'is_read',
        'created_at',
    ];

    public function __construct()
    {
        $this->contactMessageModel = new ContactMessageModel();
    }

        /**
     * GET /contact-messages
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

            $status = trim(
                (string) (
                    $this->request->getGet('status')
                    ?? ''
                )
            );

            $messageType = trim(
                (string) (
                    $this->request->getGet('message_type')
                    ?? ''
                )
            );

            $isRead = $this->request->getGet(
                'is_read'
            );

            $sortBy = (string) (
                $this->request->getGet('sort_by')
                ?? 'created_at'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet(
                        'sort_direction'
                    )
                    ?? 'desc'
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

            $builder = $this->contactMessageModel
                ->select([
                    'uuid',
                    'full_name',
                    'email',
                    'phone',
                    'subject',
                    'message_type',
                    'status',
                    'is_read',
                    'read_at',
                    'ip_address',
                    'created_at',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'full_name',
                        $search
                    )
                    ->orLike(
                        'email',
                        $search
                    )
                    ->orLike(
                        'phone',
                        $search
                    )
                    ->orLike(
                        'subject',
                        $search
                    )
                    ->orLike(
                        'message',
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

            if ($messageType !== '') {

                $builder->where(
                    'message_type',
                    $messageType
                );
            }

            if (
                $isRead !== null
                && $isRead !== ''
            ) {

                $builder->where(
                    'is_read',
                    (int) $isRead
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Contact messages fetched successfully.',
                [
                    'items' => $records,

                    'statistics' => [
                        'total_unread' => $this->contactMessageModel
                            ->getUnreadCount(),

                        'total_new' => $this->contactMessageModel
                            ->getNewCount(),
                    ],

                    'pagination' => [
                        'current_page' => $page,

                        'per_page' => $perPage,

                        'total' => $this->contactMessageModel
                            ->pager
                            ->getTotal(),

                        'last_page' => $this->contactMessageModel
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
                'Unable to fetch contact messages.'
            );
        }
    }

        /**
     * GET /contact-messages/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $contactMessage = $this->contactMessageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactMessage) {
                return $this->notFoundResponse(
                    'Contact message not found.'
                );
            }

            /**
             * Auto mark as read
             */
            if (
                (int) (
                    $contactMessage['is_read']
                    ?? 0
                ) === 0
            ) {

                $this->contactMessageModel
                    ->markAsRead(
                        (int) $contactMessage['id']
                    );

                $contactMessage = $this->contactMessageModel
                    ->find(
                        $contactMessage['id']
                    );
            }

            return $this->successResponse(
                'Contact message fetched successfully.',
                $contactMessage
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch contact message.'
            );
        }
    }

        /**
     * POST /contact-messages
     */
    public function create(): ResponseInterface
    {
        try {

            $payload = 
                $this->getRequestData();

            $data = [
                'full_name' => trim(
                    (string) (
                        $payload['full_name']
                        ?? ''
                    )
                ),

                'email' => trim(
                    (string) (
                        $payload['email']
                        ?? ''
                    )
                ),

                'phone' => trim(
                    (string) (
                        $payload['phone']
                        ?? ''
                    )
                ),

                'subject' => trim(
                    (string) (
                        $payload['subject']
                        ?? ''
                    )
                ),

                'message' => trim(
                    (string) (
                        $payload['message']
                        ?? ''
                    )
                ),

                'message_type' => trim(
                    (string) (
                        $payload['message_type']
                        ?? 'general'
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? 'new'
                    )
                ),

                'is_read' => (int) (
                    $payload['is_read']
                    ?? 0
                ),

                'read_at' => ! empty(
                    $payload['read_at']
                )
                    ? $payload['read_at']
                    : null,

                'ip_address' => $this->request->getIPAddress(),

                'user_agent' => (string) $this->request
                    ->getUserAgent(),
            ];

            if (
                ! $this->contactMessageModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->contactMessageModel->errors()
                );
            }

            $contactMessage = $this->contactMessageModel
                ->find(
                    $this->contactMessageModel
                        ->getInsertID()
                );

            return $this->successResponse(
                'Contact message created successfully.',
                $contactMessage,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create contact message.'
            );
        }
    }

        /**
     * PUT /contact-messages/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $contactMessage = $this->contactMessageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactMessage) {
                return $this->notFoundResponse(
                    'Contact message not found.'
                );
            }

            $payload =
                $this->getRequestData();

            $data = [
                'full_name' => trim(
                    (string) (
                        $payload['full_name']
                        ?? $contactMessage['full_name']
                    )
                ),

                'email' => trim(
                    (string) (
                        $payload['email']
                        ?? $contactMessage['email']
                    )
                ),

                'phone' => trim(
                    (string) (
                        $payload['phone']
                        ?? (
                            $contactMessage['phone']
                            ?? ''
                        )
                    )
                ),

                'subject' => trim(
                    (string) (
                        $payload['subject']
                        ?? $contactMessage['subject']
                    )
                ),

                'message' => trim(
                    (string) (
                        $payload['message']
                        ?? $contactMessage['message']
                    )
                ),

                'message_type' => trim(
                    (string) (
                        $payload['message_type']
                        ?? $contactMessage['message_type']
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $contactMessage['status']
                    )
                ),

                'is_read' => isset(
                    $payload['is_read']
                )
                    ? (int) $payload['is_read']
                    : (int) $contactMessage['is_read'],
            ];

            if (
                ! $this->contactMessageModel->update(
                    $contactMessage['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->contactMessageModel->errors()
                );
            }

            return $this->successResponse(
                'Contact message updated successfully.',
                $this->contactMessageModel->find(
                    $contactMessage['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update contact message.'
            );
        }
    }

    /**
     * DELETE /contact-messages/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $contactMessage = $this->contactMessageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactMessage) {
                return $this->notFoundResponse(
                    'Contact message not found.'
                );
            }

            $this->contactMessageModel->delete(
                $contactMessage['id']
            );

            return $this->successResponse(
                'Contact message deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete contact message.'
            );
        }
    }

    /**
     * PATCH /contact-messages/{uuid}/mark-read
     */
    public function markRead($id = null): ResponseInterface
    {
        try {

            $contactMessage = $this->contactMessageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactMessage) {
                return $this->notFoundResponse(
                    'Contact message not found.'
                );
            }

            $this->contactMessageModel->markAsRead(
                (int) $contactMessage['id']
            );

            return $this->successResponse(
                'Contact message marked as read.',
                $this->contactMessageModel->find(
                    $contactMessage['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to mark contact message as read.'
            );
        }
    }

    /**
     * PATCH /contact-messages/{uuid}/mark-unread
     */
    public function markUnread($id = null): ResponseInterface
    {
        try {

            $contactMessage = $this->contactMessageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactMessage) {
                return $this->notFoundResponse(
                    'Contact message not found.'
                );
            }

            $this->contactMessageModel->markAsUnread(
                (int) $contactMessage['id']
            );

            return $this->successResponse(
                'Contact message marked as unread.',
                $this->contactMessageModel->find(
                    $contactMessage['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to mark contact message as unread.'
            );
        }
    }

    /**
     * PATCH /contact-messages/{uuid}/resolve
     */
    public function markResolved($id = null): ResponseInterface
    {
        try {

            $contactMessage = $this->contactMessageModel
                ->findByUuid(
                    (string) $id
                );

            if (! $contactMessage) {
                return $this->notFoundResponse(
                    'Contact message not found.'
                );
            }

            $this->contactMessageModel->markAsResolved(
                (int) $contactMessage['id']
            );

            return $this->successResponse(
                'Contact message marked as resolved.',
                $this->contactMessageModel->find(
                    $contactMessage['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to resolve contact message.'
            );
        }
    }
}