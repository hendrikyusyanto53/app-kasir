<?php
// api/koneksi.php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'db_kasir';
$user = 'root';
$pass = ''; // Kosongkan jika tidak ada password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Koneksi database gagal: ' . $e->getMessage()]);
    exit;
}
?>