<?php
 date_default_timezone_set('Asia/Manila');
 $servername = "localhost";
 $username = "root";
 $password = "";
 $database = "e_library";

 $conn = new mysqli($servername, $username, $password, $database);

 if ($conn->connect_error) {
     $conn = false; // Set to false instead of dying
 }

?>