<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseApiController extends ResourceController
{
    /**
     * Default response format.
     */
    protected $format = 'json';

    /**
     * Current authenticated profile.
     */
    protected ?array $profile = null;

    /**
     * Current authenticated profile id.
     */
    protected ?int $profileId = null;

    /**
     * Current authenticated role id.
     */
    protected ?int $roleId = null;

    /**
     * Return success response.
     */
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $statusCode = ResponseInterface::HTTP_OK
    ): ResponseInterface {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON(
                success_response(
                    $message,
                    $data,
                    $statusCode
                )
            );
    }

    /**
     * Return error response.
     */
    protected function errorResponse(
        string $message,
        mixed $errors = null,
        int $statusCode = ResponseInterface::HTTP_BAD_REQUEST
    ): ResponseInterface {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON(
                error_response(
                    $message,
                    $errors,
                    $statusCode
                )
            );
    }

    /**
     * Return validation error response.
     */
    protected function validationResponse(
        array $errors,
        string $message = 'Validation failed.'
    ): ResponseInterface {
        return $this->response
            ->setStatusCode(
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            )
            ->setJSON(
                validation_response(
                    $errors,
                    $message
                )
            );
    }

    /**
     * Return not found response.
     */
    protected function notFoundResponse(
        string $message = 'Resource not found.'
    ): ResponseInterface {
        return $this->response
            ->setStatusCode(
                ResponseInterface::HTTP_NOT_FOUND
            )
            ->setJSON(
                not_found_response($message)
            );
    }

    /**
     * Return unauthorized response.
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized access.'
    ): ResponseInterface {
        return $this->response
            ->setStatusCode(
                ResponseInterface::HTTP_UNAUTHORIZED
            )
            ->setJSON(
                unauthorized_response($message)
            );
    }

    /**
     * Return forbidden response.
     */
    protected function forbiddenResponse(
        string $message = 'Access denied.'
    ): ResponseInterface {
        return $this->response
            ->setStatusCode(
                ResponseInterface::HTTP_FORBIDDEN
            )
            ->setJSON(
                forbidden_response($message)
            );
    }

    /**
     * Return server error response.
     */
    protected function serverErrorResponse(
        string $message = 'Internal server error.'
    ): ResponseInterface {
        return $this->response
            ->setStatusCode(
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            )
            ->setJSON(
                error_response(
                    $message,
                    [],
                    ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
                )
            );
    }

    /**
     * Get JSON request body.
     */
    protected function getRequestData(): array
    {
        $data = $this->request->getJSON(true);

        if (is_array($data)) {
            return $data;
        }

        return $this->request->getPost();
    }

    /**
     * Get pagination parameters.
     */
    protected function getPagination(): array
    {
        $page = (int) $this->request->getGet('page');
        $perPage = (int) $this->request->getGet('per_page');

        $page = $page > 0 ? $page : 1;

        $perPage = match (true) {
            $perPage < 1 => 10,
            $perPage > 100 => 100,
            default => $perPage,
        };

        return [
            'page'     => $page,
            'per_page' => $perPage,
            'offset'   => ($page - 1) * $perPage,
        ];
    }

    /**
     * Build pagination response.
     */
    protected function paginationData(
        int $total,
        int $page,
        int $perPage
    ): array {
        return [
            'total'         => $total,
            'per_page'      => $perPage,
            'current_page'  => $page,
            'total_pages'   => (int) ceil($total / $perPage),
        ];
    }
}