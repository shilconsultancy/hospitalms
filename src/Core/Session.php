<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
	public static function start(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
	}

	public static function set(string $key, mixed $value): void
	{
		$_SESSION[$key] = $value;
	}

	public static function get(string $key, mixed $default = null): mixed
	{
		return $_SESSION[$key] ?? $default;
	}

	public static function remove(string $key): void
	{
		unset($_SESSION[$key]);
	}

	public static function destroy(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			$_SESSION = [];
			session_destroy();
		}
	}
}
