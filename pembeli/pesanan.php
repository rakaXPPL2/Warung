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
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pesanan - <?= htmlspecialchars($kantin['nama']) ?></title>
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
    .navbar-custom {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .filter-buttons {
      display: flex;
      gap: 10px;
      padding: 16px;
      justify-content: center;
      background: white;
      margin-bottom: 20px;
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .filter-btn {
      padding: 8px 16px;
      border-radius: 20px;
      border: none;
      background: #f8f9fa;
      color: #6c757d;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .filter-btn.active {
      background: #28a745;
      color: white;
    }
    .filter-btn:hover {
      background: #28a745;
      color: white;
      transform: translateY(-2px);
    }
    .menu-header {
      background: white;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      padding: 0 16px;
    }
    .menu-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease;
      text-align: center;
      padding: 20px;
    }
    .menu-card:hover {
      transform: translateY(-5px);
    }
    .menu-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 15px;
    }
    .menu-card h3 {
      color: #28a745;
      font-weight: 600;
      margin-bottom: 10px;
      font-size: 18px;
    }
    .menu-card .spicy-badge {
      background: #dc3545;
      color: white;
      padding: 4px 8px;
      border-radius: 10px;
      font-size: 12px;
      margin-bottom: 10px;
      display: inline-block;
    }
    .menu-card p {
      color: #6c757d;
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 15px;
    }
    .menu-card button {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
    }
    .menu-card button:hover {
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
      transform: translateY(-2px);
    }
    .cart-button {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .cart-button:hover {
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
      transform: translateY(-2px);
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

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="#">
      <i class="fas fa-store me-2"></i><?= htmlspecialchars($kantin['nama']) ?>
    </a>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">
        <i class="fas fa-user-circle me-1"></i>Halo, <?= htmlspecialchars($username) ?>
      </span>
      <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<div class="container my-4">
  <!-- Filter Buttons -->
  <div class="filter-buttons">
    <button class="filter-btn active" onclick="filterMenu('semua')">Semua</button>
    <button class="filter-btn" onclick="filterMenu('makanan')">Makanan</button>
    <button class="filter-btn" onclick="filterMenu('minuman')">Minuman</button>
    <button class="filter-btn" onclick="filterMenu('snack')">Snack</button>
  </div>

  <!-- Menu Header -->
  <div class="menu-header">
    <div>
      <h3 class="mb-0">
        <i class="fas fa-utensils me-2"></i>Menu <?= htmlspecialchars($kantin['nama']) ?>
      </h3>
      <small class="text-muted">Pilih menu favorit Anda</small>
    </div>
    <button class="cart-button" onclick="location.href='keranjang.php'">
      <i class="fas fa-shopping-cart me-2"></i>Lihat Keranjang
      <span class="badge bg-light text-dark ms-2">
        <?php echo isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0; ?>
      </span>
    </button>
  </div>

  <!-- Menu Grid -->
  <div class="menu-grid">
    <?php while ($item = $menu_items->fetch_assoc()):
          $hasSpicy = $item['spicy'] ? '1' : '0';
          $maxLevel = (int)$item['spicy_levels'];
      ?>
      <div class="menu-card" data-kategori="<?= htmlspecialchars($item['kategori']) ?>"
           data-spicy="<?= $hasSpicy ?>" data-levels="<?= $maxLevel ?>">
        <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
        <h3><?= htmlspecialchars($item['nama']) ?></h3>
        <?php if ($hasSpicy): ?>
          <div class="spicy-badge">
            <i class="fas fa-pepper-hot me-1"></i>Opsional Pedas
          </div>
        <?php endif; ?>
        <p>Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
        <button onclick="tambahKeranjang(this, '<?= htmlspecialchars($item['nama']) ?>', <?= $item['harga'] ?>, <?= $kantin_id ?>)">
          <i class="fas fa-plus me-1"></i>Pesan
        </button>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- Footer Navigation -->
<div class="footer-nav">
  <div class="container text-center">
    <a href="dashboard.php">
      <i class="fas fa-home me-1"></i>Beranda
    </a>
    <a href="status_pesanan.php">
      <i class="fas fa-list me-1"></i>Status Pesanan
    </a>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<script>
function filterMenu(kategori) {
  const cards = document.querySelectorAll('.menu-card');
  const buttons = document.querySelectorAll('.filter-btn');

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
  const card = btn.closest('.menu-card');
  const hasSpicy = card.dataset.spicy === '1';
  const maxLevel = parseInt(card.dataset.levels) || 0;

  let payload = { nama, harga, kantin_id: kantinId };

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