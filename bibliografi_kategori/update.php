<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($id <= 0 || !$deskripsi) {
        header('Location: index.php?error=' . urlencode('ID dan deskripsi kategori wajib diisi.'));
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE bibliografi_kategori SET deskripsi = ? WHERE id = ?");
        if (!$stmt->execute([$deskripsi, $id])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Gagal mengupdate kategori: " . $errorInfo[2]);
        }
        header('Location: index.php?success=' . urlencode('Kategori berhasil diupdate.'));
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: index.php');
exit;
