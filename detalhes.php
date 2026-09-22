<?php
// detalhes.php
require_once 'config/Database.php';
require_once 'controllers/ProductController.php';

$controller = new ProductController();
$controller->detalhes();
?>