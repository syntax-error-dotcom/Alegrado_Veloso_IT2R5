<?php
// receipt.php
// Accessible via: receipt.php?transaction_id=1&type=checkin
//                 receipt.php?transaction_id=1&type=checkout
include('../../app/middleware/admin.php');
include('../../app/config/config.php');
include('./includes/header.php');

// ── 1. Validate GET parameters ─────────────────────────────────────────────
$transactionId = (int) ($_GET['transaction_id'] ?? 0);
$type          = $_GET['type'] ?? '';

if ($transactionId <= 0 || !in_array($type, ['checkin', 'checkout'], true)) {
    $_SESSION['message'] = 'Invalid receipt request.';
    $_SESSION['code']    = 'error';
    header("Location: /eLibrary/public/admin/circulation.php");
    exit();
}

// ── 2. Fetch the receipt row ────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT r.*, 
           CONCAT(u.firstName, ' ', u.middleName, ' ', u.lastName) AS full_name,
           u.emailAddress,
           u.username,
           b.title AS book_title_live,
           i.bookNumber,
           i.location
    FROM receipts r
    JOIN transactions t  ON r.transaction_id = t.transaction_id
    JOIN users u         ON t.user_id = u.user_id
    JOIN books b         ON t.book_id = b.book_id
    JOIN inventory i     ON t.inventory_id = i.inventory_id
    WHERE r.transaction_id = ? AND r.type = ?
    LIMIT 1
");
$stmt->bind_param("is", $transactionId, $type);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── 3. If no receipt found yet, show friendly error ─────────────────────────
if (!$receipt) {
    echo '
    <div class="container mt-5">
        <div class="alert alert-warning text-center">
            No ' . htmlspecialchars($type) . ' receipt found for this transaction yet.
            <br><small class="text-muted">It may not have been generated yet.</small>
        </div>
        <div class="text-center mt-3">
            <a href="/eLibrary/public/admin/circulation.php" class="btn btn-secondary">
                &larr; Back to Circulation
            </a>
        </div>
    </div>';
    include('./includes/footer.php');
    exit();
}

// ── 4. Prepare display values ───────────────────────────────────────────────
$displayBookTitle = htmlspecialchars($receipt['book_title'] ?: $receipt['book_title_live']);
$displayUserName  = htmlspecialchars(trim($receipt['full_name']));
$displayEmail     = htmlspecialchars($receipt['emailAddress']);
$displayUsername  = htmlspecialchars($receipt['username']);
$displayInventory = htmlspecialchars($receipt['inventory_id']);
$displayBookNum   = htmlspecialchars($receipt['bookNumber']);
$displayLocation  = htmlspecialchars($receipt['location']);
$receiptId        = 'RCP-' . str_pad($receipt['receipt_id'], 5, '0', STR_PAD_LEFT);
$generatedAt      = date('F j, Y  g:i A', strtotime($receipt['generated_at']));

$borrowDate = $receipt['borrow_date'] ? date('F j, Y', strtotime($receipt['borrow_date'])) : '—';
$dueDate    = $receipt['due_date']    ? date('F j, Y', strtotime($receipt['due_date']))    : '—';
$returnDate = $receipt['return_date'] ? date('F j, Y', strtotime($receipt['return_date'])) : '—';

$fineAmount = (float) $receipt['fine_amount'];
$isOverdue  = $fineAmount > 0;
$isCheckin  = $type === 'checkin';

$receiptLabel = $isCheckin ? 'Borrow Receipt' : 'Return Receipt';

// Status pill values
if ($isCheckin) {
    $statusLabel = 'Active Borrow';
    $statusStyle = 'background:#d1e7dd; color:#0f5132;';
} elseif ($isOverdue) {
    $statusLabel = 'Returned — Overdue';
    $statusStyle = 'background:#f8d7da; color:#842029;';
} else {
    $statusLabel = 'Returned — On Time';
    $statusStyle = 'background:#d1e7dd; color:#0f5132;';
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
  .receipt-page {
    background: #f5f5f5;
    min-height: 100vh;
    padding: 2rem 1rem 4rem;
    font-family: 'Segoe UI', sans-serif;
    font-size: 13px;
  }

  .receipt-wrap {
    max-width: 480px;
    margin: 0 auto;
  }

  .receipt-nav {
    margin-bottom: 1rem;
    font-size: 12px;
    color: #6c757d;
  }

  .receipt-nav a {
    color: #6c757d;
    text-decoration: none;
  }

  .receipt-nav a:hover { color: #343a40; }

  .receipt-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
  }

  .receipt-section {
    padding: 1rem 1.5rem;
    border-bottom: 1px dashed #e0e0e0;
  }

  .receipt-section:last-of-type {
    border-bottom: none;
  }

  .receipt-section-label {
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #adb5bd;
    margin: 0 0 8px;
  }

  .receipt-header {
    padding: 1.5rem;
    text-align: center;
    border-bottom: 1px dashed #e0e0e0;
  }

  .receipt-header .system-name {
    font-size: 11px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #adb5bd;
    margin: 0 0 4px;
  }

  .receipt-header .receipt-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 4px;
    color: #212529;
  }

  .receipt-header .receipt-meta {
    font-size: 11px;
    color: #adb5bd;
    margin: 0;
  }

  .info-table {
    width: 100%;
    border-collapse: collapse;
  }

  .info-table td {
    padding: 4px 0;
    vertical-align: top;
  }

  .info-table .info-label {
    color: #6c757d;
    width: 42%;
  }

  .info-table .info-value {
    text-align: right;
    font-weight: 500;
    color: #212529;
  }

  .status-pill {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 50px;
    display: inline-block;
  }

  .receipt-footer-note {
    padding: 1rem 1.5rem;
    text-align: center;
    background: #f8f9fa;
    border-top: 1px dashed #e0e0e0;
  }

  .receipt-footer-note p {
    font-size: 11px;
    color: #adb5bd;
    margin: 0;
    line-height: 1.6;
  }

  .receipt-actions {
    display: flex;
    gap: .5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
  }

  .receipt-actions .btn {
    font-size: 12px;
    padding: .4rem .9rem;
    border-radius: 6px;
  }

  @media print {
    body, .receipt-page { background: white !important; }
    .receipt-nav, .receipt-actions { display: none !important; }
    .receipt-card { border: none !important; box-shadow: none !important; }
  }
