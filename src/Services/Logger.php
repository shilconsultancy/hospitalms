<?php
declare(strict_types=1);

namespace App\Services;

final class Logger
{
	private string $logFile;

	public function __construct(?string $path = null)
	{
		$this->logFile = $path ?: dirname(__DIR__, 2) . '/logs/app.log';
	}

	public function info(string $message, array $context = []): void
	{
		$this->write('INFO', $message, $context);
	}

	public function warning(string $message, array $context = []): void
	{
		$this->write('WARNING', $message, $context);
	}

	public function error(string $message, array $context = []): void
	{
		$this->write('ERROR', $message, $context);
	}

	private function write(string $level, string $message, array $context): void
	{
		$timestamp = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('c');
		$line = sprintf('[%s] %s: %s %s', $timestamp, $level, $message, $context ? json_encode($context) : '');
		file_put_contents($this->logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
	}
}


