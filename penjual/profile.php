<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'penjual') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$id = $_SESSION['id'];
$kantin_id = $_SESSION['kantin_id'];

// get current user and kantin info
$stmt = $conn->prepare("SELECT username, password, role FROM db_akun WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM kantin WHERE id = ?");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$kantin = $stmt->get_result()->fetch_assoc();

// additional stats
$menu_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) cnt FROM menu WHERE kantin_id=?");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$menu_count = $stmt->get_result()->fetch_assoc()['cnt'];

$order_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) cnt FROM pesanan WHERE kantin_id=?");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$order_count = $stmt->get_result()->fetch_assoc()['cnt'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update password or kantin details
    if (isset($_POST['new_password']) && trim($_POST['new_password']) !== '') {
        $newpass = trim($_POST['new_password']);
        $stmt2 = $conn->prepare("UPDATE db_akun SET password = ? WHERE id = ?");
        $stmt2->bind_param("si", $newpass, $id);
        $stmt2->execute();
        $message = 'Password diperbarui.';
    }
    if (isset($_POST['kantin_name'])) {
        $newname = trim($_POST['kantin_name']);
        $newlogo = trim($_POST['kantin_logo']);
        $stmt3 = $conn->prepare("UPDATE kantin SET nama = ?, gambar = ? WHERE id = ?");
        $stmt3->bind_param("ssi", $newname, $newlogo, $kantin_id);
        $stmt3->execute();
        $message .= ($message ? ' ' : '') . 'Data kantin diperbarui.';
    }
    // refresh data
    $stmt = $conn->prepare("SELECT * FROM kantin WHERE id = ?");
    $stmt->bind_param("i", $kantin_id);
    $stmt->execute();
    $kantin = $stmt->get_result()->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil Penjual</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      padding-bottom: 80px;
    }
    .header-custom {
      background: white;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .profile-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 30px;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .profile-card:hover {
      transform: translateY(-5px);
    }
    .profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #28a745;
      margin-bottom: 20px;
    }
    .profile-info {
      margin-bottom: 20px;
    }
    .info-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #f8f9fa;
    }
    .info-item:last-child {
      border-bottom: none;
    }
    .info-label {
      font-weight: 600;
      color: #6c757d;
    }
    .info-value {
      font-weight: 500;
      color: #495057;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    .stat-item {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      border-left: 4px solid #28a745;
    }
    .stat-value {
      font-size: 24px;
      font-weight: 700;
      color: #28a745;
      display: block;
    }
    .stat-label {
      font-size: 14px;
      color: #6c757d;
      margin-top: 5px;
    }
    .form-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 30px;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .form-card:hover {
      transform: translateY(-5px);
    }
    .form-control {
      border-radius: 10px;
      border: 1px solid #ddd;
      padding: 10px;
    }
    .btn-success {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      border: none;
      border-radius: 25px;
      padding: 10px 20px;
      font-weight: 600;
    }
    .alert {
      border-radius: 10px;
    }
    .footer-nav {
      background: white;
      border-top: 1px solid #e9ecef;
      padding: 15px 0;
      position: fixed;
      bottom: 0;
      width: 100%;
      z-index: 1000;
    }
    .footer-nav a {
      text-decoration: none;
      color: #6c757d;
      font-weight: 500;
      padding: 10px 20px;
      border-radius: 25px;
      transition: all 0.3s ease;
      margin: 0 5px;
    }
    .footer-nav a.active {
      background: #28a745;
      color: white;
    }
    .footer-nav a:hover {
      background: #28a745;
      color: white;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

<!-- Header -->
<div class="header-custom">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="mb-0">Profil Kantin</h1>
        <small class="text-muted">Kelola informasi kantin Anda</small>
      </div>
      <div class="d-flex align-items-center">
        <span class="me-3">Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-pill">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <?php if ($message): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <!-- Profile Info Card -->
  <div class="profile-card">
    <div class="text-center">
      <?php if (!empty($kantin['gambar'])): ?>
        <img src="<?= htmlspecialchars($kantin['gambar']) ?>" alt="Logo Kantin" class="profile-avatar">
      <?php else: ?>
        <div class="profile-avatar bg-success d-flex align-items-center justify-content-center text-white" style="font-size: 40px;">
          <i class="fas fa-store"></i>
        </div>
      <?php endif; ?>
      <h3 class="mb-1"><?= htmlspecialchars($kantin['nama']) ?></h3>
      <p class="text-muted mb-4">Kantin ID: <?= $kantin_id ?></p>
    </div>

    <div class="profile-info">
      <div class="info-item">
        <span class="info-label">Username:</span>
        <span class="info-value"><?= htmlspecialchars($user['username']) ?> <em>(<?= htmlspecialchars($user['role']) ?>)</em></span>
      </div>
      <div class="info-item">
        <span class="info-label">Nama Kantin:</span>
        <span class="info-value"><?= htmlspecialchars($kantin['nama']) ?></span>
      </div>
      <div class="info-item">
        <span class="info-label">Logo/URL Gambar:</span>
        <span class="info-value">
          <?php if (!empty($kantin['gambar'])): ?>
            <a href="<?= htmlspecialchars($kantin['gambar']) ?>" target="_blank">Lihat Gambar</a>
          <?php else: ?>
            Tidak ada
          <?php endif; ?>
        </span>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-item">
        <span class="stat-value"><?= $menu_count ?></span>
        <div class="stat-label">Menu Terdaftar</div>
      </div>
      <div class="stat-item">
        <span class="stat-value"><?= $order_count ?></span>
        <div class="stat-label">Total Pesanan</div>
      </div>
    </div>

    <div class="text-center">
      <button class="btn btn-success" onclick="document.getElementById('editForm').scrollIntoView({behavior: 'smooth'})">
        <i class="fas fa-edit me-2"></i>Edit Profil
      </button>
    </div>
  </div>

  <!-- Edit Form Card -->
  <div class="form-card" id="editForm">
    <h3 class="mb-4">Edit Profil Kantin</h3>
    <form method="post" class="row g-3">
      <div class="col-12">
        <label class="form-label">Ganti Password</label>
        <input type="password" name="new_password" class="form-control" placeholder="Kosongkan jika tidak diubah">
        <div class="form-text">Biarkan kosong jika tidak ingin mengubah password.</div>
      </div>
      <hr class="my-4">
      <div class="col-12">
        <label class="form-label">Nama Kantin</label>
        <input type="text" name="kantin_name" class="form-control" value="<?= htmlspecialchars($kantin['nama']) ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label">Logo/URL Gambar</label>
        <input type="text" name="kantin_logo" class="form-control" value="<?= htmlspecialchars($kantin['gambar']) ?>" placeholder="URL gambar kantin">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save me-2"></i>Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Footer Navigation -->
<div class="footer-nav">
  <div class="container text-center">
    <a href="dashboard.php">
      <i class="fas fa-home me-1"></i>Beranda
    </a>
    <a href="menu.php">
      <i class="fas fa-utensils me-1"></i>Menu
    </a>
    <a href="profile.php" class="active">
      <i class="fas fa-user me-1"></i>Profil
    </a>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>