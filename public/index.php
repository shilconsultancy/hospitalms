<?php
declare(strict_types=1);

use App\Core\App;

require dirname(__DIR__) . "/vendor/autoload.php";

$app = new App();
$app->run();
