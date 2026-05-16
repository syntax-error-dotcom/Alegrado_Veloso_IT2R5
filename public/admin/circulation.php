<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');



$transactions = [];
$transactionsSql = "SELECT t.transaction_id,
                    t.user_id,
                    u.username,
                    t.inventory_id,
                    b.title,
                    t.borrowDate,
                    t.dueDate,
                    t.returnDate,
                    t.status
                    FROM transactions t
                    INNER JOIN users u ON t.user_id = u.user_id
                    INNER JOIN inventory i ON t.inventory_id = i.inventory_id
                    INNER JOIN books b ON i.book_id = b.book_id
                    ORDER BY t.borrowDate DESC";
$transactionsResult = $conn->query($transactionsSql);

if ($transactionsResult && $transactionsResult->num_rows > 0) {
    while ($row = $transactionsResult->fetch_assoc()) {
        $transactions[] = $row;
    }
} elseif ($transactionsResult === false) {
    echo '<p class="text-danger px-3">Could not load circulation rows. SQL error: ' . htmlspecialchars($conn->error) . '</p>';
}



?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="mb-4">
        <h1 class="h3 mb-2 text-gray-800">Circulation</h1>

        <!-- Content Row -->
        <div class="row">

           
        </div>
    </div>
     <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Circulation</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Inventory ID</th>
                            <th>Book Title</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
if (count($transactions) > 0) {
    foreach ($transactions as $tx) {
        $txId = (int) $tx['transaction_id'];
        $uid = (int) $tx['user_id'];
        $uname = htmlspecialchars($tx['username'] ?? '', ENT_QUOTES, 'UTF-8');
        $inventoryId = (int) $tx['inventory_id'];
        $title = htmlspecialchars($tx['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $borrowDate = htmlspecialchars($tx['borrowDate'] ?? '', ENT_QUOTES, 'UTF-8');
        $dueDate = htmlspecialchars($tx['dueDate'] ?? '', ENT_QUOTES, 'UTF-8');
        $returnDate = htmlspecialchars($tx['returnDate'] ?? '', ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($tx['status'] ?? '', ENT_QUOTES, 'UTF-8');
        $isLocked = in_array(($tx['status'] ?? ''), ['Returned', 'Denied'], true);

        echo '<tr>';
        echo '<td>' . $txId . '</td>';
        echo '<td><span class="font-weight-bold">' . $uname . '</span><br><small class="text-muted">user_id: ' . $uid . '</small></td>';
        echo '<td>' . $inventoryId . '</td>';
        echo '<td>' . $title . '</td>';
        echo '<td>' . $borrowDate . '</td>';
        echo '<td>' . $dueDate . '</td>';
        echo '<td>' . $returnDate . '</td>';
        echo '<td>' . $status . '</td>';
        
        echo '<td>';
if ($isLocked) {
    echo '<button type="button" class="btn btn-secondary btn-sm" disabled>Locked</button>';
    // checkout receipt — only shows when Returned
    if ($tx['status'] === 'Returned') {
        echo ' <a href="/eLibrary/public/admin/receipt.php?transaction_id=' . $txId . '&type=checkout" '
            . 'class="fa fa-receipt" title="View Return Receipt">'
            . '<i class="bi bi-receipt"></i></a>';
    }
} else {
    echo '<button type="button" class="btn btn-success text-nowrap btn-sm open-set-status" '
        . 'data-toggle="modal" data-target="#setStatusModal" '
        . 'data-transaction-id="' . $txId . '" '
        . 'data-current-status="' . $status . '">Set Status</button>';
}
echo '</td></tr>';
    } // closes foreach
} else {
    echo "<tr><td colspan='9' class='text-center'>No circulation transactions found.</td></tr>";
} // closes if
?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="setStatusModal" tabindex="-1" role="dialog" aria-labelledby="stockBookLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-center w-100" id="stockBookLabel">Set Status</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form method="POST" action="../../app/controllers/adminBackend/circulationController.php" id="setStatusForm">
          <div class="form-group">
            <label for="transaction_id">Transaction ID</label>
            <input type="text" class="form-control" id="transaction_id_display" readonly>
            <input type="hidden" id="transaction_id" name="transaction_id" required>
          </div>
          
          <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status" required>
              <option value="Borrowed">Borrowed</option>
              <option value="Returned">Returned</option>
              <option value="Overdue">Overdue</option>
            </select>
            <small id="returnedWarning" class="form-text text-warning d-none">
              You selected Returned. You will not be able to change this after completing transaction.
            </small>
          </div>
          <button type="submit" id="setStatusButton" name="setStatusButton" class="btn btn-primary">Set Status</button>
          <button type="submit" id="completeTransactionButton" name="completeTransactionButton" class="btn btn-success">Complete Transaction</button>
          <button type="submit" id="createReceipt" name="createReceipt" class="btn btn-info">Create Receipt</button>  
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('setStatusModal');
    const txHidden = document.getElementById('transaction_id');
    const txDisplay = document.getElementById('transaction_id_display');
    const statusSelect = document.getElementById('status');
    const setStatusBtn = document.getElementById('setStatusButton');
    const completeBtn = document.getElementById('completeTransactionButton');
    const warning = document.getElementById('returnedWarning');

    document.querySelectorAll('.open-set-status').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const txId = btn.getAttribute('data-transaction-id');
            const current = btn.getAttribute('data-current-status');
            txHidden.value = txId;
            txDisplay.value = txId;
            statusSelect.value = current === 'Overdue' ? 'Overdue' : 'Borrowed';
            warning.classList.add('d-none');
            setStatusBtn.disabled = false;
            completeBtn.disabled = false;
        });
    });

    statusSelect.addEventListener('change', function () {
        if (statusSelect.value === 'Returned') {
            warning.classList.remove('d-none');
            setStatusBtn.disabled = true;
            completeBtn.disabled = false;
        } else {
            warning.classList.add('d-none');
            setStatusBtn.disabled = false;
        }
    });

    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            txHidden.value = '';
            txDisplay.value = '';
            statusSelect.value = 'Borrowed';
            warning.classList.add('d-none');
            setStatusBtn.disabled = false;
            completeBtn.disabled = false;
        });
    }
});
</script>

<?php
include(__DIR__ . '/includes/footer.php');
?>