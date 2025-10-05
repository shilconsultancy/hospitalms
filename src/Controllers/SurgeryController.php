<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class SurgeryController extends Controller
{
	public function index(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = 'SELECT s.id, s.title, s.scheduled_at, p.mrn, p.first_name, p.last_name FROM surgeries s JOIN patients p ON p.id = s.patient_id ORDER BY s.scheduled_at DESC';
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>OT Schedule</h1><a href='/ot/create'>Schedule Surgery</a><ul>";
		foreach ($rows as $r) {
			$html .= sprintf('<li>%s - %s %s @ %s</li>', htmlspecialchars($r['title']), htmlspecialchars($r['first_name']), htmlspecialchars($r['last_name']), htmlspecialchars($r['scheduled_at']));
		}
		$html .= '</ul>';
		return $this->view($html);
	}

	public function create(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		return $this->view("<h1>New Surgery</h1><form method='POST' action='/ot/store'><input name='patient_id' placeholder='Patient ID' required><input name='doctor_id' placeholder='Doctor ID' required><input name='title' placeholder='Title' required><input name='scheduled_at' placeholder='YYYY-MM-DD HH:MM:SS' required><button type='submit'>Save</button></form>");
	}

	public function store(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		// Conflict check stub: could query overlapping surgeries for doctor/time
		Database::connection()->prepare('INSERT INTO surgeries (patient_id, doctor_id, title, scheduled_at) VALUES (:p,:d,:t,:s)')->execute([
			'p' => (int)($_POST['patient_id'] ?? 0),
			'd' => (int)($_POST['doctor_id'] ?? 0),
			't' => $_POST['title'] ?? '',
			's' => $_POST['scheduled_at'] ?? '',
		]);
		header('Location: /ot');
		return '';
	}
}


