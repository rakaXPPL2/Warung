<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$username = $_SESSION['username'];
$keranjang = isset($_SESSION['keranjang']) ? $_SESSION['keranjang'] : [];
$kantin_id = isset($_SESSION['kantin_id']) ? $_SESSION['kantin_id'] : 1;

// Cek jika keranjang kosong
if (empty($keranjang)) {
    header('Location: dashboard.php');
    exit;
}

$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['jumlah'];
}

// Generate nomor antrian
$nomor_antrian = strtoupper(substr(uniqid(), -6));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['metode_pembayaran'])) {
    $metode = $_POST['metode_pembayaran'];
    
    // Cek ID pembeli dari db
    $stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ? AND role = 'pembeli'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $pembeli = $result->fetch_assoc();
    $pembeli_id = $pembeli['id'] ?? 1;
    
    // Insert pesanan
    $stmt = $conn->prepare("INSERT INTO pesanan (pembeli_id, pembeli_nama, kantin_id, nomor_antrian, metode_pembayaran, total_harga, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isisis", $pembeli_id, $username, $kantin_id, $nomor_antrian, $metode, $total);
    $stmt->execute();
    $pesanan_id = $conn->insert_id;
    
    // Insert detail pesanan
    foreach ($keranjang as $item) {
        $stmt = $conn->prepare("INSERT INTO pesanan_detail (pesanan_id, nama_menu, harga, jumlah) 
                                VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $pesanan_id, $item['nama'], $item['harga'], $item['jumlah']);
        $stmt->execute();
    }
    
    // Clear keranjang
    unset($_SESSION['keranjang']);
    
    // Redirect ke success page
    header("Location: pesanan_sukses.php?nomor=$nomor_antrian&metode=$metode");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pembayaran</title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/pembayaran.css">
</head>
<body>

<div class="header">
  <h1>Pembayaran Pesanan</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="pembayaran-container">
  
  <div class="ringkasan">
    <h3>Ringkasan Pesanan</h3>
    <?php foreach ($keranjang as $item): ?>
      <div class="ringkasan-item">
        <span><?= htmlspecialchars($item['nama']) ?> x <?= $item['jumlah'] ?></span>
        <span>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
      </div>
    <?php endforeach; ?>
    <div class="ringkasan-total">
      <span>Total Pembayaran:</span>
      <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
    </div>
  </div>

  <div class="metode-container">
    <h3>Pilih Metode Pembayaran</h3>
    
    <form method="post">
      <label class="metode-option">
        <input type="radio" name="metode_pembayaran" value="cod" required>
        <div class="metode-text">
          <div class="metode-title">💵 Bayar di Tempat (COD)</div>
          <div class="metode-desc">Bayar saat mengambil pesanan di kasir</div>
        </div>
      </label>

      <label class="metode-option">
        <input type="radio" name="metode_pembayaran" value="online" required>
        <div class="metode-text">
          <div class="metode-title">🏧 Transfer Bank / E-Wallet</div>
          <div class="metode-desc">Bayar sekarang untuk confirm pesanan</div>
        </div>
      </label>

      <button type="submit" class="btn-bayar">Lanjutkan Pembayaran</button>
      <button type="button" class="btn-kembali" onclick="location.href='keranjang.php'">Kembali ke Keranjang</button>
    </form>
  </div>

</div>

<div class="nav">
  <a href="dashboard.php">Beranda</a>
  <a href="status_pesanan.php">Status Pesanan</a>
</div>

</body>
</html>
