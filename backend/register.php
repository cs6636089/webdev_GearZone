<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/connect.php';

$username   = trim($_POST['username'] ?? '');
$password   = trim($_POST['password'] ?? '');
$confirm_pw = trim($_POST['confirm-password'] ?? '');
$email      = trim($_POST['email'] ?? '');
$first      = trim($_POST['first_name'] ?? '');
$last       = trim($_POST['last_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$address    = trim($_POST['address'] ?? '');

if ($username === '' || $password === '' || $email === '') {
    header('Location: /~cs6636089/GearZone/frontend/register.html?error=empty');
    exit;
}
if ($password !== $confirm_pw) {
    header('Location: /~cs6636089/GearZone/frontend/register.html?error=pwmismatch');
    exit;
}

$dup = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE username=:u OR email=:e");
$dup->execute([':u' => $username, ':e' => $email]);
if ($dup->fetchColumn() > 0) {
    header('Location: /~cs6636089/GearZone/frontend/register.html?error=duplicate');
    exit;
}

// $hash = password_hash($password, PASSWORD_DEFAULT);
$hash = $password;

$ins = $pdo->prepare(
    "INSERT INTO Users(username,password,email,first_name,last_name,phone,address,is_admin)
  VALUES(:u,:p,:e,:f,:l,:ph,:ad,0)"
);
$ins->execute([
    ':u' => $username,
    ':p' => $hash,
    ':e' => $email,
    ':f' => $first,
    ':l' => $last,
    ':ph' => $phone,
    ':ad' => $address
]);

$_SESSION['user'] = [
    'id'       => (int)$pdo->lastInsertId(),
    'username' => $username,
    'email'    => $email,
    'first'    => $first,
    'last'     => $last,
    'phone'    => $phone,
    'address'  => $address,
    'is_admin' => 0
];

header('Location: /~cs6636089/GearZone/frontend/user.php');
exit;
