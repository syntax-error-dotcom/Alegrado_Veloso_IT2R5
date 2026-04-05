<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>


<h1>Users PAGE</h1>

<?php
include(__DIR__ . './includes/footer.php');
?>