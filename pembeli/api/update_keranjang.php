<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['index']) || !isset($data['jumlah'])) {
    http_response_code(400);
    exit;
}

$index = (int)$data['index'];
$jumlah = (int)$data['jumlah'];

if ($jumlah < 1) $jumlah = 1;

if (isset($_SESSION['keranjang'][$index])) {
    $_SESSION['keranjang'][$index]['jumlah'] = $jumlah;
}

http_response_code(200);
echo json_encode(['success' => true]);
?>
