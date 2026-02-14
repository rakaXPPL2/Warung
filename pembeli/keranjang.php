<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$username = $_SESSION['username'];
$keranjang = isset($_SESSION['keranjang']) ? $_SESSION['keranjang'] : [];

$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Keranjang Belanja</title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/keranjang.css">
</head>
<body>

<div class="header">
  <h1>Keranjang Belanja</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="keranjang-container">
    <?php if (empty($keranjang)): ?>
    <div class="kosong">
      <p style="font-size: 18px; margin-bottom: 20px;">Keranjang Anda kosong</p>
      <button onclick="location.href='dashboard.php'" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
        Kembali Belanja
      </button>
    </div>
  <?php else: ?>
    <table class="cart-table">
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
          <td><?= htmlspecialchars($item['nama']) ?></td>
          <td style="text-align: center;">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
          <td style="text-align: center;">
            <input type="number" value="<?= $item['jumlah'] ?>" min="1" onchange="updateJumlah(<?= $index ?>, this.value)">
          </td>
          <td style="text-align: center; font-weight: bold;">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
          <td style="text-align: center;">
            <button onclick="hapusItem(<?= $index ?>)" style="padding: 5px 10px; background: #ff4757; color: white; border: none; border-radius: 5px; cursor: pointer;">Hapus</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="total-section">
      <p>Subtotal: Rp <?= number_format($total, 0, ',', '.') ?></p>
      <p style="color: var(--muted); font-size: 14px;">Biaya pembayaran akan ditambahkan saat checkout</p>
      <div class="total-harga">Total: Rp <?= number_format($total, 0, ',', '.') ?></div>
    </div>

    <div class="action-buttons">
      <button class="btn-cancel" onclick="location.href='dashboard.php'">Lanjut Belanja</button>
      <button class="btn-checkout" onclick="checkout()">Lanjut ke Pembayaran</button>
    </div>
  <?php endif; ?>
</div>

<div class="nav">
  <a href="dashboard.php">Beranda</a>
  <a href="status_pesanan.php">Status Pesanan</a>
</div>

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
