<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pesanan_id'], $_POST['status'])) {
        $pesanan_id = (int)$_POST['pesanan_id'];
        $status = $_POST['status'];
        
        // Validate status
        $valid_statuses = ['pending', 'proses', 'selesai', 'diambil'];
        if (in_array($status, $valid_statuses)) {
            $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $pesanan_id);
            $stmt->execute();
        }
    }
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
    
    .orders-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    
    .pesanan-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      transition: transform 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .pesanan-card:hover {
      transform: translateY(-5px);
    }
    
    .pesanan-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      height: 4px;
      width: 100%;
    }
    
    .pesanan-card.status-pending::after { background: #ffc107; }
    .pesanan-card.status-proses::after { background: #28a745; }
    .pesanan-card.status-selesai::after { background: #17a2b8; }
    .pesanan-card.status-diambil::after { background: #6c757d; }

    .pesanan-info {
      display: grid;
      grid-template-columns: 1fr 150px;
      gap: 20px;
      align-items: start;
    }
    
    .nomor-antrian {
      font-size: 36px;
      font-weight: 700;
      color: #28a745;
      margin-bottom: 10px;
    }
    
    .pesanan-detail {
      margin: 10px 0;
    }
    
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
    
    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
      text-align: center;
      margin-bottom: 15px;
    }
    
    .status-pending { background: #fff3cd; color: #856404; }
    .status-proses { background: #d1ecf1; color: #0c5460; }
    .status-selesai { background: #d4edda; color: #155724; }
    .status-diambil { background: #e2e3e5; color: #383d41; }
    
    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    
    .btn {
      padding: 10px 16px;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.3s ease;
    }
    
    .btn-proses { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
    .btn-proses:hover { background: linear-gradient(135deg, #20c997 0%, #28a745 100%); transform: translateY(-2px); }
    .btn-selesai { background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); color: white; }
    .btn-selesai:hover { background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); transform: translateY(-2px); }
    .btn-ambil { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white; }
    .btn-ambil:hover { background: linear-gradient(135deg, #5a32a3 0%, #6f42c1 100%); transform: translateY(-2px); }
    
    .kosong {
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin: 20px 0;
    }
    
    .kosong i {
      font-size: 4rem;
      color: #6c757d;
      margin-bottom: 20px;
    }
    
    .kosong h4 {
      color: #28a745;
      margin-bottom: 10px;
    }
    
    .items {
      font-size: 12px;
      color: #6c757d;
      margin-top: 8px;
      background: #f8f9fa;
      padding: 10px;
      border-radius: 8px;
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
            <a class="nav-link" href="history.php">
              <i class="fas fa-history me-1"></i>Riwayat
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="kantin.php">
              <i class="fas fa-store me-1"></i>Kantin
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
      <i class="fas fa-list-check me-2"></i>Daftar Pesanan
    </h2>

    <?php if ($pesanan_list->num_rows === 0): ?>
      <div class="kosong">
        <i class="fas fa-shopping-cart"></i>
        <h4>Tidak ada pesanan aktif</h4>
        <p class="text-muted">Semua pesanan sudah diproses atau diambil.</p>
      </div>
    <?php else: ?>
      <div class="orders-grid">
            <?php while ($pesanan = $pesanan_list->fetch_assoc()): ?>
              <div class="pesanan-card status-<?= htmlspecialchars($pesanan['status']) ?>">
                <div class="pesanan-info">
                  <div>
                    <div class="nomor-antrian">#<?= htmlspecialchars($pesanan['nomor_antrian']) ?></div>
                    <div class="pesanan-detail">
                      <div class="detail-row">
                        <span><i class="fas fa-user me-1"></i>Pembeli:</span>
                        <strong><?= htmlspecialchars($pesanan['pembeli_nama']) ?></strong>
                      </div>
                      <div class="detail-row">
                        <span><i class="fas fa-store me-1"></i>Kantin:</span>
                        <strong>Kantin <?= $pesanan['kantin_id'] ?></strong>
                      </div>
                      <div class="detail-row">
                        <span><i class="fas fa-money-bill-wave me-1"></i>Total:</span>
                        <strong class="text-success">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
                      </div>
                      <div class="detail-row">
                        <span><i class="fas fa-credit-card me-1"></i>Metode:</span>
                        <span class="badge bg-warning">
                          💵 Bayar di Tempat
                        </span>
                      </div>

                    </div>
                  </div>

                  <div class="action-buttons">
                    <div class="status-badge status-<?= $pesanan['status'] ?>">
                      <?php
                      $status_text = [
                        'pending' => '⏳ Menunggu Pembayaran',
                        'proses' => '✅ Sudah Bayar',
                        'selesai' => '🖨️ Siap Ambil',
                        'diambil' => '🎉 Selesai'
                      ];
                      echo $status_text[$pesanan['status']] ?? $pesanan['status'];
                      ?>
                    </div>

                     <?php if ($pesanan['status'] === 'pending'): ?>
                      <form method="post" style="margin: 0;">
                        <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
                        <input type="hidden" name="status" value="proses">
                        <button type="submit" class="btn btn-proses w-100">
                          <i class="fas fa-check me-1"></i>Cek Pembayaran
                        </button>
                      </form>
                    <?php else: ?>
                      <div class="text-center p-3">
                        <span class="text-muted">Tunggu penjual memproses</span>
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
  <button class="floating-chat" onclick="openChat()" title="Chat dengan Pembeli & Penjual">
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  
  <script>
    function openChat() {
      alert('Chat akan dibuka dengan Pembeli & Penjual secara real-time.\n\nFitur ini sedang dikembangkan dengan WebSocket/Socket.io untuk komunikasi real-time.');
    }
  </script>
</body>
</html>