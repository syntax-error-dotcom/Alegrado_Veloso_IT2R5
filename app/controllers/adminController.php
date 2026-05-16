<?php
session_start();
$root = dirname(__DIR__);
include($root . '/config/config.php');

function _generateUUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
};


if (isset($_POST['logoutButton'])) {
    unset($_SESSION['authUser']);
    unset($_SESSION['user_id']);
    unset($_SESSION['role']);
    session_unset();
    session_destroy();

    header("Location: /eLibrary/public/login.php");
    exit();
}

if (isset($_POST['adminCreateUser'])) {
    $firstname = $_POST['firstName'];
    $middlename = $_POST['middleName'] ?? '';
    $lastname = $_POST['lastName'];
    $email = $_POST['emailAddress'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $repeatPassword = $_POST['repeatPassword'];
    $street = $_POST['street'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $zip = $_POST['zip'] ?? '';
    $role = 'user';
    $uuid = _generateUUID();

    // Validates format of email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }

    // Checks if emailAddress already exists in the database
    $checkQuery = mysqli_query($conn, "SELECT user_id from users where emailAddress='$email' LIMIT 1");
    if ($checkQuery && mysqli_num_rows($checkQuery) > 0) {
        $_SESSION['message'] = "Email address already exists.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }

    // Checks if username already exists in the database
    $checkQuery = mysqli_query($conn, "SELECT user_id from users where username='$username' LIMIT 1");
    if ($checkQuery && mysqli_num_rows($checkQuery) > 0) {
        $_SESSION['message'] = "Username already exists.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }

    // Validate password match
    if ($password !== $repeatPassword) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users 
        (uuid, firstName, middleName, lastName, emailAddress, username, password, street, barangay, city, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $uuid, $firstname, $middlename, $lastname, $email, $username, $password, $street, $barangay, $city, $role);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User account created successfully!";
        $_SESSION['code'] = "success";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    } else {
        $_SESSION['message'] = "Something went wrong: " . mysqli_error($conn) . " Please try again.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user.php");
        exit();
    }
}

if (isset($_POST['deleteUser'])) {
    // Validate userId is provided and is numeric
    if (!isset($_POST['userId']) || empty($_POST['userId']) || !is_numeric($_POST['userId'])) {
        $_SESSION['message'] = "Invalid user ID. Please try again.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $userId = (int)$_POST['userId'];

    // Prevent deleting admin accounts
    $checkUserRole = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $checkUserRole->bind_param("i", $userId);
    $checkUserRole->execute();
    $userResult = $checkUserRole->get_result();

    if ($userResult->num_rows === 0) {
        $_SESSION['message'] = "User not found.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $userRow = $userResult->fetch_assoc();
    $checkUserRole->close();

    if ($userRow['role'] === 'admin') {
        $_SESSION['message'] = "Cannot delete admin users.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    if (!$stmt) {
        $_SESSION['message'] = "Database error: " . $conn->error;
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/user_management.php");
        exit();
    }

    $stmt->bind_param("i", $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['code'] = "success";
    } else {
        $_SESSION['message'] = "Failed to delete user. Please try again. Error: " . $stmt->error;
        $_SESSION['code'] = "error";
    }

    $stmt->close();
    header("Location: /eLibrary/public/admin/user_management.php");
    exit();
}



if (isset($_POST['addBookButton'])) {
    $uuid = _generateUUID();
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $yearPublished = $_POST['yearPublished'];
    $category = (int)$_POST['category'];
    $description = $_POST['description'];

    // Handle file upload for cover image
    $coverImage = null;
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
        $coverImage = file_get_contents($_FILES['coverImage']['tmp_name']);
    }

    $currentYear = date("Y");

    // Check if yearPublished is valid (extract year from date YYYY-MM-DD)
    $publishedYear = substr($yearPublished, 0, 4);
    if ((int)$publishedYear > (int)$currentYear) {
        $_SESSION['message'] = "Invalid year. The book hasn't been published yet!";
        $_SESSION['code'] = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO books (uuid, title, author, publisher, yearPublished, category_id, description, coverImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiss", $uuid, $title, $author, $publisher, $yearPublished, $category, $description, $coverImage);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book added successfully!";
            $_SESSION['code'] = "success";
            $stmt->close();
        } else {
            $_SESSION['message'] = "Failed to add book. Please try again.";
            $_SESSION['code'] = "error";
        }
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}

if (isset($_POST['updateBookButton'])) {
    $uuid = $_POST['bookUuid'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $yearPublished = $_POST['yearPublished'];
    $category = (int)$_POST['category'];
    $description = $_POST['description'];

    // Handle file upload for cover image
    $coverImage = null;
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
        $coverImage = file_get_contents($_FILES['coverImage']['tmp_name']);
    }

    $currentYear = date("Y");

    // Check if yearPublished is valid (extract year from date YYYY-MM-DD)
    $publishedYear = substr($yearPublished, 0, 4);
    if ((int)$publishedYear > (int)$currentYear) {
        $_SESSION['message'] = "Invalid year. The book hasn't been published yet!";
        $_SESSION['code'] = "error";
    } else {
        if ($coverImage) {
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, publisher=?, yearPublished=?, category_id=?, description=?, coverImage=? WHERE uuid=?");
            $stmt->bind_param("ssssisss", $title, $author, $publisher, $yearPublished, $category, $description, $coverImage, $uuid);
        } else {
            // If no new cover image is uploaded, keep the existing one
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, publisher=?, yearPublished=?, category_id=?, description=? WHERE uuid=?");
            $stmt->bind_param("ssssiss", $title, $author, $publisher, $yearPublished, $category, $description, $uuid);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book updated successfully!";
            $_SESSION['code'] = "success";
            $stmt->close();
        } else {
            $_SESSION['message'] = "Failed to update book. Please try again.";
            $_SESSION['code'] = "error";
        }
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}



if (isset($_POST['deleteBookButton'])) {
    $uuid = $_POST['bookUuid'];

    $stmt = $conn->prepare("DELETE FROM books WHERE uuid = ?");
    $stmt->bind_param("s", $uuid);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Book deleted successfully!";
        $_SESSION['code'] = "success";
        $stmt->close();
    } else {
        $_SESSION['message'] = "Failed to delete book. Please try again.";
        $_SESSION['code'] = "error";
    }

    header("Location: /eLibrary/public/admin/catalogue.php");
    exit();
}








if (isset($_POST['stockBookButton'])) {
    $bookId    = (int)$_POST['bookId'];
    $numCopies = (int)$_POST['numCopies'];
    $location  = $_POST['location'];
    $status    = 'Available';



    // Step 2: Loop to insert new rows
    for ($i = 0; $i < $numCopies; $i++) {
        $bookNumber = _generateUUID(); // unique per copy
        $sql = "INSERT INTO inventory (book_id, bookNumber, location, status) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $bookId, $bookNumber, $location, $status);
        $stmt->execute();
    }

    $_SESSION['message'] = "Stocked $numCopies copies successfully!";
    $_SESSION['code'] = "success";
    header("Location: /eLibrary/public/admin/inventory.php");
    exit();
}

if (isset($_POST['removeBookButton'])) {
    $inventoryId = (int)$_POST['inventory_id'];

    $stmt = $conn->prepare("DELETE FROM inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $inventoryId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Book copy removed successfully!";
        $_SESSION['code'] = "success";
        $stmt->close();
    } else {
        $_SESSION['message'] = "Failed to remove book copy. Please try again.";
        $_SESSION['code'] = "error";
    }

    header("Location: /eLibrary/public/admin/inventory.php");
    exit();
}




// Must be separate top-level if blocks—do not attach else to accept only.
// Otherwise every non-Accept POST (including Deny) hits the accept else branch.

/** Days until due date once member picks up the book. */
$eLibraryLoanDays = 14;
/** Days before unclaimed/ignored reservation is cleaned up. */
$eLibraryReservationExpiryDays = 3;

/**
 * Mark open loans as Overdue when due date has passed (same idea as checking on page load / cron).
 * transactions.status ENUM: Borrowed | Denied | Returned | Overdue (per your schema).
 */
function eLibraryMarkTransactionsOverdue($conn): void
{
    $conn->query(
        "UPDATE transactions SET status = 'Overdue' 
         WHERE status = 'Borrowed' AND dueDate < CURDATE()"
    );
}

function eLibraryCleanupExpiredReservations($conn, int $expiryDays): void
{
    if ($expiryDays < 1) {
        $expiryDays = 3;
    }

    // Release copies from expired reservations first.
    $release = $conn->prepare(
        "UPDATE inventory i
         INNER JOIN reservation r ON r.inventory_id = i.inventory_id
         SET i.status = 'Available'
         WHERE
            (r.status = 'Pending' AND r.requestDate < DATE_SUB(NOW(), INTERVAL ? DAY))
            OR
            (r.status = 'Approved' AND COALESCE(r.pickupExpiryDate, DATE_ADD(r.requestDate, INTERVAL ? DAY)) < NOW())"
    );
    $release->bind_param("ii", $expiryDays, $expiryDays);
    $release->execute();
    $release->close();

    // Delete expired reservation rows (as requested: row should be gone).
    $del = $conn->prepare(
        "DELETE FROM reservation
         WHERE
            (status = 'Pending' AND requestDate < DATE_SUB(NOW(), INTERVAL ? DAY))
            OR
            (status = 'Approved' AND COALESCE(pickupExpiryDate, DATE_ADD(requestDate, INTERVAL ? DAY)) < NOW())"
    );
    $del->bind_param("ii", $expiryDays, $expiryDays);
    $del->execute();
    $del->close();
}

if (isset($_POST['approveReservationButton'])) {
    $reservationId = (int) ($_POST['reservation_id'] ?? 0);

    if ($reservationId <= 0) {
        $_SESSION['message'] = "Invalid reservation.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/request.php");
        exit();
    }

    eLibraryMarkTransactionsOverdue($conn);
    eLibraryCleanupExpiredReservations($conn, (int) $eLibraryReservationExpiryDays);

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

        $userId = (int) $resRow['user_id'];
        $inventoryId = (int) $resRow['inventory_id'];

        // Approval = ready for pickup only. Start 3-day pickup timer now.
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
        $_SESSION['code'] = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not approve: ' . $e->getMessage();
        $_SESSION['code'] = 'error';
    }

    header("Location: /eLibrary/public/admin/request.php");
    exit();
}

if (isset($_POST['confirmBorrowButton'])) {
    $reservationId = (int) ($_POST['reservation_id'] ?? 0);

    if ($reservationId <= 0) {
        $_SESSION['message'] = "Invalid reservation.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/request.php");
        exit();
    }

    eLibraryMarkTransactionsOverdue($conn);
    eLibraryCleanupExpiredReservations($conn, (int) $eLibraryReservationExpiryDays);

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
        $bookId      = (int) $resRow['book_id']; // ← new

        $loanDays = (int) $eLibraryLoanDays;
        if ($loanDays < 1) {
            $loanDays = 14;
        }

        $borrowDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+' . $loanDays . ' days'));
        $txStatus = 'Borrowed';

        $ins = $conn->prepare(
            "INSERT INTO transactions (user_id, inventory_id, book_id, reservation_id, borrowDate, dueDate, returnDate, status) 
     VALUES (?, ?, ?, ?, ?, ?, NULL, ?)"
        );
        $ins->bind_param(
            "iiiisss",
            $userId,
            $inventoryId,
            $bookId,      // ← new
            $reservationId,
            $borrowDate,
            $dueDate,
            $txStatus
        );
        if (!$ins->execute()) {
            throw new RuntimeException($ins->error);
        }
        $newTxId = $ins->insert_id; // get the ID of the transaction we just inserted
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

        // get snapshot data
        $snap = $conn->prepare("
            SELECT b.title, CONCAT(u.firstName, ' ', u.middleName, ' ', u.lastName) AS full_name
            FROM transactions t
            JOIN books b ON t.book_id = b.book_id
            JOIN users u ON t.user_id = u.user_id
            WHERE t.reservation_id = ?
            LIMIT 1
        ");
        $snap->bind_param("i", $reservationId);
        $snap->execute();
        $snapData = $snap->get_result()->fetch_assoc();
        $snap->close();

        $rec = $conn->prepare("
            INSERT INTO receipts (transaction_id, type, book_title, user_name, inventory_id, borrow_date, due_date)
            VALUES (?, 'checkin', ?, ?, ?, ?, ?)
        ");
        $rec->bind_param(
            "issisd",
            $newTxId,
            $snapData['title'],
            $snapData['full_name'],
            $inventoryId,
            $borrowDate,
            $dueDate
        );
        if (!$rec->execute()) {
            throw new RuntimeException('Receipt generation failed: ' . $rec->error);
        }

        $rec->close();

        $conn->commit();
        $_SESSION['message'] = 'Borrow confirmed. Transaction started.';
        $_SESSION['code'] = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not confirm borrow: ' . $e->getMessage();
        $_SESSION['code'] = 'error';
    }

    header("Location: /eLibrary/public/admin/request.php");
    exit();
}

if (isset($_POST['denyReservationButton'])) {
    $reservationId = (int) ($_POST['reservation_id'] ?? 0);

    if ($reservationId <= 0) {
        $_SESSION['message'] = "Invalid reservation.";
        $_SESSION['code'] = "error";
        header("Location: /eLibrary/public/admin/request.php");
        exit();
    }

    eLibraryMarkTransactionsOverdue($conn);
    eLibraryCleanupExpiredReservations($conn, (int) $eLibraryReservationExpiryDays);

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
        $rejected = 'Rejected';

        $upd = $conn->prepare(
            "UPDATE reservation SET status = ? WHERE reservation_id = ? AND status IN ('Pending', 'Approved')"
        );
        $upd->bind_param("si", $rejected, $reservationId);
        $upd->execute();
        if ($upd->affected_rows === 0) {
            throw new RuntimeException('Reservation was already processed.');
        }
        $upd->close();

        $avail = 'Available';
        $invUpd = $conn->prepare("UPDATE inventory SET status = ? WHERE inventory_id = ?");
        $invUpd->bind_param("si", $avail, $inventoryId);
        if (!$invUpd->execute()) {
            throw new RuntimeException($invUpd->error);
        }
        $invUpd->close();

        $conn->commit();
        $_SESSION['message'] = 'Reservation rejected.';
        $_SESSION['code'] = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not reject: ' . $e->getMessage();
        $_SESSION['code'] = 'error';
    }

    header("Location: /eLibrary/public/admin/request.php");
    exit();
}

if (isset($_POST['setStatusButton'])) {
    $transactionId = (int) ($_POST['transaction_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $allowedStatus = ['Borrowed', 'Overdue', 'Returned'];

    if ($transactionId <= 0 || !in_array($newStatus, $allowedStatus, true)) {
        $_SESSION['message'] = 'Invalid transaction update.';
        $_SESSION['code'] = 'error';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    if ($newStatus === 'Returned') {
        $_SESSION['message'] = 'Use Complete Transaction to finalize Returned and set return date.';
        $_SESSION['code'] = 'warning';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    // inside your setStatusButton block, after the lock check, before the UPDATE
    if ($newStatus === 'Overdue') {
        error_log("DEBUG: Overdue triggered for transaction $transactionId");
        $penaltyCheck = $conn->prepare("SELECT penalty_id FROM penalties WHERE transaction_id = ? LIMIT 1");
        $penaltyCheck->bind_param("i", $transactionId);
        $penaltyCheck->execute();
        $exists = $penaltyCheck->get_result()->fetch_assoc();
        $penaltyCheck->close();

        error_log("DEBUG: Penalty exists? " . ($exists ? 'YES' : 'NO'));

        if (!$exists) { // only insert once, not every time
            $reason = "Book not returned by due date";
            $points = 10; // adjust as needed
            $pen = $conn->prepare("INSERT INTO penalties (user_id, transaction_id, penaltyPoints, reason, dateIssued, status) 
            SELECT user_id, transaction_id, ?, ?, CURDATE(), 'unpaid' 
            FROM transactions WHERE transaction_id = ?");
            $pen->bind_param("isi", $points, $reason, $transactionId);
            $pen->execute();
            $pen->close();
        }
    }

    $check = $conn->prepare("SELECT status FROM transactions WHERE transaction_id = ? LIMIT 1");
    $check->bind_param("i", $transactionId);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$row) {
        $_SESSION['message'] = 'Transaction not found.';
        $_SESSION['code'] = 'error';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    if (in_array($row['status'], ['Returned', 'Denied'], true)) {
        $_SESSION['message'] = 'This transaction is locked and can no longer be edited.';
        $_SESSION['code'] = 'warning';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    $upd = $conn->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
    $upd->bind_param("si", $newStatus, $transactionId);
    if ($upd->execute()) {
        $_SESSION['message'] = 'Transaction status updated.';
        $_SESSION['code'] = 'success';
    } else {
        $_SESSION['message'] = 'Could not update status: ' . $upd->error;
        $_SESSION['code'] = 'error';
    }
    $upd->close();

    header("Location: /eLibrary/public/admin/circulation.php");
    exit();
}

if (isset($_POST['completeTransactionButton'])) {
    $transactionId = (int) ($_POST['transaction_id'] ?? 0);

    if ($transactionId <= 0) {
        $_SESSION['message'] = 'Invalid transaction.';
        $_SESSION['code'] = 'error';
        header("Location: /eLibrary/public/admin/circulation.php");
        exit();
    }

    $conn->begin_transaction();
    try {
        $sel = $conn->prepare("SELECT status, inventory_id, borrowDate, dueDate FROM transactions WHERE transaction_id = ? LIMIT 1 FOR UPDATE");
        $sel->bind_param("i", $transactionId);
        $sel->execute();
        $txRow = $sel->get_result()->fetch_assoc();
        $sel->close();

        if (!$txRow) {
            throw new RuntimeException('Transaction not found.');
        }

        $current     = $txRow['status'];
        $inventoryId = (int) $txRow['inventory_id'];
        $borrowDate  = $txRow['borrowDate']; // ← new
        $dueDate     = $txRow['dueDate'];    // ← new

        if (in_array($current, ['Returned', 'Denied'], true)) {
            throw new RuntimeException('Transaction already finalized and locked.');
        }

        $returned = 'Returned';
        $upd = $conn->prepare("UPDATE transactions SET status = ?, returnDate = CURDATE() WHERE transaction_id = ?");
        $upd->bind_param("si", $returned, $transactionId);
        if (!$upd->execute()) {
            throw new RuntimeException($upd->error);
        }
        $upd->close();

        $available = 'Available';
        $inv = $conn->prepare("UPDATE inventory SET status = ? WHERE inventory_id = ?");
        $inv->bind_param("si", $available, $inventoryId);
        if (!$inv->execute()) {
            throw new RuntimeException($inv->error);
        }
        $inv->close();

        $snap = $conn->prepare("
    SELECT b.title, 
           CONCAT(u.firstName, ' ', u.middleName, ' ', u.lastName) AS full_name
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    JOIN users u ON t.user_id = u.user_id
    WHERE t.transaction_id = ?
    LIMIT 1
");
        $snap->bind_param("i", $transactionId);
        $snap->execute();
        $snapData = $snap->get_result()->fetch_assoc();
        $snap->close();

        // calculate fine if overdue
        $fineAmount = 0.00;
        $today = date('Y-m-d');
        if ($dueDate && $today > $dueDate) {
            $daysLate   = (int)((strtotime($today) - strtotime($dueDate)) / 86400);
            $fineAmount = $daysLate * 5.00; // 5 pesos per day
        }


        $rec = $conn->prepare("
    INSERT INTO receipts 
    (transaction_id, type, book_title, user_name, inventory_id, borrow_date, due_date, return_date, fine_amount)
    VALUES (?, 'checkout', ?, ?, ?, ?, ?, CURDATE(), ?)
");
        $rec->bind_param(
            "ississd",
            $transactionId,
            $snapData['title'],
            $snapData['full_name'],
            $inventoryId,
            $borrowDate,
            $dueDate,
            $fineAmount
        );
        if (!$rec->execute()) {
            throw new RuntimeException('Checkout receipt failed: ' . $rec->error);
        }
        $rec->close();

        $conn->commit();
        $_SESSION['message'] = 'Transaction completed. Return date recorded.';
        $_SESSION['code'] = 'success';
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['message'] = 'Could not complete transaction: ' . $e->getMessage();
        $_SESSION['code'] = 'error';
    }

    header("Location: /eLibrary/public/admin/circulation.php");
    exit();
}
