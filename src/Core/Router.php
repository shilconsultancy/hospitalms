<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
	private array $routes = [
		'GET' => [],
		'POST' => [],
	];

	public function get(string $path, callable|array $handler): void
	{
		$this->routes['GET'][$this->normalize($path)] = $handler;
	}

	public function post(string $path, callable|array $handler): void
	{
		$this->routes['POST'][$this->normalize($path)] = $handler;
	}

	public function dispatch(string $uri): void
	{
		$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$path = parse_url($uri, PHP_URL_PATH) ?: '/';
		$path = $this->normalize($path);
		$handler = $this->routes[$method][$path] ?? null;

		if ($handler === null) {
			http_response_code(404);
			echo '404';
			return;
		}

		if (is_callable($handler)) {
			$handler();
		} else {
			[$controllerClass, $method] = $handler;
			$controller = new $controllerClass();
			$controller->$method();
		}
	}

	private function normalize(string $path): string
	{
		$path = trim($path, '/');
		return $path === '' ? '/' : '/' . $path;
	}
}
