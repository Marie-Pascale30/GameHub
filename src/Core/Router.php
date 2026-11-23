<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routeur minimal : chemins statiques ou paramètres numériques {id}.
 */
final class Router
{
    /** @var list<array{method:string,regex:string,handler:array{0:class-string,1:string}}> */
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[1-9][0-9]{0,8})', $pattern) . '$#';
        $this->routes[] = ['method' => $method, 'regex' => $regex, 'handler' => $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        $methodMismatch = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $methodMismatch = true;
                continue;
            }
            $params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            [$class, $action] = $route['handler'];
            (new $class())->$action(...array_values($params));
            return;
        }

        if ($methodMismatch) {
            Response::error(405, 'Méthode non autorisée.');
        }
        Response::error(404, 'Page introuvable.');
    }
}
