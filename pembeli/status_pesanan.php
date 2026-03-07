<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Get pembeli ID
$stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ? AND role = 'pembeli'");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$pembeli = $result->fetch_assoc();
$pembeli_id = $pembeli['id'] ?? 0;

// Get pesanan pembeli
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE pembeli_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $pembeli_id);
$stmt->execute();
$pesanan_list = $stmt->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Status Pesanan</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .navbar-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .order-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .order-card:hover {
      transform: translateY(-5px);
    }
    .order-header {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .order-number {
      font-size: 24px;
      font-weight: 700;
    }
    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-proses { background: #d1ecf1; color: #0c5460; }
    .status-selesai { background: #d4edda; color: #155724; }
    .status-diambil { background: #e2e3e5; color: #383d41; }
    .order-body {
      padding: 20px;
    }
    .detail-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      padding: 10px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .detail-row:last-child {
      border-bottom: none;
    }
    .label {
      font-weight: 600;
      color: #6c757d;
    }
    .value {
      font-weight: 500;
      color: #495057;
    }
    .items-list {
      margin: 20px 0;
    }
    .item {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .total-harga {
      font-size: 18px;
      font-weight: 700;
      color: #28a745;
      text-align: right;
      margin: 20px 0;
    }
    .alert-custom {
      border-radius: 10px;
      margin-top: 15px;
    }
    .no-orders {
      text-align: center;
      padding: 50px;
      color: #6c757d;
    }
    .footer-nav {
      background: white;
      border-top: 1px solid #e9ecef;
      padding: 15px 0;
      margin-top: 50px;
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
      <i class="fas fa-store me-2"></i>Kantin Sekolah
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
  <h2 class="mb-4 text-center">
    <i class="fas fa-list me-2"></i>Status Pesanan
  </h2>
  
  <?php if ($pesanan_list->num_rows === 0): ?>
    <div class="no-orders">
      <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
      <h4>Anda belum ada pesanan</h4>
      <button onclick="location.href='dashboard.php'" class="btn btn-success btn-lg rounded-pill mt-3">
        <i class="fas fa-plus me-2"></i>Mulai Pesan Sekarang
      </button>
    </div>
  <?php else: ?>
    <div class="row">
      <?php while ($pesanan = $pesanan_list->fetch_assoc()): ?>
        <div class="col-lg-6 col-md-12 mb-4">
          <div class="order-card">
            <div class="order-header">
              <div>
                <div style="font-size: 14px; opacity: 0.8;">Nomor Antrian</div>
                <div class="order-number">#<?= htmlspecialchars($pesanan['nomor_antrian']) ?></div>
              </div>
              <span class="status-badge status-<?= $pesanan['status'] ?>">
                <?php
                $status_text = [
                  'pending' => '⏳ Menunggu',
                  'proses' => '👨‍🍳 Diproses',
                  'selesai' => '✅ Siap Diambil',
                  'diambil' => '🎉 Selesai'
                ];
                echo $status_text[$pesanan['status']];
                ?>
              </span>
            </div>
            <div class="order-body">
              <?php if (!empty($pesanan['catatan'])): ?>
                <div class="detail-row">
                  <span class="label">Catatan Anda:</span>
                  <span class="value"><?= htmlspecialchars($pesanan['catatan']) ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($pesanan['seller_catatan'])): ?>
                <div class="detail-row">
                  <span class="label">Catatan Penjual:</span>
                  <span class="value"><?= htmlspecialchars($pesanan['seller_catatan']) ?></span>
                </div>
              <?php endif; ?>
              <div class="detail-row">
                <span class="label">Metode Pembayaran:</span>
                <span class="value"><?= $pesanan['metode_pembayaran'] === 'cod' ? '💵 Bayar di Tempat' : '🏧 Transfer' ?></span>
              </div>
              <div class="detail-row">
                <span class="label">Waktu Pesanan:</span>
                <span class="value"><?= date('d/m/Y H:i', strtotime($pesanan['created_at'])) ?></span>
              </div>

              <div class="items-list">
                <strong style="color: #071332;">Detail Menu:</strong>
                <?php
                // Get items untuk pesanan ini
                $stmt2 = $conn->prepare("SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
                $stmt2->bind_param("i", $pesanan['id']);
                $stmt2->execute();
                $items = $stmt2->get_result();
                while ($item = $items->fetch_assoc()):
                ?>
                  <div class="item">
                    <span><?= htmlspecialchars($item['nama_menu']) ?> x<?= $item['jumlah'] ?></span>
                    <span>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
                  </div>
                <?php endwhile; ?>
              </div>

              <div class="total-harga">Total: Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></div>

              <?php if ($pesanan['status'] === 'selesai'): ?>
                <div class="alert alert-success alert-custom">
                  <i class="fas fa-check-circle me-2"></i>Pesanan Anda siap! Tunjukkan nomor antrian ke kasir
                </div>
              <?php elseif ($pesanan['status'] === 'diambil'): ?>
                <div class="alert alert-info alert-custom">
                  <i class="fas fa-star me-2"></i>Pesanan telah diambil, terima kasih!
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
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
