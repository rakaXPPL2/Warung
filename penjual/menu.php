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

// ensure database schema has needed columns (for flavor/spicy options)
$check = $conn->query("SHOW COLUMNS FROM menu LIKE 'flavor_options'");
if ($check && $check->num_rows === 0) {
    // add missing fields gracefully
    $conn->query("ALTER TABLE menu 
        ADD COLUMN flavor_options TEXT DEFAULT NULL,
        ADD COLUMN spicy TINYINT(1) DEFAULT 0,
        ADD COLUMN spicy_levels INT DEFAULT 5");
}

// handle add / delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $nama = trim($_POST['nama']);
        $harga = (int)$_POST['harga'];
        $kategori = $_POST['kategori'];
        $gambar = trim($_POST['gambar']);
        $flavor = trim($_POST['flavor_options'] ?? '');
        $spicy = isset($_POST['spicy']) ? 1 : 0;
        $levels = (int)($_POST['spicy_levels'] ?? 5);

        if ($nama !== '' && $harga > 0 && in_array($kategori, ['makanan','minuman','snack'])) {
            $stmt = $conn->prepare("INSERT INTO menu (kantin_id, nama, harga, gambar, kategori, flavor_options, spicy, spicy_levels) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isisssii", $kantin_id, $nama, $harga, $gambar, $kategori, $flavor, $spicy, $levels);
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
        ['nama'=>'Sample Teh', 'harga'=>3000, 'kategori'=>'minuman', 'flavor_options'=>'manis'],
        ['nama'=>'Sample Snack', 'harga'=>5000, 'kategori'=>'snack'],
    ];
    foreach ($samples as $s) {
        $stmt2 = $conn->prepare("INSERT INTO menu (kantin_id,nama,harga,gambar,kategori,flavor_options,spicy,spicy_levels) VALUES (?,?,?,?,?,?,0,5)");
        $g = $defaultImage;
        $f = $s['flavor_options'] ?? null;
        $stmt2->bind_param("isisss", $kantin_id, $s['nama'], $s['harga'], $g, $s['kategori'], $f);
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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kelola Menu Kantin</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    body { padding-bottom: 100px; }
    .header { display:flex; justify-content: space-between; align-items:center; padding: 16px; background: var(--card); }
    .container { max-width: 800px; margin: 20px auto; }
    .menu-form { display: grid; grid-template-columns: repeat(auto-fit,minmax(140px,1fr)); gap: 8px; margin-bottom: 24px; }
    .menu-form input, .menu-form select, .menu-form textarea { padding: 8px; border:1px solid #ccc; border-radius:6px; }
    .menu-form button { padding: 10px 18px; background: var(--primary); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; }    .item-list { border-collapse: collapse; width:100%; }
    .item-list th, .item-list td { padding: 8px; border:1px solid #e2e8f0; text-align:left; }
    .btn-delete { background:#ff6b6b; color:#fff; padding:4px 8px; border:none; border-radius:4px; cursor:pointer; }
  </style>
</head>
<body>
<div class="header">
  <h1>Menu Kantin #<?= htmlspecialchars($kantin_id) ?></h1>
  <div>
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <h2>Tambahkan Item Baru</h2>
  <form class="menu-form" method="post">
    <input type="hidden" name="action" value="add">
    <input type="text" name="nama" placeholder="Nama menu" required>
    <input type="number" name="harga" placeholder="Harga (Rp)" required>
    <select name="kategori" id="kategori" required onchange="toggleOptions()">
      <option value="makanan">Makanan</option>
      <option value="minuman">Minuman</option>
    </select>
    <input type="text" name="gambar" placeholder="URL gambar (opsional)">
    <div id="flavor-field" style="display:none; margin-top:8px;">
      <label>Opsi Rasa (pisahkan dengan koma)</label>
      <input type="text" name="flavor_options" placeholder="Contoh: strawberry,melon">
    </div>
    <div id="spicy-field" style="display:none; margin-top:8px;">
      <label><input type="checkbox" id="spicy-checkbox" name="spicy" value="1" onclick="toggleSpiceLevel()"> Bisa Pedas?</label>
      <div id="spice-level" style="display:none; margin-top:4px;">
        <label>Level Maksimal:</label>
        <input type="number" name="spicy_levels" min="1" max="10" value="5">
      </div>
    </div>
    <button type="submit">Tambah</button>
  </form>

  <h2>Daftar Menu</h2>
  <?php if ($menus->num_rows === 0): ?>
    <p>Belum ada menu.</p>
  <?php else: ?>
    <table class="item-list">
      <thead>
        <tr><th>ID</th><th>Nama</th><th>Harga</th><th>Kategori</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php while ($row = $menus->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
          <td><?= htmlspecialchars($row['kategori']) ?></td>
          <td>
            <form method="post" style="display:inline;">
              <button class="btn-delete" name="delete" value="<?= $row['id'] ?>">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
function toggleOptions() {
  var cat = document.getElementById('kategori').value;
  document.getElementById('flavor-field').style.display = cat === 'minuman' ? 'block' : 'none';
  document.getElementById('spicy-field').style.display = cat === 'makanan' ? 'block' : 'none';
}
function toggleSpiceLevel() {
  document.getElementById('spice-level').style.display = document.getElementById('spicy-checkbox').checked ? 'block' : 'none';
}
// initialize on load
setTimeout(toggleOptions, 0);
</script>

<footer class="nav">
  <a href="dashboard.php">Beranda</a>
  <a class="active">Menu</a>
  <a href="profile.php">Profil</a>
</footer>

</body>
</html>