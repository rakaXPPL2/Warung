<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$username = $_SESSION['username'];
$keranjang = isset($_SESSION['keranjang']) ? $_SESSION['keranjang'] : [];
// purge old flavor keys if present
foreach ($keranjang as &$it) {
    if (isset($it['flavor'])) unset($it['flavor']);
}
$_SESSION['keranjang'] = $keranjang;

$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Keranjang Belanja</title>
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
    .empty-cart {
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin: 20px;
    }
    .empty-cart i {
      font-size: 4rem;
      color: #6c757d;
      margin-bottom: 20px;
    }
    .cart-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      margin: 20px;
      transition: transform 0.3s ease;
    }
    .cart-card:hover {
      transform: translateY(-5px);
    }
    .cart-table {
      margin-bottom: 0;
    }
    .cart-table th {
      background: #28a745;
      color: white;
      border: none;
      padding: 15px;
      font-weight: 600;
    }
    .cart-table td {
      padding: 15px;
      border: none;
      vertical-align: middle;
    }
    .cart-table tbody tr:nth-child(even) {
      background-color: #f8f9fa;
    }
    .quantity-input {
      width: 80px;
      text-align: center;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 5px;
    }
    .btn-delete {
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 20px;
      padding: 8px 16px;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .btn-delete:hover {
      background: #c82333;
      transform: translateY(-2px);
    }
    .spicy-note {
      font-size: 12px;
      color: #dc3545;
      font-weight: 500;
    }
    .total-section {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
      margin: 20px;
      border-left: 4px solid #28a745;
    }
    .total-price {
      font-size: 24px;
      font-weight: 700;
      color: #28a745;
      text-align: center;
      margin: 15px 0;
    }
    .action-buttons {
      display: flex;
      gap: 15px;
      margin: 20px;
      justify-content: center;
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
    }
    .btn-secondary-custom:hover {
      background: #5a6268;
      transform: translateY(-2px);
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
    }
    .btn-primary-custom:hover {
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
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
      <i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja
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
  <?php if (empty($keranjang)): ?>
    <div class="empty-cart">
      <i class="fas fa-shopping-cart"></i>
      <h3>Keranjang Kosong</h3>
      <p>Yuk mulai belanja di kantin favorit Anda!</p>
      <button onclick="location.href='dashboard.php'" class="btn-primary-custom">
        <i class="fas fa-arrow-left me-2"></i>Kembali Belanja
      </button>
    </div>
  <?php else: ?>
    <!-- Cart Items -->
    <div class="cart-card">
      <div class="table-responsive">
        <table class="table cart-table">
          <thead>
            <tr>
              <th>Menu</th>
              <th>Harga</th>
              <th>Jumlah</th>
              <th>Subtotal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($keranjang as $index => $item): ?>
            <tr>
              <td>
                <div>
                  <strong><?= htmlspecialchars($item['nama']) ?></strong>
                  <?php if (isset($item['spicy_level'])): ?>
                    <div class="spicy-note">
                      <i class="fas fa-pepper-hot me-1"></i>Pedas level <?= htmlspecialchars($item['spicy_level']) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </td>
              <td class="text-center">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
              <td class="text-center">
                <input type="number" class="quantity-input" value="<?= $item['jumlah'] ?>" min="1" onchange="updateJumlah(<?= $index ?>, this.value)">
              </td>
              <td class="text-center">
                <strong>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></strong>
              </td>
              <td class="text-center">
                <button onclick="hapusItem(<?= $index ?>)" class="btn-delete">
                  <i class="fas fa-trash me-1"></i>Hapus
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Total Section -->
    <div class="total-section">
      <div class="text-center mb-3">
        <small class="text-muted">Biaya pembayaran akan ditambahkan saat checkout</small>
      </div>
      <div class="total-price">
        Total: Rp <?= number_format($total, 0, ',', '.') ?>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn-secondary-custom" onclick="location.href='dashboard.php'">
        <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
      </button>
      <button class="btn-primary-custom" onclick="checkout()">
        <i class="fas fa-credit-card me-2"></i>Lanjut ke Pembayaran
      </button>
    </div>
  <?php endif; ?>
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
function updateJumlah(index, jumlah) {
  fetch('api/update_keranjang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ index: index, jumlah: parseInt(jumlah) })
  }).then(() => location.reload());
}

function hapusItem(index) {
  if (confirm('Hapus item ini dari keranjang?')) {
    fetch('api/hapus_keranjang.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ index: index })
    }).then(() => location.reload());
  }
}

function checkout() {
  if (<?= count($keranjang) ?> === 0) {
    alert('Keranjang kosong!');
    return;
  }
  location.href = 'pembayaran.php';
}
</script>

</body>
</html>
