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
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Profil Penjual</title>
  <link rel="stylesheet" href="../css/base.css">
  <style>
    .container{max-width:600px;margin:20px auto;padding:20px;}
    .field{margin-bottom:16px;}
    .field label{display:block;margin-bottom:4px;font-weight:600;}
    .field input{width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;}
    .btn{padding:10px 18px;background:var(--primary);color:#fff;border:none;border-radius:6px;cursor:pointer;}
    .message{padding:10px;background:#dafbe1;color:#166534;border-radius:6px; margin-bottom:20px;}
  </style>
</head>
<body>
<div class="header">
  <h1>Profil Penjual</h1>
  <div class="header-actions">
    <span class="user"><?= htmlspecialchars($_SESSION['username']) ?></span>
    <a class="logout" href="../logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

  <div class="profile-info">
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?> <em>(<?= htmlspecialchars($user['role']) ?>)</em></p>
    <p><strong>Kantin ID:</strong> <?= $kantin_id ?> &ndash; <?= htmlspecialchars($kantin['nama']) ?></p>
    <?php if(!empty($kantin['gambar'])): ?>
      <p><img src="<?= htmlspecialchars($kantin['gambar']) ?>" alt="Logo Kantin" style="max-width:120px;border-radius:8px;"></p>
    <?php endif; ?>
    <p><strong>Menu Terdaftar:</strong> <?= $menu_count ?></p>
    <p><strong>Total Pesanan:</strong> <?= $order_count ?></p>
  </div>

  <form method="post">
    <div class="field">
      <label>Ganti Password</label>
      <input type="password" name="new_password" placeholder="Kosongkan jika tidak diubah">
    </div>
    <hr>
    <div class="field">
      <label>Nama Kantin</label>
      <input type="text" name="kantin_name" value="<?= htmlspecialchars($kantin['nama']) ?>">
    </div>
    <div class="field">
      <label>Logo/URL Gambar</label>
      <input type="text" name="kantin_logo" value="<?= htmlspecialchars($kantin['gambar']) ?>">
    </div>
    <button class="btn" type="submit">Simpan Perubahan</button>
  </form>
</div>

<footer class="nav">
  <a href="dashboard.php">Beranda</a>
  <a href="menu.php">Menu</a>
  <a class="active">Profil</a>
</footer>

</body>
</html>