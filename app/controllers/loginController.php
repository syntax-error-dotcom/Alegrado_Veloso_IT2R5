<?php
session_start();
$root = dirname(__DIR__);
include($root . '/config/config.php');

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $loginQuery = "SELECT uuid, firstName, lastName, username, password, role 
                   FROM users 
                   WHERE username = ? AND password = ? 
                   LIMIT 1";
    $stmt = $conn->prepare($loginQuery);

    if ($stmt) {
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $database = $result->fetch_assoc();

            $user_id  = $database['uuid']; // 
            $fullname = $database['firstName'] . ' ' . $database['lastName'];
            $userRole = $database['role']; 

            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $userRole;
            $_SESSION['authUser'] = [
                'userid'   => $user_id,
                'fullname' => $fullname,
                'username' => $database['username'],
            ];

            $_SESSION['message'] = "Welcome! $fullname";
            $_SESSION['code'] = "success";

            if ($userRole === 'admin') {
                header("Location: /eLibrary/public/admin/index");
                exit();
            } else {
                header("Location: /eLibrary/public/user/index");
                exit();
            }
        } else {
            $_SESSION['message'] = "Invalid username or password.";
            $_SESSION['code'] = "error";
            header("Location: /eLibrary/public/login");
            exit();
        }
    } else {
        $_SESSION['message'] = "Something went wrong: " . $conn->error . " Please try again.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/login");
        exit();
    }
}
?>
