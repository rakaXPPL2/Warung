<?php
include "db_Warung/db_akun.php";
session_start();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role     = $_POST['role'];

    $sql = "SELECT * FROM db_akun 
            WHERE username='$username' 
            AND password='$password'
            AND role='$role'";

    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        if ($role == 'pembeli') {
            header("Location: pembeli/dashboard.php");
        } elseif ($role == 'kasir') {
            header("Location: kasir/dashboard.php");
        } elseif ($role == 'penjual') {
            header("Location: penjual/dashboard.php");
        }
        exit;
    } else {
        echo "<script>alert('Login gagal');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login Kantin</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">

    <h2>Login Kantin</h2>
    <p class="subtitle">Pilih peran lalu masuk</p>
    <div class="role-select">
      <button type="button" class="role active" onclick="setRole('pembeli', this)">Pembeli</button>
      <button type="button" class="role" onclick="setRole('kasir', this)">Kasir</button>
      <button type="button" class="role" onclick="setRole('penjual', this)">Penjual</button>
    </div>

    <form method="post">
      <input type="hidden" name="role" id="role" value="pembeli">
      <label>Username</label>
      <input type="text" placeholder="Masukkan username" name="username">

      <label>Password</label>
      <input type="password" placeholder="Masukkan password" name="password">

      <button class="login-btn" name="login">Login</button>
    </form>

  </div>
</div>
    <script>
    function setRole(role, btn) {
      document.getElementById("role").value = role;

      document.querySelectorAll(".role").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
    }
    </script>

</body>
</html>