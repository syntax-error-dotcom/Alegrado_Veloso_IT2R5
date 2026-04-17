<?php
session_start();
$root = dirname(__DIR__, 2);
include($root . '/app/config/config.php');

if (isset($_POST['logoutButton'])) {
    unset($_SESSION['authUser']);
    unset($_SESSION['user_id']);
    unset($_SESSION['role']);
    session_unset();
    session_destroy();

    header("Location: /eLibrary/public/login.php");
    exit();
}

exit();
?>