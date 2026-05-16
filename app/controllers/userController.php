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
}

if (isset($_POST['borrowBookButton'])) {
    $bookId = (int)$_POST['bookId'];
    $userId = 0;
    $sessionUserIdRaw = $_SESSION['user_id'] ?? null;

    // 1) Prefer numeric session user_id if it truly exists in users table.
    if (is_numeric($sessionUserIdRaw)) {
        $candidateUserId = (int)$sessionUserIdRaw;
        if ($candidateUserId > 0) {
            $checkUserStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? LIMIT 1");
            $checkUserStmt->bind_param("i", $candidateUserId);
            $checkUserStmt->execute();
            $checkUserResult = $checkUserStmt->get_result();
            if ($checkUserResult && $checkUserResult->num_rows > 0) {
                $userId = $candidateUserId;
            }
        }
    }

    // 2) Fallback: resolve user_id from uuid stored in session.
    if ($userId <= 0) {
        $sessionUuid = $_SESSION['user_uuid'] ?? ($_SESSION['authUser']['uuid'] ?? null);
        if (!empty($sessionUuid)) {
            $resolveUserStmt = $conn->prepare("SELECT user_id FROM users WHERE uuid = ? LIMIT 1");
            $resolveUserStmt->bind_param("s", $sessionUuid);
            $resolveUserStmt->execute();
            $resolveUserResult = $resolveUserStmt->get_result();
            if ($resolveUserResult && ($resolvedUser = $resolveUserResult->fetch_assoc())) {
                $userId = (int)$resolvedUser['user_id'];
                $_SESSION['user_id'] = $userId;
            }
        }
    }

    // 3) Fallback: logged-in username (handles older sessions without user_uuid).
    if ($userId <= 0 && !empty($_SESSION['authUser']['username'])) {
        $uname = $_SESSION['authUser']['username'];
        $resolveUserStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        $resolveUserStmt->bind_param("s", $uname);
        $resolveUserStmt->execute();
        $resolveUserResult = $resolveUserStmt->get_result();
        if ($resolveUserResult && ($resolvedUser = $resolveUserResult->fetch_assoc())) {
            $userId = (int)$resolvedUser['user_id'];
            $_SESSION['user_id'] = $userId;
        }
    }

    if ($userId <= 0 || $bookId <= 0) {
        $_SESSION['message'] = "Unable to process borrow request. Please log in again.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/user/borrow.php?book_id=$bookId");
        exit();
    }

    // Step 1: Find an available copy
    $sql = "SELECT inventory_id FROM inventory 
            WHERE book_id = ? AND status = 'Available' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $inventoryId = $row['inventory_id'];
        $conn->begin_transaction();
        try {
            // Step 2: Insert reservation record
            $sql = "INSERT INTO reservation (user_id, book_id, inventory_id, requestDate, status) 
                    VALUES (?, ?, ?, NOW(), 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $userId, $bookId, $inventoryId);
            if (!$stmt->execute()) {
                throw new Exception("Reservation insert failed: " . $stmt->error);
            }

            // Step 3: Update inventory status
            $sql = "UPDATE inventory SET status = 'Borrowed' WHERE inventory_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $inventoryId);
            if (!$stmt->execute()) {
                throw new Exception("Inventory update failed: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['message'] = "Book borrowed successfully!";
            $_SESSION['code'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Borrow failed: " . $e->getMessage();
            $_SESSION['code'] = "error";
        }
    } else {
        $_SESSION['message'] = "No available copies.";
        $_SESSION['code'] = "error";
    }

    header("Location: /eLibrary/public/user/borrow.php?book_id=$bookId");
exit();

}




