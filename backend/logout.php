<?php
require_once __DIR__ . '/config/session.php';
$_SESSION = [];
session_destroy();
header('Location: /~cs6636089/GearZone/index.html');
exit;
