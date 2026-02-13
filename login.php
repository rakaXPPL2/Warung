<?php
include "db_Warung/db_akun.php";
session_start();

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'pembeli';

    if ($username === '' || $password === '') {
        $error = 'Isi username dan password.';
    } else {
        $stmt = $db->prepare("SELECT username, password, role FROM db_akun WHERE username = ? AND role = ? LIMIT 1");
        $stmt->bind_param('ss', $username, $role);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $hash = $row['password'];

            // Support both hashed passwords and legacy plain-text
            if ((function_exists('password_verify') && password_verify($password, $hash)) || $password === $hash) {
                session_regenerate_id(true);
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                header('Location: ' . $row['role'] . '/dashboard.php');
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } else {
            $error = 'Akun tidak ditemukan untuk peran yang dipilih.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login Kantin</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">

    <h2>Login Kantin</h2>
    <p class="subtitle">Pilih peran lalu masuk</p>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="role-select">
      <button type="button" class="role <?= (isset($_POST['role']) && $_POST['role']==='pembeli') ? 'active' : 'active' ?>" onclick="setRole('pembeli', this)">Pembeli</button>
      <button type="button" class="role <?= (isset($_POST['role']) && $_POST['role']==='kasir') ? 'active' : '' ?>" onclick="setRole('kasir', this)">Kasir</button>
      <button type="button" class="role <?= (isset($_POST['role']) && $_POST['role']==='penjual') ? 'active' : '' ?>" onclick="setRole('penjual', this)">Penjual</button>
    </div>

    <form method="post" autocomplete="off">
      <input type="hidden" name="role" id="role" value="<?= htmlspecialchars($_POST['role'] ?? 'pembeli') ?>">

      <label>Username</label>
      <input type="text" placeholder="Masukkan username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

      <label>Password</label>
      <input type="password" placeholder="Masukkan password" name="password">

      <button class="login-btn" name="login">Login</button>
    </form>

  </div>
</div>
<script>
function setRole(role, btn) {
  document.getElementById('role').value = role;
  document.querySelectorAll('.role').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>
</body>
</html>