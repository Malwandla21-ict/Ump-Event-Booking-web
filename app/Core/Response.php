<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** @var (callable(): void)|null */
    private $streamer = null;

    public function __construct(
        private string $body = '',
        private int $status = 200,
        private array $headers = [],
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /** Redirect to an app-relative path such as "/events". */
    public static function redirect(string $path, int $status = 303): self
    {
        return new self('', $status, ['Location' => url($path)]);
    }

    /**
     * Stream a CSV download. Cells that start with = + - @ are prefixed with
     * an apostrophe so spreadsheet apps won't execute them as formulas.
     *
     * @param string[] $header
     * @param iterable<array> $rows
     */
    public static function csv(string $filename, array $header, iterable $rows): self
    {
        $response = new self('', 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '-', $filename) . '"',
        ]);

        $response->streamer = static function () use ($header, $rows): void {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, array_map(
                    static fn ($cell) => is_string($cell) && preg_match('/^[=+\-@\t\r]/', $cell) ? "'" . $cell : $cell,
                    $row,
                ));
            }
            fclose($out);
        };

        return $response;
    }

    public function isHtmlPage(): bool
    {
        return $this->status === 200 && str_starts_with($this->headers['Content-Type'] ?? '', 'text/html');
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            header_remove('X-Powered-By'); // don't advertise the PHP version
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }

        if ($this->streamer) {
            ($this->streamer)();

            return;
        }

        echo $this->body;
    }
}
