<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// get all orders (limit 100)
$stmt = $conn->prepare("SELECT * FROM pesanan ORDER BY created_at DESC LIMIT 100");
$stmt->execute();
$pesanan_list = $stmt->get_result();
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>History Pembelian - Kasir</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    .container { max-width: 1000px; margin: 20px auto; padding: 20px; }
    .pesanan-card { background: var(--card); border:1px solid #f3f4f6; border-radius:12px; padding:20px; margin-bottom:20px; }
    .nomor-antrian { font-size:28px; font-weight:bold; color:var(--primary); }
    .detail-row { display:flex; justify-content:space-between; padding:4px 0; }
    .items { font-size:12px; color:var(--muted); margin-top:8px; }
  </style>
</head>
<body>
<div class="header">
  <h1>History Pembelian</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="container">
<?php if ($pesanan_list->num_rows === 0): ?>
  <p>Tidak ada riwayat pesanan.</p>
<?php else: ?>
  <?php while ($p = $pesanan_list->fetch_assoc()): ?>
    <div class="pesanan-card" style="margin-bottom:20px;">
      <div class="nomor-antrian"><?= htmlspecialchars($p['nomor_antrian']) ?></div>
      <div class="detail-row"><span>Pembeli:</span><strong><?= htmlspecialchars($p['pembeli_nama']) ?></strong></div>
      <div class="detail-row"><span>Kantin:</span><strong><?= $p['kantin_id'] ?></strong></div>
      <div class="detail-row"><span>Total:</span><strong>Rp <?= number_format($p['total_harga'],0,',','.') ?></strong></div>
      <div class="detail-row"><span>Status:</span><strong><?= htmlspecialchars($p['status']) ?></strong></div>
      <div class="detail-row"><span>Waktu:</span><strong><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></strong></div>
      <div class="items" style="margin-top:8px;font-size:12px;color:var(--muted);">
        <strong>Item:</strong><br>
        <?php
        $stmt2 = $conn->prepare("SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
        $stmt2->bind_param("i", $p['id']);
        $stmt2->execute();
        $items = $stmt2->get_result();
        while ($it = $items->fetch_assoc()):
        ?>
          <?= htmlspecialchars($it['nama_menu']) ?> x<?= $it['jumlah'] ?> (Rp <?= number_format($it['harga']*$it['jumlah'],0,',','.') ?>)<br>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endwhile; ?>
<?php endif; ?>
</div>

<footer class="nav">
  <a href="dashboard.php">Beranda</a>
  <a class="active">Riwayat Pesanan</a>
  <a href="kantin.php">Kantin</a>
</footer>

</body>
</html>