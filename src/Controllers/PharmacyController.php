<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class PharmacyController extends Controller
{
	public function inventory(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = "SELECT i.id, i.name, COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE -sm.quantity END), 0) AS stock
			FROM items i
			LEFT JOIN stock_movements sm ON sm.item_id = i.id
			GROUP BY i.id, i.name ORDER BY i.name";
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = '<h1>Inventory</h1><ul>';
		foreach ($rows as $r) { $html .= sprintf('<li>%s: %d</li>', htmlspecialchars($r['name']), (int)$r['stock']); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function prescriptions(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = 'SELECT pr.id, p.mrn, p.first_name, p.last_name, pr.created_at FROM prescriptions pr JOIN patients p ON p.id = pr.patient_id ORDER BY pr.id DESC';
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Prescriptions</h1><a href='/pharmacy/prescriptions/create'>New Prescription</a><ul>";
		foreach ($rows as $r) { $html .= sprintf('<li>#%d - %s %s (%s)</li>', (int)$r['id'], htmlspecialchars($r['first_name']), htmlspecialchars($r['last_name']), htmlspecialchars($r['mrn'])); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function createPrescription(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		return $this->view("<h1>New Prescription</h1><form method='POST' action='/pharmacy/prescriptions/store'><input name='patient_id' placeholder='Patient ID' required><input name='doctor_id' placeholder='Doctor ID' required><button type='submit'>Create</button></form>");
	}

	public function storePrescription(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare('INSERT INTO prescriptions (patient_id, doctor_id) VALUES (:p,:d)')->execute([
			'p' => (int)($_POST['patient_id'] ?? 0),
			'd' => (int)($_POST['doctor_id'] ?? 0),
		]);
		header('Location: /pharmacy/prescriptions');
		return '';
	}
}


