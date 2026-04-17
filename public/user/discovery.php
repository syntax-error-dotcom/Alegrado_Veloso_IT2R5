<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');

include(__DIR__ . '/../../app/config/config.php');

// Get 4 random recommended books
$sqlRecommended = "SELECT uuid, title, author, description FROM books ORDER BY RAND() LIMIT 4";
$resultRecommended = $conn->query($sqlRecommended);
$recommendedBooks = [];
if ($resultRecommended && $resultRecommended->num_rows > 0) {
    while ($row = $resultRecommended->fetch_assoc()) {
        $row['image'] = '../api/get-book-image.php?uuid=' . $row['uuid'];
        $recommendedBooks[] = $row;
    }
}   

// Get 4 most recent books
$sqlNew = "SELECT uuid, title, author, description FROM books ORDER BY book_id DESC LIMIT 4";
$resultNew = $conn->query($sqlNew);
$newBooks = [];
if ($resultNew && $resultNew->num_rows > 0) {
    while ($row = $resultNew->fetch_assoc()) {
        $row['image'] = '../api/get-book-image.php?uuid=' . $row['uuid'];
        $newBooks[] = $row;
    }
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Discovery Page</h1>
    </div>

    <!-- Recommended Books Section -->
    <div class="section mb-5">
        <h2 class="section-title">Recommended Books</h2>
        <div class="books-grid">
            <?php foreach ($recommendedBooks as $book): ?>
                <div class="book-card" data-uuid="<?php echo $book['uuid']; ?>">
                    <img src="<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>">
                    <h3><?php echo $book['title']; ?></h3>
                    <p class="book-author">Author: <?php echo $book['author']; ?></p>
                    <p><?php echo $book['description']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-outline-primary see-more-btn">See More</button>
    </div>

    <!-- Popular Books Section -->
    <div class="section mb-5">
        <h2 class="section-title">New Books</h2>
        <div class="books-grid">
            <?php foreach ($newBooks as $book): ?>
                <div class="book-card" data-uuid="<?php echo $book['uuid']; ?>">
                    <img src="<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>">
                    <h3><?php echo $book['title']; ?></h3>
                    <p class="book-author">Author: <?php echo $book['author']; ?></p>
                    <p><?php echo $book['description']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-outline-primary see-more-btn">See More</button>
    </div>  
</div>
<!-- /.container-fluid -->

<!-- Book Details Modal -->
<div class="modal fade" id="bookDetailsModal" tabindex="-1" role="dialog" aria-labelledby="bookDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookDetailsModalLabel">Book Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="bookImage" src="" alt="Book Cover" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                    <div class="col-md-8">
                        <h3 id="bookTitle"></h3>
                        <p><strong>Author:</strong> <span id="bookAuthor"></span></p>
                        <p><strong>Publisher:</strong> <span id="bookPublisher"></span></p>
                        <p><strong>Year Published:</strong> <span id="bookYear"></span></p>
                        <p><strong>Category:</strong> <span id="bookCategory"></span></p>
                        <p><strong>Availability:</strong> <span id="bookAvailability"></span></p>
                        <p><strong>Description:</strong></p>
                        <p id="bookDescription"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="bookNowBtn">Book Now</button>
            </div>
        </div>
    </div>
</div>

<script>
// Handle book card clicks
document.addEventListener('DOMContentLoaded', function() {
    const bookCards = document.querySelectorAll('.book-card');
    bookCards.forEach(card => {
        card.addEventListener('click', function() {
            const uuid = this.getAttribute('data-uuid');
            showBookDetails(uuid);
        });
    });
});

function showBookDetails(uuid) {
    // Fetch book details
    fetch('../api/get-book-details.php?uuid=' + encodeURIComponent(uuid))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Book not found');
                return;
            }
            
            // Populate modal
            document.getElementById('bookImage').src = '../api/get-book-image.php?uuid=' + data.uuid;
            document.getElementById('bookTitle').textContent = data.title;
            document.getElementById('bookAuthor').textContent = data.author;
            document.getElementById('bookPublisher').textContent = data.publisher;
            document.getElementById('bookYear').textContent = data.yearPublished;
            document.getElementById('bookCategory').textContent = data.category_name || 'N/A';
            document.getElementById('bookAvailability').textContent = data.availability;
            document.getElementById('bookDescription').textContent = data.description;
            
            // Store uuid for booking
            document.getElementById('bookNowBtn').setAttribute('data-uuid', data.uuid);
            
            // Show modal
            $('#bookDetailsModal').modal('show');
        })
        .catch(error => {
            console.error('Error fetching book details:', error);
            alert('Error loading book details');
        });
}

