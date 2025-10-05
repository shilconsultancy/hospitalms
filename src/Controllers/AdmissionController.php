<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class AdmissionController extends Controller
{
	public function dashboard(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = "SELECT w.name AS ward, b.id, b.bed_no, b.status, a.patient_id
			FROM beds b
			JOIN wards w ON w.id = b.ward_id
			LEFT JOIN admissions a ON a.bed_id = b.id AND a.discharged_at IS NULL
			ORDER BY w.name, b.bed_no";
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = '<h1>Bed Management</h1><ul>';
		foreach ($rows as $r) {
			$label = sprintf('%s - Bed %s (%s)%s', $r['ward'], $r['bed_no'], $r['status'], $r['patient_id'] ? ' - Occupied' : '');
			$html .= '<li>' . htmlspecialchars($label) . '</li>';
		}
		$html .= '</ul>';
		return $this->view($html);
	}

	public function admitForm(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$patients = Database::connection()->query('SELECT id, mrn, first_name, last_name FROM patients ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
		$beds = Database::connection()->query("SELECT b.id, w.name AS ward, b.bed_no FROM beds b JOIN wards w ON w.id = b.ward_id WHERE b.status = 'available' ORDER BY w.name, b.bed_no")->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Admit Patient</h1><form method='POST' action='/admissions/store'><label>Patient</label><select name='patient_id'>";
		foreach ($patients as $p) { $html .= sprintf("<option value='%d'>%s - %s %s</option>", (int)$p['id'], htmlspecialchars($p['mrn']), htmlspecialchars($p['first_name']), htmlspecialchars($p['last_name'])); }
		$html .= "</select><label>Bed</label><select name='bed_id'>";
		foreach ($beds as $b) { $html .= sprintf("<option value='%d'>%s - %s</option>", (int)$b['id'], htmlspecialchars($b['ward']), htmlspecialchars($b['bed_no'])); }
		$html .= "</select><button type='submit'>Admit</button></form>";
		return $this->view($html);
	}

	public function store(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$pdo = Database::connection();
		$pdo->prepare("INSERT INTO admissions (patient_id, bed_id, admitted_at) VALUES (:p,:b,NOW())")->execute([
			'p' => (int)($_POST['patient_id'] ?? 0),
			'b' => (int)($_POST['bed_id'] ?? 0),
		]);
		$pdo->prepare("UPDATE beds SET status = 'occupied' WHERE id = :b")->execute(['b' => (int)($_POST['bed_id'] ?? 0)]);
		header('Location: /inpatient');
		return '';
	}

	public function discharge(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$admissionId = (int)($_POST['admission_id'] ?? 0);
		$pdo = Database::connection();
		// Find bed id for this admission
		$bedId = (int)($pdo->query("SELECT bed_id FROM admissions WHERE id = {$admissionId}")->fetchColumn() ?: 0);
		$pdo->prepare('UPDATE admissions SET discharged_at = NOW() WHERE id = :id')->execute(['id' => $admissionId]);
		if ($bedId > 0) {
			$pdo->prepare("UPDATE beds SET status = 'available' WHERE id = :b")->execute(['b' => $bedId]);
		}
		header('Location: /inpatient');
		return '';
	}
}


