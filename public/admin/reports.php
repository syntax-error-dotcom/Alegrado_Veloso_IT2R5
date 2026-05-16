<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function safeDate($value, $fallback = '')
{
    $value = trim($value);
    if ($value === '') {
        return $fallback;
    }
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date ? $date->format('Y-m-d') : $fallback;
}

function fetchScalar($conn, $sql, $default = 0)
{
    $result = $conn->query($sql);
    if ($result && ($row = $result->fetch_row())) {
        return $row[0] ?? $default;
    }
    return $default;
}

function fetchRows($conn, $sql)
{
    $rows = [];
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function tableExists($conn, $tableName)
{
    $result = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($tableName) . "'");
    return $result && $result->num_rows > 0;
}

function periodCondition($period, $dateField)
{
    switch ($period) {
        case 'daily':
            return "DATE($dateField) = CURDATE()";
        case 'weekly':
            return "$dateField >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        case 'monthly':
            return "$dateField >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        case 'yearly':
            return "$dateField >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        default:
            return '1=1';
    }
}

$period = $_GET['period'] ?? 'all';
$period = in_array($period, ['daily', 'weekly', 'monthly', 'yearly', 'all'], true) ? $period : 'all';
$login_from = safeDate($_GET['login_from'] ?? date('Y-m-d', strtotime('-7 days')), date('Y-m-d', strtotime('-7 days')));
$login_to = safeDate($_GET['login_to'] ?? date('Y-m-d'), date('Y-m-d'));

$login_from_ts = $login_from . ' 00:00:00';
$login_to_ts = $login_to . ' 23:59:59';

$periodBorrowCondition = periodCondition($period, 't.borrowDate');
$periodReservationCondition = periodCondition($period, 'DATE(r.requestDate)');

$totalBooks = fetchScalar($conn, 'SELECT COUNT(*) FROM books');
$totalUsers = fetchScalar($conn, 'SELECT COUNT(*) FROM users');
$totalAvailableBooks = fetchScalar($conn, "SELECT COUNT(*) FROM inventory WHERE status = 'Available'");
$totalBorrowedBooks = fetchScalar($conn, "SELECT COUNT(*) FROM transactions WHERE status = 'Borrowed'");
$totalOverdueBooks = fetchScalar($conn, "SELECT COUNT(*) FROM transactions WHERE status = 'Overdue' OR (status = 'Borrowed' AND dueDate < CURDATE())");

