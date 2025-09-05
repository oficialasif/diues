<?php
/**
 * Test File Upload
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode([
        'method' => 'POST',
        'files' => $_FILES,
        'post' => $_POST,
        'file_count' => count($_FILES),
        'has_image' => isset($_FILES['image']),
        'image_error' => $_FILES['image']['error'] ?? 'No image field',
        'image_size' => $_FILES['image']['size'] ?? 'No image field',
        'image_name' => $_FILES['image']['name'] ?? 'No image field'
    ]);
} else {
    echo json_encode([
        'method' => 'GET',
        'message' => 'Send POST request with file upload'
    ]);
}
?>
