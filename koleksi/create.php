<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
  header('Location: ../login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bibliografi_id = (int)($_POST['bibliografi_id'] ?? 0);
  $tanggal_beli = $_POST['tanggal_beli'] ?? null;
  $harga = $_POST['harga'] ?? null;
  $status = $_POST['status'] ?? 'tersedia';

  if ($bibliografi_id <= 0) {
    header('Location: index.php?error=' . urlencode('Bibliografi wajib dipilih.'));
    exit;
  }

  try {
    $stmt = $conn->prepare("INSERT INTO koleksi (bibliografi_id, tanggal_beli, harga, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$bibliografi_id, $tanggal_beli, $harga, $status]);

    header('Location: index.php?success=' . urlencode('Data koleksi berhasil ditambahkan.'));
    exit;
  } catch (Exception $e) {
    header('Location: index.php?error=' . urlencode('Gagal menambahkan data: ' . $e->getMessage()));
    exit;
  }
}

header('Location: index.php');
exit;
