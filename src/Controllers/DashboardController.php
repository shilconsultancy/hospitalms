<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

final class DashboardController extends Controller
{
	public function index(): string
	{
		if (!Auth::check() || !Auth::userHasPermission('access_dashboard')) {
			return $this->view('Forbidden', 403);
		}
		return $this->view('Admin Dashboard');
	}
}


