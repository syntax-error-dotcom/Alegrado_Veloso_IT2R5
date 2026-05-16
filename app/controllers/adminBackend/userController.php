<?php
session_start();

$root = dirname(__DIR__);
include($root . '/config/config.php');
include($root . '/controllers/adminHelpers.php');

// ==================== LOGOUT ====================
if (isset($_POST['logoutButton'])) {
    unset($_SESSION['authUser']);
    unset($_SESSION['user_id']);
    unset($_SESSION['role']);
    session_unset();
    session_destroy();

    header("Location: /eLibrary/public/login.php");
    exit();
}

// ==================== CREATE USER ====================
if (isset($_POST['adminCreateUser'])) {
    $firstname      = $_POST['firstName'];
    $middlename     = $_POST['middleName'] ?? '';
    $lastname       = $_POST['lastName'];
    $email          = $_POST['emailAddress'];
    $username       = $_POST['username'];
    $password       = $_POST['password'];
    $repeatPassword = $_POST['repeatPassword'];
    $street         = $_POST['street'];
    $barangay       = $_POST['barangay'];
    $city           = $_POST['city'];
    $zip            = $_POST['zip'] ?? '';
    $role           = 'user';
    $uuid           = _generateUUID();

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['code']    = "error";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE emailAddress = ? LIMIT 1");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        $_SESSION['message'] = "Email address already exists.";
        $_SESSION['code']    = "error";
        $checkEmail->close();
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }
    $checkEmail->close();

    // Check if username already exists
    $checkUsername = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $checkUsername->store_result();
    if ($checkUsername->num_rows > 0) {
        $_SESSION['message'] = "Username already exists.";
        $_SESSION['code']    = "error";
        $checkUsername->close();
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }
    $checkUsername->close();

    // Validate password match
    if ($password !== $repeatPassword) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['code']    = "error";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare(
        "INSERT INTO users 
         (uuid, firstName, middleName, lastName, emailAddress, username, password, street, barangay, city, role) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssssssssss",
        $uuid, $firstname, $middlename, $lastname,
        $email, $username, $hashedPassword,
        $street, $barangay, $city, $role
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "User account created successfully!";
        $_SESSION['code']    = "success";
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
        $_SESSION['code']    = "error";
    }
    $stmt->close();

    header("Location: /eLibrary/public/admin/user.php");
    exit();
}

// ==================== DELETE USER ====================
if (isset($_POST['deleteUser'])) {
    if (!isset($_POST['userId']) || empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
        $_SESSION['message'] = "Invalid user ID. Please try again.";
        $_SESSION['code']    = "error";
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $userId = (int) $_POST['userId'];

    // Prevent deleting admin accounts
    $checkRole = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $checkRole->bind_param("i", $userId);
    $checkRole->execute();
    $userResult = $checkRole->get_result();

    if ($userResult->num_rows === 0) {
        $_SESSION['message'] = "User not found.";
        $_SESSION['code']    = "error";
        $checkRole->close();
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $userRow = $userResult->fetch_assoc();
    $checkRole->close();

    if ($userRow['role'] === 'admin') {
        $_SESSION['message'] = "Cannot delete admin users.";
        $_SESSION['code']    = "error";
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    if (!$stmt) {
        $_SESSION['message'] = "Database error: " . $conn->error;
        $_SESSION['code']    = "error";
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $stmt->bind_param("i", $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['code']    = "success";
    } else {
        $_SESSION['message'] = "Failed to delete user. Please try again.";
        $_SESSION['code']    = "error";
    }
    $stmt->close();

    header("Location: /eLibrary/public/admin/user_management.php");
    exit();
}