<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class PatientController extends Controller
{
	public function index(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$rows = Database::connection()
			->query('SELECT id, mrn, first_name, last_name FROM patients ORDER BY id DESC')
			->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Patients</h1><a href='/patients/create'>Register</a><ul>";
		foreach ($rows as $r) {
			$html .= sprintf(
				"<li>%s - %s %s</li>",
				htmlspecialchars($r['mrn']),
				htmlspecialchars($r['first_name']),
				htmlspecialchars($r['last_name'])
			);
		}
		$html .= '</ul>';
		return $this->view($html);
	}

	public function show(int $id): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$sql = 'SELECT p.*, a.id AS admission_id, a.bed_id, a.admitted_at, a.discharged_at FROM patients p LEFT JOIN admissions a ON a.patient_id = p.id AND a.discharged_at IS NULL WHERE p.id = :id LIMIT 1';
		$stmt = Database::connection()->prepare($sql);
		$stmt->execute(['id' => $id]);
		$patient = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$patient) { return $this->view('Not Found', 404); }
		$html = '<h1>EMR</h1>';
		$html .= sprintf('<div>MRN: %s</div>', htmlspecialchars($patient['mrn']));
		$html .= sprintf('<div>Name: %s %s</div>', htmlspecialchars($patient['first_name']), htmlspecialchars($patient['last_name']));
		if (!empty($patient['admission_id'])) {
			$html .= sprintf('<div>Admitted: %s (Bed #%s)</div>', htmlspecialchars((string)$patient['admitted_at']), htmlspecialchars((string)$patient['bed_id']));
		}
		// Show latest appointments
		$apps = Database::connection()->prepare('SELECT scheduled_at, status FROM appointments WHERE patient_id = :pid ORDER BY scheduled_at DESC LIMIT 5');
		$apps->execute(['pid' => $id]);
		$html .= '<h2>Recent Appointments</h2><ul>';
		foreach ($apps->fetchAll(PDO::FETCH_ASSOC) as $a) {
			$html .= sprintf('<li>%s - %s</li>', htmlspecialchars($a['scheduled_at']), htmlspecialchars($a['status']));
		}
		$html .= '</ul>';
		return $this->view($html);
	}

	public function create(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		return $this->view("<h1>Register Patient</h1><form method='POST' action='/patients/store'><input name='mrn' placeholder='MRN' required><input name='first_name' placeholder='First' required><input name='last_name' placeholder='Last' required><button type='submit'>Save</button></form>");
	}

	public function store(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$pdo = Database::connection();
		$stmt = $pdo->prepare('INSERT INTO patients (mrn, first_name, last_name) VALUES (:m,:f,:l)');
		$stmt->execute([
			'm' => $_POST['mrn'] ?? '',
			'f' => $_POST['first_name'] ?? '',
			'l' => $_POST['last_name'] ?? '',
		]);
		header('Location: /patients');
		return '';
	}
}


