<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'penjual') {
    header('Location: ../login.php');
    exit;
}

include '../db_Warung/db_akun.php';

$kantin_id = $_SESSION['kantin_id'] ?? null;
if (!$kantin_id) {
    echo "Kantin tidak ditemukan. Silakan login ulang.";
    exit;
}

// update pesanan status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesanan_id'], $_POST['status'])) {
    $pid = (int)$_POST['pesanan_id'];
    $st = $_POST['status'];
    $stmt = $conn->prepare("UPDATE pesanan SET status=? WHERE id=? AND kantin_id=?");
    $stmt->bind_param("sii", $st, $pid, $kantin_id);
    $stmt->execute();
}

// stats (total orders + income across relevant statuses)
$stmt = $conn->prepare("SELECT COUNT(*) cnt, SUM(total_harga) tot FROM pesanan WHERE kantin_id=? AND status IN ('proses','selesai','diambil')");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$order_count = $stats['cnt'] ?? 0;
$total_income = $stats['tot'] ?? 0;

// today orders
$stmt = $conn->prepare("SELECT COUNT(*) cnt FROM pesanan WHERE kantin_id=? AND DATE(created_at)=CURDATE()");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$today_count = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// week orders (ISO week)
$stmt = $conn->prepare("SELECT COUNT(*) cnt FROM pesanan WHERE kantin_id=? AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$week_count = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// you could extend this query later for per-status or per-day breakdown

// fetch orders
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE kantin_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $kantin_id);
$stmt->execute();
$orders = $stmt->get_result();

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard Penjual</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    body{padding-bottom:120px;background:var(--bg);}
    .header{background:var(--card);padding:16px 20px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 18px var(--shadow);}
    .stats{padding:16px 20px;display:flex;gap:16px;justify-content:center;}
    .stats .card{width:160px;height:120px;background:var(--card);border:1px solid #e2e8f0;border-radius:8px;display:flex;flex-direction:column;justify-content:center;align-items:center;position:relative;}
    .stats .label{font-size:14px;text-align:center;margin-bottom:6px;}
    .stats .value{font-size:22px;font-weight:700;}
    .stats .yellow::after,.stats .red::after,.stats .green::after{content:'';position:absolute;bottom:0;left:0;width:100%;height:4px;}
    .stats .yellow::after{background:yellow;}
    .stats .red::after{background:red;}
    .stats .green::after{background:green;}
    .orders{max-width:1000px;margin:20px auto;padding:0 20px;}
    .order-card{background:var(--card);border:1px solid #f3f4f6;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 6px rgba(0,0,0,0.05);}
    .order-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
    .order-header .number{font-size:24px;font-weight:700;color:var(--primary);}
    .status-badge{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;}
    .status-pending{background:#fef3c7;color:#92400e;}
    .status-proses{background:#dbeafe;color:#1e40af;}
    .status-selesai{background:#dcfce7;color:#166534;}
    .status-diambil{background:#f3e8ff;color:#581c87;}
    .detail-row{display:flex;justify-content:space-between;padding:6px 0;font-size:14px;}
    .items{background:#f8fafc;padding:12px;border-radius:8px;margin:12px 0;font-size:13px;}
    .items div{display:flex;justify-content:space-between;padding:4px 0;}
    .actions{margin-top:10px;}
    .btn{padding:6px 14px;border:none;border-radius:6px;cursor:pointer;font-weight:600;}
    .btn-proses{background:#dbeafe;color:#1e40af;}
    .btn-selesai{background:#dcfce7;color:#166534;}
    .btn-ambil{background:#f3e8ff;color:#581c87;}
    .no-orders{color:var(--muted);text-align:center;padding:40px;}
  </style>
</head>
<body>

<div class="header">
  <h1>Kantin <?= $kantin_id ?></h1>
  <div><span class="user">Halo, <?= htmlspecialchars($username) ?></span> <a class="logout" href="../logout.php">Logout</a></div>
</div>

<div class="stats">
  <div class="card yellow">
    <div class="label">Total Pesanan (Hari Ini)</div>
    <div class="value"><?= $today_count ?></div>
  </div>
  <div class="card red">
    <div class="label">Total Pesanan (Minggu Ini)</div>
    <div class="value"><?= $week_count ?></div>
  </div>
  <div class="card green">
    <div class="label">Total Uang</div>
    <div class="value">Rp <?= number_format($total_income,0,',','.') ?></div>
  </div>
</div>

<div class="orders">
  <?php if ($orders->num_rows===0): ?>
    <p class="no-orders">Tidak ada pesanan.</p>
  <?php else: ?>
    <?php while($p=$orders->fetch_assoc()): ?>
      <div class="order-card">
        <div class="order-header">
          <div class="number"><?= htmlspecialchars($p['nomor_antrian']) ?></div>
          <span class="status-badge status-<?= $p['status'] ?>"><?php
            $m=['pending'=>'⏳ Menunggu','proses'=>'👨‍🍳 Diproses','selesai'=>'✅ Siap Diambil','diambil'=>'🎉 Selesai'];
            echo $m[$p['status']];
          ?></span>
        </div>
        <div class="detail-row"><span>Pembeli:</span><strong><?= htmlspecialchars($p['pembeli_nama']) ?></strong></div>
        <?php if(!empty($p['catatan'])): ?>
        <div class="detail-row"><span>Catatan:</span><strong><?= htmlspecialchars($p['catatan']) ?></strong></div>
        <?php endif; ?>
        <div class="detail-row"><span>Metode:</span><strong><?= $p['metode_pembayaran']==='cod'?'💵 COD':'🏧 Online' ?></strong></div>
        <div class="detail-row"><span>Waktu:</span><strong><?= date('d/m/Y H:i',strtotime($p['created_at'])) ?></strong></div>
        <div class="items">
          <?php
          $stm = $conn->prepare("SELECT * FROM pesanan_detail WHERE pesanan_id=?");
          $stm->bind_param("i",$p['id']);$stm->execute();$its=$stm->get_result();
          while($it=$its->fetch_assoc()): ?>
            <div><span><?= htmlspecialchars($it['nama_menu']) ?> x<?= $it['jumlah'] ?></span><span>Rp <?= number_format($it['harga']*$it['jumlah'],0,',','.') ?></span></div>
          <?php endwhile; ?>
        </div>
        <div class="actions">
          <?php if($p['status']==='pending'): ?><form method="post"><input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>"><input type="hidden" name="status" value="proses"><button class="btn btn-proses">Mulai</button></form><?php endif; ?>
          <?php if($p['status']==='proses'): ?><form method="post"><input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>"><input type="hidden" name="status" value="selesai"><button class="btn btn-selesai">Selesai</button></form><?php endif; ?>
          <?php if($p['status']==='selesai'): ?><form method="post"><input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>"><input type="hidden" name="status" value="diambil"><button class="btn btn-ambil">Diambil</button></form><?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<footer class="nav">
  <a class="active">Beranda</a>
  <a href="menu.php">Menu</a>
  <a href="profile.php">Profil</a>
</footer>

</body>
</html>
