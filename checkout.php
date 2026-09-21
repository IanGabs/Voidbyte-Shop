<?php
session_start();
require_once __DIR__ . '/controllers/CheckoutController.php';

$current_page = 'checkout.php';

$controller = new CheckoutController();
$controller->router();
?>