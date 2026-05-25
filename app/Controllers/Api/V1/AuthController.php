<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Libraries\JwtLibrary;
use App\Models\ProfileModel;
use App\Models\ProfileSessionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class AuthController extends BaseApiController
{
    protected ProfileModel $profileModel;
    protected ProfileSessionModel $profileSessionModel;
    protected JwtLibrary $jwtLibrary;

    public function __construct()
    {
        $this->profileModel        = new ProfileModel();
        $this->profileSessionModel = new ProfileSessionModel();
        $this->jwtLibrary          = new JwtLibrary();
    }

    /**
     * Login using email or phone.
     *
     * POST /api/v1/auth/login
     */
    public function login(): ResponseInterface
    {
        try {

            $data = $this->getRequestData();

            $rules = [
                'login' => [
                    'rules' => 'required|max_length[191]',
                ],
                'password' => [
                    'rules' => 'required|min_length[6]|max_length[255]',
                ],
            ];

            if (! $this->validateData($data, $rules)) {
                return $this->validationResponse(
                    $this->validator->getErrors()
                );
            }

            $login    = trim((string) $data['login']);
            $password = (string) $data['password'];

            $profile = $this->profileModel
                ->groupStart()
                    ->where('email', $login)
                    ->orWhere('phone', $login)
                ->groupEnd()
                ->first();

            if ($profile === null) {
                return $this->unauthorizedResponse(
                    'Invalid credentials.'
                );
            }

            /**
             * Account status validation.
             */
            if ($profile['status'] !== 'active') {
                return $this->unauthorizedResponse(
                    'Account is inactive.'
                );
            }

            /**
             * Account lock validation.
             */
            if (
                ! empty($profile['locked_until']) &&
                strtotime((string) $profile['locked_until']) > time()
            ) {
                return $this->errorResponse(
                    'Account is temporarily locked. Please try again later.',
                    null,
                    ResponseInterface::HTTP_LOCKED
                );
            }

            /**
             * Verify password.
             */
            if (
                ! password_verify(
                    $password,
                    $profile['password_hash']
                )
            ) {

                $failedAttempts =
                    ((int) $profile['failed_login_attempts']) + 1;

                $updateData = [
                    'failed_login_attempts' => $failedAttempts,
                ];

                /**
                 * Lock after 5 failed attempts.
                 */
                if ($failedAttempts >= 5) {

                    $updateData['locked_until'] = date(
                        'Y-m-d H:i:s',
                        strtotime('+30 minutes')
                    );
                }

                $this->profileModel->update(
                    $profile['id'],
                    $updateData
                );

                return $this->unauthorizedResponse(
                    'Invalid credentials.'
                );
            }

            /**
             * Reset failed attempts.
             */
            $this->profileModel->update(
                $profile['id'],
                [
                    'failed_login_attempts' => 0,
                    'locked_until'          => null,
                    'last_login_at'         => date('Y-m-d H:i:s'),
                    'last_login_ip'         => $this->request->getIPAddress(),
                ]
            );

            /**
             * Refresh token.
             */
            $refreshToken = $this->jwtLibrary
                ->generateRefreshToken();

            /**
             * Session UUID.
             */
            $sessionUuid = generate_uuid();

            /**
             * Create session.
             */
            $result = $this->profileSessionModel->insert([
                'uuid'               => $sessionUuid,
                'profile_id'         => $profile['id'],
                'refresh_token_hash' => hash(
                    'sha256',
                    $refreshToken
                ),

                'device_type' => 'web',

                'device_name' => null,

                'browser' => $this->request
                    ->getUserAgent()
                    ->getBrowser(),

                'platform' => $this->request
                    ->getUserAgent()
                    ->getPlatform(),

                'user_agent' => $this->request
                    ->getUserAgent()
                    ->getAgentString(),

                'ip_address' => $this->request
                    ->getIPAddress(),

                'login_method' => 'password',

                'login_at' => date('Y-m-d H:i:s'),

                'last_activity_at' => date('Y-m-d H:i:s'),

                'expires_at' => date(
                    'Y-m-d H:i:s',
                    time() + config('Jwt')->refreshTokenExpiry
                ),

                'is_active' => 1,
            ]);

            if ($result === false) {
                dd($this->profileSessionModel->errors());
            }

            /**
             * Generate JWT access token.
             */
            $accessToken = $this->jwtLibrary
                ->generateAccessToken(
                    $profile,
                    $sessionUuid
                );

            /**
             * Activity log.
             */
            activity_log(
                profileId: (int) $profile['id'],
                module: 'auth',
                action: 'login'
            );

            unset($profile['password_hash']);

            return $this->successResponse(
                'Login successful.',
                [
                    'access_token'  => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_in'    => config('Jwt')
                        ->accessTokenExpiry,
                    'profile'       => $profile,
                ]
            );

        } catch (Exception $exception) {

            log_message(
                'error',
                $exception->getMessage()
            );

            return $this->serverErrorResponse();
        }
    }

    /**
     * Current authenticated profile.
     *
     * GET /api/v1/auth/me
     */
    public function me(): ResponseInterface
    {
        try {

            $authUser = service('authUser');

            if ($authUser->profile === null) {
                return $this->unauthorizedResponse(
                    'Authentication required.'
                );
            }

            $profile = $authUser->profile;

            unset($profile['password_hash']);

            return $this->successResponse(
                'Profile fetched successfully.',
                $profile
            );

        } catch (\Exception $exception) {

            log_message(
                'error',
                $exception->getMessage()
            );

            return $this->serverErrorResponse();
        }
    }

    /**
     * Refresh access token.
     *
     * POST /api/v1/auth/refresh-token
     */
    public function refreshToken(): ResponseInterface
    {
        try {

            $data = $this->getRequestData();

            $rules = [
                'refresh_token' => [
                    'rules' => 'required'
                ],
            ];

            if (! $this->validateData($data, $rules)) {
                return $this->validationResponse(
                    $this->validator->getErrors()
                );
            }

            $refreshToken = (string) $data['refresh_token'];

            $refreshTokenHash = hash(
                'sha256',
                $refreshToken
            );

            $session = $this->profileSessionModel
                ->where(
                    'refresh_token_hash',
                    $refreshTokenHash
                )
                ->where('is_active', 1)
                ->where('revoked_at IS NULL', null, false)
                ->where(
                    'expires_at >=',
                    date('Y-m-d H:i:s')
                )
                ->first();

            if ($session === null) {
                return $this->unauthorizedResponse(
                    'Invalid or expired refresh token.'
                );
            }

            $profile = $this->profileModel
                ->where('id', $session['profile_id'])
                ->where('status', 'active')
                ->first();

            if ($profile === null) {
                return $this->unauthorizedResponse(
                    'Profile not found or inactive.'
                );
            }

            /**
             * Update activity timestamp.
             */
            $this->profileSessionModel->update(
                $session['id'],
                [
                    'last_activity_at' => date(
                        'Y-m-d H:i:s'
                    ),
                ]
            );

            $accessToken = $this->jwtLibrary
                ->generateAccessToken(
                    $profile,
                    $session['uuid']
                );

            activity_log(
                profileId: (int) $profile['id'],
                module: 'auth',
                action: 'refresh_token'
            );

            return $this->successResponse(
                'Token refreshed successfully.',
                [
                    'access_token' => $accessToken,
                    'expires_in'   => config('Jwt')
                        ->accessTokenExpiry,
                ]
            );

        } catch (\Exception $exception) {

            log_message(
                'error',
                $exception->getMessage()
            );

            return $this->serverErrorResponse();
        }
    }

    /**
     * Logout current session.
     *
     * POST /api/v1/auth/logout
     */
    public function logout(): ResponseInterface
    {
        try {

            $authUser = service('authUser');

            if ($authUser->profile === null) {
                return $this->unauthorizedResponse(
                    'Authentication required.'
                );
            }

            $session = $authUser->session;

            $this->profileSessionModel->update(
                $session['id'],
                [
                    'is_active'        => 0,
                    'logout_at'        => date('Y-m-d H:i:s'),
                    'revoked_at'       => date('Y-m-d H:i:s'),
                    'last_activity_at' => date('Y-m-d H:i:s'),
                ]
            );

            activity_log(
                profileId: (int) $authUser->profileId,
                module: 'auth',
                action: 'logout'
            );

            return $this->successResponse(
                'Logout successful.'
            );

        } catch (\Exception $exception) {

            log_message(
                'error',
                $exception->getMessage()
            );

            return $this->serverErrorResponse();
        }
    }

    /**
     * Logout all active sessions.
     *
     * POST /api/v1/auth/logout-all
     */
    public function logoutAll(): ResponseInterface
    {
        try {

            $authUser = service('authUser');

            if ($authUser->profile === null) {
                return $this->unauthorizedResponse(
                    'Authentication required.'
                );
            }

            $this->profileSessionModel
                ->where(
                    'profile_id',
                    $authUser->profileId
                )
                ->where('is_active', 1)
                ->set([
                    'is_active'        => 0,
                    'logout_at'        => date('Y-m-d H:i:s'),
                    'revoked_at'       => date('Y-m-d H:i:s'),
                    'last_activity_at' => date('Y-m-d H:i:s'),
                ])
                ->update();

            activity_log(
                profileId: (int) $authUser->profileId,
                module: 'auth',
                action: 'logout_all'
            );

            return $this->successResponse(
                'Logged out from all devices successfully.'
            );

        } catch (\Exception $exception) {

            log_message(
                'error',
                $exception->getMessage()
            );

            return $this->serverErrorResponse();
        }
    }

    /**
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword(): ResponseInterface
    {
        try {

            $data = $this->getRequestData();

            $rules = [
                'email' => [
                    'rules' => 'required|valid_email'
                ],
            ];

            if (! $this->validateData($data, $rules)) {
                return $this->validationResponse(
                    $this->validator->getErrors()
                );
            }

            $profile = $this->profileModel
                ->where('email', $data['email'])
                ->where('status', 'active')
                ->first();

            /**
             * Prevent email enumeration.
             */
            if ($profile === null) {
                return $this->successResponse(
                    'If the account exists, a password reset link has been sent.'
                );
            }

            $plainToken = bin2hex(
                random_bytes(32)
            );

            $tokenHash = hash(
                'sha256',
                $plainToken
            );

            $passwordResetModel =
                new \App\Models\PasswordResetModel();

            $passwordResetModel->insert([
                'profile_id' => $profile['id'],
                'token_hash' => $tokenHash,
                'expires_at' => date(
                    'Y-m-d H:i:s',
                    strtotime('+1 hour')
                ),
            ]);

            /**
             * TODO:
             * Send email containing $plainToken.
             */

            activity_log(
                profileId: (int) $profile['id'],
                module: 'auth',
                action: 'forgot_password'
            );

            return $this->successResponse(
                'If the account exists, a password reset link has been sent.',
                [
                    'reset_token' => $plainToken,
                ]
            );

        } catch (\Exception $exception) {

            log_message(
                'error',
                $exception->getMessage()
            );

            return $this->serverErrorResponse();
        }
    }

    /**
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword(): ResponseInterface
    {
        try {

            $data = $this->getRequestData();

            $rules = [
                'token' => [
                    'rules' => 'required',
                ],

                'password' => [
                    'rules' => 'required|min_length[8]',
                ],

                'password_confirmation' => [
                    'rules' => 'required|matches[password]',
                ],
            ];

            if (! $this->validateData($data, $rules)) {
                return $this->validationResponse(
                    $this->validator->getErrors()
                );
            }

            $tokenHash = hash(
                'sha256',
                $data['token']
            );

            $passwordResetModel =
                new \App\Models\PasswordResetModel();

            $reset = $passwordResetModel
                ->where(
                    'token_hash',
                    $tokenHash
                )
                ->where('used_at IS NULL', null, false)
                ->where(
                    'expires_at >=',
                    date('Y-m-d H:i:s')
                )
                ->first();

            if ($reset === null) {
                return $this->unauthorizedResponse(
                    'Invalid or expired token.'
                );
            }

            $profile = $this->profileModel
                ->find(
                    $reset['profile_id']
                );

            if ($profile === null) {
                return $this->notFoundResponse(
                    'Profile not found.'
                );
            }

            $this->profileModel->update(
                $profile['id'],
                [
                    'password_hash' => password_hash(
                        $data['password'],
                        PASSWORD_DEFAULT
                    ),

                    'failed_login_attempts' => 0,

                    'locked_until' => null,
                ]
            );

            $passwordResetModel->update(
                $reset['id'],
                [
                    'used_at' => date(
                        'Y-m-d H:i:s'
                    ),
                ]
            );

            /**
             * Revoke all active sessions.
             */
            $this->profileSessionModel
                ->where(
                    'profile_id',
                    $profile['id']
                )
                ->set([
                    'is_active' => 0,
                    'revoked_at' => date(
                        'Y-m-d H:i:s'
                    ),
                    'logout_at' => date(
                        'Y-m-d H:i:s'
                    ),
                ])
                ->update();

            activity_log(
                profileId: (int) $profile['id'],
                module: 'auth',
                action: 'password_reset'
            );

            return $this->successResponse(
                'Password reset successful.'
            );

        } catch (\Exception $exception) {

            log_message(
                'error',
                $exception->getMessage()
            );

            return $this->serverErrorResponse();
        }
    }

}