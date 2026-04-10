<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');

// Fetch books from API
ob_start();
include(__DIR__ . '/../api/get-discovery-books.php');
$json = ob_get_clean();
$booksData = json_decode($json, true);
$recommendedBooks = $booksData['recommended'] ?? [];
$newBooks = $booksData['new'] ?? [];

// Add image URLs
foreach ($recommendedBooks as &$book) {
    $book['image'] = '/api/get-book-image.php?uuid=' . $book['uuid'];
}
foreach ($newBooks as &$book) {
    $book['image'] = '/api/get-book-image.php?uuid=' . $book['uuid'];
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Discovery Page</h1>
    </div>

    <!-- Search Bar -->
    <div class="search-bar mb-4">
        <input type="text" id="searchInput" placeholder="Search books..." class="form-control">
        <button class="btn btn-primary mt-2" id="searchBtn"><i class="fas fa-search"></i> Search</button>
    </div>

    <!-- Recommended Books Section -->
    <div class="section mb-5">
        <h2 class="section-title">Recommended Books</h2>
        <div class="books-grid">
            <?php foreach ($recommendedBooks as $book): ?>
                <div class="book-card">
                    <img src="<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>">
                    <h3><?php echo $book['title']; ?></h3>
                    <p><?php echo $book['description']; ?></p>
                    <button class="btn btn-success read-btn">Read</button>
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
                <div class="book-card">
                    <img src="<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>">
                    <h3><?php echo $book['title']; ?></h3>
                    <p><?php echo $book['description']; ?></p>
                    <button class="btn btn-success read-btn">Read</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-outline-primary see-more-btn">See More</button>
    </div>

</div>
<!-- /.container-fluid -->

<?php
include(__DIR__ . '/includes/footer.php');
?>