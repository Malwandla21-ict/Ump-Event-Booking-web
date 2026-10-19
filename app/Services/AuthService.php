<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Validator;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

final class AuthService
{
    /** bcrypt hash of a random string nobody knows — see attempt(). */
    private const DUMMY_HASH = '$2y$10$K3Jn9la5uYwWZ3rulka.3OL.WjaBUwtuWjobBA5bhVpI9KOiZOBqu';

    public function __construct(
        private readonly UserRepository $users = new UserRepository(),
        private readonly LoginThrottle $throttle = new LoginThrottle(),
    ) {
    }

    /**
     * @throws ValidationException on bad credentials or when locked out
     */
    public function attempt(string $email, string $password, string $ip): array
    {
        $email = mb_strtolower($email);

        if ($this->throttle->tooManyAttempts($email, $ip)) {
            $minutes = $this->throttle->minutesUntilUnlocked($email, $ip);
            throw new ValidationException(
                ['email' => "Too many failed attempts. Try again in " . pluralize($minutes, 'minute') . '.'],
                ['email' => $email],
            );
        }

        $user = $this->users->findWithPasswordByEmail($email);

        // Verify against a dummy hash when the user doesn't exist so response
        // time doesn't reveal which emails are registered.
        $hash = $user['password_hash'] ?? self::DUMMY_HASH;
        $valid = password_verify($password, $hash) && $user !== null;

        if (!$valid) {
            $this->throttle->hit($email, $ip);
            // Same message for "no such email" and "wrong password" (no user enumeration).
            throw new ValidationException(['email' => 'Those credentials do not match our records.'], ['email' => $email]);
        }

        $this->throttle->clear($email, $ip);

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        unset($user['password_hash']);
        Auth::login($user);

        return $user;
    }

    /** @throws ValidationException */
    public function register(array $input): array
    {
        $min = Config::get('app.auth.password_min_length');
        $data = Validator::validate($input, [
            'full_name' => 'required|min:2|max:100|name',
            'email' => 'required|email|max:120',
            'password' => "required|min:$min|max:72|password|confirmed",
        ], ['full_name' => 'full name']);

        $email = mb_strtolower($data['email']);
        if ($this->users->emailTaken($email)) {
            throw new ValidationException(
                ['email' => 'An account with this email already exists. Try signing in instead.'],
                ['full_name' => $data['full_name'], 'email' => $email],
            );
        }

        $id = $this->users->create($data['full_name'], $email, password_hash($data['password'], PASSWORD_DEFAULT));
        $user = $this->users->find($id);
        Auth::login($user);

        return $user;
    }
}
