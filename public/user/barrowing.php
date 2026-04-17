<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

session_start();
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');

include(__DIR__ . '/../../app/config/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's reservations
$sqlReservations = "SELECT r.reservation_id, r.status, r.reservation_date, r.expected_pickup_date, r.actual_pickup_date, r.return_date, b.uuid, b.title, b.author, b.publisher, b.yearPublished FROM reservations r JOIN books b ON r.book_id = b.book_id WHERE r.user_id = ? ORDER BY r.reservation_date DESC";
$stmtReservations = $conn->prepare($sqlReservations);
$stmtReservations->bind_param("i", $user_id);
$stmtReservations->execute();
$resultReservations = $stmtReservations->get_result();
$reservations = [];

if ($resultReservations && $resultReservations->num_rows > 0) {
    while ($row = $resultReservations->fetch_assoc()) {
        $row['image'] = '../api/get-book-image.php?uuid=' . $row['uuid'];
        $reservations[] = $row;
    }
}
$stmtReservations->close();

// Group reservations by status
$reservationsByStatus = [
    'reserved' => [],
    'pending' => [],
    'collected' => [],
    'returned' => [],
    'cancelled' => []
];

foreach ($reservations as $reservation) {
    $status = $reservation['status'];
    if (isset($reservationsByStatus[$status])) {
        $reservationsByStatus[$status][] = $reservation;
    }
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Reservations</h1>
    </div>

    <!-- Reserved Books Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Reserved Books (<?php echo count($reservationsByStatus['reserved']); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($reservationsByStatus['reserved'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Reserved Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservationsByStatus['reserved'] as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['title']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['author']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                    <td><span class="badge badge-info">Reserved</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="cancelReservation(<?php echo $reservation['reservation_id']; ?>)">Cancel</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No reserved books</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Books Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pending Books (<?php echo count($reservationsByStatus['pending']); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($reservationsByStatus['pending'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Reserved Date</th>
                                <th>Expected Pickup</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservationsByStatus['pending'] as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['title']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['author']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                    <td><?php echo $reservation['expected_pickup_date'] ? date('M d, Y', strtotime($reservation['expected_pickup_date'])) : 'N/A'; ?></td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No pending books</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Collected Books Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Collected Books (<?php echo count($reservationsByStatus['collected']); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($reservationsByStatus['collected'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Pickup Date</th>
                                <th>Due Return Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservationsByStatus['collected'] as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['title']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['author']); ?></td>
                                    <td><?php echo $reservation['actual_pickup_date'] ? date('M d, Y', strtotime($reservation['actual_pickup_date'])) : 'N/A'; ?></td>
                                    <td><?php echo $reservation['return_date'] ? date('M d, Y', strtotime($reservation['return_date'])) : 'N/A'; ?></td>
                                    <td><span class="badge badge-success">Collected</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No collected books</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Returned Books Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Returned Books (<?php echo count($reservationsByStatus['returned']); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($reservationsByStatus['returned'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Pickup Date</th>
                                <th>Returned Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservationsByStatus['returned'] as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['title']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['author']); ?></td>
                                    <td><?php echo $reservation['actual_pickup_date'] ? date('M d, Y', strtotime($reservation['actual_pickup_date'])) : 'N/A'; ?></td>
                                    <td><?php echo $reservation['return_date'] ? date('M d, Y', strtotime($reservation['return_date'])) : 'N/A'; ?></td>
                                    <td><span class="badge badge-secondary">Returned</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No returned books</p>
            <?php endif; ?>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<script>
    function cancelReservation(reservationId) {
        if (confirm('Are you sure you want to cancel this reservation?')) {
            fetch('../api/cancel-reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reservation_id: reservationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reservation cancelled successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Could not cancel the reservation'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error cancelling the reservation. Please try again.');
            });
        }
    }
</script>

<?php
include(__DIR__ . '/includes/footer.php');
?>