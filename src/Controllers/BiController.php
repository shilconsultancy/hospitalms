<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

final class BiController extends Controller
{
	public function dashboard(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$pdo = Database::connection();
		$totalBeds = (int)($pdo->query('SELECT COUNT(1) FROM beds')->fetchColumn() ?: 0);
		$occupiedBeds = (int)($pdo->query("SELECT COUNT(1) FROM beds WHERE status = 'occupied'")->fetchColumn() ?: 0);
		$occupancy = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 2) : 0.0;
		$alOS = (float)($pdo->query("SELECT COALESCE(AVG(DATEDIFF(COALESCE(discharged_at, NOW()), admitted_at)),0) FROM admissions WHERE discharged_at IS NOT NULL")->fetchColumn() ?: 0);
		$apptsPerDay = (int)($pdo->query("SELECT COUNT(1) FROM appointments WHERE DATE(scheduled_at) = CURRENT_DATE")->fetchColumn() ?: 0);
		$html = '<h1>BI Dashboard</h1>';
		$html .= sprintf('<div>Bed Occupancy: %s%%</div>', number_format($occupancy, 2));
		$html .= sprintf('<div>ALOS: %s days</div>', number_format($alOS, 2));
		$html .= sprintf('<div>Appointments Today: %d</div>', $apptsPerDay);
		return $this->view($html);
	}
}


