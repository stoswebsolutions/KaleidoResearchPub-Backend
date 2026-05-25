<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\JwtLibrary;
use App\Models\ProfileModel;
use App\Models\ProfileSessionModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        $jwtLibrary = new JwtLibrary();

        $token = $jwtLibrary->getBearerToken(
            $request->getHeaderLine('Authorization')
        );

        if (empty($token)) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(
                    unauthorized_response(
                        'Authorization token is required.'
                    )
                );
        }

        try {

            $payload = $jwtLibrary->decode($token);

        } catch (\Throwable $exception) {

            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(
                    unauthorized_response(
                        'Invalid or expired token.'
                    )
                );
        }

        if (! isset($payload->profile_id)) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(
                    unauthorized_response(
                        'Invalid token payload.'
                    )
                );
        }

        $profileModel = new ProfileModel();

        $profile = $profileModel
            ->where('id', (int) $payload->profile_id)
            ->where('status', 'active')
            ->first();

        if ($profile === null) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(
                    unauthorized_response(
                        'Profile not found or inactive.'
                    )
                );
        }

        $profileSessionModel = new ProfileSessionModel();

        $session = $profileSessionModel
            ->where('uuid', $payload->session_uuid)
            ->where('is_active', 1)
            ->where('revoked_at IS NULL', null, false)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if ($session === null) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(
                    unauthorized_response(
                        'Session expired or revoked.'
                    )
                );
        }

        /**
         * Authenticated user information.
         */
        $authUser = service('authUser');
        $authUser->profileId = (int) $profile['id'];
        $authUser->roleId = (int) $profile['role_id'];
        $authUser->email = $profile['email'];
        $authUser->profile = $profile;
        $authUser->session = $session;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ): void {
        // No action required.
    }
}