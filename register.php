<?php
// Carrega o controlador de autenticação e avisa que a rota padrão para este arquivo é 'register_form'
require_once __DIR__ . '/controllers/AuthController.php';

$authController = new AuthController();
$authController->router('register_form');
?>