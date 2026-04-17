<?php
error_reporting(0);
ini_set('display_errors', 0);
include(dirname(__DIR__, 2) . '/app/config/config.php');

if (!$conn) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if (isset($_GET['uuid'])) {
    $uuid = $_GET['uuid'];
    
    $sql = "SELECT uuid, title, author, description FROM books WHERE uuid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("s", $uuid);
    if (!$stmt->execute()) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database execute error: ' . $stmt->error]);
        exit();
    }
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $book = $result->fetch_assoc();
        
        // Add default values for missing fields
        $book['publisher'] = $book['publisher'] ?? 'N/A';
        $book['yearPublished'] = $book['yearPublished'] ?? 'N/A';
        $book['category_name'] = $book['category_name'] ?? 'N/A';
        
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

