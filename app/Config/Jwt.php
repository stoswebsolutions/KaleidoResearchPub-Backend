<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Jwt extends BaseConfig
{
    /**
     * JWT secret key.
     */
    public string $secretKey;

    /**
     * JWT algorithm.
     */
    public string $algorithm;

    /**
     * JWT issuer.
     */
    public string $issuer;

    /**
     * JWT audience.
     */
    public string $audience;

    /**
     * Access token expiry (seconds).
     */
    public int $accessTokenExpiry;

    /**
     * Refresh token expiry (seconds).
     */
    public int $refreshTokenExpiry;

    public function __construct()
    {
        parent::__construct();

        $this->secretKey = (string) env(
            'JWT_SECRET_KEY',
            'change-this-secret-key'
        );

        $this->algorithm = (string) env(
            'JWT_ALGORITHM',
            'HS256'
        );

        $this->issuer = (string) env(
            'JWT_ISSUER',
            'Kaleido Research Publication'
        );

        $this->audience = (string) env(
            'JWT_AUDIENCE',
            'KRP Users'
        );

        $this->accessTokenExpiry = (int) env(
            'JWT_ACCESS_TOKEN_EXPIRY',
            3600
        );

        $this->refreshTokenExpiry = (int) env(
            'JWT_REFRESH_TOKEN_EXPIRY',
            2592000
        );
    }
}