<?php
session_start();

$root = dirname(__DIR__, 2);
include($root . '/config/config.php');
include($root . '/controllers/adminBackend/adminHelpers.php');

// ==================== CONFIRM BORROW (PICKUP) ====================
if (isset($_POST['confirmBorrowButton'])) {
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
            "SELECT r.user_id, r.inventory_id, i.book_id 
             FROM reservation r
             JOIN inventory i ON r.inventory_id = i.inventory_id
             WHERE r.reservation_id = ? AND r.status = 'Approved' LIMIT 1 FOR UPDATE"
        );
        $sel->bind_param("i", $reservationId);
        $sel->execute();
        $resRow = $sel->get_result()->fetch_assoc();
        $sel->close();

        if (!$resRow) {
            throw new RuntimeException('Reservation not found or already processed.');
        }

        $userId      = (int) $resRow['user_id'];
        $inventoryId = (int) $resRow['inventory_id'];
        $bookId      = (int) $resRow['book_id'];

        $loanDays   = ($eLibraryLoanDays >= 1) ? (int) $eLibraryLoanDays : 14;
        $borrowDate = date('Y-m-d');
        $dueDate    = date('Y-m-d', strtotime('+' . $loanDays . ' days'));
        $txStatus   = 'Borrowed';

        $ins = $conn->prepare(
            "INSERT INTO transactions (user_id, inventory_id, book_id, reservation_id, borrowDate, dueDate, returnDate, status) 
             VALUES (?, ?, ?, ?, ?, ?, NULL, ?)"
        );
        $ins->bind_param("iiiisss", $userId, $inventoryId, $bookId, $reservationId, $borrowDate, $dueDate, $txStatus);

        if (!$ins->execute()) {
            throw new RuntimeException($ins->error);
        }
        $newTxId = $ins->insert_id;
        $ins->close();

        $completed = 'Completed';
        $upd = $conn->prepare(
            "UPDATE reservation SET status = ? WHERE reservation_id = ? AND status = 'Approved'"
        );
        $upd->bind_param("si", $completed, $reservationId);
        $upd->execute();

        if ($upd->affected_rows === 0) {
            throw new RuntimeException('Reservation was already processed or not approved.');
        }
        $upd->close();

        // Get snapshot for receipt
        $snap = $conn->prepare(
            "SELECT b.title, CONCAT(u.firstName, ' ', u.middleName, ' ', u.lastName) AS full_name
             FROM transactions t
             JOIN books b ON t.book_id = b.book_id
             JOIN users u ON t.user_id = u.user_id
             WHERE t.reservation_id = ? LIMIT 1"
        );
        $snap->bind_param("i", $reservationId);
        $snap->execute();
        $snapData = $snap->get_result()->fetch_assoc();
        $snap->close();

        // Insert check-in receipt
        $rec = $conn->prepare(
            "INSERT INTO receipts (transaction_id, type, book_title, user_name, inventory_id, borrow_date, due_date)
             VALUES (?, 'checkin', ?, ?, ?, ?, ?)"
        );
        $rec->bind_param("issisd", $newTxId, $snapData['title'], $snapData['full_name'], $inventoryId, $borrowDate, $dueDate);

        if (!$rec->execute()) {
            throw new RuntimeException('Receipt generation failed: ' . $rec->error);
        }
        $rec->close();

        $conn->commit();
        $_SESSION['message'] = 'Borrow confirmed. Transaction started.';
        $_SESSION['code']    = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not confirm borrow: ' . $e->getMessage();
        $_SESSION['code']    = 'error';
    }

    header("Location: /eLibrary/public/admin/request.php");
    exit();
}

