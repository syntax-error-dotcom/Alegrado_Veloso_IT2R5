<?php
include('../../app/config/config.php');

if (isset($_GET['uuid'])) {
    $uuid = $_GET['uuid'];
    
    $sql = "SELECT uuid, title, author, publisher, yearPublished, category_id, description FROM books WHERE uuid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $book = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($book);
    } else {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Book not found']);
    }
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'UUID required']);
}
?>

