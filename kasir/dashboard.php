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
      padding-bottom: 80px;
    }
    .navbar-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
      padding: 5px 0;
      font-size: 14px;
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
    .btn-selesai { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
    .btn-selesai:hover { background: linear-gradient(135deg, #20c997 0%, #28a745 100%); transform: translateY(-2px); }
    .btn-bayar { background: #ffc107; color: #212529; }
    .btn-bayar:hover { background: #e0a800; transform: translateY(-2px); }
    .btn-ambil { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
    .btn-ambil:hover { background: linear-gradient(135deg, #20c997 0%, #28a745 100%); transform: translateY(-2px); }
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
    .items {
      font-size: 12px;
      color: #6c757d;
      margin-top: 8px;
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
        <i class="fas fa-cash-register me-2"></i>Dashboard Kasir
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
          <i class="fas fa-list-check me-2"></i>Daftar Pesanan
        </h2>

        <?php if ($pesanan_list->num_rows === 0): ?>
          <div class="kosong">
            <i class="fas fa-shopping-cart"></i>
            <h4 class="text-muted">Tidak ada pesanan aktif</h4>
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
                        <span class="badge bg-<?= $pesanan['metode_pembayaran'] === 'cod' ? 'warning' : 'success' ?>">
                          <?= $pesanan['metode_pembayaran'] === 'cod' ? '💵 COD' : '✅ Online' ?>
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="action-buttons">
                    <div class="status-badge status-<?= $pesanan['status'] ?>">
                      <?php
                      $status_text = [
                        'pending' => '⏳ Menunggu',
                        'proses' => '👨‍🍳 Diproses',
                        'selesai' => '✅ Siap Ambil',
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
                          <i class="fas fa-play me-1"></i>Mulai Proses
                        </button>
                      </form>
                    <?php elseif ($pesanan['status'] === 'proses'): ?>
                      <form method="post" style="margin: 0;">
                        <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit" class="btn btn-selesai w-100">
                          <i class="fas fa-check me-1"></i>Tandai Selesai
                        </button>
                      </form>
                    <?php elseif ($pesanan['status'] === 'selesai'): ?>
                      <form method="post" style="margin: 0;">
                        <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
                        <input type="hidden" name="status" value="diambil">
                        <button type="submit" class="btn btn-ambil w-100 mb-2">
                          <i class="fas fa-hand-holding me-1"></i>Tandai Diambil
                        </button>
                      </form>
                      <?php if ($pesanan['metode_pembayaran'] === 'cod'): ?>
                        <button class="btn btn-bayar w-100" onclick="handlePayment(<?= $pesanan['total_harga'] ?>)">
                          <i class="fas fa-money-bill-wave me-1"></i>Bayar COD
                        </button>
                      <?php endif; ?>
                    <?php else: ?>
                      <div class="text-center p-3 text-muted">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <br>Sudah Diambil
                      </div>
                    <?php endif; ?>
                  </div>
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
      <a href="dashboard.php" class="active">
        <i class="fas fa-home me-1"></i>Beranda
      </a>
      <a href="history.php">
        <i class="fas fa-history me-1"></i>Riwayat
      </a>
      <a href="kantin.php">
        <i class="fas fa-store me-1"></i>Kantin
      </a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <script>
    function handlePayment(total) {
      let uang = parseInt(prompt('Masukkan jumlah uang diterima (Rp):', '0'));
      if (isNaN(uang)) return;
      let kembalian = uang - total;
      if (kembalian < 0) {
        alert('Uang kurang! Total yang harus dibayar: Rp ' + total.toLocaleString());
      } else {
        alert('Pembayaran berhasil!\nKembalian: Rp ' + kembalian.toLocaleString());
      }
    }
  </script>
</body>
</html>