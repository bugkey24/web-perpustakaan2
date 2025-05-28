<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
  header('Location: ../login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $anggota_id = (int)($_POST['anggota_id'] ?? 0);
  $bibliografi_id = (int)($_POST['bibliografi_id'] ?? 0);
  $koleksi_ids = $_POST['koleksi_ids'] ?? [];
  $tgl_pinjam = $_POST['tgl_pinjam'] ?? null;
  $tgl_kembali = $_POST['tgl_kembali'] ?? null;

  if ($anggota_id <= 0 || $bibliografi_id <= 0 || empty($koleksi_ids) || !$tgl_pinjam) {
    header('Location: index.php?error=' . urlencode('Lengkapi data yang wajib diisi.'));
    exit;
  }

  try {
    $conn->beginTransaction();

    // Insert peminjaman
    $stmt = $conn->prepare("INSERT INTO peminjaman (tgl_pinjam, tgl_kembali, anggota_id) VALUES (?, ?, ?)");
    $stmt->execute([$tgl_pinjam, $tgl_kembali ?: null, $anggota_id]);
    $peminjaman_id = $conn->lastInsertId();

    // Insert peminjaman_koleksi dan update status koleksi
    $stmtInsert = $conn->prepare("INSERT INTO peminjaman_koleksi (peminjaman_id, koleksi_id) VALUES (?, ?)");
    $stmtUpdateStatus = $conn->prepare("UPDATE koleksi SET status = 'dipinjam' WHERE id = ?");
    foreach ($koleksi_ids as $koleksi_id) {
      $stmtInsert->execute([$peminjaman_id, (int)$koleksi_id]);
      $stmtUpdateStatus->execute([(int)$koleksi_id]);
    }

    $conn->commit();
    header('Location: index.php?success=' . urlencode('Data peminjaman berhasil ditambahkan.'));
  } catch (Exception $e) {
    $conn->rollBack();
    header('Location: index.php?error=' . urlencode('Gagal menambahkan data: ' . $e->getMessage()));
  }
  exit;
}

header('Location: index.php');
exit;
