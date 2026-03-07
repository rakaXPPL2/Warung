<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Handle mark as diambil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesanan_id'])) {
    $pesanan_id = (int)$_POST['pesanan_id'];
    
    $stmt = $conn->prepare("UPDATE pesanan SET status = 'diambil' WHERE id = ?");
    $stmt->bind_param("i", $pesanan_id);
    $stmt->execute();
}

// Get pesanan (all statuses except diambil) for cashier overview
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE status <> 'diambil' ORDER BY created_at DESC LIMIT 50");
$stmt->execute();
$pesanan_list = $stmt->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Kasir</title>
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
    .navbar-brand {
      font-weight: 600;
      color: white !important;
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
      text-align: center;
    }
    .order-number {
      font-size: 48px;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .order-status {
      font-size: 18px;
      font-weight: 500;
    }
    .order-body {
      padding: 20px;
    }
    .order-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 20px;
    }
    .detail-item {
      background: #f8f9fa;
      padding: 10px 15px;
      border-radius: 10px;
      border-left: 4px solid #28a745;
    }
    .detail-label {
      font-size: 12px;
      color: #6c757d;
      font-weight: 600;
      text-transform: uppercase;
      margin-bottom: 5px;
    }
    .detail-value {
      font-size: 14px;
      font-weight: 500;
      color: #495057;
    }
    .action-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .btn-custom {
      padding: 10px 20px;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }
    .btn-success-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }
    .btn-success-custom:hover {
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
      transform: translateY(-2px);
    }
    .btn-warning-custom {
      background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
      color: #212529;
    }
    .btn-warning-custom:hover {
      background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
      transform: translateY(-2px);
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
    .footer-nav a:hover {
      background: #28a745;
      color: white;
      transform: translateY(-2px);
    }
    .footer-nav .active {
      background: #28a745;
      color: white;
    }
  </style>
</head>
<body>
<script>
function handlePayment(total) {
  let uang = parseInt(prompt('Masukkan jumlah uang diterima (Rp):','0'));
  if (isNaN(uang)) return;
  let kembalian = uang - total;
  if (kembalian < 0) {
    alert('Uang kurang! Total Rp ' + total.toLocaleString());
  } else {
    alert('Kembalian: Rp ' + kembalian.toLocaleString());
  }
}
</script>

<div class="header">
  <h1>Dashboard Kasir</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>
<div class="container">
    <h2 style="margin-top: 0;">Daftar Pesanan</h2>
  
  <?php if ($pesanan_list->num_rows === 0): ?>
    <div class="kosong">
      <p>Tidak ada pesanan.</p>
    </div>
  <?php else: ?>
    <div class="orders-grid">
    <?php while ($pesanan = $pesanan_list->fetch_assoc()): ?>
      <div class="pesanan-card status-<?= htmlspecialchars($pesanan['status']) ?>">
        <div class="pesanan-info">
          <div class="nomor-antrian"><?= htmlspecialchars($pesanan['nomor_antrian']) ?></div>
          <div class="pesanan-detail">
            <div class="detail-row">
              <span>Pembeli:</span>
              <strong><?= htmlspecialchars($pesanan['pembeli_nama']) ?></strong>
            </div>
            <div class="detail-row">
              <span>Kantin:</span>
              <strong><?= $pesanan['kantin_id'] ?></strong>
            </div>
            <div class="detail-row">
              <span>Total:</span>
              <strong>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
            </div>
            <div class="detail-row">
              <span>Metode:</span>
              <span><?= $pesanan['metode_pembayaran'] === 'cod' ? '💵 COD' : '✅ Online' ?></span>
            </div>
          </div>
        </div>
        
        <div class="action-buttons">
          <div class="status-badge status-<?= $pesanan['status'] ?>">
            <?= $pesanan['status'] === 'selesai' ? '✅ SIAP AMBIL' : '🎉 DIAMBIL' ?>
          </div>
          
          <?php if ($pesanan['status'] === 'selesai'): ?>
            <form method="post" style="margin: 0;">
              <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
              <button type="submit" class="btn btn-ambil">✓ Tandai Diambil</button>
            </form>
            <?php if ($pesanan['metode_pembayaran'] === 'cod'): ?>
              <button class="btn btn-bayar" onclick="handlePayment(<?= $pesanan['total_harga'] ?>)">💵 Bayar</button>
            <?php endif; ?>
          <?php else: ?>

            <div style="text-align: center; padding: 10px 0; color: #571c87;">Sudah Diambil</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

</div>

<footer class="nav">
  <a class="active">🏠 Beranda</a>
  <a href="history.php">📜 Riwayat</a>
  <a href="kantin.php">🏫 Kantin</a>
</footer>

</body>
</html>