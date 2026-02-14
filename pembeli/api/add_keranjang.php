<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nama']) || !isset($data['harga']) || !isset($data['kantin_id'])) {
    http_response_code(400);
    exit;
}

// Initialize keranjang if not exists
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Track kantin_id
$_SESSION['kantin_id'] = $data['kantin_id'];

$nama = $data['nama'];
$harga = (int)$data['harga'];
$kantin_id = (int)$data['kantin_id'];

// Cek apakah sudah ada di keranjang
$found = false;
foreach ($_SESSION['keranjang'] as &$item) {
    if ($item['nama'] === $nama && $item['kantin_id'] === $kantin_id) {
        $item['jumlah'] += 1;
        $found = true;
        break;
    }
}

// Jika belum ada, tambah item baru
if (!$found) {
    $_SESSION['keranjang'][] = [
        'nama' => $nama,
        'harga' => $harga,
        'kantin_id' => $kantin_id,
        'jumlah' => 1
    ];
}

http_response_code(200);
echo json_encode(['success' => true, 'keranjang_count' => count($_SESSION['keranjang'])]);
?>
