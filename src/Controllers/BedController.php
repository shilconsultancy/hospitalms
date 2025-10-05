<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class BedController extends Controller
{
	public function index(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = 'SELECT b.id, b.bed_no, b.status, w.name AS ward FROM beds b JOIN wards w ON w.id = b.ward_id ORDER BY w.name, b.bed_no';
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Beds</h1><a href='/beds/create'>New Bed</a><ul>";
		foreach ($rows as $r) { $html .= sprintf("<li>%s - Bed %s (%s)</li>", htmlspecialchars($r['ward']), htmlspecialchars($r['bed_no']), htmlspecialchars($r['status'])); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function create(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$wards = Database::connection()->query('SELECT id, name FROM wards ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>New Bed</h1><form method='POST' action='/beds/store'><label>Ward</label><select name='ward_id'>";
		foreach ($wards as $w) { $html .= sprintf("<option value='%d'>%s</option>", (int)$w['id'], htmlspecialchars($w['name'])); }
		$html .= "</select><input name='bed_no' placeholder='Bed No' required><button type='submit'>Save</button></form>";
		return $this->view($html);
	}

	public function store(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare('INSERT INTO beds (ward_id, bed_no) VALUES (:w,:n)')->execute([
			'w' => (int)($_POST['ward_id'] ?? 0),
			'n' => $_POST['bed_no'] ?? '',
		]);
		header('Location: /beds');
		return '';
	}
}


