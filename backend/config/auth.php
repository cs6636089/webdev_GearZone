<?php
// backend/config/authguard.php
require_once __DIR__ . '/session.php';

function requireLogin()
{
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        exit('Unauthorized');
    }
}

function requireAdmin()
{
    requireLogin();
    if (empty($_SESSION['user']['is_admin'])) {
        http_response_code(403);
        exit('Forbidden');
    }
}
