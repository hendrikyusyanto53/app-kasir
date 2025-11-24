<?php
// api/produk.php
require 'koneksi.php';

try {
    $stmt = $pdo->query("SELECT ProdukID, NamaProduk, Harga, Stok FROM produk ORDER BY NamaProduk ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data produk: ' . $e->getMessage()]);
}
?>