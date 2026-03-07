<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// get all orders (limit 100)
$stmt = $conn->prepare("SELECT * FROM pesanan ORDER BY created_at DESC LIMIT 100");
$stmt->execute();
$pesanan_list = $stmt->get_result();
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Pesanan - Kasir</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    .history-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .history-card:hover {
      transform: translateY(-2px);
    }
    .nomor-antrian {
      font-size: 32px;
      font-weight: 700;
      color: #28a745;
      margin-bottom: 15px;
    }
    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .detail-row:last-child {
      border-bottom: none;
    }
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-proses { background: #d1ecf1; color: #0c5460; }
    .status-selesai { background: #d4edda; color: #155724; }
    .status-diambil { background: #e2e3e5; color: #383d41; }
    .items-section {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 15px;
      margin-top: 15px;
    }
    .item-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 0;
      font-size: 14px;
    }
    .item-row:not(:last-child) {
      border-bottom: 1px solid #e9ecef;
    }
    .total-price {
      font-size: 18px;
      font-weight: 700;
      color: #28a745;
    }
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin: 20px 0;
    }
    .empty-state i {
      font-size: 4rem;
      color: #6c757d;
      margin-bottom: 20px;
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
    <div class="container-fluid">
      <a class="navbar-brand text-white fw-bold" href="#">
        <i class="fas fa-history me-2"></i>Riwayat Pesanan
      </a>
      <div class="d-flex align-items-center">
        <span class="text-white me-3">Halo, <?= htmlspecialchars($username) ?>!</span>
        <a href="../logout.php" class="btn btn-outline-light btn-sm">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4">
    <div class="row">
      <div class="col-12">
        <h2 class="mb-4 mt-4 text-success fw-bold">
          <i class="fas fa-clock-rotate-left me-2"></i>Riwayat Semua Pesanan
        </h2>

        <?php if ($pesanan_list->num_rows === 0): ?>
          <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h4 class="text-muted">Belum ada riwayat pesanan</h4>
            <p class="text-muted">Riwayat pesanan akan muncul di sini setelah ada transaksi.</p>
          </div>
        <?php else: ?>
          <?php while ($p = $pesanan_list->fetch_assoc()): ?>
            <div class="history-card">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="nomor-antrian">#<?= htmlspecialchars($p['nomor_antrian']) ?></div>
                <span class="status-badge status-<?= htmlspecialchars($p['status']) ?>">
                  <?php
                  $status_text = [
                    'pending' => '⏳ Menunggu',
                    'proses' => '👨‍🍳 Diproses',
                    'selesai' => '✅ Siap Ambil',
                    'diambil' => '🎉 Selesai'
                  ];
                  echo $status_text[$p['status']] ?? $p['status'];
                  ?>
                </span>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="detail-row">
                    <span><i class="fas fa-user me-2 text-muted"></i>Pembeli:</span>
                    <strong class="text-dark"><?= htmlspecialchars($p['pembeli_nama']) ?></strong>
                  </div>
                  <div class="detail-row">
                    <span><i class="fas fa-store me-2 text-muted"></i>Kantin:</span>
                    <strong class="text-dark">Kantin <?= $p['kantin_id'] ?></strong>
                  </div>
                  <div class="detail-row">
                    <span><i class="fas fa-calendar me-2 text-muted"></i>Waktu Pesan:</span>
                    <strong class="text-dark"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></strong>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="detail-row">
                    <span><i class="fas fa-credit-card me-2 text-muted"></i>Metode:</span>
                    <span class="badge bg-<?= $p['metode_pembayaran'] === 'cod' ? 'warning' : 'success' ?>">
                      <?= $p['metode_pembayaran'] === 'cod' ? '💵 COD' : '✅ Online' ?>
                    </span>
                  </div>
                  <div class="detail-row">
                    <span><i class="fas fa-money-bill-wave me-2 text-muted"></i>Total:</span>
                    <span class="total-price">Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></span>
                  </div>
                </div>
              </div>

              <div class="items-section">
                <h6 class="mb-3 text-success">
                  <i class="fas fa-utensils me-2"></i>Detail Item Pesanan
                </h6>
                <?php
                $stmt2 = $conn->prepare("SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
                $stmt2->bind_param("i", $p['id']);
                $stmt2->execute();
                $items = $stmt2->get_result();
                if ($items->num_rows > 0):
                  while ($it = $items->fetch_assoc()):
                ?>
                  <div class="item-row">
                    <div>
                      <strong class="text-dark"><?= htmlspecialchars($it['nama_menu']) ?></strong>
                      <span class="text-muted ms-2">x<?= $it['jumlah'] ?></span>
                    </div>
                    <span class="text-success fw-semibold">
                      Rp <?= number_format($it['harga'] * $it['jumlah'], 0, ',', '.') ?>
                    </span>
                  </div>
                <?php
                  endwhile;
                else:
                ?>
                  <p class="text-muted mb-0">Tidak ada detail item</p>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Footer Navigation -->
  <div class="footer-nav">
    <div class="container-fluid text-center">
      <a href="dashboard.php">
        <i class="fas fa-home me-1"></i>Beranda
      </a>
      <a href="history.php" class="active">
        <i class="fas fa-history me-1"></i>Riwayat
      </a>
      <a href="kantin.php">
        <i class="fas fa-store me-1"></i>Kantin
      </a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>