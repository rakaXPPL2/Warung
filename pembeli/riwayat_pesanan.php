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

// Get semua pesanan pembeli
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE pembeli_id = ? ORDER BY created_at DESC");
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
  <title>Riwayat Pesanan</title>
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
      max-width: 1000px;
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
      font-size: 22px;
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
    
    .order-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
    }
    
    .detail-item {
      background: #f8f9fa;
      padding: 12px 15px;
      border-radius: 10px;
      border-left: 4px solid #28a745;
    }
    
    .detail-label {
      font-size: 11px;
      color: #6c757d;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 5px;
    }
    
    .detail-value {
      font-size: 15px;
      font-weight: 600;
      color: #28a745;
    }
    
    .menu-list {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
      margin-top: 15px;
    }
    
    .menu-list strong {
      color: #28a745;
      display: block;
      margin-bottom: 10px;
    }
    
    .menu-list ul {
      margin: 0;
      padding-left: 20px;
    }
    
    .menu-list li {
      color: #495057;
      margin-bottom: 5px;
    }
    
    .no-history {
      text-align: center;
      padding: 60px 20px;
      color: #6c757d;
    }
    
    .no-history svg {
      width: 80px;
      height: 80px;
      margin-bottom: 20px;
      opacity: 0.5;
    }
    
    .no-history h5 {
      color: #28a745;
      margin-bottom: 10px;
    }
    
    .no-history a {
      color: #28a745;
      text-decoration: none;
      font-weight: 600;
    }
    
    .no-history a:hover {
      text-decoration: underline;
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
          <a class="nav-link active" href="riwayat_pesanan.php">
            <i class="fas fa-history me-1"></i>Riwayat
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="status_pesanan.php">
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
    <i class="fas fa-history me-2"></i>Riwayat Pesanan Saya
  </h2>
  
  <?php if ($pesanan_list->num_rows > 0): ?>
    <div class="mb-4">
      <p style="color: #6c757d; font-weight: 500;">Total Pesanan: <strong style="color: #28a745;"><?= $pesanan_list->num_rows ?></strong></p>
    </div>
    
    <?php 
    $order_number = 1;
    while ($pesanan = $pesanan_list->fetch_assoc()): 
      // Get kantin info
      $kantin_stmt = $conn->prepare("SELECT nama FROM kantin WHERE id = ?");
      $kantin_stmt->bind_param("i", $pesanan['kantin_id']);
      $kantin_stmt->execute();
      $kantin_info = $kantin_stmt->get_result()->fetch_assoc();
      $kantin_nama = $kantin_info['nama'] ?? 'Kantin ' . $pesanan['kantin_id'];
      
      // Get menu items
      $detail_stmt = $conn->prepare("SELECT nama_menu, jumlah, harga FROM pesanan_detail WHERE pesanan_id = ?");
      $detail_stmt->bind_param("i", $pesanan['id']);
      $detail_stmt->execute();
      $details = $detail_stmt->get_result();
      
      // Determine status badge class
      $status_class = 'status-' . $pesanan['status'];
    ?>
      <div class="order-card">
        <div class="order-header">
          <div>
            <div class="order-number">Pesanan #<?= htmlspecialchars($pesanan['nomor_antrian']) ?></div>
            <small style="opacity: 0.9;">Kantin: <?= htmlspecialchars($kantin_nama) ?></small>
          </div>
          <span class="status-badge <?= $status_class ?>">
            <?php
            $status_text = [
              'pending' => '⏳ Menunggu',
              'proses' => '👨‍🍳 Diproses',
              'selesai' => '✅ Siap Ambil',
              'diambil' => '🎉 Selesai'
            ];
            echo $status_text[$pesanan['status']] ?? htmlspecialchars($pesanan['status']);
            ?>
          </span>
        </div>
        
        <div class="order-body">
          <div class="order-details">
            <div class="detail-item">
              <div class="detail-label">📅 Tanggal Pesanan</div>
              <div class="detail-value"><?= date('d/m/Y', strtotime($pesanan['created_at'])) ?></div>
            </div>
            <div class="detail-item">
              <div class="detail-label">⏰ Waktu</div>
              <div class="detail-value"><?= date('H:i', strtotime($pesanan['created_at'])) ?> WIB</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">💰 Total Harga</div>
              <div class="detail-value">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></div>
            </div>
            <div class="detail-item">
              <div class="detail-label">📍 Lokasi</div>
              <div class="detail-value"><?= htmlspecialchars($pesanan['lokasi_ambil'] ?? 'Standar') ?></div>
            </div>
          </div>
          
          <div class="menu-list">
            <strong><i class="fas fa-list me-2"></i>Menu yang Dipesan:</strong>
            <ul>
              <?php 
              if ($details && $details->num_rows > 0):
                while ($detail = $details->fetch_assoc()):
              ?>
                <li>
                  <?= htmlspecialchars($detail['nama_menu']) ?> 
                  <span style="color: #6c757d;">(x<?= $detail['jumlah'] ?>) - Rp <?= number_format($detail['harga'] * $detail['jumlah'], 0, ',', '.') ?></span>
                </li>
              <?php 
                endwhile;
              else:
              ?>
                <li style="color: #6c757d;">-</li>
              <?php endif; ?>
            </ul>
          </div>
          
          <?php if (!empty($pesanan['catatan'])): ?>
            <div style="background: #f0f8ff; border-left: 4px solid #0099cc; padding: 12px 15px; border-radius: 5px; margin-top: 15px;">
              <strong style="color: #0099cc;">📝 Catatan Anda:</strong>
              <p style="margin: 5px 0 0 0; color: #495057;"><?= htmlspecialchars($pesanan['catatan']) ?></p>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($pesanan['seller_catatan'])): ?>
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 15px; border-radius: 5px; margin-top: 10px;">
              <strong style="color: #856404;">👨‍🍳 Catatan Penjual:</strong>
              <p style="margin: 5px 0 0 0; color: #495057;"><?= htmlspecialchars($pesanan['seller_catatan']) ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php 
    $order_number++;
    endwhile; 
    ?>
  <?php else: ?>
    <div class="no-history">
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="30" r="20" fill="none" stroke="#bbb" stroke-width="2"/>
        <path d="M 50 30 L 50 50 M 50 30 L 60 40" stroke="#bbb" stroke-width="2" stroke-linecap="round"/>
        <path d="M 30 70 L 70 70 Q 70 85 50 90 Q 30 85 30 70" fill="none" stroke="#bbb" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <h5>Belum Ada Riwayat Pesanan</h5>
      <p>Anda belum melakukan pemesanan apapun. <a href="dashboard.php">Mulai pesan sekarang!</a></p>
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