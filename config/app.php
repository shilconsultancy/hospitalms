<?php
return [
	'app_name' => 'HospitalMS',
	'env' => getenv('APP_ENV') ?: 'local',
	'url' => getenv('APP_URL') ?: 'http://localhost/hospitalMS/public',
	'timezone' => 'UTC',
	'log_path' => dirname(__DIR__) . '/logs/app.log',
];
