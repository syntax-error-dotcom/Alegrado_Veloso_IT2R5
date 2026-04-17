<?php
session_start();
include('../../app/config/config.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the reservation ID from the request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['reservation_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Reservation ID is required']);
    exit;
}

$reservation_id = $data['reservation_id'];
$user_id = $_SESSION['user_id'];

// Verify that the reservation belongs to the user and can be cancelled
$sqlCheckReservation = "SELECT reservation_id, status FROM reservations WHERE reservation_id = ? AND user_id = ?";
$stmtCheckReservation = $conn->prepare($sqlCheckReservation);
$stmtCheckReservation->bind_param("ii", $reservation_id, $user_id);
$stmtCheckReservation->execute();
$resultCheckReservation = $stmtCheckReservation->get_result();

if ($resultCheckReservation->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    $stmtCheckReservation->close();
    exit;
}

$reservation = $resultCheckReservation->fetch_assoc();
$status = $reservation['status'];
$stmtCheckReservation->close();

// Only allow cancellation of reserved and pending reservations
if (!in_array($status, ['reserved', 'pending'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot cancel a ' . $status . ' reservation']);
    exit;
}

// Update the reservation status to cancelled
$sqlUpdateReservation = "UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ?";
$stmtUpdateReservation = $conn->prepare($sqlUpdateReservation);

if (!$stmtUpdateReservation) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmtUpdateReservation->bind_param("i", $reservation_id);

if ($stmtUpdateReservation->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error cancelling reservation: ' . $stmtUpdateReservation->error]);
}

$stmtUpdateReservation->close();
$conn->close();
?>
