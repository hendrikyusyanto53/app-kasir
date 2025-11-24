<?php
// api/pelanggan.php
require 'koneksi.php';
try {
    $stmt = $pdo->query("SELECT PelangganID, NamaPelanggan, Alamat, NomorTelepon FROM pelanggan ORDER BY NamaPelanggan ASC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Bungkus data untuk kompatibilitas DataTables
    echo json_encode(['data' => $customers]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data pelanggan: ' . $e->getMessage()]);
}
?>