<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connection();

// Basic demo data
$pdo->exec("INSERT IGNORE INTO wards (name) VALUES ('General'), ('ICU'), ('Pediatrics')");
$pdo->exec("INSERT IGNORE INTO beds (ward_id, bed_no) SELECT w.id, CONCAT('B', ROW_NUMBER() OVER ()) FROM wards w LIMIT 0");

// Ensure at least some beds per ward
$stmt = $pdo->query('SELECT id FROM wards');
while ($row = $stmt->fetch()) {
	$wardId = (int)$row['id'];
	for ($i = 1; $i <= 5; $i++) {
		$pdo->prepare('INSERT IGNORE INTO beds (ward_id, bed_no) VALUES (:w, :n)')->execute(['w' => $wardId, 'n' => 'B' . $i]);
	}
}

// Patients
for ($i = 1; $i <= 5; $i++) {
	$mrn = 'MRN' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
	$pdo->prepare('INSERT IGNORE INTO patients (mrn, first_name, last_name) VALUES (:m,:f,:l)')
		->execute(['m' => $mrn, 'f' => 'Patient' . $i, 'l' => 'Demo']);
}

// Items
$items = ['Gloves', 'Syringes', 'IV Set'];
foreach ($items as $name) {
	$pdo->prepare('INSERT IGNORE INTO items (name) VALUES (:n)')->execute(['n' => $name]);
}

echo "Seed completed\n";


