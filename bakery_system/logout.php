<?php
include 'includes/functions.php';
start_session();
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();
header('Location: index.php');
exit;
?>