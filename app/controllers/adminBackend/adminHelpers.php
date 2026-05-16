<?php

/** Days until due date once member picks up the book. */
$eLibraryLoanDays = 14;

/** Days before unclaimed/ignored reservation is cleaned up. */
$eLibraryReservationExpiryDays = 3;

/**
 * Mark open loans as Overdue when due date has passed.
 */
function eLibraryMarkTransactionsOverdue($conn): void
{
    $conn->query(
        "UPDATE transactions SET status = 'Overdue' 
         WHERE status = 'Borrowed' AND dueDate < CURDATE()"
    );
}

/**
 * Clean up expired reservations and release their inventory copies.
 */
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

    // Delete expired reservation rows.
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

/**
 * Generate a UUID v4 string.
 */
function _generateUUID(): string
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
}