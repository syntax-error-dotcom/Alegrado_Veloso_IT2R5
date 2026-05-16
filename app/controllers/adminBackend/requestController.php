<?php
session_start();

$root = dirname(__DIR__);
include($root . '/config/config.php');
include($root . '/controllers/adminHelpers.php');

// ==================== APPROVE RESERVATION ====================
if (isset($_POST['approveReservationButton'])) {
    $reservationId = (int) ($_POST['reservation_id'] ?? 0);

    if ($reservationId <= 0) {
        $_SESSION['message'] = "Invalid reservation.";
        $_SESSION['code']    = "error";
        header("Location: /eLibrary/public/admin/request.php");
        exit();
    }

    eLibraryMarkTransactionsOverdue($conn);
    eLibraryCleanupExpiredReservations($conn, $eLibraryReservationExpiryDays);

    $conn->begin_transaction();
    try {
        $sel = $conn->prepare(
            "SELECT user_id, inventory_id FROM reservation 
             WHERE reservation_id = ? AND status = 'Pending' LIMIT 1 FOR UPDATE"
        );
        $sel->bind_param("i", $reservationId);
        $sel->execute();
        $resRow = $sel->get_result()->fetch_assoc();
        $sel->close();

        if (!$resRow) {
            throw new RuntimeException('Reservation not found or already processed.');
        }

        $approved = 'Approved';
        $upd = $conn->prepare(
            "UPDATE reservation 
             SET status = ?, approvalDate = NOW(), pickupExpiryDate = DATE_ADD(NOW(), INTERVAL ? DAY)
             WHERE reservation_id = ? AND status = 'Pending'"
        );
        $upd->bind_param("sii", $approved, $eLibraryReservationExpiryDays, $reservationId);
        $upd->execute();

        if ($upd->affected_rows === 0) {
            throw new RuntimeException('Reservation was already processed.');
        }
        $upd->close();

        $conn->commit();
        $_SESSION['message'] = 'Reservation approved. Waiting for member pickup (3 days).';
        $_SESSION['code']    = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not approve: ' . $e->getMessage();
        $_SESSION['code']    = 'error';
    }

    header("Location: /eLibrary/public/admin/request.php");
    exit();
}

// ==================== DENY RESERVATION ====================
if (isset($_POST['denyReservationButton'])) {
    $reservationId = (int) ($_POST['reservation_id'] ?? 0);

    if ($reservationId <= 0) {
        $_SESSION['message'] = "Invalid reservation.";
        $_SESSION['code']    = "error";
        header("Location: /eLibrary/public/admin/request.php");
        exit();
    }

    eLibraryMarkTransactionsOverdue($conn);
    eLibraryCleanupExpiredReservations($conn, $eLibraryReservationExpiryDays);

    $conn->begin_transaction();
    try {
        $sel = $conn->prepare(
            "SELECT inventory_id FROM reservation 
             WHERE reservation_id = ? AND status IN ('Pending', 'Approved') LIMIT 1 FOR UPDATE"
        );
        $sel->bind_param("i", $reservationId);
        $sel->execute();
        $resRow = $sel->get_result()->fetch_assoc();
        $sel->close();

        if (!$resRow) {
            throw new RuntimeException('Reservation not found or already processed.');
        }

        $inventoryId = (int) $resRow['inventory_id'];
        $rejected    = 'Rejected';

        $upd = $conn->prepare(
            "UPDATE reservation SET status = ? 
             WHERE reservation_id = ? AND status IN ('Pending', 'Approved')"
        );
        $upd->bind_param("si", $rejected, $reservationId);
        $upd->execute();

        if ($upd->affected_rows === 0) {
            throw new RuntimeException('Reservation was already processed.');
        }
        $upd->close();

        $available = 'Available';
        $invUpd = $conn->prepare("UPDATE inventory SET status = ? WHERE inventory_id = ?");
        $invUpd->bind_param("si", $available, $inventoryId);
        if (!$invUpd->execute()) {
            throw new RuntimeException($invUpd->error);
        }
        $invUpd->close();

        $conn->commit();
        $_SESSION['message'] = 'Reservation rejected.';
        $_SESSION['code']    = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not reject: ' . $e->getMessage();
        $_SESSION['code']    = 'error';
    }

    header("Location: /eLibrary/public/admin/request.php");
    exit();
}