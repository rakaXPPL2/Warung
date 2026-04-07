<?php
/**
 * Script untuk mengurutkan menu_order dari semua menu yang sudah ada
 * Jalankan script ini: http://localhost/Warung/fix_menu_order.php
 */

include 'db_Warung/db_akun.php';

// Ambil semua kantin
$kantinResult = $conn->query("SELECT id FROM kantin ORDER BY id ASC");

if ($kantinResult) {
    $totalUpdated = 0;
    
    while ($kantin = $kantinResult->fetch_assoc()) {
        $kantin_id = $kantin['id'];
        
        // Ambil semua menu untuk kantin ini, diurutkan berdasarkan id (data lama dijaga)
        $menuResult = $conn->query("SELECT id FROM menu WHERE kantin_id = $kantin_id ORDER BY id ASC");
        
        if ($menuResult) {
            $menuOrder = 1;
            while ($menu = $menuResult->fetch_assoc()) {
                $menu_id = $menu['id'];
                // Update menu_order untuk setiap menu
                $conn->query("UPDATE menu SET menu_order = $menuOrder WHERE id = $menu_id");
                $menuOrder++;
                $totalUpdated++;
            }
            echo "✓ Kantin ID $kantin_id: Updated " . ($menuOrder - 1) . " menus<br>";
        }
    }
    echo "<br><strong>✓ Total " . $totalUpdated . " menu telah diurutkan!</strong><br>";
    echo "Semua menu dari setiap kantin sekarang memiliki nomor urut yang benar (1, 2, 3, dst).<br>";
    echo "Data menu lama tetap dipertahankan, hanya menu_order yang diupdate.<br>";
    echo "Anda dapat menghapus file ini sekarang.";
} else {
    echo "Error: Tidak dapat mengambil data kantin.";
}

$conn->close();
?>

