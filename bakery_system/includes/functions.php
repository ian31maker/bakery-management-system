<?php
function start_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in() {
    start_session();
    return isset($_SESSION['user_id']);
}

function get_role() {
    start_session();
    return isset($_SESSION['role']) ? $_SESSION['role'] : '';
}

function redirect_by_role() {
    $role = get_role();
    switch ($role) {
        case 'admin':
            header('Location: admin/dashboard.php');
            exit;
        case 'cashier':
            header('Location: cashier/dashboard.php');
            exit;
        case 'baker':
            header('Location: baker/dashboard.php');
            exit;
        case 'driver':
            header('Location: driver/dashboard.php');
            exit;
        case 'customer':
            header('Location: customer/menu.php');
            exit;
        default:
            header('Location: unauthorized.php');
            exit;
    }
}

function require_role($allowed_roles) {
    start_session();
    if (!is_logged_in()) {
        header('Location: ../index.php');
        exit;
    }
    if (!in_array(get_role(), $allowed_roles)) {
        header('Location: ../unauthorized.php');
        exit;
    }
}

function format_money($amount) {
    return 'KSh ' . number_format($amount, 2);
}

function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function show_error($message) {
    return '<div style="background:#ffebee;color:#c62828;padding:10px;border-radius:5px;margin:10px 0;">' . $message . '</div>';
}

function show_success($message) {
    return '<div style="background:#e8f5e9;color:#2e7d32;padding:10px;border-radius:5px;margin:10px 0;">' . $message . '</div>';
}
?>