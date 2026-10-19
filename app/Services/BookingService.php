<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Exceptions\BookingException;
use App\Repositories\BookingRepository;
use App\Repositories\EventRepository;

/**
 * All booking rules live here, so they're enforced the same way everywhere.
 */
final class BookingService
{
    private const REFERENCE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // no 0/O, 1/I/L

    private readonly Database $db;
    private readonly EventRepository $events;
    private readonly BookingRepository $bookings;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::connection();
        $this->events = new EventRepository($this->db);
        $this->bookings = new BookingRepository($this->db);
    }

    /**
     * Book seats for a user. Runs in a transaction with the event row locked
     * (SELECT … FOR UPDATE) so two people can never grab the last seat at once.
     *
     * @return string the booking reference
     * @throws BookingException
     */
    public function book(int $userId, int $eventId, int $quantity): string
    {
        $max = (int) Config::get('app.booking.max_tickets_per_booking');
        if ($quantity < 1 || $quantity > $max) {
            throw new BookingException("You can book between 1 and $max tickets at a time.");
        }

        return $this->db->transaction(function () use ($userId, $eventId, $quantity): string {
            $event = $this->events->lockForBooking($eventId);

            if (!$event || $event['status'] === 'draft') {
                throw new BookingException('This event could not be found.');
            }
            if ($event['status'] === 'cancelled') {
                throw new BookingException('This event has been cancelled.');
            }
            if (strtotime($event['starts_at']) <= time()) {
                throw new BookingException('Bookings are closed — this event has already started.');
            }

            $existing = $this->bookings->findByUserAndEvent($userId, $eventId, lock: true);
            if ($existing && $existing['status'] === 'confirmed') {
                throw new BookingException("You're already booked for this event (ref {$existing['reference']}).");
            }

            $seatsLeft = (int) $event['capacity'] - $this->bookings->seatsTaken($eventId);
            if ($seatsLeft <= 0) {
                throw new BookingException('Sorry, this event is sold out.');
            }
            if ($quantity > $seatsLeft) {
                throw new BookingException('Only ' . pluralize($seatsLeft, 'seat') . ' left — please choose fewer tickets.');
            }

            $reference = $this->newReference();
            $existing
                ? $this->bookings->reactivate((int) $existing['id'], $reference, $quantity)
                : $this->bookings->create($reference, $userId, $eventId, $quantity);

            return $reference;
        });
    }

    /** @throws BookingException */
    public function cancel(int $userId, string $reference): array
    {
        $booking = $this->bookings->findForUser($userId, $reference);

        if (!$booking) {
            throw new BookingException('Booking not found.');
        }
        if ($booking['status'] === 'cancelled') {
            throw new BookingException('This booking is already cancelled.');
        }
        if (strtotime($booking['starts_at']) <= time()) {
            throw new BookingException("Past bookings can't be cancelled.");
        }

        $this->bookings->cancel((int) $booking['id']);

        return $booking;
    }

    private function newReference(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= self::REFERENCE_ALPHABET[random_int(0, strlen(self::REFERENCE_ALPHABET) - 1)];
            }
            $reference = 'UMP-' . $code;
        } while ($this->bookings->referenceExists($reference));

        return $reference;
    }
}
