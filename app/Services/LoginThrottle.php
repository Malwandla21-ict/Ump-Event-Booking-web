<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;

/**
 * Brute-force protection: after N failed sign-ins for an email from one IP,
 * further attempts are refused for a cool-down window.
 */
final class LoginThrottle
{
    private readonly Database $db;
    private readonly int $maxAttempts;
    private readonly int $lockoutMinutes;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::connection();
        $this->maxAttempts = (int) Config::get('app.auth.max_login_attempts');
        $this->lockoutMinutes = (int) Config::get('app.auth.lockout_minutes');
    }

    public function tooManyAttempts(string $email, string $ip): bool
    {
        return $this->recentAttempts($email, $ip) >= $this->maxAttempts;
    }

    /** Minutes until the oldest counted attempt expires. */
    public function minutesUntilUnlocked(string $email, string $ip): int
    {
        $oldest = $this->db->fetchValue(
            'SELECT MIN(attempted_at) FROM login_attempts WHERE email = ? AND ip_address = ? AND attempted_at > NOW() - INTERVAL ? MINUTE',
            [$email, $ip, $this->lockoutMinutes],
        );
        if (!$oldest) {
            return 0;
        }

        $unlockAt = strtotime($oldest) + $this->lockoutMinutes * 60;

        return max(1, (int) ceil(($unlockAt - time()) / 60));
    }

    public function hit(string $email, string $ip): void
    {
        $this->db->query('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)', [$email, $ip]);

        // Opportunistic cleanup so the table never grows unbounded.
        if (random_int(1, 50) === 1) {
            $this->db->query('DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY');
        }
    }

    public function clear(string $email, string $ip): void
    {
        $this->db->query('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?', [$email, $ip]);
    }

    private function recentAttempts(string $email, string $ip): int
    {
        return (int) $this->db->fetchValue(
            'SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND attempted_at > NOW() - INTERVAL ? MINUTE',
            [$email, $ip, $this->lockoutMinutes],
        );
    }
}
