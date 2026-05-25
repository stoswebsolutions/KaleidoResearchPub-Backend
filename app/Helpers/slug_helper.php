<?php

declare(strict_types=1);

if (! function_exists('generate_slug')) {
    function generate_slug(string $value): string
    {
        return url_title(trim($value), '-', true);
    }
}