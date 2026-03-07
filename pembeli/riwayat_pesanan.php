<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Get pembeli ID
$stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ? AND role = 'pembeli'");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$pembeli = $result->fetch_assoc();
$pembeli_id = $pembeli['id'] ?? 0;

// Get semua pesanan pembeli
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE pembeli_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $pembeli_id);
$stmt->execute();
$pesanan_list = $stmt->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Riwayat Pesanan</title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/status.css">
</head>
<body>

<div class="header">
  <h1>Riwayat Pesanan</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="status-container">

  <?php if ($pesanan_list->num_rows === 0): ?>
    <div class="kosong">
      <p style="font-size: 18px; margin-bottom: 20px;">Anda belum ada pesanan</p>
      <button onclick="location.href='dashboard.php'" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
        Mulai Pesan Sekarang
      </button>
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
          <?php if (!empty($pesanan['catatan'])): ?>
          <div class="detail-row">
            <span class="label">Catatan Anda:</span>
            <span class="value"><?= htmlspecialchars($pesanan['catatan']) ?></span>
          </div>
          <?php endif; ?>
          <?php if (!empty($pesanan['seller_catatan'])): ?>
          <div class="detail-row">
            <span class="label">Catatan Penjual:</span>
            <span class="value"><?= htmlspecialchars($pesanan['seller_catatan']) ?></span>
          </div>
          <?php endif; ?>
          <div class="detail-row">
            <span class="label">Metode Pembayaran:</span>
            <span class="value"><?= $pesanan['metode_pembayaran'] === 'cod' ? '💵 Bayar di Tempat' : '🏧 Transfer' ?></span>
          </div>
          <div class="detail-row">
            <span class="label">Waktu Pesanan:</span>
            <span class="value"><?= date('d/m/Y H:i', strtotime($pesanan['created_at'])) ?></span>
          </div>
        </div>

        <div class="items-list">
          <strong style="color: #071332;">Detail Menu:</strong>
          <?php
          $stmt_detail = $conn->prepare("SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
          $stmt_detail->bind_param("i", $pesanan['id']);
          $stmt_detail->execute();
          $details = $stmt_detail->get_result();
          while ($detail = $details->fetch_assoc()):
          ?>
            <div class="item">
              <span><?= htmlspecialchars($detail['nama_menu']) ?> x<?= $detail['jumlah'] ?></span>
              <span>Rp <?= number_format($detail['harga'] * $detail['jumlah'], 0, ',', '.') ?></span>
            </div>
          <?php endwhile; ?>
          <div class="total">
            <strong>Total: Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

</div>

<div class="nav">
  <a href="dashboard.php">🏠 Beranda</a>
  <a href="status_pesanan.php">📋 Status Pesanan</a>
</div>

</body>
</html>