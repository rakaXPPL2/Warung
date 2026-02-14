<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header('Location: login.php');
    exit;
}

// Tentukan kantin berdasarkan parameter
$kantin_id = isset($_GET['kantin']) ? (int)$_GET['kantin'] : 1;
if ($kantin_id < 1 || $kantin_id > 6) $kantin_id = 1;

// Data menu untuk setiap kantin
$menu_kantin = [
    1 => [
        'nama' => 'Kantin Bu Rina',
        'items' => [
            ['nama' => 'Lumpia Basah', 'harga' => 6000, 'gambar' => 'assets/lumpia.webp', 'kategori' => 'makanan'],
            ['nama' => 'Perkedel', 'harga' => 3000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Tahu Goreng', 'harga' => 2500, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Teh Manis', 'harga' => 2000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Es Campur', 'harga' => 5000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Roti Bakar', 'harga' => 4000, 'gambar' => 'assets/J.webp', 'kategori' => 'snack'],
        ]
    ],
    2 => [
        'nama' => 'Kantin Pak Budi',
        'items' => [
            ['nama' => 'Nasi Goreng', 'harga' => 12000, 'gambar' => 'assets/nasgor.jpg', 'kategori' => 'makanan'],
            ['nama' => 'Mie Goreng', 'harga' => 10000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Soto Ayam', 'harga' => 12000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Cibay', 'harga' => 1000, 'gambar' => 'assets/cibay.jpg', 'kategori' => 'minuman'],
            ['nama' => 'Milkshake', 'harga' => 6000, 'gambar' => 'assets/milkshake.jpg', 'kategori' => 'minuman'],
            ['nama' => 'Pisang Goreng', 'harga' => 3000, 'gambar' => 'assets/J.webp', 'kategori' => 'snack'],
        ]
    ],
    3 => [
        'nama' => 'Kantin 3',
        'items' => [
            ['nama' => 'Bakso Ayam', 'harga' => 8000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Gado-gado', 'harga' => 5000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Kare Ayam', 'harga' => 11000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Jus Jeruk', 'harga' => 4000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Es Teh', 'harga' => 2500, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Kue Tart', 'harga' => 5000, 'gambar' => 'assets/J.webp', 'kategori' => 'snack'],
        ]
    ],
    4 => [
        'nama' => 'Kantin 4',
        'items' => [
            ['nama' => 'Rendang Daging', 'harga' => 15000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Spaghetti', 'harga' => 13000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Burger', 'harga' => 10000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Kopi Hitam', 'harga' => 3000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Mineral', 'harga' => 2000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Donat', 'harga' => 4000, 'gambar' => 'assets/J.webp', 'kategori' => 'snack'],
        ]
    ],
    5 => [
        'nama' => 'Kantin 5',
        'items' => [
            ['nama' => 'Tongseng', 'harga' => 14000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Capcay', 'harga' => 6000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Tahu Sup', 'harga' => 5000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Sirup Merah', 'harga' => 2500, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Jus Mangga', 'harga' => 5000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Martabak', 'harga' => 6000, 'gambar' => 'assets/J.webp', 'kategori' => 'snack'],
        ]
    ],
    6 => [
        'nama' => 'Kantin 6',
        'items' => [
            ['nama' => 'Ikan Goreng', 'harga' => 12000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Udang Keju', 'harga' => 14000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Soto Banjar', 'harga' => 10000, 'gambar' => 'assets/J.webp', 'kategori' => 'makanan'],
            ['nama' => 'Es Lemon', 'harga' => 3000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Kedondong Muda', 'harga' => 2000, 'gambar' => 'assets/J.webp', 'kategori' => 'minuman'],
            ['nama' => 'Lumpia Goreng', 'harga' => 4000, 'gambar' => 'assets/J.webp', 'kategori' => 'snack'],
        ]
    ]
];

$kantin = $menu_kantin[$kantin_id];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pesanan - <?= htmlspecialchars($kantin['nama']) ?></title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/beranda.css">
</head>
<body>

<div class="header">
  <h1><?= htmlspecialchars($kantin['nama']) ?></h1>
  <div class="header-actions">
    <span class="user">Halo, <?= htmlspecialchars($username) ?></span>
    <a class="logout" href="logout.php">Logout</a>
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
  <button onclick="location.href='pembeli/keranjang.php'" style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
    🛒 Lihat Keranjang (<?php echo isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0; ?>)
  </button>
</div>

<div class="menu">
  <?php foreach ($kantin['items'] as $item): ?>
  <div class="card" data-kategori="<?= htmlspecialchars($item['kategori']) ?>">
    <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
    <h3><?= htmlspecialchars($item['nama']) ?></h3>
    <p>Harga Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
    <button onclick="tambahKeranjang('<?= htmlspecialchars($item['nama']) ?>', <?= $item['harga'] ?>, <?= $kantin_id ?>)">Pesan</button>
  </div>
  <?php endforeach; ?>
</div>

<div class="nav">
  <a href="pembeli/dashboard.php">Beranda</a>
  <a href="status.html">Status Pesanan</a>
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
  fetch('pembeli/api/add_keranjang.php', {
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
