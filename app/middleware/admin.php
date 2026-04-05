<?php 
session_start();
$root = dirname(__DIR__);
include_once($root . '/config/config.php');

if (!isset($_SESSION['authUser'])) {
    $_SESSION['message'] = "You must log in first";
    $_SESSION['code'] = "warning";
    header("Location: /eLibrary/public/login.php");
    exit();

} else 
{
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['message'] = "Access denied: You do not have permission to access this page.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/login.php");
        exit();
    }
}



?>