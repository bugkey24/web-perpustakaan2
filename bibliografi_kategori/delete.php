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
        // Cek apakah kategori punya relasi di tabel bibliografi
        $stmtCheck = $conn->prepare("SELECT COUNT(*) AS total FROM bibliografi WHERE bibliografi_kategori_id = ?");
        $stmtCheck->execute([$id]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($row['total'] > 0) {
            throw new Exception("Kategori ini tidak dapat dihapus karena masih memiliki bibliografi terkait.");
        }

        // Hapus kategori
        $stmtDelete = $conn->prepare("DELETE FROM bibliografi_kategori WHERE id = ?");
        if (!$stmtDelete->execute([$id])) {
            $errorInfo = $stmtDelete->errorInfo();
            throw new Exception("Gagal menghapus kategori: " . $errorInfo[2]);
        }

        header('Location: index.php?success=' . urlencode('Kategori berhasil dihapus.'));
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: index.php');
exit;
