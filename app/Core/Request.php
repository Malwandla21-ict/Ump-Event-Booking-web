<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private static ?self $current = null;

    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $basePath,
        private readonly array $query,
        private readonly array $body,
        private readonly array $files,
        private readonly array $server,
    ) {
    }

    public static function capture(): self
    {
        $uriPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $basePath = self::detectBasePath($uriPath);

        $path = substr($uriPath, strlen($basePath)) ?: '/';
        $path = '/' . trim($path, '/');

        return self::$current = new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            $basePath,
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER,
        );
    }

    public static function current(): ?self
    {
        return self::$current;
    }

    /**
     * Works out the URL prefix the app lives under, e.g. "" for `php -S`
     * or "/eventBookingWeb" when served from XAMPP's htdocs (with or without /public).
     */
    private static function detectBasePath(string $uriPath): string
    {
        $configured = Config::get('app.url');
        if ($configured) {
            return rtrim((string) parse_url($configured, PHP_URL_PATH), '/');
        }

        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        // PHP's dev server reports the requested path as SCRIPT_NAME for URLs with
        // a file extension (e.g. /attendees.csv) — only trust it when it's our front controller.
        $scriptDir = basename($script) === 'index.php' ? rtrim(str_replace('\\', '/', dirname($script)), '/') : '';

        if ($scriptDir !== '' && !str_starts_with($uriPath, $scriptDir) && str_ends_with($scriptDir, '/public')) {
            // Root .htaccess rewrote /app/... to /app/public/... — hide the /public segment.
            $scriptDir = substr($scriptDir, 0, -strlen('/public'));
        }

        return str_starts_with($uriPath, $scriptDir) ? $scriptDir : '';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function query(string $key, mixed $default = null): mixed
    {
        $value = $this->query[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->body[$key] ?? $default;

        return is_string($value) ? trim($value) : $value;
    }

    /** Raw (untrimmed) value — used for passwords. */
    public function raw(string $key): string
    {
        $value = $this->body[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /** @param string[] $keys */
    public function only(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->input($key, '');
        }

        return $out;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || ($this->server['SERVER_PORT'] ?? null) == 443;
    }

    /** Same-origin path of the previous page, falling back to $fallback. */
    public function previousPath(string $fallback = '/'): string
    {
        $referer = $this->server['HTTP_REFERER'] ?? '';
        $host = $this->server['HTTP_HOST'] ?? '';
        if ($referer === '' || parse_url($referer, PHP_URL_HOST) . (parse_url($referer, PHP_URL_PORT) ? ':' . parse_url($referer, PHP_URL_PORT) : '') !== $host) {
            return $fallback;
        }

        $path = parse_url($referer, PHP_URL_PATH) ?: '/';
        $query = parse_url($referer, PHP_URL_QUERY);
        if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
            $path = substr($path, strlen($this->basePath)) ?: '/';
        }

        return $path . ($query ? '?' . $query : '');
    }
}
