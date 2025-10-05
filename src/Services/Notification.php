<?php
declare(strict_types=1);

namespace App\Services;

final class Notification
{
	public function sendEmail(string $to, string $subject, string $body): void
	{
		// Stub: integrate with mailer later
	}

	public function sendSms(string $to, string $message): void
	{
		// Stub: integrate with SMS provider later
	}
}


