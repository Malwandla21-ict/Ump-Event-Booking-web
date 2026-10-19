<?php

declare(strict_types=1);

namespace App\Repositories;

final class BookingRepository extends Repository
{
    private const WITH_EVENT = <<<SQL
        SELECT b.id, b.reference, b.quantity, b.status, b.created_at, b.cancelled_at,
               e.id AS event_id, e.title, e.slug, e.venue, e.category, e.starts_at, e.ends_at,
               e.image_path, e.status AS event_status
        FROM bookings b
        JOIN events e ON e.id = b.event_id
        SQL;

    /** @param 'upcoming'|'past'|'cancelled' $scope */
    public function forUser(int $userId, string $scope): array
    {
        $condition = match ($scope) {
            'upcoming' => "b.status = 'confirmed' AND e.starts_at >= NOW() ORDER BY e.starts_at ASC",
            'past' => "b.status = 'confirmed' AND e.starts_at < NOW() ORDER BY e.starts_at DESC",
            'cancelled' => "b.status = 'cancelled' ORDER BY b.cancelled_at DESC",
        };

        return $this->db->fetchAll(self::WITH_EVENT . " WHERE b.user_id = ? AND $condition", [$userId]);
    }

    /** @return array{upcoming: int, past: int, cancelled: int} */
    public function countsForUser(int $userId): array
    {
        return array_map('intval', $this->db->fetch(<<<SQL
            SELECT COALESCE(SUM(b.status = 'confirmed' AND e.starts_at >= NOW()), 0) AS upcoming,
                   COALESCE(SUM(b.status = 'confirmed' AND e.starts_at < NOW()), 0) AS past,
                   COALESCE(SUM(b.status = 'cancelled'), 0) AS cancelled
            FROM bookings b JOIN events e ON e.id = b.event_id
            WHERE b.user_id = ?
            SQL, [$userId]));
    }

    /** Scoped to the user, so nobody can view or cancel someone else's booking by guessing a reference. */
    public function findForUser(int $userId, string $reference): ?array
    {
        return $this->db->fetch(self::WITH_EVENT . ' WHERE b.user_id = ? AND b.reference = ?', [$userId, $reference]);
    }

    public function findByUserAndEvent(int $userId, int $eventId, bool $lock = false): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM bookings WHERE user_id = ? AND event_id = ?' . ($lock ? ' FOR UPDATE' : ''),
            [$userId, $eventId],
        );
    }

    public function seatsTaken(int $eventId): int
    {
        return (int) $this->db->fetchValue(
            "SELECT COALESCE(SUM(quantity), 0) FROM bookings WHERE event_id = ? AND status = 'confirmed'",
            [$eventId],
        );
    }

    public function referenceExists(string $reference): bool
    {
        return (bool) $this->db->fetchValue('SELECT 1 FROM bookings WHERE reference = ?', [$reference]);
    }

    public function create(string $reference, int $userId, int $eventId, int $quantity): void
    {
        $this->db->query(
            'INSERT INTO bookings (reference, user_id, event_id, quantity) VALUES (?, ?, ?, ?)',
            [$reference, $userId, $eventId, $quantity],
        );
    }

    /** Re-activate a previously cancelled booking (one row per user+event). */
    public function reactivate(int $bookingId, string $reference, int $quantity): void
    {
        $this->db->query(
            "UPDATE bookings SET status = 'confirmed', reference = ?, quantity = ?, created_at = NOW(), cancelled_at = NULL WHERE id = ?",
            [$reference, $quantity, $bookingId],
        );
    }

    public function cancel(int $bookingId): void
    {
        $this->db->query("UPDATE bookings SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?", [$bookingId]);
    }

    public function attendees(int $eventId): array
    {
        return $this->db->fetchAll(<<<SQL
            SELECT b.reference, b.quantity, b.status, b.created_at, b.cancelled_at, u.full_name, u.email
            FROM bookings b JOIN users u ON u.id = b.user_id
            WHERE b.event_id = ?
            ORDER BY b.status ASC, b.created_at DESC
            SQL, [$eventId]);
    }

    public function recent(int $limit): array
    {
        return $this->db->fetchAll(<<<SQL
            SELECT b.reference, b.quantity, b.status, b.created_at, u.full_name, e.title, e.id AS event_id
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            JOIN events e ON e.id = b.event_id
            ORDER BY b.created_at DESC
            LIMIT ?
            SQL, [$limit]);
    }

    public function countConfirmed(): int
    {
        return (int) $this->db->fetchValue("SELECT COALESCE(SUM(quantity), 0) FROM bookings WHERE status = 'confirmed'");
    }
}
