<?php
session_start();
$root = dirname(__DIR__, 2);
include($root . '/app/config/config.php');

if (isset($_POST['bookNow']) && isset($_POST['uuid'])) {
    $uuid = $_POST['uuid'];
    $user_id = $_SESSION['user_id'];
    
    // Insert into reservation table
    $sql = "INSERT INTO reservation (user_id, book_uuid, reservation_date, status) VALUES (?, ?, NOW(), 'reserved')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $uuid);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to reserve the book']);
    }
    
    $stmt->close();
    exit();
} elseif (isset($_POST['logoutButton'])) {
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
?>