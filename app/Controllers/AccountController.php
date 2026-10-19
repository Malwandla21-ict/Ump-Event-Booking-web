<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Exceptions\ValidationException;
use App\Repositories\BookingRepository;
use App\Repositories\UserRepository;

final class AccountController extends Controller
{
    public function edit(Request $request): string
    {
        return $this->view('account/profile', [
            'pageTitle' => 'Profile & security',
            'user' => Auth::user(),
            'counts' => (new BookingRepository())->countsForUser(Auth::id()),
        ]);
    }

    public function updateProfile(Request $request): Response
    {
        $input = $request->only(['full_name', 'email']);
        $data = Validator::validate($input, [
            'full_name' => 'required|min:2|max:100|name',
            'email' => 'required|email|max:120',
        ], ['full_name' => 'full name']);

        $users = new UserRepository();
        $email = mb_strtolower($data['email']);
        if ($users->emailTaken($email, Auth::id())) {
            throw new ValidationException(['email' => 'That email is already used by another account.'], $input);
        }

        $users->updateProfile(Auth::id(), $data['full_name'], $email);
        Auth::refresh();

        return $this->redirect('/account/profile', 'Profile updated.');
    }

    public function updatePassword(Request $request): Response
    {
        $min = Config::get('app.auth.password_min_length');
        $input = [
            'current_password' => $request->raw('current_password'),
            'password' => $request->raw('password'),
            'password_confirmation' => $request->raw('password_confirmation'),
        ];
        Validator::validate($input, [
            'current_password' => 'required',
            'password' => "required|min:$min|max:72|password|confirmed",
        ], ['current_password' => 'current password', 'password' => 'new password']);

        $users = new UserRepository();
        if (!password_verify($input['current_password'], (string) $users->passwordHash(Auth::id()))) {
            throw ValidationException::withMessage('current_password', 'Your current password is incorrect.');
        }

        $users->updatePassword(Auth::id(), password_hash($input['password'], PASSWORD_DEFAULT));
        Session::regenerate();

        return $this->redirect('/account/profile', 'Password changed.');
    }
}
