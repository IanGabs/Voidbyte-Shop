<?php
// Carrega o controlador do Administrador
require_once __DIR__ . '/controllers/AdminController.php';

// Inicia o controlador. Ele vai verificar automaticamente se o usuário está logado e se é 'admin'
$adminController = new AdminController();
$adminController->router();
?>