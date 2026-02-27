<?php

session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    include 'db_Warung/db_akun.php';
    
    if ($conn) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        // also retrieve kantin_id so we can remember which kantin penjual owns
        $stmt = $conn->prepare("SELECT id, password, kantin_id FROM db_akun WHERE username = ? AND role = ?");
        if (!$stmt) {
            $error = "Query error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $username, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Cek password (support plain text)
                if ($password === $user['password']) {
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    // store kantin_id if available (null for non-penjual)
                    $_SESSION['kantin_id'] = $user['kantin_id'] ?? null;

                    if ($role == 'penjual') {
                        header("Location: penjual/dashboard.php");
                    } elseif ($role == 'kasir') {
                        header("Location: kasir/dashboard.php");
                    } else {
                        header("Location: pembeli/dashboard.php");
                    }
                    exit;
                } else {
                    $error = "Password salah!";
                }
            } else {
                $error = "Username atau role tidak ditemukan!";
            }
            
            $stmt->close();
        }
        $conn->close();
    }
}

$role_selected = $_POST['role'] ?? 'pembeli';

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
      <button type="button" class="role <?= $role_selected === 'pembeli' ? 'active' : '' ?>" onclick="setRole('pembeli', this)">Pembeli</button>
      <button type="button" class="role <?= $role_selected === 'kasir' ? 'active' : '' ?>" onclick="setRole('kasir', this)">Kasir</button>
      <button type="button" class="role <?= $role_selected === 'penjual' ? 'active' : '' ?>" onclick="setRole('penjual', this)">Penjual</button>
    </div>

    <form method="post" autocomplete="off">
      <input type="hidden" name="role" id="role" value="<?= htmlspecialchars($role_selected) ?>">

      <label>Username</label>
      <input type="text" placeholder="Masukkan username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

      <label>Password</label>
      <input type="password" placeholder="Masukkan password" name="password">

      <button class="login-btn" name="login">Login</button>
    </form>
    <p style="margin-top:20px; font-size:14px; text-align:center;">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
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