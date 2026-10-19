<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public static function notFound(string $message = 'The page you are looking for does not exist.'): self
    {
        return new self(404, $message);
    }

    public static function forbidden(string $message = 'You do not have permission to view this page.'): self
    {
        return new self(403, $message);
    }
}
