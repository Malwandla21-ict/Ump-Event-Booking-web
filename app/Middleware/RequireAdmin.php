<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;

/** Must run after `auth`. */
final class RequireAdmin implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Auth::isAdmin()) {
            throw HttpException::forbidden();
        }

        return $next($request);
    }
}
