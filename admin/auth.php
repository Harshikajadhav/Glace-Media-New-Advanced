<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['admin_user_id'])) {
    $requestedPath = $_SERVER['REQUEST_URI'] ?? '/glace-new/admin/';
    header('Location: ../login.php?redirect=' . rawurlencode($requestedPath));
    exit;
}