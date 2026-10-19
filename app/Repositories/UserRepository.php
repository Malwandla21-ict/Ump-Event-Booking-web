<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends Repository
{
    /** Safe columns — the password hash is only ever selected explicitly. */
    private const COLUMNS = 'id, full_name, email, role, created_at';

    public function find(int $id): ?array
    {
        return $this->db->fetch('SELECT ' . self::COLUMNS . ' FROM users WHERE id = ?', [$id]);
    }

    /** Includes password_hash — for authentication only. */
    public function findWithPasswordByEmail(string $email): ?array
    {
        return $this->db->fetch('SELECT ' . self::COLUMNS . ', password_hash FROM users WHERE email = ?', [$email]);
    }

    public function passwordHash(int $id): ?string
    {
        return $this->db->fetchValue('SELECT password_hash FROM users WHERE id = ?', [$id]);
    }

    public function emailTaken(string $email, ?int $exceptId = null): bool
    {
        return (bool) $this->db->fetchValue(
            'SELECT 1 FROM users WHERE email = ? AND id <> ?',
            [$email, $exceptId ?? 0],
        );
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        return $this->db->insert(
            'INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)',
            [$name, $email, $passwordHash],
        );
    }

    public function updateProfile(int $id, string $name, string $email): void
    {
        $this->db->query('UPDATE users SET full_name = ?, email = ? WHERE id = ?', [$name, $email, $id]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $this->db->query('UPDATE users SET password_hash = ? WHERE id = ?', [$passwordHash, $id]);
    }

    public function count(): int
    {
        return (int) $this->db->fetchValue("SELECT COUNT(*) FROM users WHERE role = 'user'");
    }
}
