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
        $row['image'] = '/api/get-book-image.php?uuid=' . $row['uuid'];
        $recommendedBooks[] = $row;
    }
}

// Get 4 most recent books
$sqlNew = "SELECT uuid, title, author, description FROM books ORDER BY book_id DESC LIMIT 4";
$resultNew = $conn->query($sqlNew);
$newBooks = [];
if ($resultNew && $resultNew->num_rows > 0) {
    while ($row = $resultNew->fetch_assoc()) {
        $row['image'] = '/api/get-book-image.php?uuid=' . $row['uuid'];
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