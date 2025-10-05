<?php
declare(strict_types=1);

namespace App\Core;

final class Bootstrap
{
	public static function init(): void
	{
		self::ensureDirectories();
	}

	private static function ensureDirectories(): void
	{
		$paths = [
			dirname(__DIR__, 2) . '/logs',
			dirname(__DIR__, 2) . '/storage',
		];
		foreach ($paths as $path) {
			if (!is_dir($path)) {
				mkdir($path, 0775, true);
			}
		}
	}
}
