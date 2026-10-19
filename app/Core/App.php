<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ValidationException;
use App\Middleware\Middleware;
use Throwable;

final class App
{
    /** Short names that routes use to refer to middleware. */
    private const MIDDLEWARE = [
        'csrf' => \App\Middleware\VerifyCsrfToken::class,
        'auth' => \App\Middleware\Authenticate::class,
        'guest' => \App\Middleware\RedirectIfAuthenticated::class,
        'admin' => \App\Middleware\RequireAdmin::class,
    ];

    public function __construct(private readonly Router $router)
    {
    }

    public function run(): void
    {
        $request = Request::capture();
        Session::start($request->isSecure());

        try {
            $response = $this->handle($request);

            // Remember the last page shown so failed form posts can return to it.
            if ($request->method === 'GET' && $response->isHtmlPage()) {
                $query = $_SERVER['QUERY_STRING'] ?? '';
                Session::put('_previous_path', $request->path . ($query !== '' ? "?$query" : ''));
            }
        } catch (ValidationException $e) {
            Session::flash('errors', $e->errors);
            Session::flash('old', $e->old);
            $response = Response::redirect($e->redirectTo ?? Session::get('_previous_path') ?? $request->previousPath());
        } catch (Throwable $e) {
            $response = ErrorHandler::render($e);
        }

        $this->withSecurityHeaders($response, $request)->send();
    }

    private function handle(Request $request): Response
    {
        $route = $this->router->match($request->method, $request->path);

        // CSRF runs on every route; it only acts on POST requests.
        $middleware = ['csrf', ...$route['middleware']];

        $core = function (Request $request) use ($route): Response {
            [$class, $method] = $route['handler'];
            $result = (new $class())->{$method}($request, ...array_values($route['params']));

            return $result instanceof Response ? $result : Response::html((string) $result);
        };

        $pipeline = array_reduce(
            array_reverse($middleware),
            static function (callable $next, string $alias): callable {
                /** @var Middleware $instance */
                $instance = new (self::MIDDLEWARE[$alias])();

                return static fn (Request $request): Response => $instance->handle($request, $next);
            },
            $core,
        );

        return $pipeline($request);
    }

    private function withSecurityHeaders(Response $response, Request $request): Response
    {
        $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->withHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->withHeader('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self'",
                "style-src 'self'",
                "img-src 'self' data:",
                "font-src 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "object-src 'none'",
            ]));

        if ($request->isSecure()) {
            $response->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Pages may contain personal data — keep them out of shared caches.
        return $response->withHeader('Cache-Control', 'no-store, private');
    }
}
