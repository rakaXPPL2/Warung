<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// compute total revenue per kantin and amount due
$stmt = $conn->prepare("SELECT kantin_id, SUM(total_harga) AS pendapatan, SUM(CASE WHEN metode_pembayaran='cod' AND status IN ('selesai','diambil') THEN total_harga ELSE 0 END) AS cod_collected FROM pesanan GROUP BY kantin_id");
$stmt->execute();
$reports = $stmt->get_result();
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Keuangan - Kasir</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    .container { max-width: 800px; margin: 20px auto; padding: 20px; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:8px; border:1px solid #e2e8f0; text-align:left; }
  </style>
</head>
<body>
<div class="header">
  <h1>Keuangan</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <table>
    <thead><tr><th>Kantin ID</th><th>Total Pendapatan</th><th>COD Terbayar</th></tr></thead>
    <tbody>
      <?php while($r = $reports->fetch_assoc()): ?>
      <tr>
        <td><?= $r['kantin_id'] ?></td>
        <td>Rp <?= number_format($r['pendapatan'],0,',','.') ?></td>
        <td>Rp <?= number_format($r['cod_collected'],0,',','.') ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<footer class="nav">
  <a href="dashboard.php">Beranda</a>
  <a href="history.php">Riwayat Pesanan</a>
  <a class="active">Kantin</a>
</footer>

</body>
</html>