<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Jwt;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Exception;

class JwtLibrary
{
    protected Jwt $config;

    public function __construct()
    {
        $this->config = config('Jwt');
    }

    /**
     * Generate access token.
     */
    public function generateAccessToken(array $profile, string $sessionUuid): string
    {
        $issuedAt = time();

        $payload = [
            'iss'        => $this->config->issuer,
            'aud'        => $this->config->audience,
            'iat'        => $issuedAt,
            'nbf'        => $issuedAt,
            'exp'        => $issuedAt + $this->config->accessTokenExpiry,

            'profile_id' => (int) $profile['id'],
            'role_id'    => (int) $profile['role_id'],
            'email'      => $profile['email'],
            'session_uuid' => $sessionUuid,
        ];

        return FirebaseJWT::encode(
            $payload,
            $this->config->secretKey,
            $this->config->algorithm
        );
    }

    /**
     * Generate refresh token.
     */
    public function generateRefreshToken(): string
    {
        return bin2hex(
            random_bytes(64)
        );
    }

    /**
     * Decode JWT token.
     */
    public function decode(string $token): object
    {
        return FirebaseJWT::decode(
            $token,
            new Key(
                $this->config->secretKey,
                $this->config->algorithm
            )
        );
    }

    /**
     * Validate JWT token.
     */
    public function validate(string $token): bool
    {
        try {

            $this->decode($token);

            return true;

        } catch (Exception) {

            return false;
        }
    }

    /**
     * Get bearer token.
     */
    public function getBearerToken(?string $header): ?string
    {
        if (
            empty($header) ||
            ! preg_match(
                '/Bearer\s(\S+)/',
                $header,
                $matches
            )
        ) {
            return null;
        }

        return $matches[1];
    }
}