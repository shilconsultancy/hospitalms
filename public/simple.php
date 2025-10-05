<?php
echo "Start<br>";
require dirname(__DIR__) . "/vendor/autoload.php";
echo "Autoload successful<br>";
use App\Core\App;
echo "App class loaded<br>";
$app = new App();
echo "App instance created<br>";
$result = $app->run();
echo "App run completed<br>";
echo "Result: " . $result;
?>