<?php
session_start();

$root = dirname(__DIR__);
include($root . '/config/config.php');
include($root . '/controllers/adminHelpers.php');

// ==================== STOCK BOOK (ADD COPIES) ====================
if (isset($_POST['stockBookButton'])) {
    $bookId    = (int) $_POST['bookId'];
    $numCopies = (int) $_POST['numCopies'];
    $location  = $_POST['location'];
    $status    = 'Available';

    for ($i = 0; $i < $numCopies; $i++) {
        $bookNumber = _generateUUID();
        $stmt = $conn->prepare(
            "INSERT INTO inventory (book_id, bookNumber, location, status) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isss", $bookId, $bookNumber, $location, $status);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['message'] = "Stocked $numCopies copies successfully!";
    $_SESSION['code']    = "success";

    header("Location: /eLibrary/public/admin/inventory.php");
    exit();
}

// ==================== REMOVE BOOK COPY ====================
if (isset($_POST['removeBookButton'])) {
    $inventoryId = (int) $_POST['inventory_id'];

    $stmt = $conn->prepare("DELETE FROM inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $inventoryId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Book copy removed successfully!";
        $_SESSION['code']    = "success";
    } else {
        $_SESSION['message'] = "Failed to remove book copy. Please try again.";
        $_SESSION['code']    = "error";
    }
    $stmt->close();

    header("Location: /eLibrary/public/admin/inventory.php");
    exit();
}