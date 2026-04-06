<?php
session_start();
$root = dirname(__DIR__);
include($root . '/config/config.php');


if (isset($_POST['logoutButton'])) {
    unset($_SESSION['authUser']);
    unset($_SESSION['user_id']);
    unset($_SESSION['role']);
    session_unset();
    session_destroy();

    header("Location: /eLibrary/public/login.php");
    exit();


} else {
    exit();
}

