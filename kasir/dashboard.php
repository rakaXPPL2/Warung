<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Handle mark as diambil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesanan_id'])) {
    $pesanan_id = (int)$_POST['pesanan_id'];
    
    $stmt = $conn->prepare("UPDATE pesanan SET status = 'diambil' WHERE id = ?");
    $stmt->bind_param("i", $pesanan_id);
    $stmt->execute();
}

// Get pesanan (all statuses except diambil) for cashier overview
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE status <> 'diambil' ORDER BY created_at DESC LIMIT 50");
$stmt->execute();
$pesanan_list = $stmt->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard Kasir</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    .container { max-width: 1000px; margin: 20px auto; padding: 20px; }
    .orders-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(45%,1fr)); gap:20px; }
    .pesanan-card {
      position: relative;
      background: var(--card);
      border: 1px solid #f3f4f6;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      display: grid;
      grid-template-columns: 1fr 150px;
      gap: 20px;
      align-items: start;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .pesanan-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      height: 4px;
      width: 100%;
    }
    .pesanan-card.status-pending::after { background: red; }
    .pesanan-card.status-proses::after { background: green; }
    .pesanan-card.status-selesai::after { background: yellow; }

    .pesanan-info { }
    .nomor-antrian { 
      font-size: 36px; 
      font-weight: bold; 
      color: var(--primary);
      margin-bottom: 10px;
    }
    .pesanan-detail { margin: 10px 0; fontSize: 14px; }
    .detail-row { display: flex; justify-content: space-between; padding: 5px 0; }
    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 14px;
      text-align: center;
    }
    .status-selesai { background: #dcfce7; color: #166534; }
    .status-diambil { background: #f3e8ff; color: #581c87; }
    .action-buttons { display: flex; flex-direction: column; gap: 10px; }
    .btn { 
      padding: 10px 16px; 
      border: none; 
      border-radius: 6px; 
      cursor: pointer; 
      font-weight: bold;
      font-size: 14px;
    }
    .btn-bayar { background: #fbbf24; color: #78350f; }
    .btn-bayar:hover { background: #f59e0b; }
    .btn-ambil { background: #dcfce7; color: #166534; }
    .btn-ambil:hover { background: #bbf7d0; }
    .kosong { text-align: center; padding: 40px; color: var(--muted); }
    .items { font-size: 12px; color: var(--muted); margin-top: 8px; }
  </style>
</head>
<body>
<script>
function handlePayment(total) {
  let uang = parseInt(prompt('Masukkan jumlah uang diterima (Rp):','0'));
  if (isNaN(uang)) return;
  let kembalian = uang - total;
  if (kembalian < 0) {
    alert('Uang kurang! Total Rp ' + total.toLocaleString());
  } else {
    alert('Kembalian: Rp ' + kembalian.toLocaleString());
  }
}
</script>

<div class="header">
  <h1>Dashboard Kasir</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>
<div class="container">
    <h2 style="margin-top: 0;">Daftar Pesanan</h2>
  
  <?php if ($pesanan_list->num_rows === 0): ?>
    <div class="kosong">
      <p>Tidak ada pesanan.</p>
    </div>
  <?php else: ?>
    <div class="orders-grid">
    <?php while ($pesanan = $pesanan_list->fetch_assoc()): ?>
      <div class="pesanan-card status-<?= htmlspecialchars($pesanan['status']) ?>">
        <div class="pesanan-info">
          <div class="nomor-antrian"><?= htmlspecialchars($pesanan['nomor_antrian']) ?></div>
          <div class="pesanan-detail">
            <div class="detail-row">
              <span>Pembeli:</span>
              <strong><?= htmlspecialchars($pesanan['pembeli_nama']) ?></strong>
            </div>
            <div class="detail-row">
              <span>Kantin:</span>
              <strong><?= $pesanan['kantin_id'] ?></strong>
            </div>
            <div class="detail-row">
              <span>Total:</span>
              <strong>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
            </div>
            <div class="detail-row">
              <span>Metode:</span>
              <span><?= $pesanan['metode_pembayaran'] === 'cod' ? '💵 COD' : '✅ Online' ?></span>
            </div>
          </div>
        </div>
        
        <div class="action-buttons">
          <div class="status-badge status-<?= $pesanan['status'] ?>">
            <?= $pesanan['status'] === 'selesai' ? '✅ SIAP AMBIL' : '🎉 DIAMBIL' ?>
          </div>
          
          <?php if ($pesanan['status'] === 'selesai'): ?>
            <form method="post" style="margin: 0;">
              <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
              <button type="submit" class="btn btn-ambil">✓ Tandai Diambil</button>
            </form>
            <?php if ($pesanan['metode_pembayaran'] === 'cod'): ?>
              <button class="btn btn-bayar" onclick="handlePayment(<?= $pesanan['total_harga'] ?>)">💵 Bayar</button>
            <?php endif; ?>
          <?php else: ?>

            <div style="text-align: center; padding: 10px 0; color: #571c87;">Sudah Diambil</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

</div>

<footer class="nav">
  <a class="active">Beranda</a>
  <a href="history.php">Riwayat Pesanan</a>
  <a href="kantin.php">Kantin</a>
</footer>

</body>
</html>