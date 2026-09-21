<?php
session_start();
require_once __DIR__ . '/controllers/ExportController.php';

$controller = new ExportController();
$controller->router();
?>
