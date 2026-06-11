<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ContactMessageModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ContactMessageController extends BaseApiController
{
    protected ContactMessageModel $contactMessageModel;

    public function __construct()
    {
        $this->contactMessageModel = new ContactMessageModel();
    }

    /**
     * POST /public/contact-messages
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

                'status' => 'new',

                'is_read' => 0,

                'ip_address' => $this->request
                    ->getIPAddress(),

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
                'Your message has been submitted successfully.',
                [
                    'uuid' => $contactMessage['uuid'],
                ],
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to submit contact message.'
            );
        }
    }

    /**
     * GET /public/contact-messages/{uuid}
     *
     * Optional endpoint.
     * Can be removed if public viewing
     * of submitted messages is not required.
     */
    public function show(
        $id = null
    ): ResponseInterface {
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

            return $this->successResponse(
                'Contact message fetched successfully.',
                [
                    'id' => $contactMessage['id'],
                    'uuid' => $contactMessage['uuid'],
                    'full_name' => $contactMessage['full_name'],
                    'email' => $contactMessage['email'],
                    'subject' => $contactMessage['subject'],
                    'message_type' => $contactMessage['message_type'],
                    'status' => $contactMessage['status'],
                    'created_at' => $contactMessage['created_at'],
                ]
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
}