<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class AppointmentController extends Controller
{
	public function index(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$sql = 'SELECT a.id, p.mrn, p.first_name, p.last_name, a.scheduled_at FROM appointments a JOIN patients p ON p.id = a.patient_id ORDER BY a.scheduled_at DESC';
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Appointments</h1><a href='/appointments/create'>Schedule</a><ul>";
		foreach ($rows as $r) {
			$html .= sprintf(
				"<li>%s - %s %s @ %s</li>",
				htmlspecialchars($r['mrn']),
				htmlspecialchars($r['first_name']),
				htmlspecialchars($r['last_name']),
				htmlspecialchars($r['scheduled_at'])
			);
		}
		$html .= '</ul>';
		return $this->view($html);
	}

	public function create(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		return $this->view("<h1>Schedule Appointment</h1><form method='POST' action='/appointments/store'><input name='patient_id' placeholder='Patient ID' required><input name='doctor_id' placeholder='Doctor ID' required><input name='scheduled_at' placeholder='YYYY-MM-DD HH:MM:SS' required><button type='submit'>Save</button></form>");
	}

	public function store(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$pdo = Database::connection();
		$stmt = $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, scheduled_at) VALUES (:p,:d,:s)');
		$stmt->execute([
			'p' => (int)($_POST['patient_id'] ?? 0),
			'd' => (int)($_POST['doctor_id'] ?? 0),
			's' => $_POST['scheduled_at'] ?? '',
		]);
		header('Location: /appointments');
		return '';
	}
}


