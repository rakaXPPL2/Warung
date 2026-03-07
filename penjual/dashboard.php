<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'penjual') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$kantin_id = $_SESSION['kantin_id'] ?? null;
if (!$kantin_id) {
    echo "Kantin tidak ditemukan. Silakan login ulang.";
    exit;
}
// ensure seller_catatan column exists for two‑way notes
$checkSeller = $conn->query("SHOW COLUMNS FROM pesanan LIKE 'seller_catatan'");
if ($checkSeller && $checkSeller->num_rows === 0) {
    $conn->query("ALTER TABLE pesanan ADD COLUMN seller_catatan VARCHAR(255) DEFAULT NULL");
}

// update pesanan status or seller note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pesanan_id'], $_POST['status'])) {
        $pid = (int)$_POST['pesanan_id'];
        $st = $_POST['status'];
        $stmt = $conn->prepare("UPDATE pesanan SET status=? WHERE id=? AND kantin_id=?");
        $stmt->bind_param("sii", $st, $pid, $kantin_id);
        $stmt->execute();
    }
    if (isset($_POST['pesanan_id'], $_POST['seller_catatan'])) {
        $pid = (int)$_POST['pesanan_id'];
        $note = trim($_POST['seller_catatan']);
        $stmt2 = $conn->prepare("UPDATE pesanan SET seller_catatan=? WHERE id=? AND kantin_id=?");
        $stmt2->bind_param("sii", $note, $pid, $kantin_id);
        $stmt2->execute();
    }
}

// stats (total orders + income across relevant statuses)
$stmt = $conn->prepare("SELECT COUNT(*) cnt, SUM(total_harga) tot FROM pesanan WHERE kantin_id=? AND status IN ('proses','selesai','diambil')");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$order_count = $stats['cnt'] ?? 0;
$total_income = $stats['tot'] ?? 0;

