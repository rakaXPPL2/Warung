<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'penjual') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Assume penjual memiliki kantin_id (default 1-6)
$kantin_id = isset($_SESSION['kantin_id']) ? $_SESSION['kantin_id'] : 1;

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesanan_id']) && isset($_POST['status'])) {
    $pesanan_id = (int)$_POST['pesanan_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ? AND kantin_id = ?");
    $stmt->bind_param("sii", $status, $pesanan_id, $kantin_id);
    $stmt->execute();
}

// Get pesanan dari kantin ini
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE kantin_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$pesanan_list = $stmt->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard Penjual</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    .container { max-width: 1000px; margin: 20px auto; padding: 20px; }
    .pesanan-card {
      background: var(--card);
      border: 1px solid #f3f4f6;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .pesanan-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid #f3f4f6; padding-bottom: 15px; }
    .nomor-antrian { font-size: 28px; font-weight: bold; color: var(--primary); }
    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 14px;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-proses { background: #dbeafe; color: #1e40af; }
    .status-selesai { background: #dcfce7; color: #166534; }
    .status-diambil { background: #f3e8ff; color: #581c87; }
    .pesanan-detail { margin: 15px 0; }
    .detail-row { display: flex; justify-content: space-between; padding: 8px 0; }
    .items-list { background: #f8fafc; padding: 12px; border-radius: 8px; margin: 15px 0; }
    .item { display: flex; justify-content: space-between; padding: 8px; border-bottom: 1px solid #e2e8f0; }
    .item:last-child { border: none; }
    .action-buttons { margin-top: 15px; display: flex; gap: 10px; }
    .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    .btn-proses { background: #dbeafe; color: #1e40af; }
    .btn-selesai { background: #dcfce7; color: #166534; }
    .kosong { text-align: center; padding: 40px; color: var(--muted); }
  </style>
</head>
<body>

<div class="header">
  <h1>Dashboard Penjual - Kantin <?= $kantin_id ?></h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="container">
  
  <h2 style="margin-top: 0;">Pesanan Masuk</h2>
  
  <?php if ($pesanan_list->num_rows === 0): ?>
    <div class="kosong">
      <p>Belum ada pesanan</p>
    </div>
  <?php else: ?>
    <?php while ($pesanan = $pesanan_list->fetch_assoc()): ?>
      <div class="pesanan-card">
        <div class="pesanan-header">
          <div>
            <div style="color: var(--muted); font-size: 12px;">Nomor Antrian</div>
            <div class="nomor-antrian"><?= htmlspecialchars($pesanan['nomor_antrian']) ?></div>
          </div>
          <span class="status-badge status-<?= $pesanan['status'] ?>">
            <?php
            $status_text = [
              'pending' => '⏳ Menunggu',
              'proses' => '👨‍🍳 Diproses',
              'selesai' => '✅ Siap Diambil',
              'diambil' => '🎉 Selesai'
            ];
            echo $status_text[$pesanan['status']];
            ?>
          </span>
        </div>

        <div class="pesanan-detail">
          <div class="detail-row">
            <span>Pembeli:</span>
            <span><?= htmlspecialchars($pesanan['pembeli_nama']) ?></span>
          </div>
          <div class="detail-row">
            <span>Metode Pembayaran:</span>
            <span><?= $pesanan['metode_pembayaran'] === 'cod' ? '💵 COD' : '🏧 Online' ?></span>
          </div>
          <div class="detail-row">
            <span>Waktu Pesanan:</span>
            <span><?= date('d/m/Y H:i', strtotime($pesanan['created_at'])) ?></span>
          </div>
        </div>

        <div class="items-list">
          <strong>Menu:</strong>
          <?php
          $stmt2 = $conn->prepare("SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
          $stmt2->bind_param("i", $pesanan['id']);
          $stmt2->execute();
          $items = $stmt2->get_result();
          while ($item = $items->fetch_assoc()):
          ?>
            <div class="item">
              <span><?= htmlspecialchars($item['nama_menu']) ?> x<?= $item['jumlah'] ?></span>
              <span>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></span>
            </div>
          <?php endwhile; ?>
          <div class="detail-row" style="margin-top: 8px; border-top: 1px solid #e2e8f0; padding-top: 8px;">
            <strong>Total:</strong>
            <strong>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
          </div>
        </div>

        <div class="action-buttons">
          <?php if ($pesanan['status'] === 'pending'): ?>
            <form method="post" style="display: inline;">
              <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
              <input type="hidden" name="status" value="proses">
              <button type="submit" class="btn btn-proses">👨‍🍳 Mulai Proses</button>
            </form>
          <?php endif; ?>
          
          <?php if ($pesanan['status'] === 'proses'): ?>
            <form method="post" style="display: inline;">
              <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
              <input type="hidden" name="status" value="selesai">
              <button type="submit" class="btn btn-selesai">✅ Selesai</button>
            </form>
          <?php endif; ?>
          
          <?php if ($pesanan['status'] === 'selesai'): ?>
            <div style="display: flex; align-items: center; gap: 10px;">
              <span class="status-badge status-selesai" style="margin: 0;">✅ Siap Diambil</span>
              <form method="post" style="margin: 0;">
                <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
                <input type="hidden" name="status" value="diambil">
                <button type="submit" class="btn" style="background: #f3e8ff; color: #581c87;">🎉 Pesanan Diambil</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

</div>

</body>
</html>