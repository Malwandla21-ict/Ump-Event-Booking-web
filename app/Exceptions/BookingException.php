<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

/**
 * A booking rule was broken (sold out, past event, already booked…).
 * The message is safe to show to the user.
 */
final class BookingException extends DomainException
{
}
