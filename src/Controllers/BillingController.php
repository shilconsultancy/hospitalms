<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class BillingController extends Controller
{
	public function invoices(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = 'SELECT i.id, i.status, p.mrn, p.first_name, p.last_name FROM invoices i JOIN patients p ON p.id = i.patient_id ORDER BY i.id DESC';
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Invoices</h1><a href='/billing/invoices/create'>New Invoice</a><ul>";
		foreach ($rows as $r) { $html .= sprintf('<li>#%d - %s %s (%s) - %s</li>', (int)$r['id'], htmlspecialchars($r['first_name']), htmlspecialchars($r['last_name']), htmlspecialchars($r['mrn']), htmlspecialchars($r['status'])); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function createInvoice(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		return $this->view("<h1>New Invoice</h1><form method='POST' action='/billing/invoices/store'><input name='patient_id' placeholder='Patient ID' required><button type='submit'>Create</button></form>");
	}

	public function storeInvoice(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare("INSERT INTO invoices (patient_id, status) VALUES (:p, 'open')")->execute(['p' => (int)($_POST['patient_id'] ?? 0)]);
		header('Location: /billing/invoices');
		return '';
	}

	public function addItem(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		return $this->view("<h1>Add Invoice Item</h1><form method='POST' action='/billing/invoices/items/store'><input name='invoice_id' placeholder='Invoice ID' required><input name='description' placeholder='Description' required><input name='amount' placeholder='Amount' required><button type='submit'>Add</button></form>");
	}

	public function storeItem(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare('INSERT INTO invoice_items (invoice_id, description, amount) VALUES (:i,:d,:a)')->execute([
			'i' => (int)($_POST['invoice_id'] ?? 0),
			'd' => $_POST['description'] ?? '',
			'a' => (float)($_POST['amount'] ?? 0),
		]);
		header('Location: /billing/invoices');
		return '';
	}

	public function addPayment(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		return $this->view("<h1>Add Payment</h1><form method='POST' action='/billing/payments/store'><input name='invoice_id' placeholder='Invoice ID' required><input name='amount' placeholder='Amount' required><input name='method' placeholder='Method'><button type='submit'>Pay</button></form>");
	}

	public function storePayment(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare('INSERT INTO payments (invoice_id, amount, paid_at, method) VALUES (:i,:a,NOW(),:m)')->execute([
			'i' => (int)($_POST['invoice_id'] ?? 0),
			'a' => (float)($_POST['amount'] ?? 0),
			'm' => $_POST['method'] ?? null,
		]);
		header('Location: /billing/invoices');
		return '';
	}
}


