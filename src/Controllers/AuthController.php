<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

final class AuthController extends Controller
{
	public function showLogin(): string
	{
		return $this->view('<form method="POST" action="/login"><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Password" required><button type="submit">Login</button></form>');
	}

	public function login(): string
	{
		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';
		if (!$email || !$password) {
			return $this->view('Invalid credentials', 422);
		}
		if (Auth::attempt($email, $password)) {
			header('Location: /dashboard');
			return '';
		}
		return $this->view('Unauthorized', 401);
	}

	public function logout(): string
	{
		Auth::logout();
		header('Location: /');
		return '';
	}
}


