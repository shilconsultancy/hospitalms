<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
	private static ?PDO $connection = null;

	public static function connection(): PDO
	{
		if (self::$connection instanceof PDO) {
			return self::$connection;
		}
		$config = require dirname(__DIR__, 2) . '/config/database.php';
		$dsn = sprintf(
			'mysql:host=%s;port=%d;dbname=%s;charset=%s',
			$config['host'],
			$config['port'],
			$config['database'],
			$config['charset']
		);
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		try {
			self::$connection = new PDO($dsn, $config['username'], $config['password'], $options);
			return self::$connection;
		} catch (PDOException $e) {
			http_response_code(500);
			echo 'Database connection failed';
			exit(1);
		}
	}
}


