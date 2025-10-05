<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
	protected function view(string $content, int $status = 200): string
	{
		http_response_code($status);
		return $content;
	}
}
