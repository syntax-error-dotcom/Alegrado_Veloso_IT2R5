<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include('../../app/config/config.php');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');

// Auto-clean expired rows (3 days) so ignored reservations disappear.
$expiryDays = 3;
$releaseSql = "UPDATE inventory i
               INNER JOIN reservation r ON r.inventory_id = i.inventory_id
               SET i.status = 'Available'
               WHERE
                 (r.status = 'Pending' AND r.requestDate < DATE_SUB(NOW(), INTERVAL ? DAY))
                 OR
                 (r.status = 'Approved' AND COALESCE(r.pickupExpiryDate, DATE_ADD(r.requestDate, INTERVAL ? DAY)) < NOW())";
$releaseStmt = $conn->prepare($releaseSql);
$releaseStmt->bind_param("ii", $expiryDays, $expiryDays);
$releaseStmt->execute();
$releaseStmt->close();

$deleteSql = "DELETE FROM reservation
              WHERE
                (status = 'Pending' AND requestDate < DATE_SUB(NOW(), INTERVAL ? DAY))
                OR
                (status = 'Approved' AND COALESCE(pickupExpiryDate, DATE_ADD(requestDate, INTERVAL ? DAY)) < NOW())";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("ii", $expiryDays, $expiryDays);
$deleteStmt->execute();
$deleteStmt->close();

// Fetch reservations with related user + book names (JOIN = pull columns from linked tables;
// FK constraints in phpMyAdmin only enforce integrity—they do not auto-fill joined data in SELECT).
$reservation = [];
$reservationSql = "SELECT r.reservation_id,
       r.user_id,
       u.username,
       b.title,
       r.requestDate,
       r.approvalDate,
       r.pickupExpiryDate,
       r.status,
       r.inventory_id,
       t.transaction_id,  -- ← add this
       CASE
           WHEN r.status = 'Approved' THEN GREATEST(0, TIMESTAMPDIFF(DAY, NOW(), COALESCE(r.pickupExpiryDate, DATE_ADD(r.requestDate, INTERVAL 3 DAY))))
           WHEN r.status = 'Pending'  THEN GREATEST(0, TIMESTAMPDIFF(DAY, NOW(), DATE_ADD(r.requestDate, INTERVAL 3 DAY)))
           ELSE NULL
       END AS days_left
FROM reservation r
INNER JOIN users u ON r.user_id = u.user_id
INNER JOIN books b ON r.book_id = b.book_id
LEFT JOIN transactions t ON t.reservation_id = r.reservation_id  -- ← add this
ORDER BY r.requestDate DESC";

$reservationResult = $conn->query($reservationSql);

if ($reservationResult && $reservationResult->num_rows > 0) {
    while ($row = $reservationResult->fetch_assoc()) {
        $reservation[] = $row;
    }
} elseif ($reservationResult === false) {
    echo '<p class="text-danger px-3">Could not load reservations. SQL error: ' . htmlspecialchars($conn->error) . '</p>';
} elseif ($reservationResult->num_rows === 0) {
    // empty array; table body shows friendly row below
}

?>

<div class="container-fluid">

    <!-- Page Heading -->
   <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-2 text-gray-800">Request</h1>

        <!-- Content Row -->
       
        <button type="button" class="btn btn-info shadow-sm text-white mr-2" 
                    onclick="location.reload();">
                <i class="fa fa-sync-alt fa-sm text-white-50"></i> Refresh
        </button>





    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Requests</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Book Title</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Expiration (days left)</th>
                            <th>Review Action</th>
                            <th>Borrow Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($reservation) > 0) {
                            foreach ($reservation as $req) {
                                $rid = (int) $req['reservation_id'];
                                $uid = (int) $req['user_id'];
                                $uname = htmlspecialchars($req['username'] ?? '', ENT_QUOTES, 'UTF-8');
                                $title = htmlspecialchars($req['title'] ?? '', ENT_QUOTES, 'UTF-8');
                                $reqDate = htmlspecialchars($req['requestDate'] ?? '', ENT_QUOTES, 'UTF-8');
                                $st = htmlspecialchars($req['status'] ?? '', ENT_QUOTES, 'UTF-8');
                                $daysLeft = isset($req['days_left']) ? (int)$req['days_left'] : null;
                                $isPending = (($req['status'] ?? '') === 'Pending');
                                $isApproved = (($req['status'] ?? '') === 'Approved');
                                $isExpiredApproved = $isApproved && $daysLeft !== null && $daysLeft <= 0;

                                echo '<tr>';
                                echo '<td><span class="font-weight-bold">' . $uname . '</span><br><small class="text-muted">user_id: ' . $uid . '</small></td>';
                                echo '<td>' . $title . '</td>';
                                echo '<td>' . $reqDate . '</td>';
                                echo '<td>' . $st . '</td>';
                                echo '<td>';
                                if ($isApproved || $isPending) {
                                    echo $daysLeft . ' day(s)';
                                } else {
                                    echo '<span class="text-muted">—</span>';
                                }
                                echo '</td>';

                                // Review action column: Approve / Deny for pending only.
                                echo '<td>';
                                if ($isPending) {
                                    echo '<div class="d-inline-block mr-1">';
                                    echo '<form method="POST" action="../../app/controllers/adminController.php" class="d-inline">';
                                    echo '<input type="hidden" name="reservation_id" value="' . $rid . '">';
                                    echo '<button type="submit" name="approveReservationButton" class="btn btn-success btn-sm">Accept</button>';
                                    echo '</form>';
                                    echo '</div>';
                                    echo '<div class="d-inline-block">';
                                    echo '<form method="POST" action="../../app/controllers/adminController.php" class="d-inline">';
                                    echo '<input type="hidden" name="reservation_id" value="' . $rid . '">';
                                    echo '<button type="submit" name="denyReservationButton" class="btn btn-danger btn-sm">Deny</button>';
                                    echo '</form>';
                                    echo '</div>';
                                } else {
                                    echo '<span class="text-muted">—</span>';
                                }

                                // Borrow action column
                                echo '<td>';
                                echo '<form method="POST" action="../../app/controllers/adminController.php" class="d-inline">';
                                echo '<input type="hidden" name="reservation_id" value="' . $rid . '">';
                                if ($isApproved && !$isExpiredApproved) {
                                    echo '<button type="submit" name="confirmBorrowButton" class="btn btn-primary text-nowrap btn-sm">Confirm</button>';
                                } else {
                                    echo '<button type="button" class="btn btn-secondary text-nowrap btn-sm" disabled>Confirm</button>';
                                }
                                echo '</form>';

                                // checkin receipt — only shows if reservation is Completed (book was borrowed)
                                if (($req['status'] ?? '') === 'Completed') {
                                    $linkedTxId = (int) ($req['transaction_id'] ?? 0);
                                    if ($linkedTxId > 0) {
                                        echo ' <a href="/eLibrary/public/admin/receipt.php?transaction_id=' . $linkedTxId . '&type=checkin" '
                                            . 'class="fa fa-receipt" title="View Borrow Receipt">'
                                            . '<i class="bi bi-receipt"></i></a>';
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No reservation requests yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<?php
include(__DIR__ . '/includes/footer.php');
?>