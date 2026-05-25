<?php

declare(strict_types=1);

namespace App\Libraries;

class AuthUser
{
    public ?array $profile = null;

    public ?array $session = null;

    public ?int $profileId = null;

    public ?int $roleId = null;

    public ?string $email = null;
}