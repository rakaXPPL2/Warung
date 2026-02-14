<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

$nomor = isset($_GET['nomor']) ? htmlspecialchars($_GET['nomor']) : 'ERROR';
$metode = isset($_GET['metode']) ? htmlspecialchars($_GET['metode']) : 'cod';
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pesanan Berhasil</title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/pesanan_sukses.css">
</head>
<body>

<div class="header">
  <h1>Pesanan Berhasil</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="success-container">
  
  <div class="success-box">
    <div class="check-icon">✅</div>
    <h1>Pesanan Diterima!</h1>
    <p>Pesanan Anda sedang diproses</p>
  </div>

  <div class="nomor-antrian-box">
    <div class="antrian-label">NOMOR ANTRIAN ANDA</div>
    <div class="nomor-display"><?= $nomor ?></div>
    <div class="antrian-hint">Simpan nomor ini untuk mengambil pesanan</div>
  </div>

  <div class="metode-info">
    <div class="info-item">
      <div class="info-label">Metode Pembayaran</div>
      <div class="info-value">
        <?= $metode === 'cod' ? '💵 Bayar di Tempat (COD)' : '🏧 Transfer Bank / E-Wallet' ?>
      </div>
    </div>
    <div class="info-item">
      <div class="info-label">Status Pesanan</div>
      <div class="info-value" style="color: #f59e0b;">⏳ Menunggu Konfirmasi Penjual</div>
    </div>
  </div>

  <div class="instructions">
    <h3>📋 Petunjuk Selanjutnya</h3>
    <ul>
      <li>Tunggu penjual memproses pesanan Anda</li>
      <li>Cek status pesanan di menu "Status Pesanan"</li>
      <li>Ketika pesanan selesai, kasir akan memberitahu Anda</li>
      <li>Tunjukkan nomor antrian ke kasir untuk mengambil pesanan</li>
      <?php if ($metode === 'cod'): ?>
        <li><strong>Ingat untuk membawa uang untuk membayar</strong></li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="btn-group">
    <button class="btn-main" onclick="location.href='status_pesanan.php'">Lihat Status Pesanan</button>
    <button class="btn-secondary" onclick="location.href='dashboard.php'">Kembali ke Beranda</button>
  </div>

</div>

<div class="nav">
  <a href="dashboard.php">Beranda</a>
  <a href="status_pesanan.php">Status Pesanan</a>
</div>

</body>
</html>
