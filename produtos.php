<?php
// Carrega o controlador de Produtos
require_once __DIR__ . '/controllers/ProductController.php';

$productController = new ProductController();
$productController->router();
?>