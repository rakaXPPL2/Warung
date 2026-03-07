<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// fetch kantin list with total earnings
$query = "SELECT k.id, k.nama, k.gambar, COALESCE(SUM(p.total_harga),0) AS pendapatan
          FROM kantin k
          LEFT JOIN pesanan p ON p.kantin_id = k.id
          GROUP BY k.id";
$result = $conn->query($query);

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Kantin - Kasir</title>
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
    .kantin-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    .kantin-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    .kantin-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    .kantin-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      height: 4px;
      width: 100%;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .kantin-image {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      object-fit: cover;
      margin-bottom: 15px;
      border: 3px solid #f8f9fa;
    }
    .kantin-name {
      font-size: 20px;
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 10px;
    }
    .kantin-id {
      background: #e8f5e8;
      color: #28a745;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 15px;
    }
    .earnings-section {
      background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e8 100%);
      border-radius: 10px;
      padding: 15px;
      margin-top: 15px;
    }
    .earnings-label {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 5px;
    }
    .earnings-amount {
      font-size: 24px;
      font-weight: 700;
      color: #28a745;
      margin: 0;
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
        <i class="fas fa-store me-2"></i>Daftar Kantin
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
          <i class="fas fa-utensils me-2"></i>Overview Kantin & Pendapatan
        </h2>

        <?php if ($result->num_rows === 0): ?>
          <div class="empty-state">
            <i class="fas fa-store-slash"></i>
            <h4 class="text-muted">Belum ada kantin terdaftar</h4>
            <p class="text-muted">Kantin akan muncul di sini setelah didaftarkan oleh penjual.</p>
          </div>
        <?php else: ?>
          <div class="kantin-grid">
            <?php while($row = $result->fetch_assoc()): ?>
              <div class="kantin-card">
                <div class="text-center">
                  <?php if($row['gambar']): ?>
                    <img src="<?= htmlspecialchars($row['gambar']) ?>" alt="Kantin <?= htmlspecialchars($row['nama']) ?>" class="kantin-image">
                  <?php else: ?>
                    <div class="kantin-image d-flex align-items-center justify-content-center bg-light">
                      <i class="fas fa-store fa-2x text-muted"></i>
                    </div>
                  <?php endif; ?>

                  <h3 class="kantin-name"><?= htmlspecialchars($row['nama']) ?></h3>
                  <span class="kantin-id">ID: <?= $row['id'] ?></span>
                </div>

                <div class="earnings-section">
                  <div class="earnings-label">
                    <i class="fas fa-chart-line me-1"></i>Total Pendapatan
                  </div>
                  <p class="earnings-amount">
                    Rp <?= number_format($row['pendapatan'], 0, ',', '.') ?>
                  </p>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
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
      <a href="history.php">
        <i class="fas fa-history me-1"></i>Riwayat
      </a>
      <a href="kantin.php" class="active">
        <i class="fas fa-store me-1"></i>Kantin
      </a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>