// ==================== SET TRANSACTION STATUS ====================
if (isset($_POST['setStatusButton'])) {
    $transactionId  = (int) ($_POST['transaction_id'] ?? 0);
    $newStatus      = $_POST['status'] ?? '';
    $allowedStatus  = ['Borrowed', 'Overdue', 'Returned'];

    if ($transactionId <= 0 || !in_array($newStatus, $allowedStatus, true)) {
        $_SESSION['message'] = 'Invalid transaction update.';
        $_SESSION['code']    = 'error';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    if ($newStatus === 'Returned') {
        $_SESSION['message'] = 'Use Complete Transaction to finalize Returned and set return date.';
        $_SESSION['code']    = 'warning';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    // Insert penalty if marking as Overdue (only once)
    if ($newStatus === 'Overdue') {
        $penaltyCheck = $conn->prepare("SELECT penalty_id FROM penalties WHERE transaction_id = ? LIMIT 1");
        $penaltyCheck->bind_param("i", $transactionId);
        $penaltyCheck->execute();
        $penaltyExists = $penaltyCheck->get_result()->fetch_assoc();
        $penaltyCheck->close();

        if (!$penaltyExists) {
            $penaltyReason = "Book not returned by due date";
            $penaltyPoints = 10;
            $pen = $conn->prepare(
                "INSERT INTO penalties (user_id, transaction_id, penaltyPoints, reason, dateIssued, status) 
                 SELECT user_id, transaction_id, ?, ?, CURDATE(), 'unpaid' 
                 FROM transactions WHERE transaction_id = ?"
            );
            $pen->bind_param("isi", $penaltyPoints, $penaltyReason, $transactionId);
            $pen->execute();
            $pen->close();
        }
    }

    // Check if transaction is locked
    $check = $conn->prepare("SELECT status FROM transactions WHERE transaction_id = ? LIMIT 1");
    $check->bind_param("i", $transactionId);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$row) {
        $_SESSION['message'] = 'Transaction not found.';
        $_SESSION['code']    = 'error';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    if (in_array($row['status'], ['Returned', 'Denied'], true)) {
        $_SESSION['message'] = 'This transaction is locked and can no longer be edited.';
        $_SESSION['code']    = 'warning';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    $upd = $conn->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
    $upd->bind_param("si", $newStatus, $transactionId);

    if ($upd->execute()) {
        $_SESSION['message'] = 'Transaction status updated.';
        $_SESSION['code']    = 'success';
    } else {
        $_SESSION['message'] = 'Could not update status: ' . $upd->error;
        $_SESSION['code']    = 'error';
    }
    $upd->close();

    header("Location: /eLibrary/public/admin/circulation.php");
    exit();
}

// ==================== COMPLETE TRANSACTION (RETURN) ====================
if (isset($_POST['completeTransactionButton'])) {
    $transactionId = (int) ($_POST['transaction_id'] ?? 0);

    if ($transactionId <= 0) {
        $_SESSION['message'] = 'Invalid transaction.';
        $_SESSION['code']    = 'error';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    $conn->begin_transaction();
    try {
        $sel = $conn->prepare(
            "SELECT status, inventory_id, borrowDate, dueDate 
             FROM transactions WHERE transaction_id = ? LIMIT 1 FOR UPDATE"
        );
        $sel->bind_param("i", $transactionId);
        $sel->execute();
        $txRow = $sel->get_result()->fetch_assoc();
        $sel->close();

        if (!$txRow) {
            throw new RuntimeException('Transaction not found.');
        }

        $currentStatus = $txRow['status'];
        $inventoryId   = (int) $txRow['inventory_id'];
        $borrowDate    = $txRow['borrowDate'];
        $dueDate       = $txRow['dueDate'];

        if (in_array($currentStatus, ['Returned', 'Denied'], true)) {
            throw new RuntimeException('Transaction already finalized and locked.');
        }

        // Mark as Returned with today's return date
        $returned = 'Returned';
        $upd = $conn->prepare("UPDATE transactions SET status = ?, returnDate = CURDATE() WHERE transaction_id = ?");
        $upd->bind_param("si", $returned, $transactionId);
        if (!$upd->execute()) {
            throw new RuntimeException($upd->error);
        }
        $upd->close();

        // Release inventory copy
        $available = 'Available';
        $inv = $conn->prepare("UPDATE inventory SET status = ? WHERE inventory_id = ?");
        $inv->bind_param("si", $available, $inventoryId);
        if (!$inv->execute()) {
            throw new RuntimeException($inv->error);
        }
        $inv->close();

        // Get snapshot for receipt
        $snap = $conn->prepare(
            "SELECT b.title, CONCAT(u.firstName, ' ', u.middleName, ' ', u.lastName) AS full_name
             FROM transactions t
             JOIN books b ON t.book_id = b.book_id
             JOIN users u ON t.user_id = u.user_id
             WHERE t.transaction_id = ? LIMIT 1"
        );
        $snap->bind_param("i", $transactionId);
        $snap->execute();
        $snapData = $snap->get_result()->fetch_assoc();
        $snap->close();

        // Calculate fine if overdue (5 pesos per day)
        $fineAmount = 0.00;
        $today = date('Y-m-d');
        if ($dueDate && $today > $dueDate) {
            $daysLate   = (int) ((strtotime($today) - strtotime($dueDate)) / 86400);
            $fineAmount = $daysLate * 5.00;
        }

        // Insert checkout receipt
        $rec = $conn->prepare(
            "INSERT INTO receipts 
             (transaction_id, type, book_title, user_name, inventory_id, borrow_date, due_date, return_date, fine_amount)
             VALUES (?, 'checkout', ?, ?, ?, ?, ?, CURDATE(), ?)"
        );
        $rec->bind_param("ississd", $transactionId, $snapData['title'], $snapData['full_name'], $inventoryId, $borrowDate, $dueDate, $fineAmount);

        if (!$rec->execute()) {
            throw new RuntimeException('Checkout receipt failed: ' . $rec->error);
        }
        $rec->close();

        $conn->commit();
        $_SESSION['message'] = 'Transaction completed. Return date recorded.';
        $_SESSION['code']    = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not complete transaction: ' . $e->getMessage();
        $_SESSION['code']    = 'error';
    }

    header("Location: /eLibrary/public/admin/circulation.php");
    exit();
}