<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include(__DIR__ . './includes/header.php');
include(__DIR__ . './includes/sidebar.php');
include(__DIR__ . './includes/topbar.php');
?>

<h1>Inventory PAGE</h1>

<?php
include(__DIR__ . './includes/footer.php');
?>