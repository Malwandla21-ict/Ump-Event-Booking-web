<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;

/** Escape for HTML output. Use for every piece of user-controlled data in views. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

/** Build an app URL that respects the sub-folder the app is served from. */
function url(string $path = '/', array $query = []): string
{
    $base = Request::current()?->basePath ?? '';
    $url = $base . '/' . ltrim($path, '/');
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');

    return ($url === '' ? '/' : $url) . ($query ? '?' . http_build_query($query) : '');
}

/** URL for a file in /public, with a cache-busting version. */
function asset(string $path): string
{
    $file = BASE_PATH . '/public/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . filemtime($file) : '';

    return url($path) . $version;
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . Csrf::FIELD . '" value="' . e(Csrf::token()) . '">';
}

/** Previous input after a failed form submission. */
function old(string $key, mixed $default = ''): string
{
    return (string) (Session::getFlash('old', [])[$key] ?? $default);
}

function error(string $key): ?string
{
    return Session::getFlash('errors', [])[$key] ?? null;
}

function errors(): array
{
    return Session::getFlash('errors', []);
}

/** aria-invalid / aria-describedby attributes for an input with a validation error. */
function invalid(string $field): string
{
    return error($field) ? 'aria-invalid="true" aria-describedby="' . e($field) . '-error"' : '';
}

/** The inline error message under an input. */
function field_error(string $field): string
{
    return \App\Core\View::partial('field-error', ['name' => $field]);
}

function auth(): ?array
{
    return Auth::user();
}

/** Is the current path equal to (or under) $path? Used for nav highlighting. */
function is_active(string $path, bool $exact = false): bool
{
    $current = Request::current()?->path ?? '/';

    return $exact || $path === '/' ? $current === $path : str_starts_with($current, $path);
}

/** Inline SVG icon from the sprite in views/partials/icons.php. */
function icon(string $name, string $class = ''): string
{
    return '<svg class="icon ' . e($class) . '" aria-hidden="true" focusable="false"><use href="#i-' . e($name) . '"></use></svg>';
}

function format_date(string|DateTimeInterface $date, string $format = 'D, j M Y'): string
{
    $date = $date instanceof DateTimeInterface ? $date : new DateTimeImmutable($date);

    return $date->format($format);
}

function format_time(string $date): string
{
    return (new DateTimeImmutable($date))->format('H:i');
}

function category_label(string $key): string
{
    return config("app.categories.$key.0", ucfirst($key));
}

function category_icon(string $key): string
{
    return config("app.categories.$key.1", 'calendar');
}

/** Image URL for an event, or null to show the illustrated placeholder. */
function event_image(?string $path): ?string
{
    return $path ? asset($path) : null;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

    return mb_strtoupper($first . $last);
}

function pluralize(int $count, string $singular, ?string $plural = null): string
{
    return $count . ' ' . ($count === 1 ? $singular : ($plural ?? $singular . 's'));
}
