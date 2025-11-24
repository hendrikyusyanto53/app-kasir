<?php
// api/penjualan.php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Metode request tidak valid.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['items']) || empty($data['items'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Data penjualan tidak lengkap.']);
    exit;
}

$pelangganID = $data['pelangganID'] ?? null;
$items = $data['items'];
$uangBayar = $data['payment'] ?? 0;
$kembalian = $data['change'] ?? 0;

// Hitung total harga dari server-side untuk keamanan
$totalHarga = array_sum(array_column($items, 'subtotal'));

try {
    // Mulai transaksi
    $pdo->beginTransaction();

    // 1. Masukkan data ke tabel 'penjualan'
    $stmt = $pdo->prepare(
        "INSERT INTO penjualan (TanggalPenjualan, TotalHarga, UangBayar, Kembalian, PelangganID) VALUES (NOW(), ?, ?, ?, ?)"
    );
    $stmt->execute([$totalHarga, $uangBayar, $kembalian, $pelangganID]);
    $penjualanID = $pdo->lastInsertId();

    // 2. Siapkan statement untuk detail dan update stok
    $detailStmt = $pdo->prepare(
        "INSERT INTO detailpenjualan (PenjualanID, ProdukID, JumlahProduk, Subtotal) VALUES (?, ?, ?, ?)"
    );
    $updateStokStmt = $pdo->prepare("UPDATE produk SET Stok = Stok - ? WHERE ProdukID = ? AND Stok >= ?");

    // 3. Loop setiap item di keranjang
    foreach ($items as $item) {
        $produkID = $item['ProdukID'];
        $jumlah = $item['jumlah'];
        $subtotal = $item['subtotal'];

        // Masukkan ke 'detailpenjualan'
        $detailStmt->execute([$penjualanID, $produkID, $jumlah, $subtotal]);

        // Kurangi stok produk
        $updateStokStmt->execute([$jumlah, $produkID, $jumlah]);
        
        // Periksa apakah stok berhasil diupdate (stok cukup)
        if ($updateStokStmt->rowCount() == 0) {
            throw new Exception("Stok produk tidak mencukupi untuk ID: $produkID.");
        }
    }

    // Jika semua berhasil, commit transaksi
    $pdo->commit();

    echo json_encode(['message' => 'Penjualan berhasil diproses!', 'PenjualanID' => $penjualanID]);

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan (rollback)
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memproses penjualan: ' . $e->getMessage()]);
}
?>