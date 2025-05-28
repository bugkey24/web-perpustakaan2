<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!$deskripsi) {
        header('Location: index.php?error=' . urlencode('Deskripsi kategori wajib diisi.'));
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO bibliografi_kategori (deskripsi) VALUES (?)");
        if (!$stmt->execute([$deskripsi])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Gagal menambahkan kategori: " . $errorInfo[2]);
        }
        header('Location: index.php?success=' . urlencode('Kategori berhasil ditambahkan.'));
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: index.php');
exit;
