<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Database;
use PDO;

final class Auth
{
	public static function id(): ?int
	{
		return Session::get('user_id');
	}

	public static function check(): bool
	{
		return self::id() !== null;
	}

	public static function attempt(string $email, string $password): bool
	{
		$pdo = Database::connection();
		$stmt = $pdo->prepare('SELECT id, password, is_active FROM users WHERE email = :email LIMIT 1');
		$stmt->execute(['email' => $email]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$user || (int)$user['is_active'] !== 1) {
			return false;
		}
		if (!password_verify($password, $user['password'])) {
			return false;
		}
		Session::set('user_id', (int)$user['id']);
		return true;
	}

	public static function logout(): void
	{
		Session::remove('user_id');
	}

	public static function userHasPermission(string $permission): bool
	{
		$userId = self::id();
		if ($userId === null) {
			return false;
		}
		$sql = 'SELECT COUNT(1) AS cnt
			FROM permission_role pr
			JOIN roles r ON r.id = pr.role_id
			JOIN role_user ru ON ru.role_id = r.id
			JOIN permissions p ON p.id = pr.permission_id
			WHERE ru.user_id = :uid AND p.name = :perm';
		$stmt = Database::connection()->prepare($sql);
		$stmt->execute(['uid' => $userId, 'perm' => $permission]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return isset($row['cnt']) && (int)$row['cnt'] > 0;
	}
}