$mostBorrowedBook = fetchRows($conn, "SELECT b.title, b.author, COALESCE(c.categoryName, 'Uncategorized') AS categoryName,
    COALESCE(tb.borrow_count, 0) AS borrow_count,
    COALESCE(tr.reservation_count, 0) AS reservation_count,
    COALESCE(tb.borrow_count, 0) + COALESCE(tr.reservation_count, 0) AS total_actions,
    COALESCE(ti.avail_copies, 0) AS available_copies
FROM books b
LEFT JOIN categories c ON c.category_id = b.category_id
LEFT JOIN (
    SELECT i.book_id, COUNT(t.transaction_id) AS borrow_count
    FROM transactions t
    JOIN inventory i ON i.inventory_id = t.inventory_id
    WHERE t.status IN ('Borrowed', 'Returned', 'Overdue') AND $periodBorrowCondition
    GROUP BY i.book_id
) tb ON tb.book_id = b.book_id
LEFT JOIN (
    SELECT book_id, COUNT(*) AS reservation_count
    FROM reservation r
    WHERE r.status IN ('Pending', 'Approved', 'Completed') AND $periodReservationCondition
    GROUP BY book_id
) tr ON tr.book_id = b.book_id
LEFT JOIN (
    SELECT book_id, SUM(status = 'Available') AS avail_copies
    FROM inventory
    GROUP BY book_id
) ti ON ti.book_id = b.book_id
ORDER BY total_actions DESC, borrow_count DESC
LIMIT 10");

$topCategory = fetchRows($conn, "SELECT c.categoryName, COUNT(t.transaction_id) AS borrow_count
FROM categories c
JOIN books b ON b.category_id = c.category_id
JOIN inventory i ON i.book_id = b.book_id
JOIN transactions t ON t.inventory_id = i.inventory_id
WHERE t.status IN ('Borrowed', 'Returned', 'Overdue')
GROUP BY c.category_id
ORDER BY borrow_count DESC
LIMIT 1");

$recentBooks = fetchRows($conn, "SELECT b.title, b.author, COALESCE(c.categoryName, 'Uncategorized') AS categoryName,
    DATE_FORMAT(b.yearPublished, '%Y') AS yearPublished
FROM books b
LEFT JOIN categories c ON c.category_id = b.category_id
ORDER BY b.book_id DESC
LIMIT 5");

$inventoryRows = fetchRows($conn, "SELECT b.book_id, b.title, b.author, COALESCE(c.categoryName, 'Uncategorized') AS categoryName,
    COUNT(i.inventory_id) AS total_quantity,
    SUM(i.status = 'Available') AS available_quantity,
    SUM(i.status = 'Borrowed') AS borrowed_quantity,
    CASE
        WHEN SUM(i.status = 'Available') = 0 THEN 'Out of Stock'
        WHEN SUM(i.status = 'Available') <= 2 THEN 'Low Stock'
        ELSE 'Available'
    END AS current_status
FROM books b
LEFT JOIN categories c ON c.category_id = b.category_id
LEFT JOIN inventory i ON i.book_id = b.book_id
GROUP BY b.book_id, b.title, b.author, c.categoryName
ORDER BY available_quantity ASC, total_quantity DESC");

$overdueRows = fetchRows($conn, "SELECT CONCAT(u.firstName, ' ', u.lastName) AS borrower_name,
    b.title AS book_title,
    t.borrowDate,
    t.dueDate,
    t.returnDate,
    CASE
        WHEN t.status = 'Returned' THEN GREATEST(DATEDIFF(t.returnDate, t.dueDate), 0)
        WHEN t.dueDate < CURDATE() THEN DATEDIFF(CURDATE(), t.dueDate)
        ELSE 0
    END AS overdue_days,
    t.status
FROM transactions t
JOIN users u ON u.user_id = t.user_id
JOIN inventory i ON i.inventory_id = t.inventory_id
JOIN books b ON b.book_id = i.book_id
ORDER BY t.dueDate DESC");

$activityRows = fetchRows($conn, "SELECT user_id, user_name, role, action, details, activity_date, status
FROM (
    SELECT u.user_id,
        CONCAT(u.firstName, ' ', u.lastName) AS user_name,
        u.role,
        'Borrow' AS action,
        CONCAT('Borrowed \"', b.title, '\"') AS details,
        t.borrowDate AS activity_date,
        t.status AS status
    FROM transactions t
    JOIN users u ON u.user_id = t.user_id
    JOIN inventory i ON i.inventory_id = t.inventory_id
    JOIN books b ON b.book_id = i.book_id
    WHERE t.status IN ('Borrowed', 'Returned', 'Overdue')
    UNION ALL
    SELECT u.user_id,
        CONCAT(u.firstName, ' ', u.lastName) AS user_name,
        u.role,
        'Return' AS action,
        CONCAT('Returned \"', b.title, '\"') AS details,
        COALESCE(t.returnDate, t.borrowDate) AS activity_date,
        t.status AS status
    FROM transactions t
    JOIN users u ON u.user_id = t.user_id
    JOIN inventory i ON i.inventory_id = t.inventory_id
    JOIN books b ON b.book_id = i.book_id
    WHERE t.returnDate IS NOT NULL
    UNION ALL
    SELECT u.user_id,
        CONCAT(u.firstName, ' ', u.lastName) AS user_name,
        u.role,
        'Reservation' AS action,
        CONCAT(r.status, ' reservation for \"', b.title, '\"') AS details,
        r.requestDate AS activity_date,
        r.status AS status
    FROM reservation r
    JOIN users u ON u.user_id = r.user_id
    JOIN books b ON b.book_id = r.book_id
) combined
ORDER BY activity_date DESC
LIMIT 200");

$loginTrendRows = [];
$dailyLoginRows = [];
if (tableExists($conn, 'login_audit')) {
    $dailyLoginRows = fetchRows($conn, "SELECT DATE(login_timestamp) AS log_date,
        COUNT(*) AS total_logins,
        GROUP_CONCAT(DISTINCT role ORDER BY role SEPARATOR ', ') AS roles
    FROM login_audit
    WHERE login_timestamp BETWEEN '" . $conn->real_escape_string($login_from_ts) . "' AND '" . $conn->real_escape_string($login_to_ts) . "'
    GROUP BY DATE(login_timestamp)
    ORDER BY log_date DESC");

    $loginTrendRows = fetchRows($conn, "SELECT DATE(login_timestamp) AS log_date,
        COUNT(*) AS total_logins
    FROM login_audit
    WHERE login_timestamp BETWEEN '" . $conn->real_escape_string($login_from_ts) . "' AND '" . $conn->real_escape_string($login_to_ts) . "'
    GROUP BY DATE(login_timestamp)
    ORDER BY log_date");
}

$todayLogins = 0;
if (tableExists($conn, 'login_audit')) {
    $todayLogins = fetchScalar($conn, "SELECT COUNT(*) FROM login_audit WHERE DATE(login_timestamp) = CURDATE()");
}

$topCategoryLabel = 'N/A';
if (count($topCategory) > 0) {
    $topCategoryLabel = $topCategory[0]['categoryName'];
}

$loginChartLabels = [];
$loginChartLogins = [];
foreach ($loginTrendRows as $row) {
    $loginChartLabels[] = $row['log_date'];
    $loginChartLogins[] = (int) $row['total_logins'];
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reports Dashboard</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary" onclick="printSection('reportsPage')"><i
                    class="fas fa-print"></i> Print Report</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="exportPagePDF()"><i
                    class="fas fa-file-pdf"></i> Export PDF</button>
        </div>
    </div>

    <div class="row" id="reportsPage">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Books</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo h($totalBooks); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-book fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo h($totalUsers); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Borrowed Books
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo h($totalBorrowedBooks); ?>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-book-reader fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Available Copies
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo h($totalAvailableBooks); ?>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Logins Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo h($todayLogins); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-sign-in-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue Books</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo h($totalOverdueBooks); ?>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Login Activity Trend</h6>
                    <small><?php echo h($login_from); ?> to <?php echo h($login_to); ?></small>
                </div>
                <div class="card-body">
                    <div class="chart-area"><canvas id="loginTrendChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Popular Categories</h6>
                    <small>Top category based on borrows</small>
                </div>
                <div class="card-body">
                    <div class="h5 mb-2"><?php echo h($topCategoryLabel); ?></div>
                    <p class="text-muted">Category popularity is derived from transaction history.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Borrow Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCategory as $category): ?>
                                    <tr>
                                        <td><?php echo h($category['categoryName']); ?></td>
                                        <td><?php echo h($category['borrow_count']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">Most Borrowed / Most Booked Books</h6>
                <div class="small text-muted">Sorted by borrow and booking activity.</div>
            </div>
            <form class="form-inline" method="get" action="reports.php">
                <label class="mr-2">Period</label>
                <select name="period" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="all" <?php echo $period === 'all' ? 'selected' : ''; ?>>All Time</option>
                    <option value="daily" <?php echo $period === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo $period === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo $period === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="yearly" <?php echo $period === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="borrowedTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Borrowed Count</th>
                            <th>Booked Count</th>
                            <th>Available Copies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mostBorrowedBook as $book): ?>
                            <tr>
                                <td><?php echo h($book['title']); ?></td>
                                <td><?php echo h($book['author']); ?></td>
                                <td><?php echo h($book['categoryName']); ?></td>
                                <td><?php echo h($book['borrow_count']); ?></td>
                                <td><?php echo h($book['reservation_count']); ?></td>
                                <td><?php echo h($book['available_copies']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recently Added Books</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentBooks as $recent): ?>
                            <li class="list-group-item">
                                <strong><?php echo h($recent['title']); ?></strong><br>
                                <span class="small text-muted"><?php echo h($recent['author']); ?> &middot;
                                    <?php echo h($recent['categoryName']); ?> &middot;
                                    <?php echo h($recent['yearPublished']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">Complete Inventory Report</h6>
                <div class="small text-muted">Inventory status for every title in the system.</div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="inventoryTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Book ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Total Qty</th>
                            <th>Available Qty</th>
                            <th>Borrowed Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventoryRows as $inventory): ?>
                            <tr
                                class="<?php echo ($inventory['current_status'] === 'Low Stock' ? 'table-warning' : ($inventory['current_status'] === 'Out of Stock' ? 'table-danger' : '')); ?>">
                                <td><?php echo h($inventory['book_id']); ?></td>
                                <td><?php echo h($inventory['title']); ?></td>
                                <td><?php echo h($inventory['author']); ?></td>
                                <td><?php echo h($inventory['categoryName']); ?></td>
                                <td><?php echo h($inventory['total_quantity']); ?></td>
                                <td><?php echo h($inventory['available_quantity']); ?></td>
                                <td><?php echo h($inventory['borrowed_quantity']); ?></td>
                                <td><?php echo h($inventory['current_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">Overdue and Returned Books Report</h6>
                <div class="small text-muted">Shows borrower, due dates, return status, and overdue days.</div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="overdueTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Borrower Name</th>
                            <th>Book Title</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Overdue Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overdueRows as $overdue): ?>
                            <tr>
                                <td><?php echo h($overdue['borrower_name']); ?></td>
                                <td><?php echo h($overdue['book_title']); ?></td>
                                <td><?php echo h($overdue['borrowDate']); ?></td>
                                <td><?php echo h($overdue['dueDate']); ?></td>
                                <td><?php echo h($overdue['returnDate'] ?: 'N/A'); ?></td>
                                <td><?php echo h($overdue['overdue_days']); ?></td>
                                <td><?php echo h($overdue['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Activity Report</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="activityTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activityRows as $activity): ?>
                            <tr>
                                <td><?php echo h($activity['user_name']); ?></td>
                                <td><?php echo h($activity['role']); ?></td>
                                <td><?php echo h($activity['action']); ?></td>
                                <td><?php echo h($activity['details']); ?></td>
                                <td><?php echo h($activity['activity_date']); ?></td>
                                <td><?php echo h($activity['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script>
    function downloadTableCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;
        let csv = [];
        for (let row of table.querySelectorAll('tr')) {
            let cols = [];
            for (let cell of row.querySelectorAll('th, td')) {
                let text = cell.innerText.replace(/\n/g, ' ').replace(/"/g, '""');
                cols.push('"' + text + '"');
            }
            csv.push(cols.join(','));
        }
        const blob = new Blob([csv.join('\n')], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function printSection(sectionId) {
        const content = document.getElementById(sectionId);
        if (!content) return;
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Print Report</title>');
        printWindow.document.write('<link rel="stylesheet" href="./assets/vendor/fontawesome-free/css/all.min.css">');
        printWindow.document.write('<link rel="stylesheet" href="./assets/css/sb-admin-2.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content.outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    function exportPagePDF() {
        const element = document.getElementById('reportsPage');
        if (!element) return;
        html2pdf().set({
            margin: 10,
            filename: 'library-reports.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            }
        }).from(element).save();
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#borrowedTable, #loginTable, #inventoryTable, #overdueTable, #activityTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthChange: false,
                ordering: true,
                order: [
                    [0, 'desc']
                ]
            });
        }

        const loginLabels = <?php echo json_encode($loginChartLabels); ?>;
        const loginCounts = <?php echo json_encode($loginChartLogins); ?>;
        if (document.getElementById('loginTrendChart')) {
            new Chart(document.getElementById('loginTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: loginLabels,
                    datasets: [{
                        label: 'Total Logins',
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        data: loginCounts,
                        fill: true,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        display: true
                    }
                }
            });
        }
    });
</script>

<?php include(__DIR__ . '/includes/footer.php'); ?>