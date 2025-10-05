<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class WardController extends Controller
{
	public function index(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$rows = Database::connection()->query('SELECT id, name FROM wards ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Wards</h1><a href='/wards/create'>New Ward</a><ul>";
		foreach ($rows as $r) { $html .= sprintf("<li>%s</li>", htmlspecialchars($r['name'])); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function create(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		return $this->view("<h1>New Ward</h1><form method='POST' action='/wards/store'><input name='name' placeholder='Ward name' required><button type='submit'>Save</button></form>");
	}

	public function store(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare('INSERT INTO wards (name) VALUES (:n)')->execute(['n' => $_POST['name'] ?? '']);
		header('Location: /wards');
		return '';
	}
}


