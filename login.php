<?php
// Carrega o controlador de autenticação e chama o roteador
require_once __DIR__ . '/controllers/AuthController.php';

$authController = new AuthController();
$authController->router();
?>