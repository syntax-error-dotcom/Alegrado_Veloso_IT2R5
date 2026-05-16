<?php
include('../../app/config/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['inventory_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'inventory_id is required']);
        exit;
    }

    $inventoryId = (int)$data['inventory_id'];

    $stmt = $conn->prepare("DELETE FROM inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $inventoryId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Inventory item deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete inventory item']);
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
