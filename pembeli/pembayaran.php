<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$username = $_SESSION['username'];
$keranjang = isset($_SESSION['keranjang']) ? $_SESSION['keranjang'] : [];
$kantin_id = isset($_SESSION['kantin_id']) ? $_SESSION['kantin_id'] : 1;

// migration: add catatan and seller_catatan columns if missing
$check = $conn->query("SHOW COLUMNS FROM pesanan LIKE 'catatan'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE pesanan ADD COLUMN catatan VARCHAR(255) DEFAULT NULL");
}
$check2 = $conn->query("SHOW COLUMNS FROM pesanan LIKE 'seller_catatan'");
if ($check2 && $check2->num_rows === 0) {
    $conn->query("ALTER TABLE pesanan ADD COLUMN seller_catatan VARCHAR(255) DEFAULT NULL");
}

// Cek jika keranjang kosong
if (empty($keranjang)) {
    header('Location: dashboard.php');
    exit;
}

$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['jumlah'];
}

// Generate nomor antrian
$nomor_antrian = strtoupper(substr(uniqid(), -6));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['metode_pembayaran'])) {
    $metode = $_POST['metode_pembayaran'];
    $catatan = trim($_POST['catatan'] ?? '');
    
    // Cek ID pembeli dari db
    $stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ? AND role = 'pembeli'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $pembeli = $result->fetch_assoc();
    $pembeli_id = $pembeli['id'] ?? 1;
    
    // Insert pesanan
    $stmt = $conn->prepare("INSERT INTO pesanan (pembeli_id, pembeli_nama, kantin_id, nomor_antrian, metode_pembayaran, total_harga, catatan, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isisiss", $pembeli_id, $username, $kantin_id, $nomor_antrian, $metode, $total, $catatan);
    $stmt->execute();
    $pesanan_id = $conn->insert_id;
    
    // Insert detail pesanan
    foreach ($keranjang as $item) {
        $menuName = $item['nama'];
        if (isset($item['spicy_level'])) {
            $menuName .= " - Pedas lvl " . $item['spicy_level'];
        }
        $stmt = $conn->prepare("INSERT INTO pesanan_detail (pesanan_id, nama_menu, harga, jumlah) 
                                VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $pesanan_id, $menuName, $item['harga'], $item['jumlah']);
        $stmt->execute();
    }
    
    // Clear keranjang
    unset($_SESSION['keranjang']);
    
    // Redirect ke success page
    header("Location: pesanan_sukses.php?nomor=$nomor_antrian&metode=$metode");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pembayaran</title>
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
    .summary-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .summary-card:hover {
      transform: translateY(-5px);
    }
    .summary-card h3 {
      color: #28a745;
      margin-bottom: 20px;
      font-weight: 600;
    }
    .summary-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .summary-item:last-child {
      border-bottom: none;
      font-weight: 700;
      font-size: 18px;
      color: #28a745;
      border-top: 2px solid #28a745;
      margin-top: 10px;
      padding-top: 15px;
    }
    .spicy-note {
      font-size: 12px;
      color: #dc3545;
      font-weight: 500;
      margin-left: 10px;
    }
    .payment-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .payment-card:hover {
      transform: translateY(-5px);
    }
    .payment-card h3 {
      color: #28a745;
      margin-bottom: 20px;
      font-weight: 600;
    }
    .form-floating {
      margin-bottom: 15px;
    }
    .payment-option {
      display: flex;
      align-items: center;
      padding: 15px;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .payment-option:hover {
      border-color: #28a745;
      background-color: #f8fff9;
    }
    .payment-option input[type="radio"] {
      margin-right: 15px;
      transform: scale(1.2);
    }
    .payment-option.selected {
      border-color: #28a745;
      background-color: #f8fff9;
    }
    .payment-title {
      font-weight: 600;
      color: #28a745;
      margin-bottom: 5px;
    }
    .payment-desc {
      font-size: 14px;
      color: #6c757d;
    }
    .btn-primary-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      border-radius: 25px;
      padding: 12px 24px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      margin-bottom: 10px;
    }
    .btn-primary-custom:hover {
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
      transform: translateY(-2px);
    }
    .btn-secondary-custom {
      background: #6c757d;
      color: white;
      border: none;
      border-radius: 25px;
      padding: 12px 24px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
    }
    .btn-secondary-custom:hover {
      background: #5a6268;
      transform: translateY(-2px);
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
  <div class="container">
    <a class="navbar-brand" href="#">
      <i class="fas fa-credit-card me-2"></i>Pembayaran Pesanan
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
  <div class="row">
    <!-- Order Summary -->
    <div class="col-lg-6 mb-4">
      <div class="summary-card">
        <h3>
          <i class="fas fa-receipt me-2"></i>Ringkasan Pesanan
        </h3>
        <?php foreach ($keranjang as $item): ?>
          <div class="summary-item">
            <span>
              <?= htmlspecialchars($item['nama']) ?> x <?= $item['jumlah'] ?>
              <?php if (isset($item['spicy_level'])): ?>
                <div class="spicy-note">
                  <i class="fas fa-pepper-hot me-1"></i>Pedas lvl <?= htmlspecialchars($item['spicy_level']) ?>
                </div>
              <?php endif; ?>
            </span>
            <span>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
          </div>
        <?php endforeach; ?>
        <div class="summary-item">
          <span>Total Pembayaran:</span>
          <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
        </div>
      </div>
    </div>

    <!-- Payment Method -->
    <div class="col-lg-6 mb-4">
      <div class="payment-card">
        <h3>
          <i class="fas fa-money-bill-wave me-2"></i>Pilih Metode Pembayaran
        </h3>

        <form method="post">
          <div class="form-floating mb-3">
            <textarea class="form-control" name="catatan" id="catatan" rows="3" placeholder="Catatan untuk penjual (mis. jangan pedas)"></textarea>
            <label for="catatan">Catatan untuk penjual (opsional)</label>
          </div>

          <div class="payment-option" onclick="selectPayment('cod')">
            <input type="radio" name="metode_pembayaran" value="cod" id="cod" required>
            <div>
              <div class="payment-title">
                <i class="fas fa-hand-holding-usd me-2"></i>💵 Bayar di Tempat (COD)
              </div>
              <div class="payment-desc">Bayar saat mengambil pesanan di kasir kantin</div>
            </div>
          </div>

          <div class="payment-option" onclick="selectPayment('online')">
            <input type="radio" name="metode_pembayaran" value="online" id="online" required>
            <div>
              <div class="payment-title">
                <i class="fas fa-mobile-alt me-2"></i>🏧 Transfer Bank / E-Wallet
              </div>
              <div class="payment-desc">Bayar sekarang untuk konfirmasi pesanan</div>
            </div>
          </div>

          <button type="submit" class="btn-primary-custom">
            <i class="fas fa-check me-2"></i>Lanjutkan Pembayaran
          </button>
          <button type="button" class="btn-secondary-custom" onclick="location.href='keranjang.php'">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Keranjang
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Footer Navigation -->
<div class="footer-nav">
  <div class="container text-center">
    <a href="dashboard.php">
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

<script>
function selectPayment(method) {
  // Remove selected class from all options
  document.querySelectorAll('.payment-option').forEach(option => {
    option.classList.remove('selected');
  });

  // Add selected class to clicked option
  event.currentTarget.classList.add('selected');

  // Check the radio button
  document.getElementById(method).checked = true;
}

// Add click listeners to payment options
document.querySelectorAll('.payment-option').forEach(option => {
  option.addEventListener('click', function() {
    const radio = this.querySelector('input[type="radio"]');
    selectPayment(radio.value);
  });
});
</script>

</body>
</html>
