<?php
include('../../app/config/config.php');

if (isset($_GET['uuid'])) {
    $uuid = $_GET['uuid'];
    
    $sql = "SELECT b.uuid, b.title, b.author, b.publisher, b.yearPublished, b.category_id, b.description, c.category_name 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.category_id 
            WHERE b.uuid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $book = $result->fetch_assoc();
        
        $availabilitySql = "SELECT COUNT(*) as borrowed FROM borrowings WHERE book_uuid = ? AND status = 'borrowed'";
        $availStmt = $conn->prepare($availabilitySql);
        $availStmt->bind_param("s", $uuid);
        $availStmt->execute();
        $availResult = $availStmt->get_result();
        $availRow = $availResult->fetch_assoc();
        $book['availability'] = $availRow['borrowed'] == 0 ? 'Available' : 'Borrowed';
        
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

