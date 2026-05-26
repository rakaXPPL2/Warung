<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Update status to diambil when customer picks up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesanan_id'])) {
    $pesanan_id = (int)$_POST['pesanan_id'];
    $pembeli_id = $_SESSION['pembeli_id'] ?? 0;
    
    // Get pembeli ID if not in session
    if (!$pembeli_id) {
        $stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ? AND role = 'pembeli'");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $pembeli = $result->fetch_assoc();
        $pembeli_id = $pembeli['id'] ?? 0;
        $_SESSION['pembeli_id'] = $pembeli_id;
    }
    
    // Update status to diambil
    $stmt = $conn->prepare("UPDATE pesanan SET status='diambil' WHERE id=? AND pembeli_id=? AND status='selesai'");
    $stmt->bind_param("ii", $pesanan_id, $pembeli_id);
    $stmt->execute();
    
    // Refresh page
    header("Location: status_pesanan.php");
    exit;
}

// Get pembeli ID
$stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ? AND role = 'pembeli'");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$pembeli = $result->fetch_assoc();
$pembeli_id = $pembeli['id'] ?? 0;
$_SESSION['pembeli_id'] = $pembeli_id;

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
    
    .container-main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
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
      flex-wrap: wrap;
      gap: 10px;
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
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 8px;
      border-left: 4px solid #28a745;
    }
    
    .label {
      font-weight: 700;
      color: #6c757d;
      font-size: 12px;
      text-transform: uppercase;
    }
    
    .value {
      font-weight: 600;
      color: #28a745;
    }
    
    .items-list {
      margin: 20px 0;
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
    }
    
    .items-list strong {
      color: #28a745;
      display: block;
      margin-bottom: 12px;
    }
    
    .item {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid white;
      color: #495057;
    }
    
    .item:last-child {
      border-bottom: none;
    }
    
    .total-harga {
      font-size: 18px;
      font-weight: 700;
      color: #28a745;
      text-align: right;
      margin: 15px 0;
      padding: 15px 0;
      border-top: 2px solid #e9ecef;
    }
    
    .btn-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      border-radius: 10px;
      padding: 12px 24px;
      font-weight: 600;
      transition: all 0.3s ease;
      width: 100%;
    }
    
    .btn-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
    }
    
    .alert-custom {
      border-radius: 10px;
      margin-top: 15px;
      background: #d4edda;
      border: none;
      color: #155724;
    }
    
    .no-orders {
      text-align: center;
      padding: 60px 20px;
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
      margin-bottom: 15px;
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
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-home me-1"></i>Beranda
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="riwayat_pesanan.php">
            <i class="fas fa-history me-1"></i>Riwayat
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="status_pesanan.php">
            <i class="fas fa-list me-1"></i>Status Pemesanan
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
  <h2 class="mb-4">
    <i class="fas fa-list me-2"></i>Status Pesanan Saya
  </h2>
  
  <?php if ($pesanan_list->num_rows === 0): ?>
    <div class="no-orders">
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="30" r="20" fill="none" stroke="#bbb" stroke-width="2"/>
        <path d="M 50 30 L 50 50 M 50 30 L 60 40" stroke="#bbb" stroke-width="2" stroke-linecap="round"/>
        <path d="M 30 70 L 70 70 Q 70 85 50 90 Q 30 85 30 70" fill="none" stroke="#bbb" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <h4>Anda belum ada pesanan</h4>
      <p style="color: #6c757d; margin-bottom: 20px;">Mulai pesan makanan favorit Anda sekarang!</p>
      <a href="dashboard.php" class="btn btn-custom">
        <i class="fas fa-plus me-2"></i>Mulai Pesan Sekarang
      </a>
    </div>
  <?php else: ?>
    <div class="row">
      <?php while ($pesanan = $pesanan_list->fetch_assoc()): ?>
        <div class="col-lg-6 col-md-12 mb-4">
          <div class="order-card">
            <div class="order-header">
              <div>
                <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Nomor Antrian</div>
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
              <div class="detail-row">
                <div>
                  <div class="label">⏰ Waktu Pesanan</div>
                  <div class="value"><?= date('d/m/Y H:i', strtotime($pesanan['created_at'])) ?></div>
                </div>
                <div>
                  <div class="label">💰 Total Harga</div>
                  <div class="value">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></div>
                </div>
              </div>
              
              <?php if (!empty($pesanan['catatan'])): ?>
                <div style="background: #f0f8ff; border-left: 4px solid #0099cc; padding: 12px; border-radius: 5px; margin-bottom: 10px;">
                  <strong style="color: #0099cc; font-size: 12px;">📝 CATATAN ANDA</strong>
                  <p style="margin: 5px 0 0 0; color: #495057; font-size: 14px;"><?= htmlspecialchars($pesanan['catatan']) ?></p>
                </div>
              <?php endif; ?>
              
              <?php if (!empty($pesanan['seller_catatan'])): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 5px; margin-bottom: 10px;">
                  <strong style="color: #856404; font-size: 12px;">👨‍🍳 CATATAN PENJUAL</strong>
                  <p style="margin: 5px 0 0 0; color: #495057; font-size: 14px;"><?= htmlspecialchars($pesanan['seller_catatan']) ?></p>
                </div>
              <?php endif; ?>

              <div class="detail-row" style="grid-template-columns: 1fr;">
                <div>
                  <div class="label">💳 Metode Pembayaran</div>
                  <div class="value">💵 Bayar di Tempat (COD)</div>
                </div>
              </div>

              <div class="items-list">
                <strong><i class="fas fa-list me-2"></i>Detail Menu</strong>
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
                    <span style="font-weight: 700;">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
                  </div>
                <?php endwhile; ?>
              </div>

              <div class="total-harga">
                💰 Total: Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?>
              </div>

              <?php if ($pesanan['status'] === 'selesai'): ?>
                <form method="post" style="margin-top: 15px;">
                  <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
                  <button type="submit" class="btn btn-custom">
                    <i class="fas fa-shopping-bag me-2"></i>Sudah Diambil
                  </button>
                </form>
              <?php elseif ($pesanan['status'] === 'diambil'): ?>
                <div class="alert alert-custom">
                  <i class="fas fa-check-circle me-2"></i>Pesanan telah diambil. Terima kasih telah berbelanja! 🙏
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Floating Chat Button -->
<button class="floating-chat" onclick="openChat()" title="Chat dengan Penjual & Kasir">
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
    alert('Chat akan dibuka dengan Penjual & Kasir secara real-time.\n\nFitur ini sedang dikembangkan dengan WebSocket/Socket.io untuk komunikasi real-time.');
  }
</script>

</body>
</html>
