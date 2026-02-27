<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    include 'db_Warung/db_akun.php';

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'];
    $namaKantin = trim($_POST['nama_kantin'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        // cek username sudah ada
        $stmt = $conn->prepare("SELECT id FROM db_akun WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Username sudah digunakan.';
        } else {
            $kantin_id = null;
            if ($role === 'penjual') {
                if ($namaKantin === '') {
                    $error = 'Silakan isi nama kantin untuk peran penjual.';
                } else {
                    // buat entri kantin baru
                    $stmt2 = $conn->prepare("INSERT INTO kantin (nama, deskripsi) VALUES (?, ?)");
                    $desc = '';
                    $stmt2->bind_param("ss", $namaKantin, $desc);
                    $stmt2->execute();
                    $kantin_id = $conn->insert_id;
                }
            }

            if ($error === '') {
                $stmt3 = $conn->prepare("INSERT INTO db_akun (username, password, role, nama_lengkap, kantin_id) VALUES (?, ?, ?, ?, ?)");
                $namaLengkap = $username;
                $stmt3->bind_param("ssssi", $username, $password, $role, $namaLengkap, $kantin_id);
                $stmt3->execute();
                header('Location: login.php');
                exit;
            }
        }
    }
}

// default pilihan
$selectedRole = $_POST['role'] ?? 'pembeli';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Kantin</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">

    <h2>Registrasi Akun</h2>
    <p class="subtitle">Pilih peran lalu buat akun</p>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="role-select">
      <button type="button" class="role <?= $selectedRole === 'pembeli' ? 'active' : '' ?>" onclick="setRole('pembeli', this)">Pembeli</button>
      <button type="button" class="role <?= $selectedRole === 'kasir' ? 'active' : '' ?>" onclick="setRole('kasir', this)">Kasir</button>
      <button type="button" class="role <?= $selectedRole === 'penjual' ? 'active' : '' ?>" onclick="setRole('penjual', this)">Penjual</button>
    </div>

    <form method="post" autocomplete="off">
      <input type="hidden" name="role" id="role" value="<?= htmlspecialchars($selectedRole) ?>">

      <label>Username</label>
      <input type="text" placeholder="Masukkan username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

      <label>Password</label>
      <input type="password" placeholder="Masukkan password" name="password">

      <div id="kantin-field" style="display: <?= $selectedRole === 'penjual' ? 'block' : 'none' ?>; margin-top:10px;">
        <label>Nama Kantin (hanya untuk penjual)</label>
        <input type="text" placeholder="Contoh: Kantin Bu Rina" name="nama_kantin" value="<?= htmlspecialchars($_POST['nama_kantin'] ?? '') ?>">
      </div>

      <button class="login-btn" name="register">Daftar</button>
    </form>

    <p style="margin-top:20px; font-size:14px; text-align:center;">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>

  </div>
</div>
<script>
function setRole(role, btn) {
  document.getElementById('role').value = role;
  document.querySelectorAll('.role').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('kantin-field').style.display = role === 'penjual' ? 'block' : 'none';
}
</script>
</body>
</html>