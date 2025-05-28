<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        // Ambil file sampul sebelum hapus
        $stmtSampul = $conn->prepare("SELECT sampul FROM bibliografi WHERE id = ?");
        $stmtSampul->execute([$id]);
        $row = $stmtSampul->fetch(PDO::FETCH_ASSOC);
        $sampulPath = $row['sampul'] ?? null;

        // Hapus data bibliografi
        $stmtDelete = $conn->prepare("DELETE FROM bibliografi WHERE id = ?");
        if (!$stmtDelete->execute([$id])) {
            $errorInfo = $stmtDelete->errorInfo();
            throw new Exception("Gagal menghapus data: " . $errorInfo[2]);
        }

        // Hapus file sampul fisik jika ada
        if ($sampulPath && file_exists("../" . $sampulPath)) {
            unlink("../" . $sampulPath);
        }

        header('Location: index.php?success=' . urlencode('Data berhasil dihapus.'));
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: index.php');
exit;
