<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include('../../app/config/config.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

// Fetch all books from database
$books = array();
$sql = "SELECT * FROM books ORDER BY title ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}
?>


<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="mb-4">
        <h1 class="h3 mb-2 text-gray-800">Catalogue</h1>

        <!-- Add Book (Green) -->
        <button type="button" class="btn btn-primary btn-success shadow-sm mb-2 mr-4 text-white" data-toggle="modal" data-target="#addBookModal">
             <i class="fas fa-plus fa-sm text-white-50"></i> Add Book
        </button>

        <!-- Update Book (Yellow/Orange) -->
        <a href="#" class="btn btn-sm btn-warning shadow-sm mb-2 mr-4 text-white">
            <i class="fas fa-edit fa-sm text-white-50"></i> Update Book
        </a>

        <!-- Delete Book (Red) -->
        <a href="#" class="btn btn-sm btn-danger shadow-sm mb-2 mr-4 text-white">
            <i class="fas fa-trash fa-sm text-white-50"></i> Delete Book
        </a>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">DataTables Example</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Cover Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Year Published</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($books) > 0) {
                            foreach($books as $book) {
                                echo "<tr>";
                                // Display cover image with error handling
                                echo "<td style='text-align: center; vertical-align: middle;'>";
                                if (!empty($book['coverImage'])) {
                                    echo "<img src='" . htmlspecialchars($book['coverImage']) . "' alt='Cover' style='height: 80px; width: auto; border-radius: 4px;' onerror=\"this.src=''; this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\' style=\\'font-size: 24px; color: #ccc;\\'></i><br><small>No Image</small>';\">";
                                } else {
                                    echo "<i class='fas fa-image' style='font-size: 24px; color: #ccc;'></i><br><small>No Image</small>";
                                }
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($book['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($book['author']) . "</td>";
                                echo "<td>" . htmlspecialchars($book['yearPublished']) . "</td>";
                                echo "<td>" . htmlspecialchars($book['isbn']) . "</td>";
                                echo "<td>" . htmlspecialchars($book['category']) . "</td>";
                                echo "<td>" . htmlspecialchars(substr($book['description'], 0, 100)) . "...</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No books found. Add your first book!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->


<!-- Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="addBookLabel">Add New Book</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      
      <form method="POST" action="../../app/controllers/adminController.php">
        <div class="modal-body">
          <div class="form-group">
            <label>Title<span style="color: red;">*</span>  </label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Author<span style="color: red;">*</span>  </label>
            <input type="text" name="author" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Year Published<span style="color: red;">*</span></label>
            <input type="number" name="yearPublished" class="form-control" required>
          </div>
          <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" class="form-control">
          </div>
          <div class="form-group">
            <label>Category<span style="color: red;">*</span></label>
            <input type="text" name="category" class="form-control">
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Cover Image URL</label>
            <input type="text" name="coverImage" class="form-control">
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="addBookButton" class="btn btn-primary">Save Book</button>
        </div>
      </form>
      
    </div>
  </div>
</div>


<?php
include(__DIR__ . './includes/footer.php');
?>