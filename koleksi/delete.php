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
    $stmtDelete = $conn->prepare("DELETE FROM koleksi WHERE id = ?");
    if (!$stmtDelete->execute([$id])) {
      $errorInfo = $stmtDelete->errorInfo();
      throw new Exception("Gagal menghapus data: " . $errorInfo[2]);
    }

    header('Location: index.php?success=' . urlencode('Data koleksi berhasil dihapus.'));
  } catch (Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
  }
  exit;
}

header('Location: index.php');
exit;
