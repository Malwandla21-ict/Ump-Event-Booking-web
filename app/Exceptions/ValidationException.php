<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when user input fails validation. The App turns it into a redirect
 * back to the form with field errors and the previous input preserved.
 */
final class ValidationException extends RuntimeException
{
    /**
     * @param array<string, string> $errors field => message
     */
    public function __construct(
        public readonly array $errors,
        public readonly array $old = [],
        public readonly ?string $redirectTo = null,
    ) {
        parent::__construct('The given data was invalid.');
    }

    public static function withMessage(string $field, string $message, array $old = []): self
    {
        return new self([$field => $message], $old);
    }
}