</style>

<div class="receipt-page">
  <div class="receipt-wrap">

    <!-- Back link -->
    <div class="receipt-nav no-print">
      <a href="/eLibrary/public/admin/circulation.php">
        <i class="bi bi-arrow-left"></i> Back to Circulation
      </a>
      <span class="mx-1">/</span>
      <span><?= $receiptLabel ?></span>
    </div>

    <div class="receipt-card">

      <!-- Header -->
      <div class="receipt-header">
        <p class="system-name">eLibrary System</p>
        <p class="receipt-title"><?= $receiptLabel ?></p>
        <p class="receipt-meta"><?= $receiptId ?> &nbsp;·&nbsp; <?= $generatedAt ?></p>
      </div>

      <!-- Borrower -->
      <div class="receipt-section">
        <p class="receipt-section-label">Borrower</p>
        <table class="info-table">
          <tr>
            <td class="info-label">Full Name</td>
            <td class="info-value"><?= $displayUserName ?></td>
          </tr>
          <tr>
            <td class="info-label">Username</td>
            <td class="info-value"><?= $displayUsername ?></td>
          </tr>
          <tr>
            <td class="info-label">Email</td>
            <td class="info-value"><?= $displayEmail ?></td>
          </tr>
        </table>
      </div>

      <!-- Book -->
      <div class="receipt-section">
        <p class="receipt-section-label">Book</p>
        <table class="info-table">
          <tr>
            <td class="info-label">Title</td>
            <td class="info-value"><?= $displayBookTitle ?></td>
          </tr>
          <tr>
            <td class="info-label">Book No.</td>
            <td class="info-value"><?= $displayBookNum ?></td>
          </tr>
          <tr>
            <td class="info-label">Inventory ID</td>
            <td class="info-value">#<?= $displayInventory ?></td>
          </tr>
          <tr>
            <td class="info-label">Location</td>
            <td class="info-value"><?= $displayLocation ?></td>
          </tr>
        </table>
      </div>

      <!-- Dates -->
      <div class="receipt-section">
        <p class="receipt-section-label">Dates</p>
        <table class="info-table">
          <tr>
            <td class="info-label">Borrowed</td>
            <td class="info-value"><?= $borrowDate ?></td>
          </tr>
          <tr>
            <td class="info-label">Due Date</td>
            <td class="info-value"><?= $dueDate ?></td>
          </tr>
          <?php if (!$isCheckin): ?>
          <tr>
            <td class="info-label">Returned</td>
            <td class="info-value <?= $isOverdue ? 'text-danger' : '' ?>"><?= $returnDate ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>

      <!-- Status -->
      <div class="receipt-section" style="display:flex; justify-content:space-between; align-items:center;">
        <span style="color:#6c757d;">Status</span>
        <span class="status-pill" style="<?= $statusStyle ?>"><?= $statusLabel ?></span>
      </div>

      <!-- Fine — checkout only -->
      <div class="receipt-section" style="display:flex; justify-content:space-between; align-items:center;">
        <span style="color:#6c757d;">Overdue Fine</span>
        <span style="font-weight:600; <?= $isOverdue ? 'color:#dc3545;' : '' ?>">
          ₱<?= number_format($fineAmount, 2) ?>
        </span>
      </div>

      <!-- Footer note -->
      <div class="receipt-footer-note">
        <p>This is an official receipt from eLibrary System.<br>Thank you for using our library services.</p>
      </div>

    </div><!-- /receipt-card -->

    <!-- Action buttons -->
    <div class="receipt-actions no-print">
      <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
      </button>
      <a href="/eLibrary/public/admin/circulation.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

  </div>
</div>

<?php include('./includes/footer.php'); ?>