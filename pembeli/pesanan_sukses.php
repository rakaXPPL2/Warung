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
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pesanan Berhasil</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      padding-bottom: 80px;
    }
    .navbar-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .success-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 40px;
      text-align: center;
      margin: 20px;
      transition: transform 0.3s ease;
    }
    .success-card:hover {
      transform: translateY(-5px);
    }
    .success-icon {
      font-size: 4rem;
      color: #28a745;
      margin-bottom: 20px;
    }
    .success-card h1 {
      color: #28a745;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .success-card p {
      color: #6c757d;
      font-size: 18px;
      margin-bottom: 0;
    }
    .queue-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 30px;
      text-align: center;
      margin: 20px;
      border-left: 4px solid #28a745;
      transition: transform 0.3s ease;
    }
    .queue-card:hover {
      transform: translateY(-5px);
    }
    .queue-label {
      font-size: 14px;
      color: #6c757d;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 10px;
    }
    .queue-number {
      font-size: 48px;
      font-weight: 700;
      color: #28a745;
      margin-bottom: 10px;
    }
    .queue-hint {
      color: #6c757d;
      font-size: 14px;
    }
    .info-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      margin: 20px;
      transition: transform 0.3s ease;
    }
    .info-card:hover {
      transform: translateY(-5px);
    }
    .info-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .info-item:last-child {
      border-bottom: none;
    }
    .info-label {
      font-weight: 600;
      color: #495057;
    }
    .info-value {
      font-weight: 500;
      color: #28a745;
    }
    .status-pending {
      color: #ffc107;
    }
    .instructions-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      margin: 20px;
      transition: transform 0.3s ease;
    }
    .instructions-card:hover {
      transform: translateY(-5px);
    }
    .instructions-card h3 {
      color: #28a745;
      margin-bottom: 20px;
    }
    .instructions-list {
      list-style: none;
      padding: 0;
    }
    .instructions-list li {
      padding: 8px 0;
      padding-left: 25px;
      position: relative;
      color: #495057;
    }
    .instructions-list li:before {
      content: "✓";
      color: #28a745;
      font-weight: bold;
      position: absolute;
      left: 0;
    }
    .instructions-list li strong {
      color: #dc3545;
    }
    .button-group {
      display: flex;
      gap: 15px;
      margin: 20px;
      justify-content: center;
    }
    .btn-primary-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      border-radius: 25px;
      padding: 12px 24px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .btn-primary-custom:hover {
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
      transform: translateY(-2px);
      color: white;
      text-decoration: none;
    }
    .btn-secondary-custom {
      background: #6c757d;
      color: white;
      border: none;
      border-radius: 25px;
      padding: 12px 24px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .btn-secondary-custom:hover {
      background: #5a6268;
      transform: translateY(-2px);
      color: white;
      text-decoration: none;
    }
    .footer-nav {
      background: white;
      border-top: 1px solid #e9ecef;
      padding: 15px 0;
      position: fixed;
      bottom: 0;
      width: 100%;
      z-index: 1000;
    }
    .footer-nav a {
      text-decoration: none;
      color: #6c757d;
      font-weight: 500;
      padding: 10px 20px;
      border-radius: 25px;
      transition: all 0.3s ease;
      margin: 0 5px;
    }
    .footer-nav a.active {
      background: #28a745;
      color: white;
    }
    .footer-nav a:hover {
      background: #28a745;
      color: white;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="#">
      <i class="fas fa-check-circle me-2"></i>Pesanan Berhasil
    </a>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">
        <i class="fas fa-user-circle me-1"></i>Halo, <?= htmlspecialchars($username) ?>
      </span>
      <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<div class="container my-4">
  <!-- Success Message -->
  <div class="success-card">
    <div class="success-icon">✅</div>
    <h1>Pesanan Diterima!</h1>
    <p>Pesanan Anda sedang diproses penjual</p>
  </div>

  <!-- Queue Number -->
  <div class="queue-card">
    <div class="queue-label">Nomor Antrian Anda</div>
    <div class="queue-number"><?= $nomor ?></div>
    <div class="queue-hint">Simpan nomor ini untuk mengambil pesanan</div>
  </div>

  <!-- Order Info -->
  <div class="info-card">
    <div class="info-item">
      <span class="info-label">Metode Pembayaran</span>
      <span class="info-value">
        <?= $metode === 'cod' ? '💵 Bayar di Tempat (COD)' : '🏧 Transfer Bank / E-Wallet' ?>
      </span>
    </div>
    <div class="info-item">
      <span class="info-label">Status Pesanan</span>
      <span class="info-value status-pending">
        <i class="fas fa-clock me-1"></i>⏳ Menunggu Konfirmasi Penjual
      </span>
    </div>
  </div>

  <!-- Instructions -->
  <div class="instructions-card">
    <h3>
      <i class="fas fa-list-check me-2"></i>Petunjuk Selanjutnya
    </h3>
    <ul class="instructions-list">
      <li>Tunggu penjual memproses pesanan Anda</li>
      <li>Cek status pesanan di menu "Status Pesanan"</li>
      <li>Ketika pesanan selesai, kasir akan memberitahu Anda</li>
      <li>Tunjukkan nomor antrian ke kasir untuk mengambil pesanan</li>
      <?php if ($metode === 'cod'): ?>
        <li><strong>Ingat untuk membawa uang tunai untuk membayar</strong></li>
      <?php endif; ?>
    </ul>
  </div>

  <!-- Action Buttons -->
  <div class="button-group">
    <a href="status_pesanan.php" class="btn-primary-custom">
      <i class="fas fa-list me-2"></i>Lihat Status Pesanan
    </a>
    <a href="dashboard.php" class="btn-secondary-custom">
      <i class="fas fa-home me-2"></i>Kembali ke Beranda
    </a>
  </div>
</div>

<!-- Footer Navigation -->
<div class="footer-nav">
  <div class="container text-center">
    <a href="dashboard.php">
      <i class="fas fa-home me-1"></i>Beranda
    </a>
    <a href="status_pesanan.php" class="active">
      <i class="fas fa-list me-1"></i>Status Pesanan
    </a>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>