// Handle Book Now button
document.getElementById('bookNowBtn').addEventListener('click', function() {
    const uuid = this.getAttribute('data-uuid');
    bookNow(uuid);
});

function bookNow(uuid) {
    // Send POST request to user controller
    fetch('controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'bookNow=true&uuid=' + encodeURIComponent(uuid)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Book booked successfully!');
            $('#bookDetailsModal').modal('hide');
        } else {
            alert('Error booking book: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error booking book:', error);
        alert('Error booking book');
    });
}

// Handle top bar search functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle desktop search form
    const topbarSearchForm = document.getElementById('topbarSearchForm');
    const topbarSearchInput = document.getElementById('topbarSearchInput');
    
    if (topbarSearchForm) {
        topbarSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = topbarSearchInput.value.trim();
            if (query) {
                performSearch(query);
            }
        });
    }
    
    // Handle mobile search form
    const mobileSearchForm = document.getElementById('mobileSearchForm');
    const mobileSearchInput = document.getElementById('mobileSearchInput');
    
    if (mobileSearchForm) {
        mobileSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = mobileSearchInput.value.trim();
            if (query) {
                performSearch(query);
            }
        });
    }
    
    // Function to perform search
    function performSearch(query) {
        // Show loading state
        showSearchResults([], true);
        
        // Make API call
        fetch('../api/search-book.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                showSearchResults(data, false);
            })
            .catch(error => {
                console.error('Search error:', error);
                showSearchResults([], false, 'Error occurred while searching');
            });
    }
    
    // Function to show search results
    function showSearchResults(books, isLoading, error = null) {
        // Remove existing search modal if any
        const existingModal = document.getElementById('searchResultsModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create modal
        const modal = document.createElement('div');
        modal.id = 'searchResultsModal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Search Results</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        ${isLoading ? '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</div>' : 
                          error ? `<div class="alert alert-danger">${error}</div>` :
                          books.length === 0 ? '<div class="text-center">No books found matching your search.</div>' :
                          `<div class="books-grid">${books.map(book => `
                            <div class="book-card" data-uuid="${book.uuid}">
                                <img src="../api/get-book-image.php?uuid=${book.uuid}" alt="${book.title}" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="width: 100%; height: 280px; background-color: #f5f5f5; border-radius: 5px; margin-bottom: 15px; display: none; align-items: center; justify-content: center; color: #999; font-size: 14px; text-align: center; padding: 10px; border: 1px solid #e0e0e0;">
                                    <i class="fas fa-image" style="font-size: 40px; margin-bottom: 10px; width: 100%; color: #ccc;"></i>
                                </div>
                                <h3>${book.title}</h3>
                                <p><strong>Author:</strong> ${book.author}</p>
                            </div>
                          `).join('')}</div>`
                        }
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        $(modal).modal('show');
        
        // Add click handlers to search result book cards
        const searchBookCards = modal.querySelectorAll('.book-card');
        searchBookCards.forEach(card => {
            card.addEventListener('click', function() {
                const uuid = this.getAttribute('data-uuid');
                $('#searchResultsModal').modal('hide');
                showBookDetails(uuid);
            });
        });
    }
    
    // Handle broken images in search results
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG' && e.target.closest('#searchResultsModal')) {
            e.target.style.display = 'none';
            const placeholder = e.target.nextElementSibling;
            if (placeholder) {
                placeholder.style.display = 'flex';
            }
        }
    }, true);
});
</script>

<?php
include(__DIR__ . '/includes/footer.php');
?>