<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data form
    $judul = trim($_POST['judul'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $penerbit = trim($_POST['penerbit'] ?? '');
    $tahun_terbit = trim($_POST['tahun_terbit'] ?? '');
    $jumlah_halaman = (int)($_POST['jumlah_halaman'] ?? 0);
    $jilid_ke = trim($_POST['jilid_ke'] ?? '');
    $abstrak = trim($_POST['abstrak'] ?? '');

    if (!$judul || !$kategori_id) {
        header('Location: index.php?error=' . urlencode('Judul dan Kategori wajib diisi.'));
        exit;
    }

    // Handle upload file sampul
    $uploadDir = '../uploads/sampul/';
    $sampulPath = null;
    if (isset($_FILES['sampul']) && $_FILES['sampul']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['sampul']['tmp_name'];
        $fileName = basename($_FILES['sampul']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            header('Location: index.php?error=' . urlencode('File sampul harus berupa gambar (jpg, png, gif).'));
            exit;
        }

        $newFileName = uniqid('sampul_', true) . '.' . $ext;
        $targetFile = $uploadDir . $newFileName;

        if (!move_uploaded_file($tmpName, $targetFile)) {
            header('Location: index.php?error=' . urlencode('Gagal mengunggah file sampul.'));
            exit;
        }

        $sampulPath = 'uploads/sampul/' . $newFileName; // path relatif
    }

    try {
        $sql = "INSERT INTO bibliografi (judul, bibliografi_kategori_id, penerbit, tahun_terbit, jumlah_halaman, jilid_ke, abstrak, sampul)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $judul,
            $kategori_id,
            $penerbit,
            $tahun_terbit,
            $jumlah_halaman ?: null,
            $jilid_ke,
            $abstrak,
            $sampulPath
        ]);

        header('Location: index.php?success=' . urlencode('Data berhasil ditambahkan.'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode('Gagal menambahkan data: ' . $e->getMessage()));
        exit;
    }
}

header('Location: index.php');
exit;
