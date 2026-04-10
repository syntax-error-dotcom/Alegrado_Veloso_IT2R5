<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');

// Dummy data for books
$recommendedBooks = [
    [
        'title' => 'The Great Gatsby',
        'description' => 'A classic novel about the American Dream.',
        'image' => 'https://via.placeholder.com/150x200/4e73df/ffffff?text=Book+1'
    ],
    [
        'title' => 'To Kill a Mockingbird',
        'description' => 'A story of racial injustice and childhood.',
        'image' => 'https://via.placeholder.com/150x200/1cc88a/ffffff?text=Book+2'
    ],
    [
        'title' => '1984',
        'description' => 'Dystopian novel about totalitarianism.',
        'image' => 'https://via.placeholder.com/150x200/36b9cc/ffffff?text=Book+3'
    ],
    [
        'title' => 'Pride and Prejudice',
        'description' => 'A romantic novel by Jane Austen.',
        'image' => 'https://via.placeholder.com/150x200/f6c23e/ffffff?text=Book+4'
    ],
  
];

$NewBooks = [
    [
        'title' => 'Harry Potter and the Sorcerer\'s Stone',
        'description' => 'The start of a magical adventure.',
        'image' => 'https://via.placeholder.com/150x200/4e73df/ffffff?text=Book+6'
    ],
    [
        'title' => 'The Hobbit',
        'description' => 'A fantasy adventure by J.R.R. Tolkien.',
        'image' => 'https://via.placeholder.com/150x200/1cc88a/ffffff?text=Book+7'
    ],
    [
        'title' => 'Dune',
        'description' => 'Science fiction epic.',
        'image' => 'https://via.placeholder.com/150x200/36b9cc/ffffff?text=Book+8'
    ],
    [
        'title' => 'The Lord of the Rings',
        'description' => 'Epic fantasy trilogy.',
        'image' => 'https://via.placeholder.com/150x200/f6c23e/ffffff?text=Book+9'
    ],

];
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
            <?php foreach ($popularBooks as $book): ?>
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