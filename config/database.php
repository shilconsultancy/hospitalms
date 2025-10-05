<?php
return [
	'driver' => 'mysql',
	'host' => getenv('DB_HOST') ?: '127.0.0.1',
	'port' => (int)(getenv('DB_PORT') ?: 3306),
	'database' => getenv('DB_DATABASE') ?: 'hospital_ms',
	'username' => getenv('DB_USERNAME') ?: 'root',
	'password' => getenv('DB_PASSWORD') ?: '',
	'charset' => 'utf8mb4',
	'collation' => 'utf8mb4_unicode_ci',
];
