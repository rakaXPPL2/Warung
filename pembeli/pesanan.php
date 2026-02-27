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
</div>

<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border-bottom: 1px solid #e2e8f0;">
  <h3 style="margin: 0;">Menu <?= htmlspecialchars($kantin['nama']) ?></h3>
  <button onclick="location.href='keranjang.php'" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
    🛒 Lihat Keranjang (<?php echo isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0; ?>)
  </button>
</div>

<div class="menu">
  <?php while ($item = $menu_items->fetch_assoc()):
        $flavors = htmlspecialchars($item['flavor_options']);
        $hasSpicy = $item['spicy'] ? '1' : '0';
        $maxLevel = (int)$item['spicy_levels'];
    ?>
  <div class="card" data-kategori="<?= htmlspecialchars($item['kategori']) ?>"
       data-flavors="<?= $flavors ?>" data-spicy="<?= $hasSpicy ?>" data-levels="<?= $maxLevel ?>">
    <?php if ($flavors):
            $list = explode(',', $flavors);
    ?>
    <div class="flavor-options" style="margin:8px 0; font-size:12px; color:var(--muted);">
      Pilih rasa:
      <?php foreach($list as $fl): $ftrim=trim($fl); if($ftrim): ?>
        <label style="margin-right:6px;"><input type="checkbox" value="<?= htmlspecialchars($ftrim) ?>"> <?= htmlspecialchars($ftrim) ?></label>
      <?php endif; endforeach; ?>
    </div>
    <?php endif; ?>
    <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
    <h3><?= htmlspecialchars($item['nama']) ?></h3>
    <?php if ($flavors): ?>
      <div style="font-size:12px;color:var(--muted);">Rasa: <?= nl2br(htmlspecialchars($flavors)) ?></div>
    <?php endif; ?>
    <?php if ($hasSpicy): ?>
      <div style="font-size:12px;color:#dc2626;">Opsional pedas</div>
    <?php endif; ?>
    <p>Harga Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
    <button onclick="tambahKeranjang(this, '<?= htmlspecialchars($item['nama']) ?>', <?= $item['harga'] ?>, <?= $kantin_id ?>)">Pesan</button>
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

function tambahKeranjang(btn, nama, harga, kantinId) {
  // read options from card
  const card = btn.closest('.card');
  const flavors = card.dataset.flavors;
  const hasSpicy = card.dataset.spicy === '1';
  const maxLevel = parseInt(card.dataset.levels) || 0;

  let payload = { nama, harga, kantin_id: kantinId };

  if (flavors) {
    // collect checked boxes
    const boxes = card.querySelectorAll('.flavor-options input[type=checkbox]:checked');
    if (boxes.length) {
      payload.flavor = Array.from(boxes).map(b=>b.value).join(', ');
    }
  }
  if (hasSpicy) {
    if (confirm('Mau pedas?')) {
      let lvl = prompt('Level pedas (1-'+maxLevel+'):', '1');
      lvl = parseInt(lvl);
      if (!isNaN(lvl) && lvl > 0) {
        payload.spicy_level = Math.min(lvl, maxLevel);
      }
    }
  }

  fetch('api/add_keranjang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
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