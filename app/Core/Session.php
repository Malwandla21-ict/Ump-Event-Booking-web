<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Hardened session handling plus one-request "flash" data (toasts, form errors, old input).
 */
final class Session
{
    private const FLASH_KEY = '_flash';
    private const FLASH_NEXT_KEY = '_flash_next';

    public static function start(bool $secure): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name(Config::get('app.session.name'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,   // not readable from JavaScript
            'samesite' => 'Lax',  // not sent on cross-site POSTs
        ]);
        session_start();

        self::enforceIdleTimeout();

        // Rotate flash data: what was set last request is readable now, then gone.
        $_SESSION[self::FLASH_KEY] = $_SESSION[self::FLASH_NEXT_KEY] ?? [];
        $_SESSION[self::FLASH_NEXT_KEY] = [];
    }

    private static function enforceIdleTimeout(): void
    {
        $timeout = (int) Config::get('app.session.idle_timeout');
        $last = $_SESSION['_last_activity'] ?? null;

        if ($last !== null && time() - $last > $timeout) {
            $wasSignedIn = isset($_SESSION['user_id']);
            $_SESSION = [];
            session_regenerate_id(true);
            if ($wasSignedIn) {
                $_SESSION[self::FLASH_NEXT_KEY]['toast'] = ['info', 'Your session expired. Please sign in again.'];
            }
        }

        $_SESSION['_last_activity'] = time();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);

        return $value;
    }

    /** Prevents session fixation: call on every privilege change (login/logout). */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $p['path'],
                'secure' => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'],
            ]);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[self::FLASH_NEXT_KEY][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        return $_SESSION[self::FLASH_KEY][$key] ?? $default;
    }

    public static function toast(string $type, string $message): void
    {
        self::flash('toast', [$type, $message]);
    }
}
