<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HealthAuthTest extends TestCase
{
	public function test_health_endpoint_is_ok(): void
	{
		// Simple sanity: include router and capture output
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/health';
		ob_start();
		require __DIR__ . '/../../public/index.php';
		$out = ob_get_clean();
		$this->assertSame('OK', $out);
	}
}


