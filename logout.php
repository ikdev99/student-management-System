<?php
require_once __DIR__ . '/controller/AuthController.php';

$auth = new AuthController(null);
$auth->logout();
?>