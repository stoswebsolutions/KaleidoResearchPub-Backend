<?php

declare(strict_types=1);

if (! function_exists('encrypt_data')) {

    function encrypt_data(
        string $value
    ): string {

        return bin2hex(
            service('encrypter')
                ->encrypt($value)
        );
    }
}

if (! function_exists('decrypt_data')) {

    function decrypt_data(
        string $value
    ): string {

        return service('encrypter')
            ->decrypt(
                hex2bin($value)
            );
    }
}