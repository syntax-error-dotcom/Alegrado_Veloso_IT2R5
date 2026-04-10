<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include('../../app/config/config.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

// Fetch all books from database with category names (using JOIN)
$books = array();
$sql = "SELECT b.*, c.categoryName 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.category_id 
        ORDER BY b.title ASC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}
else {
    // Fallback query if category table issue
    $sql = "SELECT * FROM books ORDER BY title ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
}

// Fetch all categories for the form dropdown
$categories = [];
$categorySql = "SELECT category_id, categoryName FROM categories ORDER BY categoryName ASC";
$categoryResult = $conn->query($categorySql);

if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    echo "<p style='color:red'>No categories found or query failed. Error: " . $conn->error . "</p>";
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
        <button type="button" class="btn btn-primary btn-warning shadow-sm mb-2 mr-4 text-white" data-toggle="modal" data-target="#updateBookModal">
             <i class="fas fa-edit fa-sm text-white-50"></i> Update Book
        </button>

        <!-- Delete Book (Red) -->
        <button type="button" class="btn btn-primary btn-danger shadow-sm mb-2 mr-4 text-white" data-toggle="modal" data-target="#deleteBookModal">
             <i class="fas fa-trash fa-sm text-white-50"></i> Delete Book
        </button>

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
                            <th>Cover Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Publisher</th>
                            <th>Year Published</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($books) > 0) {
                            foreach($books as $book) {
                                echo "<tr>";
                                // Display cover image from database BLOB
                                echo "<td style='text-align: center; vertical-align: middle;'>";
                                if (!empty($book['coverImage'])) {
                                    echo "<img src='../api/get-book-image.php?uuid=" . htmlspecialchars($book['uuid']) . "' alt='Cover' style='height: 80px; width: auto; border-radius: 4px;' onerror=\"this.src=''; this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\' style=\\'font-size: 24px; color: #ccc;\\'></i><br><small>No Image</small>';\">";
                                } else {
                                    echo "<i class='fas fa-image' style='font-size: 24px; color: #ccc;'></i><br><small>No Image</small>";
                                }
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($book['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($book['author']) . "</td>";
                                echo "<td>" . htmlspecialchars($book['publisher'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($book['yearPublished']) . "</td>";
                                echo "<td>" . htmlspecialchars($book['categoryName'] ?? 'Uncategorized') . "</td>";
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


<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title text-center w-100" id="addBookLabel" >Add New Book</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      
      <form method="POST" action="../../app/controllers/adminController.php" enctype="multipart/form-data" style="margin:20px;">
        <div class="modal-body">

        <div class="form-group row">
          <div class="col-sm-6">
              <label>Title<span style="color: red;">*</span>  </label>
              <input type="text" name="title" class="form-control" required>
          </div>
          <div class="col-sm-6">
              <label>Author<span style="color: red;">*</span>  </label>
              <input type="text" name="author" class="form-control" required>
          </div>
        </div>  
        
        <div class="form-group row">
          <div class="col-sm-6">
              <label>Publisher<span style="color: red;">*</span></label>
              <input type="text" name="publisher" class="form-control" required>
          </div>
          <div class="col-sm-6">
              <label>Year Published<span style="color: red;">*</span></label>
              <input type="date" name="yearPublished" class="form-control" required>
          </div>
        </div>  
        
        <div class="form-group row">
          <div class="col-sm-6">
              <label>Category<span style="color: red;">*</span></label>
            <select name="category" class="form-control" required>
              <option value="">-- Select a Category --</option>
              <?php
              foreach($categories as $cat) {
                echo "<option value='" . htmlspecialchars($cat['category_id']) . "'>" . htmlspecialchars($cat['categoryName']) . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label>Cover Image</label>
            <input type="file" name="coverImage" class="form-control" accept="image/*">
          </div>
        </div>  
    
         
          <div class="form-group">
            <label>Description<span style="color: red;">*</span> </label>
            <textarea name="description" class="form-control" required></textarea>
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



<!-- Update Book Modal -->
<div class="modal fade" id="updateBookModal" tabindex="-1" role="dialog" aria-labelledby="updateBookLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title text-center w-100" id="updateBookLabel">Update Book</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>

      <div class="form-group" style="margin: 20px;">
        <label>Search Book (by Title or Author)<span style="color: red;">*</span></label>
        <input type="search" id="searchBook" class="form-control" placeholder="Search book by title or author..." >
        <div id="searchResults" class="list-group" style="max-height: 300px; overflow-y: auto;"></div>
      </div>

      <form method="POST" action="../../app/controllers/adminController.php" enctype="multipart/form-data" style="margin:20px;">
        <input type="hidden" id="bookUuid" name="bookUuid">
        
        <div class="modal-body">
          <div class="form-group row">
            <div class="col-sm-6">
              <label>Title</label>
              <input type="text" id="updateTitle" name="title" class="form-control" disabled>
            </div>
            <div class="col-sm-6">
              <label>Author</label>
              <input type="text" id="updateAuthor" name="author" class="form-control" disabled>
            </div>
          </div>  
          
          <div class="form-group row">
            <div class="col-sm-6">
              <label>Publisher</label>
              <input type="text" id="updatePublisher" name="publisher" class="form-control" disabled>
            </div>
            <div class="col-sm-6">
              <label>Year Published</label>
              <input type="date" id="updateYearPublished" name="yearPublished" class="form-control" disabled>
            </div>
          </div>  
          
          <div class="form-group row">
            <div class="col-sm-6">
              <label>Category</label>
              <select id="updateCategory" name="category" class="form-control" disabled>
                <option value="">-- Select a Category --</option>
                <?php
                foreach($categories as $cat) {
                  echo "<option value='" . htmlspecialchars($cat['category_id']) . "'>" . htmlspecialchars($cat['categoryName']) . "</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-sm-6">
              <label>Cover Image</label>
              <input type="file" id="updateCoverImage" name="coverImage" class="form-control" accept="image/*" disabled>
            </div>
          </div>  
      
          <div class="form-group">
            <label>Description</label>
            <textarea id="updateDescription" name="description" class="form-control" disabled></textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="updateBookButton" class="btn btn-primary" id="saveChangesBtn" disabled>Save Changes</button>
        </div>
      </form>
      
    </div>
  </div>
</div>


<!-- Delete Book Modal -->
 <div class="modal fade" id="deleteBookModal" tabindex="-1" role="dialog" aria-labelledby="deleteBookLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title text-center w-100" id="deleteBookLabel">Select a Book to Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>

      <h5 class="text-center mt-3" style="color:red;">Please proceed with caution.</h5>

      <!-- Search bar -->
      <div class="form-group mx-3">
        <label>Search a Book to Delete (by Title or Author)</label>
        <input type="search" id="deleteSearchBook" class="form-control" placeholder="Search book by title or author...">
        <div id="deleteSearchResults" class="list-group" style="max-height: 300px; overflow-y: auto;"></div>
      </div>
      
      <h6 class="card-title text-center mb-3" id="deleteConfirmMessage" style="color: black; display:none;"><span style="color: red;">Are you sure you want to delete this book? Changes cannot be undone</span></h6>
      
      <!-- Book info card -->
      <div class="card mx-auto my-3" style="max-width: 80%; display:none;" id="deleteBookInfo">
        <div class="card-body">
          <h6 class="card-title">Book Information:</h6>
          <p class="mb-1"><strong>Title:</strong> <span id="deleteInfoTitle"></span></p>
          <p class="mb-1"><strong>Author:</strong> <span id="deleteInfoAuthor"></span></p>
          <p class="mb-1"><strong>Publisher:</strong> <span id="deleteInfoPublisher"></span></p>
          <p class="mb-1"><strong>Year Published:</strong> <span id="deleteInfoYearPublished"></span></p>
          <p class="mb-1"><strong>Category:</strong> <span id="deleteInfoCategory"></span></p>
        </div>
      </div>

      <form method="POST" action="../../app/controllers/adminController.php">
        <input type="hidden" id="deleteBookUuid" name="bookUuid">
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="deleteBookButton" class="btn btn-danger" id="deleteBtn" disabled>Delete Book</button>
        </div>
      </form>
    </div>
  </div>
</div>








<script>
// Combined Book Management Functionality
document.addEventListener('DOMContentLoaded', function() {
    // ===== UPDATE MODAL =====
    const searchInput = document.getElementById('searchBook');
    const searchResults = document.getElementById('searchResults');
    const bookUuidInput = document.getElementById('bookUuid');
    const saveChangesBtn = document.getElementById('saveChangesBtn');
    
    // Form inputs
    const updateInputs = {
        title: document.getElementById('updateTitle'),
        author: document.getElementById('updateAuthor'),
        publisher: document.getElementById('updatePublisher'),
        yearPublished: document.getElementById('updateYearPublished'),
        category: document.getElementById('updateCategory'),
        description: document.getElementById('updateDescription'),
        coverImage: document.getElementById('updateCoverImage')
    };
    
    // Function to disable all inputs
    function disableInputs() {
        Object.values(updateInputs).forEach(input => {
            input.disabled = true;
        });
        saveChangesBtn.disabled = true;
        bookUuidInput.value = '';
    }
    
    // Function to enable all inputs
    function enableInputs() {
        Object.values(updateInputs).forEach(input => {
            if (input.id !== 'updateCoverImage') {
                input.disabled = false;
            } else {
                input.disabled = false; // cover image can be optional
            }
        });
        saveChangesBtn.disabled = false;
    }
    
    // Function to select a book and load its details
    function selectBook(uuid) {
        console.log('Selecting book with UUID:', uuid);
        fetch(`../api/get-book-details.php?uuid=${encodeURIComponent(uuid)}`)
            .then(response => response.json())
            .then(book => {
                console.log('Book data received:', book);
                // Populate form fields
                bookUuidInput.value = book.uuid;
                updateInputs.title.value = book.title;
                updateInputs.author.value = book.author;
                updateInputs.publisher.value = book.publisher;
                updateInputs.yearPublished.value = book.yearPublished;
                updateInputs.category.value = book.category_id;
                updateInputs.description.value = book.description;
                
                // Clear search results
                searchResults.innerHTML = '';
                searchInput.value = '';
                
                // Enable inputs
                enableInputs();
                console.log('Form populated and enabled');
            })
            .catch(error => console.error('Error fetching book details:', error));
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
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${book.title} : ${book.author}`;
                        item.dataset.uuid = book.uuid;
                        
                        // Proper event binding
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            selectBook(book.uuid);
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
    
    // Disable inputs on modal close
    document.getElementById('updateBookModal').addEventListener('hidden.bs.modal', function() {
        disableInputs();
        searchResults.innerHTML = '';
        searchInput.value = '';
        Object.values(updateInputs).forEach(input => input.value = '');
    });
    
    // Initial state: disable inputs
    disableInputs();

    // ===== DELETE MODAL =====
    const deleteSearchInput = document.getElementById('deleteSearchBook');
    const deleteSearchResults = document.getElementById('deleteSearchResults');
    const deleteBookUuidInput = document.getElementById('deleteBookUuid');
    const deleteBtn = document.getElementById('deleteBtn');
    const deleteBookInfo = document.getElementById('deleteBookInfo');
    const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');
    
    // Book info display spans
    const deleteInfoSpans = {
        title: document.getElementById('deleteInfoTitle'),
        author: document.getElementById('deleteInfoAuthor'),
        publisher: document.getElementById('deleteInfoPublisher'),
        yearPublished: document.getElementById('deleteInfoYearPublished'),
        category: document.getElementById('deleteInfoCategory')
    };
    
    // Fetch category name from category_id
    function getCategoryName(categoryId) {
        const categorySelect = document.getElementById('updateCategory');
        const option = categorySelect.querySelector(`option[value="${categoryId}"]`);
        return option ? option.textContent : 'Unknown';
    }
    
    // Function to select a book for deletion and display its info
    function selectBookForDelete(uuid) {
        console.log('Selecting book for delete with UUID:', uuid);
        fetch(`../api/get-book-details.php?uuid=${encodeURIComponent(uuid)}`)
            .then(response => response.json())
            .then(book => {
                console.log('Book data received for delete:', book);
                // Populate info card
                deleteBookUuidInput.value = book.uuid;
                deleteInfoSpans.title.textContent = book.title;
                deleteInfoSpans.author.textContent = book.author;
                deleteInfoSpans.publisher.textContent = book.publisher || 'N/A';
                deleteInfoSpans.yearPublished.textContent = book.yearPublished;
                deleteInfoSpans.category.textContent = getCategoryName(book.category_id);
                
                // Show book info card and confirmation message
                deleteBookInfo.style.display = 'block';
                deleteConfirmMessage.style.display = 'block';
                
                // Clear search results and input
                deleteSearchResults.innerHTML = '';
                deleteSearchInput.value = '';
                
                // Enable delete button
                deleteBtn.disabled = false;
                console.log('Delete info displayed and button enabled');
            })
            .catch(error => console.error('Error fetching book for delete:', error));
    }
    
    // Delete search functionality
    deleteSearchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            deleteSearchResults.innerHTML = '';
            return;
        }
        
        fetch(`../api/search-book.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                console.log('Delete search results:', data);
                deleteSearchResults.innerHTML = '';
                
                if (data.length > 0) {
                    data.forEach(book => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${book.title} : ${book.author}`;
                        item.dataset.uuid = book.uuid;
                        
                        // Proper event binding
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            selectBookForDelete(book.uuid);
                        });
                        
                        deleteSearchResults.appendChild(item);
                    });
                } else {
                    const noResults = document.createElement('div');
                    noResults.className = 'list-group-item';
                    noResults.textContent = 'No books found';
                    deleteSearchResults.appendChild(noResults);
                }
            })
            .catch(error => console.error('Error in delete search:', error));
    });
    
    // Reset on modal close
    document.getElementById('deleteBookModal').addEventListener('hidden.bs.modal', function() {
        deleteSearchResults.innerHTML = '';
        deleteSearchInput.value = '';
        deleteBookUuidInput.value = '';
        deleteBtn.disabled = true;
        deleteBookInfo.style.display = 'none';
        deleteConfirmMessage.style.display = 'none';
        Object.values(deleteInfoSpans).forEach(span => span.textContent = '');
    });
    
    // Initial state: disable delete button
    deleteBtn.disabled = true;
});
</script>

<?php
include(__DIR__ . '/includes/footer.php');
?>