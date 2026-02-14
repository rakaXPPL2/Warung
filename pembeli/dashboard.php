<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}
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
  <div class="card">
    <img src="../assets/J.webp" alt="kantin-1">
    <h3>Kantin 1</h3>
    <p>Kantin Bu Rina</p>
    <button onclick="location.href='../pesanan.php?kantin=1'">Pesan</button>
  </div>

  <div class="card">
    <img src="../assets/J.webp" alt="kantin-2">
    <h3>Kantin 2</h3>
    <p>Kantin Pak Budi</p>
    <button onclick="location.href='../pesanan.php?kantin=2'">Pesan</button>
  </div>

  <div class="card">
    <img src="../assets/J.webp" alt="kantin-3">
    <h3>Kantin 3</h3>
    <p>Kantin Pak Budi</p>
    <button onclick="location.href='../pesanan.php?kantin=3'">Pesan</button>
  </div>

  <div class="card">
    <img src="../assets/J.webp" alt="kantin-4">
    <h3>Kantin 4</h3>
    <p>Kantin Pak Budi</p>
    <button onclick="location.href='../pesanan.php?kantin=4'">Pesan</button>
  </div>

  <div class="card">
    <img src="../assets/J.webp" alt="kantin-5">
    <h3>Kantin 5</h3>
    <p>Kantin Pak Budi</p>
    <button onclick="location.href='../pesanan.php?kantin=5'">Pesan</button>
  </div>

  <div class="card">
    <img src="../assets/J.webp" alt="kantin-6">
    <h3>Kantin 6</h3>
    <p>Kantin Pak Budi</p>
    <button onclick="location.href='../pesanan.php?kantin=6'">Pesan</button>
  </div>
</div>

<div class="nav">
  <a class="active">Beranda</a>
  <a href="../status.html">Status Pesanan</a>
</div>

</body>
</html>