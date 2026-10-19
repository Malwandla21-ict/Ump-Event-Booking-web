<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Synchronizer-token CSRF protection. One random token per session,
 * checked on every POST with a constant-time comparison.
 */
final class Csrf
{
    private const KEY = '_csrf_token';
    public const FIELD = '_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION[self::KEY])
            && hash_equals($_SESSION[self::KEY], $token);
    }

    public static function rotate(): void
    {
        unset($_SESSION[self::KEY]);
    }
}
