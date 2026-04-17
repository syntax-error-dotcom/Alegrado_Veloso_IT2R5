<?php
/**
 * Setup Checklist - Book Reservation System
 * This file provides step-by-step instructions for setting up the reservation system
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Setup Checklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .container { max-width: 900px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 30px; border-bottom: 3px solid #007bff; padding-bottom: 15px; }
        h2 { color: #495057; margin-top: 30px; margin-bottom: 20px; }
        .checklist-item { margin-bottom: 15px; }
        .checklist-item input[type="checkbox"] { margin-right: 10px; }
        .success-indicator { color: #28a745; font-weight: bold; }
        .code-block { background-color: #f5f5f5; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; font-family: monospace; border-radius: 4px; }
        .issue-box { background-color: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 4px; border-left: 4px solid #ffc107; }
        .solution { background-color: #e7f3ff; padding: 10px; margin-top: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Quick Setup Checklist - Book Reservation System</h1>

        <h2>Prerequisites</h2>
        <ul>
            <li>MySQL database "e_library" is running</li>
            <li>User has login credentials working</li>
            <li>PHP sessions are configured properly</li>
        </ul>

        <h2>Installation Checklist</h2>

        <div class="checklist-item">
            <input type="checkbox"> <strong>Step 1: Create Reservations Table</strong>
            <ul style="margin-top: 10px;">
                <li>Open MySQL client or phpMyAdmin</li>
                <li>Navigate to the "e_library" database</li>
                <li>Copy and paste the SQL from <code>DATABASE_SCHEMA.sql</code></li>
                <li>Execute the SQL to create the <code>reservations</code> table</li>
                <li>Verify: Run <code>SHOW TABLES;</code> and confirm "reservations" appears</li>
            </ul>
        </div>

        <div class="checklist-item">
            <input type="checkbox"> <strong>Step 2: Verify File Permissions</strong>
            <ul style="margin-top: 10px;">
                <li>Ensure <code>public/user/discovery.php</code> is readable</li>
                <li>Ensure <code>public/user/barrowing.php</code> is readable</li>
                <li>Ensure <code>public/api/reserve-book.php</code> is readable</li>
                <li>Ensure <code>public/api/cancel-reservation.php</code> is readable</li>
            </ul>
        </div>

        <div class="checklist-item">
            <input type="checkbox"> <strong>Step 3: Test Discovery Page</strong>
            <ul style="margin-top: 10px;">
                <li>Navigate to <code>http://localhost/public/user/discovery.php</code> (adjust URL as needed)</li>
                <li>Verify books display in genre sections</li>
                <li>Click on a book - modal should appear with full details</li>
                <li>Verify the modal shows: title, author, publisher, year, description</li>
            </ul>
        </div>

        <div class="checklist-item">
            <input type="checkbox"> <strong>Step 4: Test Book Reservation (Logged-In User)</strong>
            <ul style="margin-top: 10px;">
                <li>Login to your account</li>
                <li>Go to Discovery page</li>
                <li>Click on a book to open the modal</li>
                <li>Click "Book This" button</li>
                <li>Should see success message</li>
                <li>Verify new reservation appears in Borrowing page</li>
            </ul>
        </div>

        <div class="checklist-item">
            <input type="checkbox"> <strong>Step 5: Test Borrowing/Reservations Page</strong>
            <ul style="margin-top: 10px;">
                <li>Navigate to <code>http://localhost/public/user/barrowing.php</code> (adjust URL)</li>
                <li>Should see "Reserved Books" section with your booked books</li>
                <li>Verify all reservation details display correctly</li>
                <li>Test the "Cancel" button on a reserved book</li>
            </ul>
        </div>

        <div class="checklist-item">
            <input type="checkbox"> <strong>Step 6: Verify Database Records</strong>
            <ul style="margin-top: 10px;">
                <li>Open MySQL/phpMyAdmin</li>
                <li>Execute: <code>SELECT * FROM reservations;</code></li>
                <li>Verify your reservations appear in the table</li>
                <li>Check that all fields are populated correctly</li>
            </ul>
        </div>

        <h2>Common Issues & Solutions</h2>

        <div class="issue-box">
            <strong>Issue: Books not showing in Discovery</strong>
            <div class="solution">
                <strong>Solution:</strong>
                <ul>
                    <li>Check that books table has data</li>
                    <li>Verify category_id column exists in books table</li>
                    <li>Check database connection in config.php</li>
                </ul>
            </div>
        </div>

        <div class="issue-box">
            <strong>Issue: Modal doesn't open when clicking book</strong>
            <div class="solution">
                <strong>Solution:</strong>
                <ul>
                    <li>Check browser console for JavaScript errors (F12)</li>
                    <li>Verify <code>get-book-details.php</code> API is responding</li>
                    <li>Check that jQuery is included in header</li>
                </ul>
            </div>
        </div>

        <div class="issue-box">
            <strong>Issue: "Book This" button doesn't work</strong>
            <div class="solution">
                <strong>Solution:</strong>
                <ul>
                    <li>Verify user is logged in (check session)</li>
                    <li>Check browser console for errors</li>
                    <li>Verify <code>reserve-book.php</code> exists and is accessible</li>
                    <li>Check MySQL user permissions</li>
                </ul>
            </div>
        </div>

        <div class="issue-box">
            <strong>Issue: Reservations table not created</strong>
            <div class="solution">
                <strong>Solution:</strong>
                <ul>
                    <li>Verify SQL in DATABASE_SCHEMA.sql is correct</li>
                    <li>Check MySQL user has CREATE TABLE permission</li>
                    <li>Ensure foreign keys reference correct tables</li>
                </ul>
            </div>
        </div>

        <h2>Database Access</h2>

        <h3>View all reservations</h3>
        <div class="code-block">
SELECT r.*, b.title, b.author, u.email 
FROM reservations r
JOIN books b ON r.book_id = b.book_id
JOIN users u ON r.user_id = u.user_id
ORDER BY r.reservation_date DESC;
        </div>

        <h3>View user's reservations</h3>
        <div class="code-block">
SELECT r.*, b.title, b.author 
FROM reservations r
JOIN books b ON r.book_id = b.book_id
WHERE r.user_id = [USER_ID]
ORDER BY r.reservation_date DESC;
        </div>

        <h3>Update reservation status (Admin)</h3>
        <div class="code-block">
UPDATE reservations 
SET status = 'pending', expected_pickup_date = NOW() + INTERVAL 3 DAY 
WHERE reservation_id = [RESERVATION_ID];
        </div>

        <h2>API Testing (Using cURL or Postman)</h2>

        <h3>Reserve a Book</h3>
        <div class="code-block">
curl -X POST http://localhost/public/api/reserve-book.php \
  -H "Content-Type: application/json" \
  -d '{"uuid": "book-uuid-here"}' \
  -b "PHPSESSID=your_session_id"
        </div>

        <h3>Cancel a Reservation</h3>
        <div class="code-block">
curl -X POST http://localhost/public/api/cancel-reservation.php \
  -H "Content-Type: application/json" \
  -d '{"reservation_id": 1}' \
  -b "PHPSESSID=your_session_id"
        </div>

        <h2>Success Indicators</h2>
        <ul>
            <li><span class="success-indicator">✅</span> Discovery page shows books organized by genre</li>
            <li><span class="success-indicator">✅</span> Clicking a book opens a detailed modal</li>
            <li><span class="success-indicator">✅</span> Modal displays all book information</li>
            <li><span class="success-indicator">✅</span> "Book This" button successfully creates a reservation</li>
            <li><span class="success-indicator">✅</span> Borrowing page shows user's reservations</li>
            <li><span class="success-indicator">✅</span> Cancel button works on reserved books</li>
            <li><span class="success-indicator">✅</span> All data is properly stored in the database</li>
        </ul>

        <h2>Support</h2>
        <p>If you encounter any issues:</p>
        <ol>
            <li>Check the browser console for JavaScript errors (F12)</li>
            <li>Check PHP error logs for server errors</li>
            <li>Verify all files are created and in correct locations</li>
            <li>Ensure database structure matches DATABASE_SCHEMA.sql</li>
            <li>Verify user sessions are working properly</li>
        </ol>

        <div style="margin-top: 40px; padding: 20px; background-color: #d4edda; border-radius: 8px; text-align: center;">
            <h3 style="color: #155724;">Good luck! 🎉</h3>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
