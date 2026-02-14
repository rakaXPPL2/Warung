<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Ambil data kantin dari database (Professional Approach)
$result = $conn->query("SELECT * FROM kantin");
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kantin Sekolah - Pembeli</title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/beranda.css">
</head>
<body>

<div class="header">
  <h1>Kantin Sekolah</h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="menu">
  <?php while($kantin = $result->fetch_assoc()): ?>
    <div class="card">
      <img src="<?= htmlspecialchars($kantin['gambar']) ?>" alt="<?= htmlspecialchars($kantin['nama']) ?>">
      <h3><?= htmlspecialchars($kantin['nama']) ?></h3>
      <p><?= htmlspecialchars($kantin['deskripsi']) ?></p>
      <button onclick="location.href='pesanan.php?kantin=<?= $kantin['id'] ?>'">Pesan</button>
    </div>
  <?php endwhile; ?>
</div>

<div class="nav">
  <a class="active">Beranda</a>
  <a href="status_pesanan.php">Status Pesanan</a>
</div>

</body>
</html>