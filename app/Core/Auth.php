<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\DatabaseUnavailableException;
use App\Repositories\UserRepository;

/**
 * Who is signed in. Only the user id lives in the session; the record is
 * re-read from the database so role changes and deletions take effect immediately.
 */
final class Auth
{
    private static ?array $user = null;
    private static bool $resolved = false;

    public static function login(array $user): void
    {
        Session::regenerate();
        Csrf::rotate();
        Session::put('user_id', (int) $user['id']);
        self::$user = $user;
        self::$resolved = true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
        self::$resolved = true;
    }

    public static function user(): ?array
    {
        if (!self::$resolved) {
            self::$resolved = true;
            $id = Session::get('user_id');
            try {
                self::$user = $id ? (new UserRepository())->find((int) $id) : null;
            } catch (DatabaseUnavailableException) {
                return null; // lets the 503 page render with the normal layout
            }

            if ($id && self::$user === null) {
                Session::forget('user_id'); // account was removed
            }
        }

        return self::$user;
    }

    public static function id(): ?int
    {
        return isset(self::user()['id']) ? (int) self::user()['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    /** Refresh the cached user after a profile update. */
    public static function refresh(): void
    {
        self::$resolved = false;
    }
}
