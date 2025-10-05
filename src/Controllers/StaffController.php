<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class StaffController extends Controller
{
	public function index(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$rows = Database::connection()
			->query('SELECT s.id, first_name, last_name, position FROM staff s ORDER BY id DESC')
			->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Staff</h1><a href='/staff/create'>Add</a><ul>";
		foreach ($rows as $r) {
			$html .= sprintf(
				"<li>%s %s (%s)</li>",
				htmlspecialchars($r['first_name']),
				htmlspecialchars($r['last_name']),
				htmlspecialchars((string)($r['position'] ?? ''))
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
		return $this->view("<h1>New Staff</h1><form method='POST' action='/staff/store'><input name='first_name' placeholder='First' required><input name='last_name' placeholder='Last' required><input name='position' placeholder='Position'><button type='submit'>Save</button></form>");
	}

	public function store(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$pdo = Database::connection();
		$stmt = $pdo->prepare('INSERT INTO staff (first_name, last_name, position) VALUES (:f,:l,:p)');
		$stmt->execute([
			'f' => $_POST['first_name'] ?? '',
			'l' => $_POST['last_name'] ?? '',
			'p' => $_POST['position'] ?? null,
		]);
		header('Location: /staff');
		return '';
	}

	public function linkForm(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$pdo = Database::connection();
		$staff = $pdo->query('SELECT id, first_name, last_name FROM staff ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
		$users = $pdo->query('SELECT id, email FROM users ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
		$roles = $pdo->query('SELECT id, name FROM roles ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Link Staff to User & Role</h1><form method='POST' action='/staff/link/save'>";
		$html .= "<label>Staff</label><select name='staff_id'>";
		foreach ($staff as $s) {
			$html .= sprintf("<option value='%d'>%s %s</option>", (int)$s['id'], htmlspecialchars($s['first_name']), htmlspecialchars($s['last_name']));
		}
		$html .= "</select>";
		$html .= "<label>User</label><select name='user_id'>";
		foreach ($users as $u) {
			$html .= sprintf("<option value='%d'>%s</option>", (int)$u['id'], htmlspecialchars($u['email']));
		}
		$html .= "</select>";
		$html .= "<label>Role</label><select name='role_id'>";
		foreach ($roles as $r) {
			$html .= sprintf("<option value='%d'>%s</option>", (int)$r['id'], htmlspecialchars($r['name']));
		}
		$html .= "</select><button type='submit'>Link</button></form>";
		return $this->view($html);
	}

	public function linkSave(): string
	{
		if (!Auth::check()) {
			return $this->view('Unauthorized', 401);
		}
		$staffId = (int)($_POST['staff_id'] ?? 0);
		$userId = (int)($_POST['user_id'] ?? 0);
		$roleId = (int)($_POST['role_id'] ?? 0);
		$pdo = Database::connection();
		$pdo->prepare('UPDATE staff SET user_id = :uid WHERE id = :sid')->execute(['uid' => $userId, 'sid' => $staffId]);
		$pdo->prepare('INSERT IGNORE INTO role_user (user_id, role_id) VALUES (:uid, :rid)')->execute(['uid' => $userId, 'rid' => $roleId]);
		header('Location: /staff');
		return '';
	}
}


