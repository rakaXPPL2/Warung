<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Ambil data kantin dari database (Professional Approach)
$result = $conn->query("SELECT * FROM kantin");
$username = $_SESSION['username'];

// Get pembeli ID
$stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ? AND role = 'pembeli'");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result_pembeli = $stmt->get_result();
$pembeli = $result_pembeli->fetch_assoc();
$pembeli_id = $pembeli['id'] ?? 0;

// Get pesanan dalam 24 jam terakhir
$stmt_recent = $conn->prepare("SELECT * FROM pesanan WHERE pembeli_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY created_at DESC");
$stmt_recent->bind_param("i", $pembeli_id);
$stmt_recent->execute();
$recent_orders = $stmt_recent->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kantin Sekolah - Pembeli</title>
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
      font-size: 36px;
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
    .kantin-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
      text-align: center;
      padding: 20px;
    }
    .kantin-card:hover {
      transform: translateY(-5px);
    }
    .kantin-card img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 15px;
    }
    .kantin-card h3 {
      color: #28a745;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .kantin-card p {
      color: #6c757d;
      font-size: 14px;
      margin-bottom: 15px;
    }
    .kantin-card button {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .kantin-card button:hover {
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
      transform: translateY(-2px);
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
    <i class="fas fa-utensils me-2"></i>Daftar Kantin
  </h2>

  <?php if ($recent_orders->num_rows > 0): ?>
    <div class="mb-5">
      <h3 class="mb-4">
        <i class="fas fa-clock me-2"></i>Pesanan Terbaru (24 Jam)
      </h3>
      <div class="row">
        <?php while ($order = $recent_orders->fetch_assoc()): ?>
          <div class="col-lg-6 col-md-12 mb-4">
            <div class="order-card">
              <div class="order-header">
                <div class="order-number">#<?= htmlspecialchars($order['nomor_antrian']) ?></div>
                <div class="order-status">
                  <span class="status-badge status-<?= $order['status'] ?>">
                    <?php
                    $status_text = [
                      'pending' => '⏳ Menunggu',
                      'proses' => '👨‍🍳 Diproses',
                      'selesai' => '✅ Siap Diambil',
                      'diambil' => '🎉 Selesai'
                    ];
                    echo $status_text[$order['status']];
                    ?>
                  </span>
                </div>
              </div>
              <div class="order-body">
                <div class="order-details">
                  <div class="detail-item">
                    <div class="detail-label">Waktu</div>
                    <div class="detail-value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                  </div>
                  <div class="detail-item">
                    <div class="detail-label">Total</div>
                    <div class="detail-value" style="color: #28a745; font-weight: 600;">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="row">
    <?php while($kantin = $result->fetch_assoc()): ?>
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="kantin-card">
          <img src="<?= htmlspecialchars($kantin['gambar']) ?>" alt="<?= htmlspecialchars($kantin['nama']) ?>">
          <h3><?= htmlspecialchars($kantin['nama']) ?></h3>
          <p><?= htmlspecialchars($kantin['deskripsi']) ?></p>
          <button onclick="location.href='pesanan.php?kantin=<?= $kantin['id'] ?>'">
            <i class="fas fa-shopping-cart me-1"></i>Pesan
          </button>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- Footer Navigation -->
<div class="footer-nav">
  <div class="container text-center">
    <a href="dashboard.php" class="active">
      <i class="fas fa-home me-1"></i>Beranda
    </a>
    <a href="status_pesanan.php">
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