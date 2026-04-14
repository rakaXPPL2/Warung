<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kantin Sekolah SMKN 1 Garut</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/beranda.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg) url('assets/bg-pattern.svg') repeat;
      background-size: 200px;
      color: #0f172a;
      padding-bottom: 0; /* No fixed nav */
    }
    .hero {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 80px 20px;
      text-align: center;
      position: relative;
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      align-items: center;
      gap: 40px;
    }
    .hero h1 {
      font-size: 3rem;
      margin-bottom: 20px;
    }
    .hero p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }
    .hero-text {
      text-align: left;
    }
    .hero-image {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .hero-image img {
      width: 100%;
      max-width: 520px;
      border-radius: 25px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.2);
      object-fit: cover;
    }
    .features {
      padding: 60px 20px;
      background: white;
      text-align: center;
    }
    .features h2 {
      color: var(--primary);
      margin-bottom: 40px;
    }
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .feature-item {
      background: #f8f9fa;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .feature-item i {
      font-size: 3rem;
      color: var(--primary);
      margin-bottom: 20px;
    }
    .about {
      padding: 60px 20px;
      background: var(--bg);
    }
    .about h2 {
      text-align: center;
      color: var(--primary);
      margin-bottom: 40px;
    }
    .about-content {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
    }
    .cta {
      padding: 60px 20px;
      background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
      color: white;
      text-align: center;
    }
    .cta h2 {
      margin-bottom: 20px;
    }
    .cta button {
      background: white;
      color: var(--primary);
      border: none;
      padding: 15px 30px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }
    .cta button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .top-nav {
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 15px;
    }
    .top-nav a {
      color: white;
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 20px;
      background: rgba(255,255,255,0.2);
      transition: background 0.3s;
    }
    .top-nav a:hover {
      background: rgba(255,255,255,0.3);
    }
    @media (max-width: 900px) {
      .hero {
        grid-template-columns: 1fr;
        text-align: center;
      }
      .hero-text {
        text-align: center;
      }
      .hero-image {
        margin-top: 30px;
      }
    }
  </style>
</head>
<body>

  <div class="top-nav">
    <a href="register.php">Sign Up</a>
    <a href="login.php">Log In</a>
  </div>

  <div class="hero">
    <div class="hero-text">
      <h1>Selamat Datang di Kantin Sekolah SMKN 1 Garut</h1>
      <p>Pesan makanan dan minuman favorit Anda dengan mudah dan cepat melalui platform digital kami.</p>
      <p>Kami menghadirkan pengalaman kuliner modern bagi siswa SMKN 1 Garut dengan 4 kantin pilihan, layanan cepat, dan status pesanan real-time.</p>
    </div>
    <div class="hero-image">
      <img src="assets/gambar smkn.jpeg" alt="SMKN 1 Garut">
    </div>
  </div>

  <div class="about">
    <h2>Tentang Kantin SMKN 1 Garut</h2>
    <div class="about-content">
      <p>Kantin SMKN 1 Garut adalah pusat kuliner sekolah yang menyediakan berbagai macam makanan dan minuman berkualitas tinggi. Dengan 4 kantin berbeda, kami menawarkan pilihan dari masakan rumahan hingga western food, semua disiapkan dengan bahan segar dan higienis.</p>
      <p>Sistem pemesanan online kami memudahkan siswa untuk memesan tanpa antri, melacak status pesanan, dan membayar dengan aman. Kami berkomitmen untuk memberikan pengalaman makan yang menyenangkan dan mendukung gaya hidup sehat di lingkungan sekolah.</p>
      <img src="assets/gambar smkn.jpeg" alt="SMKN 1 Garut" style="width: 100%; max-width: 400px; height: auto; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
      <p>Kantin SMKN 1 Garut adalah pusat kuliner sekolah yang menyediakan berbagai macam makanan dan minuman berkualitas tinggi. Dengan 4 kantin berbeda, kami menawarkan pilihan dari masakan rumahan hingga western food, semua disiapkan dengan bahan segar dan higienis.</p>
      <p>Sistem pemesanan online kami memudahkan siswa untuk memesan tanpa antri, melacak status pesanan, dan membayar dengan aman. Kami berkomitmen untuk memberikan pengalaman makan yang menyenangkan dan mendukung gaya hidup sehat di lingkungan sekolah.</p>
  </div>

  <div class="features">
    <h2>Fitur-Fitur Website Kami</h2>
    <div class="feature-grid">
      <div class="feature-item">
        <i class="fas fa-shopping-cart"></i>
        <h3>Pemesanan Online</h3>
        <p>Pesan makanan favorit Anda dari berbagai kantin tanpa perlu antri. Pilih menu, tambahkan ke keranjang, dan selesaikan pesanan dalam hitungan menit.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-list"></i>
        <h3>Status Pesanan Real-time</h3>
        <p>Lacak status pesanan Anda secara real-time. Dapatkan notifikasi ketika pesanan sedang diproses, siap diambil, atau sudah selesai.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-credit-card"></i>
        <h3>Pembayaran Aman</h3>
        <p>Bayar pesanan Anda dengan metode COD (Cash on Delivery) atau pembayaran online yang aman dan terjamin.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-history"></i>
        <h3>Riwayat Pesanan</h3>
        <p>Lihat riwayat pesanan Anda sebelumnya. Mudah untuk memesan ulang menu favorit atau melihat pengeluaran bulanan.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-utensils"></i>
        <h3>Beragam Menu</h3>
        <p>Temukan berbagai menu dari 4 kantin berbeda, mulai dari nasi goreng, sate, hingga pizza dan burger.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-mobile-alt"></i>
        <h3>Akses Mudah</h3>
        <p>Website responsif yang dapat diakses dari desktop, tablet, atau smartphone kapan saja dan di mana saja.</p>
      </div>
    </div>
  </div>

  <div class="cta">
    <h2>Siap Memesan?</h2>
    <p>Mulai pengalaman kuliner digital di SMKN 1 Garut sekarang juga!</p>
    <button onclick="location.href='login.php'">Masuk ke Akun</button>
  </div>

  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>