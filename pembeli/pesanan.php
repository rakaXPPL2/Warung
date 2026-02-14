<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

// Tentukan kantin berdasarkan parameter
$kantin_id = isset($_GET['kantin']) ? (int)$_GET['kantin'] : 1;

// Ambil Info Kantin
$stmt = $conn->prepare("SELECT * FROM kantin WHERE id = ?");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$kantin = $stmt->get_result()->fetch_assoc();

if (!$kantin) {
    echo "Kantin tidak ditemukan";
    exit;
}

// Ambil Menu Kantin dari Database
$stmt_menu = $conn->prepare("SELECT * FROM menu WHERE kantin_id = ?");
$stmt_menu->bind_param("i", $kantin_id);
$stmt_menu->execute();
$menu_items = $stmt_menu->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pesanan - <?= htmlspecialchars($kantin['nama']) ?></title>
  <link rel="stylesheet" href="../css/base.css">
  <link rel="stylesheet" href="../css/beranda.css">
</head>
<body>

<div class="header">
  <h1><?= htmlspecialchars($kantin['nama']) ?></h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="filter">
  <button class="active" onclick="filterMenu('semua')">Semua</button>
  <button onclick="filterMenu('makanan')">Makanan</button>
  <button onclick="filterMenu('minuman')">Minuman</button>
  <button onclick="filterMenu('snack')">Snack</button>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border-bottom: 1px solid #e2e8f0;">
  <h3 style="margin: 0;">Menu <?= htmlspecialchars($kantin['nama']) ?></h3>
  <button onclick="location.href='keranjang.php'" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
    🛒 Lihat Keranjang (<?php echo isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0; ?>)
  </button>
</div>

<div class="menu">
  <?php while ($item = $menu_items->fetch_assoc()): ?>
  <div class="card" data-kategori="<?= htmlspecialchars($item['kategori']) ?>">
    <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
    <h3><?= htmlspecialchars($item['nama']) ?></h3>
    <p>Harga Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
    <button onclick="tambahKeranjang('<?= htmlspecialchars($item['nama']) ?>', <?= $item['harga'] ?>, <?= $kantin_id ?>)">Pesan</button>
  </div>
  <?php endwhile; ?>
</div>

<div class="nav">
  <a href="dashboard.php">Beranda</a>
  <a href="status_pesanan.php">Status Pesanan</a>
</div>

<script>
function filterMenu(kategori) {
  const cards = document.querySelectorAll('.card');
  const buttons = document.querySelectorAll('.filter button');
  
  buttons.forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  
  cards.forEach(card => {
    if (kategori === 'semua') {
      card.style.display = '';
    } else {
      card.style.display = card.dataset.kategori === kategori ? '' : 'none';
    }
  });
}

function tambahKeranjang(nama, harga, kantinId) {
  fetch('api/add_keranjang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
      nama: nama, 
      harga: harga, 
      kantin_id: kantinId 
    })
  })
  .then(res => res.json())
  .then(data => {
    alert('✅ ' + nama + ' ditambahkan ke keranjang!');
    location.reload(); // Reload untuk update keranjang counter
  })
  .catch(err => alert('Gagal menambahkan ke keranjang'));
}
</script>

</body>
</html>