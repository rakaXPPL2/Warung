CREATE DATABASE IF NOT EXISTS db_warung;
USE db_warung;

-- Tabel Akun Pengguna
CREATE TABLE IF NOT EXISTS db_akun (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('penjual', 'pembeli', 'kasir') NOT NULL,
    nama_lengkap VARCHAR(100),
    kantin_id INT DEFAULT NULL
);

-- Insert akun default untuk testing
-- contoh beberapa penjual masing-masing memiliki kantin_id
INSERT INTO db_akun (username, password, role, nama_lengkap, kantin_id) VALUES 
('penjual1', 'penjual', 'penjual', 'Penjual Kantin 1', 1),
('penjual2', 'penjual', 'penjual', 'Penjual Kantin 2', 2),
('penjual3', 'penjual', 'penjual', 'Penjual Kantin 3', 3),
('penjual4', 'penjual', 'penjual', 'Penjual Kantin 4', 4),
('pembeli', 'pembeli', 'pembeli', 'Pembeli Test', NULL),
('kasir', 'kasir', 'kasir', 'Kasir Utama', NULL);

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
    catatan VARCHAR(255) DEFAULT NULL,
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
(4, 'Kantin 4', 'Western food dan kopi');

-- Tabel Menu (Agar menu tidak hardcoded di PHP)
CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kantin_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    gambar VARCHAR(255),
    kategori ENUM('makanan', 'minuman', 'snack') NOT NULL,
    flavor_options TEXT DEFAULT NULL,
    spicy TINYINT(1) DEFAULT 0,
    spicy_levels INT DEFAULT 5,
    FOREIGN KEY (kantin_id) REFERENCES kantin(id)
);

-- Seed Data Menu (Memindahkan dari array PHP ke SQL)
-- all images use a common placeholder template URL for convenience
SET @placeholder = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS-Wn0wYYlJX1a8whqKxKiqodeNxNrZ6IpSgQ&s';

INSERT INTO menu (kantin_id, nama, harga, gambar, kategori, flavor_options, spicy, spicy_levels) VALUES
-- Kantin 1
(1, 'Lumpia Basah', 6000, @placeholder, 'makanan', NULL, 0, 5),
(1, 'Perkedel', 3000, @placeholder, 'makanan', NULL, 0, 5),
(1, 'Tahu Goreng', 2500, @placeholder, 'makanan', NULL, 1, 5),
(1, 'Teh Manis', 2000, @placeholder, 'minuman', 'manis,asen', 0, 5),
(1, 'Es Campur', 5000, @placeholder, 'minuman', 'coklat,vanila', 0, 5),
(1, 'Roti Bakar', 4000, @placeholder, 'snack', NULL, 0, 5),
(1, 'Sate Ayam', 8000, @placeholder, 'makanan', NULL, 1, 5),
(1, 'Nasi Uduk', 7000, @placeholder, 'makanan', NULL, 0, 5),
(1, 'Jus Alpukat', 6000, @placeholder, 'minuman', 'manis', 0, 5),
-- Kantin 2
(2, 'Nasi Goreng', 12000, @placeholder, 'makanan', NULL, 1, 5),
(2, 'Mie Goreng', 10000, @placeholder, 'makanan', NULL, 0, 5),
(2, 'Soto Ayam', 12000, @placeholder, 'makanan', NULL, 0, 5),
(2, 'Cibay', 1000, @placeholder, 'minuman', 'cola,orange', 0, 5),
(2, 'Milkshake', 6000, @placeholder, 'minuman', 'vanila,stroberi', 0, 5),
(2, 'Pisang Goreng', 3000, @placeholder, 'snack', NULL, 0, 5),
(2, 'Burger Keju', 11000, @placeholder, 'makanan', NULL, 0, 5),
(2, 'Es Teh', 2000, @placeholder, 'minuman', 'manis', 0, 5),
(2, 'Kentang Goreng', 5000, @placeholder, 'snack', NULL, 0, 5),
-- Kantin 3
(3, 'Bakso Ayam', 8000, @placeholder, 'makanan', NULL, 0, 5),
(3, 'Gado-gado', 5000, @placeholder, 'makanan', NULL, 0, 5),
(3, 'Kare Ayam', 11000, @placeholder, 'makanan', NULL, 1, 5),
(3, 'Jus Jeruk', 4000, @placeholder, 'minuman', 'asgari,manis', 0, 5),
(3, 'Pempek', 7000, @placeholder, 'snack', NULL, 0, 5),
(3, 'Nasi Padang', 13000, @placeholder, 'makanan', NULL, 0, 5),
(3, 'Teh Tarik', 3000, @placeholder, 'minuman', 'manis', 0, 5),
-- Kantin 4
(4, 'Rendang Daging', 15000, @placeholder, 'makanan', NULL, 1, 5),
(4, 'Spaghetti', 13000, @placeholder, 'makanan', NULL, 0, 5),
(4, 'Burger', 10000, @placeholder, 'makanan', NULL, 0, 5),
(4, 'Pizza', 20000, @placeholder, 'makanan', NULL, 0, 5),
(4, 'Ice Cream', 5000, @placeholder, 'snack', NULL, 0, 5);

