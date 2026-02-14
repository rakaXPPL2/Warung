CREATE DATABASE IF NOT EXISTS db_warung;
USE db_warung;

-- Tabel Akun Pengguna
CREATE TABLE IF NOT EXISTS db_akun (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('penjual', 'pembeli', 'kasir') NOT NULL,
    nama_lengkap VARCHAR(100)
);

-- Insert akun default untuk testing
INSERT INTO db_akun (username, password, role, nama_lengkap) VALUES 
('penjual', 'penjual', 'penjual', 'Penjual Kantin 1'),
('pembeli', 'pembeli', 'pembeli', 'Pembeli Test'),
('kasir', 'kasir', 'kasir', 'Kasir Utama');

-- Tabel Pesanan
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pembeli_id INT NOT NULL,
    pembeli_nama VARCHAR(50),
    kantin_id INT NOT NULL,
    nomor_antrian VARCHAR(10) UNIQUE,
    status ENUM('pending', 'proses', 'selesai', 'diambil') DEFAULT 'pending',
    metode_pembayaran ENUM('cod', 'online') NOT NULL,
    total_harga INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Detail Pesanan
CREATE TABLE IF NOT EXISTS pesanan_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    nama_menu VARCHAR(100),
    harga INT,
    jumlah INT,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
);

-- Tabel Kantin (Agar daftar kantin dinamis)
CREATE TABLE IF NOT EXISTS kantin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(255),
    gambar VARCHAR(255) DEFAULT '../assets/J.webp'
);

INSERT INTO kantin (id, nama, deskripsi) VALUES 
(1, 'Kantin Bu Rina', 'Masakan rumahan yang lezat'),
(2, 'Kantin Pak Budi', 'Spesialis nasi goreng dan mie'),
(3, 'Kantin 3', 'Aneka jajanan dan minuman'),
(4, 'Kantin 4', 'Western food dan kopi'),
(5, 'Kantin 5', 'Masakan nusantara'),
(6, 'Kantin 6', 'Seafood dan gorengan');

-- Tabel Menu (Agar menu tidak hardcoded di PHP)
CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kantin_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    gambar VARCHAR(255),
    kategori ENUM('makanan', 'minuman', 'snack') NOT NULL,
    FOREIGN KEY (kantin_id) REFERENCES kantin(id)
);

-- Seed Data Menu (Memindahkan dari array PHP ke SQL)
INSERT INTO menu (kantin_id, nama, harga, gambar, kategori) VALUES
-- Kantin 1
(1, 'Lumpia Basah', 6000, '../assets/lumpia.webp', 'makanan'),
(1, 'Perkedel', 3000, '../assets/J.webp', 'makanan'),
(1, 'Tahu Goreng', 2500, '../assets/J.webp', 'makanan'),
(1, 'Teh Manis', 2000, '../assets/J.webp', 'minuman'),
(1, 'Es Campur', 5000, '../assets/J.webp', 'minuman'),
(1, 'Roti Bakar', 4000, '../assets/J.webp', 'snack'),
-- Kantin 2
(2, 'Nasi Goreng', 12000, '../assets/nasgor.jpg', 'makanan'),
(2, 'Mie Goreng', 10000, '../assets/J.webp', 'makanan'),
(2, 'Soto Ayam', 12000, '../assets/J.webp', 'makanan'),
(2, 'Cibay', 1000, '../assets/cibay.jpg', 'minuman'),
(2, 'Milkshake', 6000, '../assets/milkshake.jpg', 'minuman'),
(2, 'Pisang Goreng', 3000, '../assets/J.webp', 'snack'),
-- Kantin 3
(3, 'Bakso Ayam', 8000, '../assets/J.webp', 'makanan'),
(3, 'Gado-gado', 5000, '../assets/J.webp', 'makanan'),
(3, 'Kare Ayam', 11000, '../assets/J.webp', 'makanan'),
(3, 'Jus Jeruk', 4000, '../assets/J.webp', 'minuman'),
-- Kantin 4
(4, 'Rendang Daging', 15000, '../assets/J.webp', 'makanan'),
(4, 'Spaghetti', 13000, '../assets/J.webp', 'makanan'),
(4, 'Burger', 10000, '../assets/J.webp', 'makanan'),
-- Kantin 5
(5, 'Tongseng', 14000, '../assets/J.webp', 'makanan'),
(5, 'Capcay', 6000, '../assets/J.webp', 'makanan'),
-- Kantin 6
(6, 'Ikan Goreng', 12000, '../assets/J.webp', 'makanan'),
(6, 'Udang Keju', 14000, '../assets/J.webp', 'makanan');