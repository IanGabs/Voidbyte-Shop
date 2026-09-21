<?php
session_start();
require_once __DIR__ . '/controllers/SetupController.php';

$current_page = 'montar-setup.php';

$controller = new SetupController();
$controller->router();
?>