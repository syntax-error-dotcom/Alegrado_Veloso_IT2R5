<?php
// ─── Sample order data (replace with your DB query) ───────────────────────
$order = [
    'receipt_no'     => 'RCP-10042',
    'date'           => date('F j, Y'),
    'customer_name'  => 'Juan dela Cruz',
    'customer_email' => 'juan@example.com',
    'payment_method' => 'Visa ending in 4242',
    'items' => [
        ['name' => 'Wireless Headphones', 'qty' => 1, 'price' => 89.99],
        ['name' => 'USB-C Cable',         'qty' => 2, 'price' => 12.00],
        ['name' => 'Phone Case',          'qty' => 1, 'price' => 19.99],
    ],
    'discount_pct' => 10,   // percent
    'tax_pct'      => 8,    // percent
];

// ─── Calculations (always do this server-side) ────────────────────────────
$subtotal = 0;
foreach ($order['items'] as $item) {
    $subtotal += $item['qty'] * $item['price'];
}
$discount = $subtotal * ($order['discount_pct'] / 100);
$taxable  = $subtotal - $discount;
$tax      = $taxable  * ($order['tax_pct']      / 100);
$total    = $taxable  + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Receipt <?= htmlspecialchars($order['receipt_no']) ?></title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }

    .receipt-card {
      max-width: 560px;
      margin: 2.5rem auto;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      background: #ffffff;
      padding: 2rem;
      box-shadow: 0 4px 24px rgba(0,0,0,.06);
    }

    .receipt-header {
      text-align: center;
      padding-bottom: 1.25rem;
      border-bottom: 1px dashed #dee2e6;
    }

    .receipt-header .store-icon {
      width: 48px; height: 48px;
      border-radius: 12px;
      background: #e8f0fe;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      color: #3b6fd4;
      margin-bottom: .6rem;
    }

    .receipt-header h5 {
      font-weight: 600;
      margin-bottom: .15rem;
      color: #1a202c;
    }

    .receipt-header small {
      color: #718096;
    }

    .items-table th {
      font-size: .75rem;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #a0aec0;
      font-weight: 500;
      border-bottom: 1px solid #edf2f7;
    }

    .items-table td {
      font-size: .875rem;
      vertical-align: middle;
      color: #2d3748;
      border-bottom: 1px solid #f7fafc;
      padding: .6rem .5rem;
    }

    .totals-section {
      font-size: .875rem;
    }

    .totals-section .row {
      padding: .3rem 0;
      color: #718096;
    }

    .totals-section .grand-total {
      font-size: 1rem;
      font-weight: 600;
      color: #1a202c;
      border-top: 1px solid #edf2f7;
      padding-top: .75rem;
      margin-top: .25rem;
    }

    .payment-badge {
      background: #f7fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: .6rem 1rem;
      font-size: .8rem;
      color: #4a5568;
    }

    .receipt-footer {
      text-align: center;
      font-size: .75rem;
      color: #a0aec0;
      border-top: 1px dashed #dee2e6;
      padding-top: 1rem;
    }

    /* Print styles */
    @media print {
      body { background: white; }
      .receipt-card { box-shadow: none; border: none; margin: 0; max-width: 100%; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>

<div class="receipt-card">

  <!-- Header -->
  <div class="receipt-header mb-3">
    <div class="store-icon"><i class="bi bi-bag-check"></i></div>
    <h5>Acme Store</h5>
    <small>
      <?= htmlspecialchars($order['receipt_no']) ?> &nbsp;·&nbsp;
      <?= htmlspecialchars($order['date']) ?>
    </small>
  </div>

  <!-- Customer Info -->
  <div class="mb-3">
    <div class="d-flex justify-content-between" style="font-size:.85rem;">
      <span class="text-muted">Customer</span>
      <span class="fw-500"><?= htmlspecialchars($order['customer_name']) ?></span>
    </div>
    <div class="d-flex justify-content-between" style="font-size:.85rem;">
      <span class="text-muted">Email</span>
      <span><?= htmlspecialchars($order['customer_email']) ?></span>
    </div>
  </div>

  <!-- Line Items -->
  <table class="table items-table mb-3">
    <thead>
      <tr>
        <th>Item</th>
        <th class="text-center">Qty</th>
        <th class="text-end">Price</th>
        <th class="text-end">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($order['items'] as $item):
        $line_total = $item['qty'] * $item['price'];
      ?>
      <tr>
        <td><?= htmlspecialchars($item['name']) ?></td>
        <td class="text-center text-muted"><?= $item['qty'] ?></td>
        <td class="text-end text-muted">$<?= number_format($item['price'], 2) ?></td>
        <td class="text-end">$<?= number_format($line_total, 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals-section mb-3">
    <div class="row">
      <div class="col">Subtotal</div>
      <div class="col text-end">$<?= number_format($subtotal, 2) ?></div>
    </div>
    <div class="row">
      <div class="col">Discount (<?= $order['discount_pct'] ?>%)</div>
      <div class="col text-end text-success">−$<?= number_format($discount, 2) ?></div>
    </div>
    <div class="row">
      <div class="col">Tax (<?= $order['tax_pct'] ?>%)</div>
      <div class="col text-end">$<?= number_format($tax, 2) ?></div>
    </div>
    <div class="row grand-total">
      <div class="col">Total</div>
      <div class="col text-end">$<?= number_format($total, 2) ?></div>
    </div>
  </div>

  <!-- Payment Method -->
  <div class="payment-badge d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-credit-card"></i>
    <span>Paid via <?= htmlspecialchars($order['payment_method']) ?></span>
  </div>

  <!-- Action Buttons -->
  <div class="d-flex gap-2 no-print mb-3">
    <button class="btn btn-outline-secondary btn-sm flex-fill"
            onclick="window.print()">
      <i class="bi bi-printer me-1"></i> Print
    </button>
    <a class="btn btn-outline-secondary btn-sm flex-fill"
       href="download_receipt.php?id=<?= urlencode($order['receipt_no']) ?>">
      <i class="bi bi-download me-1"></i> Download PDF
    </a>
    <a class="btn btn-outline-secondary btn-sm flex-fill"
       href="email_receipt.php?id=<?= urlencode($order['receipt_no']) ?>">
      <i class="bi bi-envelope me-1"></i> Email
    </a>
  </div>

  <!-- Footer -->
  <div class="receipt-footer">
    Thank you for your purchase!<br>
    Returns accepted within 30 days.
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>