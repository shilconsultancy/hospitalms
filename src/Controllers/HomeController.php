<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class HomeController extends Controller
{
	public function index(): string
	{
		return $this->view('<!DOCTYPE html>
<html lang="en">
<head>
	   <meta charset="UTF-8">
	   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	   <title>Hospital Management System</title>
	   <style>
	       body { font-family: Arial, sans-serif; margin: 40px; }
	       .container { max-width: 800px; margin: 0 auto; }
	       h1 { color: #333; }
	       .welcome { background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0; }
	       .nav { margin: 20px 0; }
	       .nav a { margin-right: 20px; text-decoration: none; color: #007bff; }
	       .nav a:hover { text-decoration: underline; }
	   </style>
</head>
<body>
	   <div class="container">
	       <h1>Welcome to Hospital Management System</h1>
	       <div class="welcome">
	           <p>This is the Hospital Management System. Please use the navigation links below or login to access the system.</p>
	       </div>
	       <div class="nav">
	           <a href="/login">Login</a>
	           <a href="/dashboard">Dashboard</a>
	           <a href="/patients">Patients</a>
	           <a href="/staff">Staff</a>
	           <a href="/appointments">Appointments</a>
	       </div>
	   </div>
</body>
</html>');
	}
}
