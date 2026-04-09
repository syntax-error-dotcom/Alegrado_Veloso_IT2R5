<?php
session_start();
$root = dirname(__DIR__);
include($root . '/config/config.php');

function _generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Handle logout
if (isset($_POST['logoutButton'])) {
    unset($_SESSION['authUser']);
    unset($_SESSION['user_id']);
    unset($_SESSION['role']);
    session_unset();
    session_destroy();

    header("Location: /eLibrary/public/login.php");
    exit();
}

// Handle add book
if (isset($_POST['addBookButton'])) {
    $uuid = _generateUUID();
    $title = $_POST['title'];
    $author = $_POST['author'];
    $yearPublished = $_POST['yearPublished'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $coverImage = $_POST['coverImage'] ?? '';

    $currentYear = date("Y");

    // Check if yearPublished is valid
    if ($yearPublished > $currentYear) {
        $_SESSION['message'] = "Invalid year. The book hasn't been published yet!";
        $_SESSION['code'] = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO books (uuid, title, author, yearPublished, isbn, category, description, coverImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissss", $uuid, $title, $author, $yearPublished, $isbn, $category, $description, $coverImage);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book added successfully!";
            $_SESSION['code'] = "success";
            $stmt->close();
        } else {
            $_SESSION['message'] = "Failed to add book. Please try again.";
            $_SESSION['code'] = "error";
        }
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}

