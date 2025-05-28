<?php
session_start();
require_once('../db.php');

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
  http_response_code(401);
  exit('Unauthorized');
}

$bibId = $_GET['bibliografi_id'] ?? '';
$currentPeminjamanId = (int)($_GET['peminjaman_id'] ?? 0);

if (!$bibId || !is_numeric($bibId)) {
  echo '<option value="">-- Pilih Koleksi --</option>';
  exit;
}

try {
  if ($currentPeminjamanId > 0) {
    // Tampilkan koleksi tersedia + koleksi yang sedang dipinjam oleh peminjaman ini
    $sql = "SELECT DISTINCT k.id FROM koleksi k
                LEFT JOIN peminjaman_koleksi pk ON k.id = pk.koleksi_id
                WHERE k.bibliografi_id = ?
                AND (k.status = 'tersedia' OR pk.peminjaman_id = ?)
                ORDER BY k.id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$bibId, $currentPeminjamanId]);
  } else {
    // Tampilkan hanya koleksi tersedia (default)
    $stmt = $conn->prepare("SELECT id FROM koleksi WHERE bibliografi_id = ? AND status = 'tersedia' ORDER BY id");
    $stmt->execute([$bibId]);
  }

  $koleksiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (!$koleksiList) {
    echo '<option value="">-- Koleksi tidak tersedia --</option>';
    exit;
  }

  echo '<option value="">-- Pilih Koleksi --</option>';
  foreach ($koleksiList as $koleksi) {
    echo '<option value="' . htmlspecialchars($koleksi['id']) . '">' . htmlspecialchars($koleksi['id']) . '</option>';
  }
} catch (Exception $e) {
  echo '<option value="">-- Gagal memuat koleksi --</option>';
}
