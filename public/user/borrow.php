<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/user.php');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');


// Decide whether to use uuid or book_id
if (isset($_GET['uuid'])) {
    $uuid = $_GET['uuid'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE uuid = ?");
    $stmt->bind_param("s", $uuid);
} elseif (isset($_GET['book_id'])) {
    $bookId = (int)$_GET['book_id'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $bookId);
}

$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
?>






<div class="container my-5">
    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <h2 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h2>
            <p class="card-text"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <p class="card-text"><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?></p>
            <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($book['description']); ?></p>

            <!-- Borrow button -->
            <form method="POST" action="../../app/controllers/userController.php" class="mt-3">
                <input type="hidden" name="bookId" value="<?php echo (int)$book['book_id']; ?>">
                <button type="submit" name="borrowBookButton" class="btn btn-primary">Confirm Borrow</button>
            </form>
        </div>
    </div>
</div>


<?php
include(__DIR__ . '/includes/footer.php');
?>
