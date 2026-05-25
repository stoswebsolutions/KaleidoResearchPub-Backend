<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

if (! function_exists('generate_uuid')) {
    /**
     * Generate RFC4122 UUID v4
     */
    function generate_uuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}