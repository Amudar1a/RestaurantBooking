<?php
require_once __DIR__ . '/../config/config.php';

$adminModel = new Admin();
$adminModel->logout();

header('Location: index.php');
exit;
