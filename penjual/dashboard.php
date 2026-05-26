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
      padding-top: 80px;
    }
    
    /* Navbar Styling */
    .navbar-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1000;
    }
    
    .navbar-brand {
      font-weight: 700;
      color: white !important;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .logo-svg {
      width: 45px;
      height: 45px;
      background: white;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 5px;
    }
    
    .navbar-nav .nav-link {
      color: rgba(255, 255, 255, 0.9) !important;
      font-weight: 600;
      margin: 0 10px;
      padding: 8px 16px !important;
      border-radius: 20px;
      transition: all 0.3s ease;
    }
    
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      background: rgba(255, 255, 255, 0.2);
      color: white !important;
    }
    
    .navbar-custom .d-flex {
      gap: 10px;
    }
    
    .user-info {
      color: white !important;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.1);
    }
    
    .btn-logout {
      background: rgba(255, 255, 255, 0.2) !important;
      border: 2px solid white !important;
      color: white !important;
      font-weight: 600;
      border-radius: 20px;
      transition: all 0.3s ease;
      padding: 8px 16px !important;
    }
    
    .btn-logout:hover {
      background: white !important;
      color: #28a745 !important;
    }
    
    .header-custom {
      background: transparent;
      padding: 0;
      box-shadow: none;
      margin-bottom: 20px;
    }
    
    .container-main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
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
      flex-wrap: wrap;
      gap: 10px;
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
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 10px;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 8px;
      border-left: 4px solid #28a745;
      font-size: 14px;
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
      padding: 8px 0;
      border-bottom: 1px solid white;
      color: #495057;
    }
    
    .item-row:last-child {
      border-bottom: none;
    }
    
    .btn-custom {
      border-radius: 25px;
      padding: 12px 24px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      transition: all 0.3s ease;
    }
    
    .btn-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
    }
    
    .note-field {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 10px;
      margin-bottom: 10px;
      font-family: 'Poppins', sans-serif;
    }
    
    .no-orders {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin: 20px 0;
      color: #6c757d;
    }
    
    .no-orders svg {
      width: 100px;
      height: 100px;
      margin-bottom: 20px;
      opacity: 0.5;
    }
    
    .no-orders h4 {
      color: #28a745;
      margin-bottom: 10px;
    }
    
    /* Floating Chat Button */
    .floating-chat {
      position: fixed;
      bottom: 30px;
      right: 20px;
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
      transition: all 0.3s ease;
      z-index: 999;
      border: none;
      padding: 0;
    }
    
    .floating-chat:hover {
      transform: scale(1.1);
      box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
    }
    
    .floating-chat svg {
      width: 30px;
      height: 30px;
      filter: drop-shadow(0 0 2px rgba(0,0,0,0.1));
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
      <div class="logo-svg">
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
          <!-- Bowl -->
          <path d="M 20 40 Q 20 60 50 65 Q 80 60 80 40" fill="none" stroke="#28a745" stroke-width="3" stroke-linecap="round"/>
          <!-- Spoon -->
          <ellipse cx="35" cy="25" rx="8" ry="12" fill="#28a745"/>
          <line x1="35" y1="37" x2="35" y2="50" stroke="#28a745" stroke-width="2" stroke-linecap="round"/>
          <!-- Fork -->
          <line x1="65" y1="20" x2="65" y2="48" stroke="#28a745" stroke-width="2" stroke-linecap="round"/>
          <line x1="58" y1="48" x2="72" y2="48" stroke="#28a745" stroke-width="2"/>
          <circle cx="59" cy="50" r="1.5" fill="#28a745"/>
          <circle cx="65" cy="50" r="1.5" fill="#28a745"/>
          <circle cx="71" cy="50" r="1.5" fill="#28a745"/>
          <!-- Rice bowl element -->
          <circle cx="50" cy="45" r="12" fill="#FFD700" opacity="0.6"/>
        </svg>
      </div>
      <span>Warung Ku</span>
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" href="dashboard.php">
            <i class="fas fa-home me-1"></i>Beranda
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="menu.php">
            <i class="fas fa-utensils me-1"></i>Menu
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">
            <i class="fas fa-user me-1"></i>Profil
          </a>
        </li>
      </ul>
      
      <div class="d-flex align-items-center gap-2 ms-3">
        <span class="user-info">
          <i class="fas fa-user-circle"></i><?= htmlspecialchars($username) ?>
        </span>
        <a href="../logout.php" class="btn btn-logout">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
      </div>
    </div>
  </div>
</nav>

<div class="container-main">
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
          <span>💵 Bayar di Tempat (COD)</span>
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
          <?php if ($p['status'] === 'proses'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="status" value="selesai">
              <button class="btn-custom btn-selesai">Selesai</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<!-- Floating Chat Button -->
<button class="floating-chat" onclick="openChat()" title="Chat dengan Pembeli">
  <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Chat bubble -->
    <path d="M 3 10 C 3 6.134 6.134 3 10 3 H 20 C 21.657 3 23 4.343 23 6 V 16 C 23 17.657 21.657 19 20 19 H 13 L 7 23 L 7 19 C 5.343 19 4 17.657 4 16 V 10" 
          fill="white" stroke="white" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/>
    <!-- Chat dots -->
    <circle cx="8" cy="11" r="1.5" fill="#28a745"/>
    <circle cx="12" cy="11" r="1.5" fill="#28a745"/>
    <circle cx="16" cy="11" r="1.5" fill="#28a745"/>
  </svg>
</button>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<script>
  function openChat() {
    alert('Chat akan dibuka dengan Pembeli secara real-time.\n\nFitur ini sedang dikembangkan dengan WebSocket/Socket.io untuk komunikasi real-time.');
  }
</script>

</body>
</html>