// today orders
$stmt = $conn->prepare("SELECT COUNT(*) cnt FROM pesanan WHERE kantin_id=? AND DATE(created_at)=CURDATE()");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$today_count = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// week orders (ISO week)
$stmt = $conn->prepare("SELECT COUNT(*) cnt FROM pesanan WHERE kantin_id=? AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$week_count = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// you could extend this query later for per-status or per-day breakdown

// fetch orders
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE kantin_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$orders = $stmt->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Penjual</title>
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
    .header-custom {
      background: white;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .stats-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      text-align: center;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
      border-bottom: 4px solid #28a745;
    }
    .stats-card:hover {
      transform: translateY(-5px);
    }
    .stats-value {
      font-size: 36px;
      font-weight: 700;
      color: #28a745;
      margin-bottom: 10px;
    }
    .stats-label {
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
    }
    .order-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .order-card:hover {
      transform: translateY(-5px);
    }
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    .order-number {
      font-size: 24px;
      font-weight: 700;
      color: #28a745;
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
    .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      font-size: 14px;
      border-bottom: 1px solid #f8f9fa;
    }
    .detail-row:last-child {
      border-bottom: none;
    }
    .items {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
      margin: 15px 0;
      font-size: 14px;
    }
    .item-row {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
    }
    .btn-custom {
      border-radius: 25px;
      padding: 10px 20px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      margin-right: 10px;
    }
    .btn-proses { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
    .btn-selesai { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
    .btn-ambil { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
    .note-field {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 10px;
      margin-bottom: 10px;
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

<!-- Header -->
<div class="header-custom">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="mb-0">Kantin Saya</h1>
        <small class="text-muted">Dashboard Kantin</small>
      </div>
      <div class="d-flex align-items-center">
        <span class="me-3">Halo, <?= htmlspecialchars($username) ?></span>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-pill">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <!-- Statistics -->
  <div class="row mb-4">
    <div class="col-md-4 mb-3">
      <div class="stats-card">
        <i class="fas fa-calendar-day fa-2x text-success mb-2"></i>
        <div class="stats-value"><?= $today_count ?></div>
        <div class="stats-label">Total Pesanan Hari Ini</div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="stats-card">
        <i class="fas fa-calendar-week fa-2x text-success mb-2"></i>
        <div class="stats-value"><?= $week_count ?></div>
        <div class="stats-label">Total Pesanan Minggu Ini</div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="stats-card">
        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
        <div class="stats-value">Rp <?= number_format($total_income, 0, ',', '.') ?></div>
        <div class="stats-label">Total Pendapatan</div>
      </div>
    </div>
  </div>

  <h3 class="mb-4">Daftar Pesanan</h3>
  <?php if ($orders->num_rows === 0): ?>
    <div class="no-orders">
      <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
      <h4>Belum ada pesanan</h4>
      <p>Tunggu pesanan dari pembeli.</p>
    </div>
  <?php else: ?>
    <?php while ($p = $orders->fetch_assoc()): ?>
      <div class="order-card">
        <div class="order-header">
          <div>
            <div class="text-muted small">Nomor Antrian</div>
            <div class="order-number">#<?= htmlspecialchars($p['nomor_antrian']) ?></div>
          </div>
          <span class="status-badge status-<?= $p['status'] ?>">
            <?php
            $m = ['pending' => '⏳ Menunggu', 'proses' => '👨‍🍳 Diproses', 'selesai' => '✅ Siap Diambil', 'diambil' => '🎉 Selesai'];
            echo $m[$p['status']];
            ?>
          </span>
        </div>

        <div class="detail-row">
          <span><strong>Pembeli:</strong></span>
          <span><?= htmlspecialchars($p['pembeli_nama']) ?></span>
        </div>
        <?php if (!empty($p['catatan'])): ?>
          <div class="detail-row">
            <span><strong>Catatan Pembeli:</strong></span>
            <span><?= htmlspecialchars($p['catatan']) ?></span>
          </div>
        <?php endif; ?>
        <?php if (!empty($p['seller_catatan'])): ?>
          <div class="detail-row">
            <span><strong>Catatan Anda:</strong></span>
            <span><?= htmlspecialchars($p['seller_catatan']) ?></span>
          </div>
        <?php endif; ?>

        <form method="post" class="mb-3">
          <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
          <textarea class="note-field" name="seller_catatan" rows="2" placeholder="Tulis catatan untuk pembeli"><?= htmlspecialchars($p['seller_catatan']) ?></textarea>
          <button class="btn btn-success btn-sm rounded-pill" type="submit">Simpan Catatan</button>
        </form>

        <div class="detail-row">
          <span><strong>Metode:</strong></span>
          <span><?= $p['metode_pembayaran'] === 'cod' ? '💵 Bayar di Tempat' : '🏧 Transfer' ?></span>
        </div>
        <div class="detail-row">
          <span><strong>Waktu:</strong></span>
          <span><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
        </div>

        <div class="items">
          <strong>Detail Menu:</strong>
          <?php
          $stm = $conn->prepare("SELECT * FROM pesanan_detail WHERE pesanan_id=?");
          $stm->bind_param("i", $p['id']);
          $stm->execute();
          $its = $stm->get_result();
          while ($it = $its->fetch_assoc()):
          ?>
            <div class="item-row">
              <span><?= htmlspecialchars($it['nama_menu']) ?> x<?= $it['jumlah'] ?></span>
              <span>Rp <?= number_format($it['harga'] * $it['jumlah'], 0, ',', '.') ?></span>
            </div>
          <?php endwhile; ?>
        </div>

        <div class="mt-3">
          <?php if ($p['status'] === 'pending'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="status" value="proses">
              <button class="btn-custom btn-proses">Mulai Proses</button>
            </form>
          <?php endif; ?>
          <?php if ($p['status'] === 'proses'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="status" value="selesai">
              <button class="btn-custom btn-selesai">Selesai</button>
            </form>
          <?php endif; ?>
          <?php if ($p['status'] === 'selesai'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="status" value="diambil">
              <button class="btn-custom btn-ambil">Sudah Diambil</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<!-- Footer Navigation -->
<div class="footer-nav">
  <div class="container text-center">
    <a href="dashboard.php" class="active">
      <i class="fas fa-home me-1"></i>Beranda
    </a>
    <a href="menu.php">
      <i class="fas fa-utensils me-1"></i>Menu
    </a>
    <a href="profile.php">
      <i class="fas fa-user me-1"></i>Profil
    </a>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>
