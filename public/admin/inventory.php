<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include('../../app/config/config.php');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');

// Fetch all books from database with category names (using JOIN)
$inventory = [];
$inventorySql = "SELECT i.inventory_id, b.book_id, b.title, i.bookNumber, i.status, i.location
                 FROM inventory i
                 JOIN books b ON i.book_id = b.book_id
                 ORDER BY b.title ASC";
$inventoryResult = $conn->query($inventorySql);

if ($inventoryResult && $inventoryResult->num_rows > 0) {
    while ($row = $inventoryResult->fetch_assoc()) {
        $inventory[] = $row;
    }
} else {
    echo "<p style='color:red'>No inventory items found or query failed. Error: " . $conn->error . "</p>";
}



$books = [];
$booksSql = "SELECT book_id, title FROM books ORDER BY title ASC";
$bookResult = $conn->query($booksSql);

if ($bookResult && $bookResult->num_rows > 0) {
    while ($row = $bookResult->fetch_assoc()) {
        $books[] = $row;
    }
} else {
    echo "<p style='color:red'>No books found or query failed. Error: " . $conn->error . "</p>";
}



?>







<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-2 text-gray-900 font-medium">Inventory</h1>

       <div class="d-flex gap-2">

        <button type="button" class="btn btn-primary btn-success shadow-sm mb-2 mr-4 text-white" data-toggle="modal" data-target="#stockBookModal">
            <i class="fas fa-boxes fa-sm text-white-50"></i> Stock Book
        </button>

    
    
    </div>   



        
       


    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Books Database</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Book ID</th>
                            <th>Book name</th>
                            <th>Book Number</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($inventory) > 0) {
                            foreach ($inventory as $item) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($item['book_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($item['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($item['bookNumber']) . "</td>";
                                echo "<td>" . htmlspecialchars($item['status']) . "</td>";
                                echo "<td>" . htmlspecialchars($item['location']) . "</td>";
                                echo "<td>
                <form method='POST' action='../../app/controllers/adminBackend/inventoryController.php'>
                    <input type='hidden' name='inventory_id' value='" . htmlspecialchars($item['inventory_id']) . "'>
                    <button type='submit' name='removeBookButton' class='btn btn-danger btn-sm'>Remove</button>
                </form>
              </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No inventory found. Add stock first!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- Stock Book Modal -->
<div class="modal fade" id="stockBookModal" tabindex="-1" role="dialog" aria-labelledby="stockBookLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-center w-100" id="stockBookLabel">Stock a Book</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="form-group" style="margin: 20px;">
                <label>Select a book to stock<span style="color: red;">*</span></label>
                <input type="search" id="searchBook" class="form-control" placeholder="Search book by title or author...">
                <div id="searchResults" class="list-group" style="max-height: 300px; overflow-y: auto;"></div>
            </div>

            <form method="POST" action="../../app/controllers/adminBackend/inventoryController.php" style="margin:20px;">
                <input type="hidden" id="bookId" name="bookId" value="">

                <div class="modal-body">
                    <!-- Selected Book Info -->
                    <div class="alert alert-info" id="selectedBookInfo" style="display:none;">
                        <strong>Selected Book:</strong> <span id="selectedBookTitle"></span> by <span id="selectedBookAuthor"></span>
                    </div>

                    <!-- Location Selection -->
                    <div class="form-group">
                        <label>Location<span style="color: red;">*</span></label>
                        <input id=location name="location" class="form-control" placeholder="Enter location (e.g. Shelf A, Storage, Reference)" required>
                    </div>

                    <!-- Quantity Control -->
                    <div class="form-group">
                        <label>Quantity<span style="color: red;">*</span></label>
                        <div class="row justify-content-center align-items-center">
                            <!-- Left buttons -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-danger me-2" id="btnMinus10">-10</button>
                                <button type="button" class="btn btn-danger" id="btnMinus1">-1</button>
                            </div>

                            <!-- Input in the middle -->
                            <div class="col-auto">
                                <input type="number" id="numCopies" name="numCopies" class="form-control text-center" placeholder="0" value="0" style="width:120px;" min="1" required>
                            </div>

                            <!-- Right buttons -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-success me-2" id="btnPlus1">+1</button>
                                <button type="button" class="btn btn-success" id="btnPlus10">+10</button>
                            </div>
                        </div>
                    </div>









                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="stockBookButton" class="btn btn-primary" id="stockBtn" disabled>Stock Book</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== STOCK BOOK MODAL =====
        const searchInput = document.getElementById('searchBook');
        const searchResults = document.getElementById('searchResults');
        const bookIdInput = document.getElementById('bookId');
        const selectedBookInfo = document.getElementById('selectedBookInfo');
        const selectedBookTitle = document.getElementById('selectedBookTitle');
        const selectedBookAuthor = document.getElementById('selectedBookAuthor');
        const numCopiesInput = document.getElementById('numCopies');
        const locationSelect = document.getElementById('location');
        const stockBtn = document.getElementById('stockBtn');

        // Quantity button elements
        const btnMinus10 = document.getElementById('btnMinus10');
        const btnMinus1 = document.getElementById('btnMinus1');
        const btnPlus1 = document.getElementById('btnPlus1');
        const btnPlus10 = document.getElementById('btnPlus10');

        // Current selected book data
        let selectedBook = null;

        // Quantity button handlers
        btnMinus10.addEventListener('click', function(e) {
            e.preventDefault();
            let current = parseInt(numCopiesInput.value) || 0;
            numCopiesInput.value = Math.max(0, current - 10);
            updateStockButtonState();
        });

        btnMinus1.addEventListener('click', function(e) {
            e.preventDefault();
            let current = parseInt(numCopiesInput.value) || 0;
            numCopiesInput.value = Math.max(0, current - 1);
            updateStockButtonState();
        });

        btnPlus1.addEventListener('click', function(e) {
            e.preventDefault();
            let current = parseInt(numCopiesInput.value) || 0;
            numCopiesInput.value = current + 1;
            updateStockButtonState();
        });

        btnPlus10.addEventListener('click', function(e) {
            e.preventDefault();
            let current = parseInt(numCopiesInput.value) || 0;
            numCopiesInput.value = current + 10;
            updateStockButtonState();
        });

        // Update quantity input value
        numCopiesInput.addEventListener('change', function() {
            if (this.value === '' || parseInt(this.value) < 0) {
                this.value = '0';
            }
            updateStockButtonState();
        });

        // Enable/disable stock button based on form state
        function updateStockButtonState() {
            const hasBook = bookIdInput.value !== '';
            const hasQuantity = parseInt(numCopiesInput.value) > 0;
            const hasLocation = locationSelect.value !== '';

            stockBtn.disabled = !(hasBook && hasQuantity && hasLocation);
        }

        // Function to select a book and load its details
        function selectBook(bookId, title, author) {
            console.log('Selecting book with ID:', bookId);

            // Store selected book
            selectedBook = {
                bookId,
                title,
                author
            };

            // Update form
            bookIdInput.value = bookId;
            selectedBookTitle.textContent = title;
            selectedBookAuthor.textContent = author;
            selectedBookInfo.style.display = 'block';

            // Clear search results and input
            searchResults.innerHTML = '';
            searchInput.value = '';

            // Reset quantity and location
            numCopiesInput.value = '0';
            locationSelect.value = '';

            // Update button state
            updateStockButtonState();
            console.log('Book selected and form updated');
        }

        // Search functionality
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            fetch(`../api/search-book.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Search results:', data);
                    searchResults.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(book => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action text-start';
                            item.textContent = `${book.title} : ${book.author}`;
                            item.dataset.bookId = book.book_id;
                            item.dataset.title = book.title;
                            item.dataset.author = book.author;

                            // Proper event binding
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                selectBook(book.book_id, book.title, book.author);
                            });

                            searchResults.appendChild(item);
                        });
                    } else {
                        const noResults = document.createElement('div');
                        noResults.className = 'list-group-item';
                        noResults.textContent = 'No books found';
                        searchResults.appendChild(noResults);
                    }
                })
                .catch(error => console.error('Error searching books:', error));
        });

        // Reset on modal close
        document.getElementById('stockBookModal').addEventListener('hidden.bs.modal', function() {
            searchResults.innerHTML = '';
            searchInput.value = '';
            bookIdInput.value = '';
            selectedBookInfo.style.display = 'none';
            selectedBookTitle.textContent = '';
            selectedBookAuthor.textContent = '';
            numCopiesInput.value = '0';
            locationSelect.value = '';
            selectedBook = null;
            updateStockButtonState();
        });

        // Initial state: disable stock button
        updateStockButtonState();
    });
</script>

<?php
include(__DIR__ . '/includes/footer.php');
?>