<?php

include 'includes/functions.php';

// if already logged in, go to their dashboard
if (is_logged_in()) {
    redirect_by_role();
}

// if not logged in, go to login page
header('Location: login.php');
exit;
?>