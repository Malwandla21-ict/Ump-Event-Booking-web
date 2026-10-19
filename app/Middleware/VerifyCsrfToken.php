<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;

final class VerifyCsrfToken implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if ($request->isPost() && !Csrf::verify($request->input(Csrf::FIELD))) {
            throw new HttpException(419);
        }

        return $next($request);
    }
}
