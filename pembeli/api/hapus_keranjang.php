<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['index'])) {
    http_response_code(400);
    exit;
}

$index = (int)$data['index'];

if (isset($_SESSION['keranjang'][$index])) {
    unset($_SESSION['keranjang'][$index]);
    $_SESSION['keranjang'] = array_values($_SESSION['keranjang']); // Re-index
}

http_response_code(200);
echo json_encode(['success' => true]);
?>
