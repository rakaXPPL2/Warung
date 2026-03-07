<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'penjual') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$kantin_id = $_SESSION['kantin_id'] ?? null;
if (!$kantin_id) {
    echo "Kantin belum ditentukan. Silakan login ulang atau hubungi admin.";
    exit;
}

// ensure database schema has needed columns (spicy options) and remove obsolete flavor column
$checkSpicy = $conn->query("SHOW COLUMNS FROM menu LIKE 'spicy'");
if ($checkSpicy && $checkSpicy->num_rows === 0) {
    $conn->query("ALTER TABLE menu ADD COLUMN spicy TINYINT(1) DEFAULT 0, ADD COLUMN spicy_levels INT DEFAULT 5");
}
// drop flavor column if still present
if ($conn->query("SHOW COLUMNS FROM menu LIKE 'flavor_options'")->num_rows) {
    $conn->query("ALTER TABLE menu DROP COLUMN flavor_options");
}

// handle add / delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $nama = trim($_POST['nama']);
        $harga = (int)$_POST['harga'];
        $kategori = $_POST['kategori'];
        $gambar = trim($_POST['gambar']);
        $spicy = isset($_POST['spicy']) ? 1 : 0;
        $levels = (int)($_POST['spicy_levels'] ?? 5);

        if ($nama !== '' && $harga > 0 && in_array($kategori, ['makanan','minuman','snack'])) {
            $stmt = $conn->prepare("INSERT INTO menu (kantin_id, nama, harga, gambar, kategori, spicy, spicy_levels) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isissii", $kantin_id, $nama, $harga, $gambar, $kategori, $spicy, $levels);
            $stmt->execute();
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['delete'];
        $stmt = $conn->prepare("DELETE FROM menu WHERE id = ? AND kantin_id = ?");
        $stmt->bind_param("ii", $id, $kantin_id);
        $stmt->execute();
    }
}

// fetch current menu
$stmt = $conn->prepare("SELECT * FROM menu WHERE kantin_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$menus = $stmt->get_result();
$username = $_SESSION['username'];

// if no menu items yet, auto-seed some sample entries
if ($menus->num_rows === 0) {
    $samples = [
        ['nama'=>'Sample Nasi', 'harga'=>8000, 'kategori'=>'makanan'],
        ['nama'=>'Sample Mie', 'harga'=>7000, 'kategori'=>'makanan'],
        ['nama'=>'Sample Teh', 'harga'=>3000, 'kategori'=>'minuman'],
        ['nama'=>'Sample Snack', 'harga'=>5000, 'kategori'=>'snack'],
    ];
    foreach ($samples as $s) {
        $stmt2 = $conn->prepare("INSERT INTO menu (kantin_id,nama,harga,gambar,kategori,spicy,spicy_levels) VALUES (?,?,?,?,?,0,5)");
        $g = $defaultImage;
        $stmt2->bind_param("isiss", $kantin_id, $s['nama'], $s['harga'], $g, $s['kategori']);
        $stmt2->execute();
    }
    // re-fetch after seeding
    $stmt->execute();
    $menus = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Menu Kantin</title>
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
    .form-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 20px;
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
    .table-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .table-card:hover {
      transform: translateY(-5px);
    }
    .table {
      margin-bottom: 0;
    }
    .table th {
      background: #28a745;
      color: white;
      border: none;
      padding: 15px;
    }
    .table td {
      padding: 15px;
      border: none;
      vertical-align: middle;
    }
    .table-hover tbody tr:hover {
      background-color: #f8f9fa;
    }
    .btn-danger {
      border-radius: 20px;
      padding: 5px 15px;
      font-size: 12px;
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
        <h1 class="mb-0">Menu Kantin</h1>
        <small class="text-muted">Kelola menu kantin Anda</small>
      </div>
      <div class="d-flex align-items-center">
        <span class="me-3">Halo, <?= htmlspecialchars($username) ?></span>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-pill">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <!-- Add Menu Form -->
  <div class="form-card">
    <h3 class="mb-4">Tambah Menu Baru</h3>
    <form method="post" class="row g-3">
      <input type="hidden" name="action" value="add">
      <div class="col-md-6">
        <label class="form-label">Nama Menu</label>
        <input type="text" name="nama" class="form-control" placeholder="Nama menu" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Harga (Rp)</label>
        <input type="number" name="harga" class="form-control" placeholder="Harga" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Kategori</label>
        <select name="kategori" class="form-select" required onchange="toggleOptions()">
          <option value="makanan">Makanan</option>
          <option value="minuman">Minuman</option>
          <option value="snack">Snack</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">URL Gambar (Opsional)</label>
        <input type="text" name="gambar" class="form-control" placeholder="URL gambar">
      </div>
      <div class="col-12" id="spicy-field" style="display:none;">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="spicy-checkbox" name="spicy" value="1" onclick="toggleSpiceLevel()">
          <label class="form-check-label" for="spicy-checkbox">
            Bisa Pedas?
          </label>
        </div>
        <div id="spice-level" style="display:none; margin-top:10px;">
          <label class="form-label">Level Maksimal:</label>
          <input type="number" name="spicy_levels" class="form-control" min="1" max="10" value="5">
        </div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-success">Tambah Menu</button>
      </div>
    </form>
  </div>

  <!-- Menu List -->
  <div class="table-card">
    <h3 class="p-3 mb-0">Daftar Menu</h3>
    <?php if ($menus->num_rows === 0): ?>
      <div class="text-center p-5">
        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
        <h5>Belum ada menu</h5>
        <p>Tambah menu pertama Anda di atas.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Harga</th>
              <th>Kategori</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $menus->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kategori']) ?></span></td>
                <td>
                  <form method="post" class="d-inline">
                    <button class="btn btn-danger btn-sm" name="delete" value="<?= $row['id'] ?>" onclick="return confirm('Hapus menu ini?')">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Footer Navigation -->
<div class="footer-nav">
  <div class="container text-center">
    <a href="dashboard.php">
      <i class="fas fa-home me-1"></i>Beranda
    </a>
    <a href="menu.php" class="active">
      <i class="fas fa-utensils me-1"></i>Menu
    </a>
    <a href="profile.php">
      <i class="fas fa-user me-1"></i>Profil
    </a>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<script>
function toggleOptions() {
  var cat = document.querySelector('select[name="kategori"]').value;
  document.getElementById('spicy-field').style.display = cat === 'makanan' ? 'block' : 'none';
}
function toggleSpiceLevel() {
  document.getElementById('spice-level').style.display = document.getElementById('spicy-checkbox').checked ? 'block' : 'none';
}
// initialize on load
setTimeout(toggleOptions, 0);
</script>

</body>
</html>