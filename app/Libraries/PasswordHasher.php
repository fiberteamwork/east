<?php

namespace App\Libraries;

final class PasswordHasher
{
    /**
     * @return array{hash: string, salt: string}
     */
    public static function hash(string $password): array
    {
        $salt = bin2hex(random_bytes(16));

        return [
            'hash' => hash('sha256', $salt . $password),
            'salt' => $salt,
        ];
    }

    public static function verify(string $password, string $salt, string $hash): bool
    {
        return hash_equals($hash, hash('sha256', $salt . $password));
    }
}
