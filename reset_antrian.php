<?php
/**
 * Script untuk reset nomor antrian dari 1 dan seterusnya per kantin
 * Jalankan: http://localhost/Warung/reset_antrian.php
 */

include 'db_Warung/db_akun.php';

// Ambil semua kantin
$kantins = $conn->query("SELECT id FROM kantin ORDER BY id ASC");

if ($kantins) {
    $total_updated = 0;
    
    while ($kantin = $kantins->fetch_assoc()) {
        $kantin_id = $kantin['id'];
        
        // Ambil semua pesanan per kantin, diurutkan berdasarkan created_at
        $pesanan = $conn->query("SELECT id FROM pesanan WHERE kantin_id = $kantin_id ORDER BY created_at ASC");
        
        if ($pesanan) {
            $antrian_no = 1;
            while ($p = $pesanan->fetch_assoc()) {
                $pesanan_id = $p['id'];
                // Update nomor_antrian dengan urutan 1, 2, 3, dst
                $conn->query("UPDATE pesanan SET nomor_antrian = '$antrian_no' WHERE id = $pesanan_id");
                $antrian_no++;
                $total_updated++;
            }
            echo "✓ Kantin ID $kantin_id: Reset " . ($antrian_no - 1) . " antrian<br>";
        }
    }
    
    echo "<br><strong>✓ Total " . $total_updated . " antrian berhasil direset!</strong><br>";
    echo "Setiap kantin sekarang memiliki nomor antrian dari 1, 2, 3, dst berdasarkan urutan pesanan.<br>";
    echo "Anda dapat menghapus file ini sekarang.";
} else {
    echo "Error: Tidak dapat mengambil data kantin.";
}

$conn->close();
?>
