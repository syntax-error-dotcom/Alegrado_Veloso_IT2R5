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
        
        // Check availability (assuming a reservation table with status)
        $book['availability'] = 'Available'; // Default to available
        $availabilitySql = "SELECT COUNT(*) as reserved FROM reservation WHERE book_uuid = ? AND status = 'reserved'";
        $availStmt = $conn->prepare($availabilitySql);
        if ($availStmt) {
            $availStmt->bind_param("s", $uuid);
            if ($availStmt->execute()) {
                $availResult = $availStmt->get_result();
                if ($availResult) {
                    $availRow = $availResult->fetch_assoc();
                    $book['availability'] = $availRow['reserved'] == 0 ? 'Available' : 'Reserved';
                }
            }
            $availStmt->close();
        }
        
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

