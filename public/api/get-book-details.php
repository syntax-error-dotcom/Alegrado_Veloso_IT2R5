<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

$configPath = dirname(dirname(__DIR__)) . '/app/config/config.php';
if (!file_exists($configPath)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Config file not found']);
    exit();
}

include($configPath);

if (!isset($conn) || !$conn) {
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
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($bookUuid, $title, $author, $description);
        $stmt->fetch();
        $book = [
            'uuid' => $bookUuid,
            'title' => $title,
            'author' => $author,
            'description' => $description,
        ];
        
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
                $availStmt->bind_result($reservedCount);
                $availStmt->fetch();
                $book['availability'] = $reservedCount == 0 ? 'Available' : 'Reserved';
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

