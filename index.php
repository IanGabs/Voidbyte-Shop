<?php
// Carrega o controlador da Loja (Cliente)
require_once __DIR__ . '/controllers/HomeController.php';

$homeController = new HomeController();
$homeController->router();
?>