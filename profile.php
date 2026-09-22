<?php
session_start();
require_once __DIR__ . '/controllers/ProfileController.php';

$current_page = 'profile.php';

$controller = new ProfileController();
$controller->router();
?>