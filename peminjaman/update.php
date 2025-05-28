<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
  header('Location: ../login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  $anggota_id = (int)($_POST['anggota_id'] ?? 0);
  $bibliografi_id = (int)($_POST['bibliografi_id'] ?? 0);
  $koleksi_ids = $_POST['koleksi_ids'] ?? [];
  $tgl_pinjam = $_POST['tgl_pinjam'] ?? null;
  $tgl_kembali = $_POST['tgl_kembali'] ?? null;

  if ($id <= 0 || $anggota_id <= 0 || $bibliografi_id <= 0 || empty($koleksi_ids) || !$tgl_pinjam) {
    header('Location: index.php?error=' . urlencode('Lengkapi data yang wajib diisi.'));
    exit;
  }

  try {
    $conn->beginTransaction();

    // Update tanggal pinjam, kembali dan anggota di tabel peminjaman (tidak update status karena tidak ada kolom status)
    $stmt = $conn->prepare("UPDATE peminjaman SET tgl_pinjam = ?, tgl_kembali = ?, anggota_id = ? WHERE id = ?");
    $stmt->execute([$tgl_pinjam, $tgl_kembali ?: null, $anggota_id, $id]);

    // Ambil koleksi lama terkait peminjaman
    $stmtOld = $conn->prepare("SELECT koleksi_id FROM peminjaman_koleksi WHERE peminjaman_id = ?");
    $stmtOld->execute([$id]);
    $oldKoleksiIds = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

    // Kembalikan status koleksi lama ke 'tersedia'
    $stmtResetStatus = $conn->prepare("UPDATE koleksi SET status = 'tersedia' WHERE id = ?");
    foreach ($oldKoleksiIds as $oldId) {
      $stmtResetStatus->execute([$oldId]);
    }

    // Hapus relasi lama
    $stmtDel = $conn->prepare("DELETE FROM peminjaman_koleksi WHERE peminjaman_id = ?");
    $stmtDel->execute([$id]);

    // Insert relasi baru dan update status koleksi ke 'dipinjam' atau 'tersedia' berdasarkan ada tidaknya tanggal kembali
    $stmtInsert = $conn->prepare("INSERT INTO peminjaman_koleksi (peminjaman_id, koleksi_id) VALUES (?, ?)");
    $stmtUpdateStatus = $conn->prepare("UPDATE koleksi SET status = ? WHERE id = ?");

    // Tentukan status koleksi: 'tersedia' jika sudah ada tanggal kembali, 'dipinjam' jika belum
    $statusKoleksi = ($tgl_kembali && !empty($tgl_kembali)) ? 'tersedia' : 'dipinjam';

    foreach ($koleksi_ids as $koleksi_id) {
      $stmtInsert->execute([$id, (int)$koleksi_id]);
      $stmtUpdateStatus->execute([$statusKoleksi, (int)$koleksi_id]);
    }

    $conn->commit();
    header('Location: index.php?success=' . urlencode('Data peminjaman berhasil diupdate.'));
  } catch (Exception $e) {
    $conn->rollBack();
    header('Location: index.php?error=' . urlencode('Gagal mengupdate data: ' . $e->getMessage()));
  }
  exit;
}

header('Location: index.php');
exit;
