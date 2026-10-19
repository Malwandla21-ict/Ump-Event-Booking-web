<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuthService;

final class AuthController extends Controller
{
    public function showLogin(Request $request): string
    {
        $this->rememberNext($request);

        return $this->view('auth/login', ['pageTitle' => 'Sign in'], 'layouts/auth');
    }

    public function login(Request $request): Response
    {
        $input = ['email' => $request->input('email', ''), 'password' => $request->raw('password')];
        Validator::validate($input, ['email' => 'required|email', 'password' => 'required']);

        $user = (new AuthService())->attempt($input['email'], $input['password'], $request->ip());

        $fallback = $user['role'] === 'admin' ? '/admin' : '/account/bookings';
        $firstName = explode(' ', $user['full_name'])[0];

        return $this->redirect($this->intended($fallback), "Welcome back, $firstName!");
    }

    public function showRegister(Request $request): string
    {
        $this->rememberNext($request);

        return $this->view('auth/register', ['pageTitle' => 'Create account'], 'layouts/auth');
    }

    public function register(Request $request): Response
    {
        $user = (new AuthService())->register([
            ...$request->only(['full_name', 'email']),
            'password' => $request->raw('password'),
            'password_confirmation' => $request->raw('password_confirmation'),
        ]);

        $firstName = explode(' ', $user['full_name'])[0];

        return $this->redirect($this->intended('/events'), "Welcome, $firstName! Your account is ready — find an event to book.");
    }

    public function logout(Request $request): Response
    {
        Auth::logout();

        return $this->redirect('/', 'You have been signed out.', 'info');
    }

    /** Where the user was heading before being asked to sign in. */
    private function intended(string $fallback): string
    {
        $path = (string) Session::pull('intended', '');

        return self::isSafePath($path) ? $path : $fallback;
    }

    /** "?next=/events/foo" from a "Sign in to book" link. */
    private function rememberNext(Request $request): void
    {
        $next = (string) $request->query('next', '');
        if (self::isSafePath($next)) {
            Session::put('intended', $next);
        }
    }

    /** Only same-site paths — blocks open redirects like "//evil.com" or "/\evil.com". */
    private static function isSafePath(string $path): bool
    {
        return preg_match('#^/(?![/\\\\])[A-Za-z0-9/_\-.?=&%]*$#', $path) === 1;
    }
}
