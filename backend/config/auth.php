<?php
require_once __DIR__ . '/session.php';

function currentUser()
{
    // รูปแบบใหม่ที่แนะนำ
    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    // รูปแบบเดิมของคุณ (field แยก)
    if (isset($_SESSION['user_id'])) {
        return [
            'id'       => (int)($_SESSION['user_id'] ?? 0),
            'username' => $_SESSION['username'] ?? '',
            'email'    => $_SESSION['email'] ?? '',
            'first'    => $_SESSION['first'] ?? '',
            'last'     => $_SESSION['last'] ?? '',
            'phone'    => $_SESSION['phone'] ?? '',
            'address'  => $_SESSION['address'] ?? '',
            'is_admin' => (int)($_SESSION['is_admin'] ?? 0),
        ];
    }
    return null;
}

function requireLogin()
{
    if (!currentUser()) {
        http_response_code(401);
        exit('Unauthorized');
    }
}

function requireAdmin()
{
    $u = currentUser();
    if (!$u || (int)($u['is_admin'] ?? 0) !== 1) {
        http_response_code(401);
        exit('Unauthorized');
    }
}
