<?php

declare(strict_types=1);

if (! function_exists('api_response')) {
    /**
     * Standard API response.
     */
    function api_response(
        bool $success,
        string $message = '',
        mixed $data = null,
        array $errors = [],
        int $statusCode = 200
    ): array {

        service('response')->setStatusCode(
            $statusCode
        );

        $startTime = defined('KRP_START_TIME')
            ? KRP_START_TIME
            : ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

        $responseTime = round(
            (microtime(true) - $startTime) * 1000,
            2
        );

        return [
            'success'   => $success,
            'status'    => $statusCode,
            'message'   => $message,
            'data'      => $data,
            '_meta'     => [
                'request_id' => uniqid(
                    'req_',
                    true
                ),
                'response_time_ms' => $responseTime,
            ],
            'errors'    => $errors,
            'timestamp' => date(
                'Y-m-d H:i:s'
            ),
        ];
    }
}

if (! function_exists('success_response')) {
    /**
     * Success response.
     */
    function success_response(
        string $message = 'Success.',
        mixed $data = null,
        int $statusCode = 200
    ): array {

        return api_response(
            true,
            $message,
            $data,
            [],
            $statusCode
        );
    }
}

if (! function_exists('error_response')) {
    /**
     * Error response.
     */
    function error_response(
        string $message = 'Something went wrong.',
        array $errors = [],
        int $statusCode = 400
    ): array {

        return api_response(
            false,
            $message,
            null,
            $errors,
            $statusCode
        );
    }
}

if (! function_exists('validation_response')) {
    /**
     * Validation error response.
     */
    function validation_response(
        array $errors,
        string $message = 'Validation failed.'
    ): array {

        return api_response(
            false,
            $message,
            null,
            $errors,
            422
        );
    }
}

if (! function_exists('not_found_response')) {
    /**
     * Resource not found response.
     */
    function not_found_response(
        string $message = 'Resource not found.'
    ): array {

        return api_response(
            false,
            $message,
            null,
            [],
            404
        );
    }
}

if (! function_exists('unauthorized_response')) {
    /**
     * Unauthorized response.
     */
    function unauthorized_response(
        string $message = 'Unauthorized access.'
    ): array {

        return api_response(
            false,
            $message,
            null,
            [],
            401
        );
    }
}

if (! function_exists('forbidden_response')) {
    /**
     * Forbidden response.
     */
    function forbidden_response(
        string $message = 'Access denied.'
    ): array {

        return api_response(
            false,
            $message,
            null,
            [],
            403
        );
    }
}

if (! function_exists('server_error_response')) {
    /**
     * Internal server error response.
     */
    function server_error_response(
        string $message = 'Internal server error.'
    ): array {

        return api_response(
            false,
            $message,
            null,
            [],
            500
        );
    }
}