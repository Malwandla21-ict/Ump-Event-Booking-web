<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\DatabaseUnavailableException;
use App\Exceptions\HttpException;
use ErrorException;
use Throwable;

/**
 * Turns PHP warnings into exceptions, logs anything unexpected, and shows a
 * friendly error page. Stack traces are only shown when APP_DEBUG=true.
 */
final class ErrorHandler
{
    private static bool $debug = false;
    private static string $logDir = '';

    public static function register(bool $debug, string $logDir): void
    {
        self::$debug = $debug;
        self::$logDir = $logDir;

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (Throwable $e): void {
            self::render($e)->send();
        });

        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::render(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']))->send();
            }
        });
    }

    public static function render(Throwable $e): Response
    {
        $status = match (true) {
            $e instanceof HttpException => $e->status,
            $e instanceof DatabaseUnavailableException => 503,
            default => 500,
        };

        if ($status >= 500) {
            self::log($e);
        }

        $messages = [
            403 => ['Access denied', 'You do not have permission to view this page.'],
            404 => ['Page not found', 'The page you are looking for does not exist or has moved.'],
            405 => ['Not allowed', 'That action is not allowed here.'],
            419 => ['Page expired', 'Your form expired for security reasons. Please go back, refresh and try again.'],
            429 => ['Slow down', 'Too many attempts. Please wait a moment and try again.'],
            503 => ['We will be right back', 'The booking service is temporarily unavailable. Please try again shortly.'],
        ];
        [$title, $message] = $messages[$status] ?? ['Something went wrong', 'An unexpected error occurred. It has been logged and we will look into it.'];

        if ($e instanceof HttpException && $e->getMessage() !== '' && $status < 500) {
            $message = $e->getMessage();
        }

        try {
            $html = View::render('errors/error', [
                'title' => $title,
                'pageTitle' => $title,
                'status' => $status,
                'message' => $message,
                'exception' => self::$debug && $status >= 500 ? $e : null,
            ]);
        } catch (Throwable $viewError) {
            // The layout itself failed (e.g. DB down while resolving the user) — fall back to bare HTML.
            self::log($viewError);
            $html = '<!doctype html><meta charset="utf-8"><title>' . $status . '</title>'
                . '<div style="font-family:system-ui;max-width:560px;margin:15vh auto;padding:0 16px">'
                . '<h1>' . htmlspecialchars($title) . '</h1><p>' . htmlspecialchars($message) . '</p>'
                . (self::$debug ? '<pre style="white-space:pre-wrap">' . htmlspecialchars((string) $e) . '</pre>' : '')
                . '</div>';
        }

        return Response::html($html, $status);
    }

    public static function log(Throwable $e): void
    {
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0775, true);
        }

        $request = Request::current();
        $line = sprintf(
            "[%s] %s: %s in %s:%d | %s %s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $request?->method ?? 'CLI',
            $request?->path ?? '',
            $e->getTraceAsString(),
        );

        @file_put_contents(self::$logDir . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}
