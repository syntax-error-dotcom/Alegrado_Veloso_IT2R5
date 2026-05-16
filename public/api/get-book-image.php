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
            // Detect image type from the binary data
            $imageData = $row['coverImage'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);
            
            // Fallback to jpeg if mime type detection fails
            if (!$mimeType || strpos($mimeType, 'image') === false) {
                $mimeType = 'image/jpeg';
            }
            
            // Set appropriate headers for image display
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . strlen($imageData));
            header('Cache-Control: max-age=3600');
            echo $imageData;
        } else {
            // Return a placeholder or 404
            http_response_code(404);
            echo 'Image not found';
        }
    } else {
        http_response_code(404);
        echo 'Book not found';
    }
    
    $stmt->close();
} else {
    http_response_code(400);
    echo 'UUID required';
}
?>
