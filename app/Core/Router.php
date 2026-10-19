<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, regex: string, handler: array, middleware: string[]}> */
    private array $routes = [];

    /** @var string[] */
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function get(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    /** Apply a URL prefix and/or middleware to every route registered inside $routes. */
    public function group(string $prefix, array $middleware, callable $routes): void
    {
        [$prevPrefix, $prevMiddleware] = [$this->groupPrefix, $this->groupMiddleware];
        $this->groupPrefix .= $prefix;
        $this->groupMiddleware = [...$this->groupMiddleware, ...$middleware];

        $routes($this);

        [$this->groupPrefix, $this->groupMiddleware] = [$prevPrefix, $prevMiddleware];
    }

    private function add(string $method, string $path, array $handler, array $middleware): self
    {
        $pattern = '/' . trim($this->groupPrefix . $path, '/');
        // {param} matches one path segment; {id:\d+} lets a route constrain it.
        $regex = preg_replace_callback(
            '/\{(\w+)(?::([^}]+))?\}/',
            static fn (array $m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
            $pattern,
        );

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => '#^' . $regex . '$#',
            'handler' => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
        ];

        return $this;
    }

    /**
     * @return array{handler: array, params: array<string, string>, middleware: string[]}
     */
    public function match(string $method, string $path): array
    {
        $allowed = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method && !($method === 'HEAD' && $route['method'] === 'GET')) {
                $allowed[] = $route['method'];
                continue;
            }

            return [
                'handler' => $route['handler'],
                'params' => array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY),
                'middleware' => $route['middleware'],
            ];
        }

        if ($allowed) {
            throw new HttpException(405, 'That action is not allowed here.');
        }

        throw HttpException::notFound();
    }
}
