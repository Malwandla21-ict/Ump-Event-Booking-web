<?php

declare(strict_types=1);

namespace App\Repositories;

final class EventRepository extends Repository
{
    /** Event columns plus how many seats are already taken by confirmed bookings. */
    private const SELECT = <<<SQL
        SELECT e.*,
               COALESCE(b.seats_taken, 0) AS seats_taken,
               GREATEST(e.capacity - COALESCE(b.seats_taken, 0), 0) AS seats_left
        FROM events e
        LEFT JOIN (
            SELECT event_id, SUM(quantity) AS seats_taken
            FROM bookings
            WHERE status = 'confirmed'
            GROUP BY event_id
        ) b ON b.event_id = e.id
        SQL;

    /**
     * Public listing with search, category filter and pagination.
     *
     * @return array{items: array[], total: int}
     */
    public function paginatePublished(string $search, ?string $category, bool $past, int $page, int $perPage): array
    {
        $where = ["e.status = 'published'", $past ? 'e.starts_at < NOW()' : 'e.starts_at >= NOW()'];
        $params = [];

        if ($search !== '') {
            $where[] = '(e.title LIKE ? OR e.venue LIKE ? OR e.description LIKE ?)';
            array_push($params, self::like($search), self::like($search), self::like($search));
        }
        if ($category !== null) {
            $where[] = 'e.category = ?';
            $params[] = $category;
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $total = (int) $this->db->fetchValue('SELECT COUNT(*) FROM events e' . $whereSql, $params);

        $items = $this->db->fetchAll(
            self::SELECT . $whereSql . ' ORDER BY e.starts_at ' . ($past ? 'DESC' : 'ASC') . ' LIMIT ? OFFSET ?',
            [...$params, $perPage, ($page - 1) * $perPage],
        );

        return ['items' => $items, 'total' => $total];
    }

    public function upcoming(int $limit): array
    {
        return $this->db->fetchAll(
            self::SELECT . " WHERE e.status = 'published' AND e.starts_at >= NOW() ORDER BY e.starts_at ASC LIMIT ?",
            [$limit],
        );
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        return $this->db->fetch(self::SELECT . " WHERE e.slug = ? AND e.status <> 'draft'", [$slug]);
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(self::SELECT . ' WHERE e.id = ?', [$id]);
    }

    /** Every event for the admin table, newest first. */
    public function allForAdmin(string $search = ''): array
    {
        if ($search === '') {
            return $this->db->fetchAll(self::SELECT . ' ORDER BY e.starts_at DESC');
        }

        return $this->db->fetchAll(
            self::SELECT . ' WHERE e.title LIKE ? OR e.venue LIKE ? ORDER BY e.starts_at DESC',
            [self::like($search), self::like($search)],
        );
    }

    /** Locks the event row until the surrounding transaction ends. */
    public function lockForBooking(int $id): ?array
    {
        return $this->db->fetch('SELECT id, capacity, starts_at, status FROM events WHERE id = ? FOR UPDATE', [$id]);
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            'INSERT INTO events (title, slug, description, category, venue, starts_at, ends_at, capacity, image_path, status, created_by)
             VALUES (:title, :slug, :description, :category, :venue, :starts_at, :ends_at, :capacity, :image_path, :status, :created_by)',
            $data,
        );
    }

    public function update(int $id, array $data): void
    {
        $sets = implode(', ', array_map(static fn (string $col) => "$col = :$col", array_keys($data)));
        $this->db->query("UPDATE events SET $sets WHERE id = :id", [...$data, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->query('DELETE FROM events WHERE id = ?', [$id]);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return (bool) $this->db->fetchValue('SELECT 1 FROM events WHERE slug = ? AND id <> ?', [$slug, $exceptId ?? 0]);
    }

    /** @return array{total: int, upcoming: int, seats: int, booked: int} */
    public function stats(): array
    {
        return array_map('intval', $this->db->fetch(<<<SQL
            SELECT COUNT(*) AS total,
                   COALESCE(SUM(e.status = 'published' AND e.starts_at >= NOW()), 0) AS upcoming,
                   COALESCE(SUM(CASE WHEN e.status = 'published' AND e.starts_at >= NOW() THEN e.capacity END), 0) AS seats,
                   COALESCE(SUM(CASE WHEN e.status = 'published' AND e.starts_at >= NOW() THEN COALESCE(b.seats_taken, 0) END), 0) AS booked
            FROM events e
            LEFT JOIN (SELECT event_id, SUM(quantity) AS seats_taken FROM bookings WHERE status = 'confirmed' GROUP BY event_id) b
                ON b.event_id = e.id
            SQL));
    }

    /** Distinct categories that currently have upcoming events (for filter chips). */
    public function activeCategories(): array
    {
        return array_column($this->db->fetchAll(
            "SELECT category, COUNT(*) AS n FROM events WHERE status = 'published' AND starts_at >= NOW() GROUP BY category ORDER BY n DESC",
        ), 'n', 'category');
    }
}
