<?php
session_start();

$root = dirname(__DIR__);
include($root . '/config/config.php');
include($root . '/controllers/adminHelpers.php');

// ==================== ADD BOOK ====================
if (isset($_POST['addBookButton'])) {
    $uuid          = _generateUUID();
    $title         = $_POST['title'];
    $author        = $_POST['author'];
    $publisher     = $_POST['publisher'];
    $yearPublished = $_POST['yearPublished'];
    $category      = (int) $_POST['category'];
    $description   = $_POST['description'];

    // Handle cover image upload
    $coverImage = null;
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
        $coverImage = file_get_contents($_FILES['coverImage']['tmp_name']);
    }

    $currentYear   = date("Y");
    $publishedYear = substr($yearPublished, 0, 4);

    if ((int) $publishedYear > (int) $currentYear) {
        $_SESSION['message'] = "Invalid year. The book hasn't been published yet!";
        $_SESSION['code']    = "error";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO books (uuid, title, author, publisher, yearPublished, category_id, description, coverImage) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssiss", $uuid, $title, $author, $publisher, $yearPublished, $category, $description, $coverImage);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book added successfully!";
            $_SESSION['code']    = "success";
        } else {
            $_SESSION['message'] = "Failed to add book. Please try again.";
            $_SESSION['code']    = "error";
        }
        $stmt->close();
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}

// ==================== UPDATE BOOK ====================
if (isset($_POST['updateBookButton'])) {
    $uuid          = $_POST['bookUuid'];
    $title         = $_POST['title'];
    $author        = $_POST['author'];
    $publisher     = $_POST['publisher'];
    $yearPublished = $_POST['yearPublished'];
    $category      = (int) $_POST['category'];
    $description   = $_POST['description'];

    // Handle cover image upload
    $coverImage = null;
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
        $coverImage = file_get_contents($_FILES['coverImage']['tmp_name']);
    }

    $currentYear   = date("Y");
    $publishedYear = substr($yearPublished, 0, 4);

    if ((int) $publishedYear > (int) $currentYear) {
        $_SESSION['message'] = "Invalid year. The book hasn't been published yet!";
        $_SESSION['code']    = "error";
    } else {
        if ($coverImage) {
            $stmt = $conn->prepare(
                "UPDATE books SET title=?, author=?, publisher=?, yearPublished=?, category_id=?, description=?, coverImage=? 
                 WHERE uuid=?"
            );
            $stmt->bind_param("ssssisss", $title, $author, $publisher, $yearPublished, $category, $description, $coverImage, $uuid);
        } else {
            // Keep existing cover image if none uploaded
            $stmt = $conn->prepare(
                "UPDATE books SET title=?, author=?, publisher=?, yearPublished=?, category_id=?, description=? 
                 WHERE uuid=?"
            );
            $stmt->bind_param("ssssiss", $title, $author, $publisher, $yearPublished, $category, $description, $uuid);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book updated successfully!";
            $_SESSION['code']    = "success";
        } else {
            $_SESSION['message'] = "Failed to update book. Please try again.";
            $_SESSION['code']    = "error";
        }
        $stmt->close();
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}

// ==================== DELETE BOOK ====================
if (isset($_POST['deleteBookButton'])) {
    $uuid = $_POST['bookUuid'];

    $stmt = $conn->prepare("DELETE FROM books WHERE uuid = ?");
    $stmt->bind_param("s", $uuid);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Book deleted successfully!";
        $_SESSION['code']    = "success";
    } else {
        $_SESSION['message'] = "Failed to delete book. Please try again.";
        $_SESSION['code']    = "error";
    }
    $stmt->close();

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}