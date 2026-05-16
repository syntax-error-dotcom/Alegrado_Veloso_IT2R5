<?php
include('../../app/config/config.php');

// Fetch inventory items with book details
$sql = "SELECT i.inventory_id, b.book_id, b.title, i.bookNumber, i.status, i.location 
        FROM inventory i 
        JOIN books b ON i.book_id = b.book_id 
        ORDER BY b.title ASC, i.bookNumber ASC";

$result = $conn->query($sql);

$inventory = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inventory[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($inventory);
?>
