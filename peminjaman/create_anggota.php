<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || !isset($_SESSION['anggota_id'])) {
  header('Location: ../login.php');
  exit;
}

$anggota_id = $_SESSION['anggota_id'];
$bibliografi_id = $_POST['bibliografi_id'] ?? null;
$tgl_pinjam = $_POST['tgl_pinjam'] ?? null;

if (!$bibliografi_id || !$tgl_pinjam) {
  header('Location: anggota.php?error=Semua field harus diisi');
  exit;
}

// Cari koleksi tersedia untuk bibliografi ini
$stmt = $conn->prepare("SELECT id FROM koleksi WHERE bibliografi_id = ? AND status = 'tersedia' LIMIT 1");
$stmt->execute([$bibliografi_id]);
$koleksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$koleksi) {
  header('Location: anggota.php?error=Buku yang dipilih sedang tidak tersedia');
  exit;
}

$koleksi_id = $koleksi['id'];

// Mulai transaksi
$conn->beginTransaction();
try {
  // Insert ke tabel peminjaman
  $stmt = $conn->prepare("INSERT INTO peminjaman (anggota_id, tgl_pinjam) VALUES (?, ?)");
  $stmt->execute([$anggota_id, $tgl_pinjam]);
  $peminjaman_id = $conn->lastInsertId();

  // Insert ke peminjaman_koleksi
  $stmt = $conn->prepare("INSERT INTO peminjaman_koleksi (peminjaman_id, koleksi_id) VALUES (?, ?)");
  $stmt->execute([$peminjaman_id, $koleksi_id]);

  // Update status koleksi jadi dipinjam
  $stmt = $conn->prepare("UPDATE koleksi SET status = 'dipinjam' WHERE id = ?");
  $stmt->execute([$koleksi_id]);

  $conn->commit();
  header('Location: anggota.php?success=Peminjaman berhasil');
} catch (Exception $e) {
  $conn->rollBack();
  header('Location: anggota.php?error=Gagal melakukan peminjaman: ' . $e->getMessage());
}
exit;
