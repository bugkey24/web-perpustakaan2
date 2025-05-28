<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
  header('Location: ../login.php');
  exit;
}

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];

  try {
    $conn->beginTransaction();

    // Ambil koleksi terkait
    $stmtKoleksi = $conn->prepare("SELECT koleksi_id FROM peminjaman_koleksi WHERE peminjaman_id = ?");
    $stmtKoleksi->execute([$id]);
    $koleksiIds = $stmtKoleksi->fetchAll(PDO::FETCH_COLUMN);

    // Kembalikan status koleksi ke tersedia
    $stmtResetStatus = $conn->prepare("UPDATE koleksi SET status = 'tersedia' WHERE id = ?");
    foreach ($koleksiIds as $kid) {
      $stmtResetStatus->execute([$kid]);
    }

    // Hapus relasi koleksi
    $stmtDelKoleksi = $conn->prepare("DELETE FROM peminjaman_koleksi WHERE peminjaman_id = ?");
    $stmtDelKoleksi->execute([$id]);

    // Hapus peminjaman
    $stmtDelPeminjaman = $conn->prepare("DELETE FROM peminjaman WHERE id = ?");
    if (!$stmtDelPeminjaman->execute([$id])) {
      $errorInfo = $stmtDelPeminjaman->errorInfo();
      throw new Exception("Gagal menghapus data: " . $errorInfo[2]);
    }

    $conn->commit();
    header('Location: index.php?success=' . urlencode('Data peminjaman berhasil dihapus.'));
  } catch (Exception $e) {
    $conn->rollBack();
    header('Location: index.php?error=' . urlencode($e->getMessage()));
  }
  exit;
}

header('Location: index.php');
exit;
