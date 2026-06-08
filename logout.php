<?php
require_once __DIR__ . '/controllers/AuthController.php';

// Inicia o processo de logout enviando a action para o Controller
$_POST['action'] = 'logout';
$authController = new AuthController();
$authController->router();
?>