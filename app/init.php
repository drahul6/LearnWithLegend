<?php
require_once '../vendor/autoload.php';

use App\core\app;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(realpath('../'));
$dotenv->load();

require_once 'database.php';
require_once 'core/app.php';
require_once 'core/controller.php';
require_once 'core/operations.php';


$app = new app;
