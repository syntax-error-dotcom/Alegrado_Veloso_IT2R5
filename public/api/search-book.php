<?php
include('../../app/config/config.php');

if (isset($_GET['q'])) {
    $searchTerm = '%' . $_GET['q'] . '%';
    
    $sql = "SELECT uuid, title, author FROM books WHERE title LIKE ? OR author LIKE ? ORDER BY title ASC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($books);
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>
