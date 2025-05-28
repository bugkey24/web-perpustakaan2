<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
  header('Location: ../login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  $bibliografi_id = (int)($_POST['bibliografi_id'] ?? 0);
  $tanggal_beli = $_POST['tanggal_beli'] ?? null;
  $harga = $_POST['harga'] ?? null;
  $status = $_POST['status'] ?? 'tersedia';

  if ($id <= 0 || $bibliografi_id <= 0) {
    header('Location: index.php?error=' . urlencode('ID koleksi dan bibliografi wajib diisi.'));
    exit;
  }

  try {
    $stmt = $conn->prepare("UPDATE koleksi SET bibliografi_id = ?, tanggal_beli = ?, harga = ?, status = ? WHERE id = ?");
    $stmt->execute([$bibliografi_id, $tanggal_beli, $harga, $status, $id]);

    header('Location: index.php?success=' . urlencode('Data koleksi berhasil diupdate.'));
    exit;
  } catch (Exception $e) {
    header('Location: index.php?error=' . urlencode('Gagal mengupdate data: ' . $e->getMessage()));
    exit;
  }
}

header('Location: index.php');
exit;
