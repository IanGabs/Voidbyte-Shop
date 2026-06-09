<?php
require_once __DIR__ . '/controllers/CompareController.php';
$compareController = new CompareController();
$compareController->router();
?>