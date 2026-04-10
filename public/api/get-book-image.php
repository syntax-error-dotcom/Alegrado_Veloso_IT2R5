<?php
include('../../app/config/config.php');

if (isset($_GET['uuid'])) {
    $uuid = $_GET['uuid'];
    
    $sql = "SELECT coverImage FROM books WHERE uuid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (!empty($row['coverImage'])) {
            // Set appropriate headers for image display
            header('Content-Type: image/jpeg');
            header('Content-Length: ' . strlen($row['coverImage']));
            echo $row['coverImage'];
        } else {
            // Return a placeholder or 404
            http_response_code(404);
        }
    } else {
        http_response_code(404);
    }
    
    $stmt->close();
} else {
    http_response_code(400);
}
?>
