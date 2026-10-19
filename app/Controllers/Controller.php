<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        return View::render($view, $data, $layout);
    }

    /** Post/Redirect/Get with an optional toast message. */
    protected function redirect(string $path, ?string $message = null, string $type = 'success'): Response
    {
        if ($message !== null) {
            Session::toast($type, $message);
        }

        return Response::redirect($path);
    }
}
