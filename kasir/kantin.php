<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// fetch kantin list with total earnings
$query = "SELECT k.id, k.nama, k.gambar, COALESCE(SUM(p.total_harga),0) AS pendapatan
          FROM kantin k
          LEFT JOIN pesanan p ON p.kantin_id = k.id
          GROUP BY k.id";
$result = $conn->query($query);

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kantin - Kasir</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    .container { max-width:800px; margin:20px auto; padding:20px; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:8px; border:1px solid #e2e8f0; text-align:left; }
    .kantin-img { max-width:60px; border-radius:6px; }
  </style>
</head>
<body>
<div class="header">
  <h1>Daftar Kantin</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <?php if ($result->num_rows === 0): ?>
    <p>Tidak ada kantin terdaftar.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>ID</th><th>Nama Kantin</th><th>Gambar</th><th>Total Penghasilan</th></tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?php if($row['gambar']): ?><img src="<?= htmlspecialchars($row['gambar']) ?>" class="kantin-img"><?php endif; ?></td>
          <td>Rp <?= number_format($row['pendapatan'],0,',','.') ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<footer class="nav">
  <a href="dashboard.php">Beranda</a>
  <a href="history.php">Riwayat Pesanan</a>
  <a class="active">Kantin</a>
</footer>

</body>
</html>