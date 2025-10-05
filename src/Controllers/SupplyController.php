<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

final class SupplyController extends Controller
{
	public function suppliers(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$rows = Database::connection()->query('SELECT id, name, email FROM suppliers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Suppliers</h1><a href='/supply/suppliers/create'>Add Supplier</a><ul>";
		foreach ($rows as $r) { $html .= sprintf('<li>%s (%s)</li>', htmlspecialchars($r['name']), htmlspecialchars((string)($r['email'] ?? ''))); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function createSupplier(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		return $this->view("<h1>New Supplier</h1><form method='POST' action='/supply/suppliers/store'><input name='name' placeholder='Name' required><input name='email' placeholder='Email'><button type='submit'>Save</button></form>");
	}

	public function storeSupplier(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare('INSERT INTO suppliers (name, email) VALUES (:n,:e)')->execute(['n' => $_POST['name'] ?? '', 'e' => $_POST['email'] ?? null]);
		header('Location: /supply/suppliers');
		return '';
	}

	public function purchaseOrders(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = 'SELECT po.id, s.name AS supplier, po.ordered_at, po.status FROM purchase_orders po JOIN suppliers s ON s.id = po.supplier_id ORDER BY po.id DESC';
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Purchase Orders</h1><a href='/supply/pos/create'>Create PO</a><ul>";
		foreach ($rows as $r) { $html .= sprintf('<li>#%d - %s (%s)</li>', (int)$r['id'], htmlspecialchars($r['supplier']), htmlspecialchars($r['status'])); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function createPo(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$suppliers = Database::connection()->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
		return $this->view("<h1>New PO</h1><form method='POST' action='/supply/pos/store'><label>Supplier</label><select name='supplier_id'>" .
			implode('', array_map(fn($s) => sprintf("<option value='%d'>%s</option>", (int)$s['id'], htmlspecialchars($s['name'])), $suppliers)) .
			"</select><button type='submit'>Create</button></form>");
	}

	public function storePo(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare("INSERT INTO purchase_orders (supplier_id, ordered_at, status) VALUES (:s, NOW(), 'ordered')")->execute(['s' => (int)($_POST['supplier_id'] ?? 0)]);
		header('Location: /supply/pos');
		return '';
	}

	public function stockMovements(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$sql = "SELECT sm.id, i.name, sm.movement_type, sm.quantity, sm.reference, sm.created_at FROM stock_movements sm JOIN items i ON i.id = sm.item_id ORDER BY sm.id DESC";
		$rows = Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$html = "<h1>Stock Movements</h1><a href='/supply/stock/create'>Add Movement</a><ul>";
		foreach ($rows as $r) { $html .= sprintf('<li>%s %s x%d (%s)</li>', htmlspecialchars($r['name']), htmlspecialchars($r['movement_type']), (int)$r['quantity'], htmlspecialchars((string)($r['reference'] ?? ''))); }
		$html .= '</ul>';
		return $this->view($html);
	}

	public function createStock(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		$items = Database::connection()->query('SELECT id, name FROM items ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
		return $this->view("<h1>New Stock Movement</h1><form method='POST' action='/supply/stock/store'><label>Item</label><select name='item_id'>" .
			implode('', array_map(fn($i) => sprintf("<option value='%d'>%s</option>", (int)$i['id'], htmlspecialchars($i['name'])), $items)) .
			"</select><label>Type</label><select name='movement_type'><option value='in'>In</option><option value='out'>Out</option></select><input name='quantity' placeholder='Qty' required><input name='reference' placeholder='Reference'><button type='submit'>Save</button></form>");
	}

	public function storeStock(): string
	{
		if (!Auth::check()) { return $this->view('Unauthorized', 401); }
		Database::connection()->prepare('INSERT INTO stock_movements (item_id, movement_type, quantity, reference) VALUES (:i,:t,:q,:r)')->execute([
			'i' => (int)($_POST['item_id'] ?? 0),
			't' => $_POST['movement_type'] ?? 'in',
			'q' => (int)($_POST['quantity'] ?? 0),
			'r' => $_POST['reference'] ?? null,
		]);
		header('Location: /supply/stock');
		return '';
	}
}


