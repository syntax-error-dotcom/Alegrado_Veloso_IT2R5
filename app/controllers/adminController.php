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
};


if (isset($_POST['logoutButton'])) {
    unset($_SESSION['authUser']);
    unset($_SESSION['user_id']);
    unset($_SESSION['role']);
    session_unset();
    session_destroy();

    header("Location: /eLibrary/public/login.php");
    exit();
}

if (isset($_POST['addBookButton'])) {
    $uuid = _generateUUID();
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $yearPublished = $_POST['yearPublished'];
    $category = (int)$_POST['category'];
    $description = $_POST['description'];
    
    // Handle file upload for cover image
    $coverImage = null;
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
        $coverImage = file_get_contents($_FILES['coverImage']['tmp_name']);
    }

    $currentYear = date("Y");

    // Check if yearPublished is valid (extract year from date YYYY-MM-DD)
    $publishedYear = substr($yearPublished, 0, 4);
    if ((int)$publishedYear > (int)$currentYear) {
        $_SESSION['message'] = "Invalid year. The book hasn't been published yet!";
        $_SESSION['code'] = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO books (uuid, title, author, publisher, yearPublished, category_id, description, coverImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiss", $uuid, $title, $author, $publisher, $yearPublished, $category, $description, $coverImage);

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

if (isset($_POST['updateBookButton'])) {
    $uuid = $_POST['bookUuid'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $yearPublished = $_POST['yearPublished'];
    $category = (int)$_POST['category'];
    $description = $_POST['description'];

    // Handle file upload for cover image
    $coverImage = null;
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
        $coverImage = file_get_contents($_FILES['coverImage']['tmp_name']);
    }

    $currentYear = date("Y");

    // Check if yearPublished is valid (extract year from date YYYY-MM-DD)
    $publishedYear = substr($yearPublished, 0, 4);
    if ((int)$publishedYear > (int)$currentYear) {
        $_SESSION['message'] = "Invalid year. The book hasn't been published yet!";
        $_SESSION['code'] = "error";
    } else {
        if ($coverImage) {
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, publisher=?, yearPublished=?, category_id=?, description=?, coverImage=? WHERE uuid=?");
            $stmt->bind_param("ssssisss", $title, $author, $publisher, $yearPublished, $category, $description, $coverImage, $uuid);
        } else {
            // If no new cover image is uploaded, keep the existing one
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, publisher=?, yearPublished=?, category_id=?, description=? WHERE uuid=?");
            $stmt->bind_param("ssssiss", $title, $author, $publisher, $yearPublished, $category, $description, $uuid);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book updated successfully!";
            $_SESSION['code'] = "success";
            $stmt->close();
        } else {
            $_SESSION['message'] = "Failed to update book. Please try again.";
            $_SESSION['code'] = "error";
        }
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}



if (isset($_POST['deleteBookButton'])) {
    $uuid = $_POST['bookUuid'];

    $stmt = $conn->prepare("DELETE FROM books WHERE uuid = ?");
    $stmt->bind_param("s", $uuid);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Book deleted successfully!";
        $_SESSION['code'] = "success";
        $stmt->close();
    } else {
        $_SESSION['message'] = "Failed to delete book. Please try again.";
        $_SESSION['code'] = "error";
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}