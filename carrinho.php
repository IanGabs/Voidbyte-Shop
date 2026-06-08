<?php
// Carrega o controlador do Carrinho
require_once __DIR__ . '/controllers/CartController.php';

$cartController = new CartController();
$cartController->router();
?>