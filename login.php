<?php
    include "db_Warung/db_akun.php";

    if(isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM db_akun where 
        username='$username' AND password='$password' 
        ";
        $result = $db->query($sql);

        if($result->num_rows > 0) {

            header("location: dashboard.php");
        }else {
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
      <button class="role active">Pembeli</button>
      <button class="role">Kasir</button>
      <button class="role">Penjual</button>
    </div>

    <form method="post">
      <label>Username</label>
      <input type="text" placeholder="Masukkan username" name="username">

      <label>Password</label>
      <input type="password" placeholder="Masukkan password" name="password">

      <button class="login-btn" name="login">Login</button>
    </form>

  </div>
</div>
</body>
</html>