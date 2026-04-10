<?php
include('../../app/config/config.php');

header('Content-Type: application/json');

// Get 4 random recommended books
$sqlRecommended = "SELECT uuid, title, author, description FROM books ORDER BY RAND() LIMIT 4";
$resultRecommended = $conn->query($sqlRecommended);
$recommendedBooks = [];
if ($resultRecommended && $resultRecommended->num_rows > 0) {
    while ($row = $resultRecommended->fetch_assoc()) {
        $recommendedBooks[] = $row;
    }
}

// Get 4 most recent books (assuming book_id is auto_increment, so higher id is newer)
$sqlNew = "SELECT uuid, title, author, description FROM books ORDER BY book_id DESC LIMIT 4";
$resultNew = $conn->query($sqlNew);
$newBooks = [];
if ($resultNew && $resultNew->num_rows > 0) {
    while ($row = $resultNew->fetch_assoc()) {
        $newBooks[] = $row;
    }
}

echo json_encode([
    'recommended' => $recommendedBooks,
    'new' => $newBooks
]);
?>