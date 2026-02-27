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

// Get pesanan pembeli
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE pembeli_id = ? ORDER BY created_at DESC LIMIT 10");
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
  <title>Status Pesanan</title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/status.css">
</head>
<body>

<div class="header">
  <h1>Status Pesanan</h1>
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
            <span class="label">Catatan:</span>
            <span class="value"><?= htmlspecialchars($pesanan['catatan']) ?></span>
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
          // Get items untuk pesanan ini
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
        </div>

        <div class="total-harga">Total: Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></div>

        <?php if ($pesanan['status'] === 'selesai'): ?>
          <div style="background: #dcfce7; padding: 12px; border-radius: 8px; margin-top: 15px; color: #166534; text-align: center; font-weight: bold;">
            ✅ Pesanan Anda siap! Tunjukkan nomor antrian ke kasir
          </div>
        <?php elseif ($pesanan['status'] === 'diambil'): ?>
          <div style="background: #f3e8ff; padding: 12px; border-radius: 8px; margin-top: 15px; color: #581c87; text-align: center; font-weight: bold;">
            🎉 Pesanan telah diambil, terima kasih!
          </div>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

</div>

<div class="nav">
  <a href="dashboard.php">Beranda</a>
  <a class="active">Status Pesanan</a>
</div>

</body>
</html>
