<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Router;
use App\Core\Bootstrap;
use App\Core\Session;

class App
{
	private Router $router;

	public function __construct()
	{
		date_default_timezone_set($this->config('app.timezone', 'UTC'));
		Session::start();
		$this->router = new Router();
		Bootstrap::init();
		$this->registerRoutes();
	}

	public function run(): void
	{
		$this->router->dispatch($_SERVER['REQUEST_URI'] ?? '/');
	}

	private function registerRoutes(): void
	{
		$this->router->get('/', [\App\Controllers\HomeController::class, 'index']);
		$this->router->get('/staff', [\App\Controllers\StaffController::class, 'index']);
		$this->router->get('/staff/create', [\App\Controllers\StaffController::class, 'create']);
		$this->router->post('/staff/store', [\App\Controllers\StaffController::class, 'store']);
		$this->router->get('/staff/link', [\App\Controllers\StaffController::class, 'linkForm']);
		$this->router->post('/staff/link/save', [\App\Controllers\StaffController::class, 'linkSave']);

		$this->router->get('/patients', [\App\Controllers\PatientController::class, 'index']);
		$this->router->get('/patients/create', [\App\Controllers\PatientController::class, 'create']);
		$this->router->post('/patients/store', [\App\Controllers\PatientController::class, 'store']);
		$this->router->get('/patients/show', function () { // simple passthrough using ?id= query for demo
			$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			$controller = new \App\Controllers\PatientController();
			return $controller->show($id);
		});

		$this->router->get('/appointments', [\App\Controllers\AppointmentController::class, 'index']);
		$this->router->get('/appointments/create', [\App\Controllers\AppointmentController::class, 'create']);
		$this->router->post('/appointments/store', [\App\Controllers\AppointmentController::class, 'store']);

		$this->router->get('/wards', [\App\Controllers\WardController::class, 'index']);
		$this->router->get('/wards/create', [\App\Controllers\WardController::class, 'create']);
		$this->router->post('/wards/store', [\App\Controllers\WardController::class, 'store']);

		$this->router->get('/beds', [\App\Controllers\BedController::class, 'index']);
		$this->router->get('/beds/create', [\App\Controllers\BedController::class, 'create']);
		$this->router->post('/beds/store', [\App\Controllers\BedController::class, 'store']);

		$this->router->get('/inpatient', [\App\Controllers\AdmissionController::class, 'dashboard']);
		$this->router->get('/admissions/create', [\App\Controllers\AdmissionController::class, 'admitForm']);
		$this->router->post('/admissions/store', [\App\Controllers\AdmissionController::class, 'store']);
		$this->router->post('/admissions/discharge', [\App\Controllers\AdmissionController::class, 'discharge']);
		$this->router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
		$this->router->post('/login', [\App\Controllers\AuthController::class, 'login']);
		$this->router->post('/logout', [\App\Controllers\AuthController::class, 'logout']);
		$this->router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);
		$this->router->get('/health', static fn () => 'OK');

		// OT
		$this->router->get('/ot', [\App\Controllers\SurgeryController::class, 'index']);
		$this->router->get('/ot/create', [\App\Controllers\SurgeryController::class, 'create']);
		$this->router->post('/ot/store', [\App\Controllers\SurgeryController::class, 'store']);

		// Pharmacy
		$this->router->get('/pharmacy/inventory', [\App\Controllers\PharmacyController::class, 'inventory']);
		$this->router->get('/pharmacy/prescriptions', [\App\Controllers\PharmacyController::class, 'prescriptions']);
		$this->router->get('/pharmacy/prescriptions/create', [\App\Controllers\PharmacyController::class, 'createPrescription']);
		$this->router->post('/pharmacy/prescriptions/store', [\App\Controllers\PharmacyController::class, 'storePrescription']);

		// Supply Chain
		$this->router->get('/supply/suppliers', [\App\Controllers\SupplyController::class, 'suppliers']);
		$this->router->get('/supply/suppliers/create', [\App\Controllers\SupplyController::class, 'createSupplier']);
		$this->router->post('/supply/suppliers/store', [\App\Controllers\SupplyController::class, 'storeSupplier']);
		$this->router->get('/supply/pos', [\App\Controllers\SupplyController::class, 'purchaseOrders']);
		$this->router->get('/supply/pos/create', [\App\Controllers\SupplyController::class, 'createPo']);
		$this->router->post('/supply/pos/store', [\App\Controllers\SupplyController::class, 'storePo']);
		$this->router->get('/supply/stock', [\App\Controllers\SupplyController::class, 'stockMovements']);
		$this->router->get('/supply/stock/create', [\App\Controllers\SupplyController::class, 'createStock']);
		$this->router->post('/supply/stock/store', [\App\Controllers\SupplyController::class, 'storeStock']);

		// Billing
		$this->router->get('/billing/invoices', [\App\Controllers\BillingController::class, 'invoices']);
		$this->router->get('/billing/invoices/create', [\App\Controllers\BillingController::class, 'createInvoice']);
		$this->router->post('/billing/invoices/store', [\App\Controllers\BillingController::class, 'storeInvoice']);
		$this->router->get('/billing/invoices/items/create', [\App\Controllers\BillingController::class, 'addItem']);
		$this->router->post('/billing/invoices/items/store', [\App\Controllers\BillingController::class, 'storeItem']);
		$this->router->get('/billing/payments/create', [\App\Controllers\BillingController::class, 'addPayment']);
		$this->router->post('/billing/payments/store', [\App\Controllers\BillingController::class, 'storePayment']);

		// BI
		$this->router->get('/bi', [\App\Controllers\BiController::class, 'dashboard']);
	}

	private function config(string $key, mixed $default = null): mixed
	{
		static $config = null;
		if ($config === null) {
			$config = require dirname(__DIR__, 2) . '/config/app.php';
		}
		$segments = explode('.', $key);
		$value = $config;
		foreach ($segments as $segment) {
			if (is_array($value) && array_key_exists($segment, $value)) {
				$value = $value[$segment];
			} else {
				return $default;
			}
		}
		return $value;
	}
}
