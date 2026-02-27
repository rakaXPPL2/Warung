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
// optional customization
$flavor = isset($data['flavor']) ? trim($data['flavor']) : null;
$spicy_level = isset($data['spicy_level']) ? (int)$data['spicy_level'] : null;

// Initialize keranjang if not exists
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$nama = $data['nama'];
$harga = (int)$data['harga'];
$kantin_id = (int)$data['kantin_id'];

// Cek konsistensi kantin: Jika item baru dari kantin berbeda, reset keranjang
if (!empty($_SESSION['keranjang'])) {
    $first_item = reset($_SESSION['keranjang']);
    if (isset($first_item['kantin_id']) && $first_item['kantin_id'] !== $kantin_id) {
        $_SESSION['keranjang'] = []; // Reset keranjang
    }
}

// Track kantin_id aktif
$_SESSION['kantin_id'] = $kantin_id;

// Cek apakah sudah ada di keranjang
$found = false;
foreach ($_SESSION['keranjang'] as &$item) {
    if ($item['nama'] === $nama && $item['kantin_id'] === $kantin_id
        && ($item['flavor'] ?? null) === $flavor
        && ($item['spicy_level'] ?? null) === $spicy_level) {
        $item['jumlah'] += 1;
        $found = true;
        break;
    }
}

// Jika belum ada, tambah item baru
if (!$found) {
    $item = [
        'nama' => $nama,
        'harga' => $harga,
        'kantin_id' => $kantin_id,
        'jumlah' => 1
    ];
    if ($flavor !== null) $item['flavor'] = $flavor;
    if ($spicy_level !== null) $item['spicy_level'] = $spicy_level;
    $_SESSION['keranjang'][] = $item;
}

http_response_code(200);
echo json_encode(['success' => true, 'keranjang_count' => count($_SESSION['keranjang'])]);
?>
