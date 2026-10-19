<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class Authenticate implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        // Remember where they were going (internal path only, so no open redirect).
        Session::put('intended', $request->isPost() ? $request->previousPath() : $request->path);
        Session::toast('info', 'Please sign in to continue.');

        return Response::redirect('/login');
    }
}
