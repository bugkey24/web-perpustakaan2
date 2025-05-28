<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username'])) {
  header('Location: ../login.php');
  exit;
}

$anggota_id = $_SESSION['anggota_id'] ?? null;

if (!$anggota_id) {
  die("Anda tidak memiliki akses ke halaman ini.");
}

// Definisikan error dan success
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Query daftar peminjaman aktif (belum dikembalikan)
$sqlActive = "SELECT
    p.id AS peminjaman_id,
    b.judul,
    k.id AS koleksi_id,
    p.tgl_pinjam,
    p.tgl_kembali
FROM peminjaman p
JOIN peminjaman_koleksi pk ON pk.peminjaman_id = p.id
JOIN koleksi k ON k.id = pk.koleksi_id
JOIN bibliografi b ON b.id = k.bibliografi_id
WHERE p.anggota_id = ? AND p.tgl_kembali IS NULL
ORDER BY p.tgl_pinjam DESC";

// Query daftar peminjaman yang sudah dikembalikan
$sqlReturned = "SELECT
    p.id AS peminjaman_id,
    b.judul,
    k.id AS koleksi_id,
    p.tgl_pinjam,
    p.tgl_kembali
FROM peminjaman p
JOIN peminjaman_koleksi pk ON pk.peminjaman_id = p.id
JOIN koleksi k ON k.id = pk.koleksi_id
JOIN bibliografi b ON b.id = k.bibliografi_id
WHERE p.anggota_id = ? AND p.tgl_kembali IS NOT NULL
ORDER BY p.tgl_kembali DESC";

// Eksekusi query untuk mendapatkan data
$stmtActive = $conn->prepare($sqlActive);
$stmtActive->execute([$anggota_id]);
$dataActive = $stmtActive->fetchAll(PDO::FETCH_ASSOC);

$stmtReturned = $conn->prepare($sqlReturned);
$stmtReturned->execute([$anggota_id]);
$dataReturned = $stmtReturned->fetchAll(PDO::FETCH_ASSOC);

// Jika tidak ada data yang ditemukan, pastikan array kosong
if (!$dataActive) {
  $dataActive = [];
}
if (!$dataReturned) {
  $dataReturned = [];
}

// Ambil daftar buku/bibliografi yang tersedia untuk peminjaman
$sqlAvail = "SELECT b.id, b.judul
FROM bibliografi b
JOIN koleksi k ON k.bibliografi_id = b.id
WHERE k.status = 'tersedia'
GROUP BY b.id, b.judul
ORDER BY b.judul";
$stmtAvail = $conn->query($sqlAvail);
$bibliografiTersedia = $stmtAvail->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar Peminjaman Saya - Perpustakaan POLIWANGI</title>
  <link rel="stylesheet" href="/assets/css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      padding-top: 1rem;
    }

    .select2-container--default .select2-selection--single {
      height: 38px;
      padding: 6px 12px;
      box-sizing: border-box;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 25px;
      padding-left: 0;
      margin-top: 0;
    }

    .dataTables_info {
      margin-bottom: 4rem;
      /* Memberikan jarak di bawah informasi teks */
    }
  </style>
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/sidebar.php'; ?>

  <main class="content" id="mainContent" tabindex="-1" role="main">
    <div class="container-fluid">
      <h1 class="mb-4 text-primary font-weight-bold">KELOLA PEMINJAMAN</h1>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <!-- Tabel Daftar Buku yang Telah Dipinjam -->
      <h3 class="mb-3">Buku yang Telah Dikembalikan</h3>
      <table class="table table-striped table-bordered" id="returnedTable">
        <thead class="thead-dark">
          <tr>
            <th>Judul Buku</th>
            <th>ID Koleksi</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dataReturned as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['judul']) ?></td>
              <td><?= htmlspecialchars($row['koleksi_id']) ?></td>
              <td><?= htmlspecialchars($row['tgl_pinjam']) ?></td>
              <td><?= htmlspecialchars($row['tgl_kembali']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Tabel Daftar Buku yang Masih Aktif Dipinjam -->
      <h3 class="mb-3">Buku yang Masih Aktif Dipinjam</h3>
      <table class="table table-striped table-bordered" id="activeTable">
        <thead class="thead-dark">
          <tr>
            <th>Judul Buku</th>
            <th>ID Koleksi</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dataActive as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['judul']) ?></td>
              <td><?= htmlspecialchars($row['koleksi_id']) ?></td>
              <td><?= htmlspecialchars($row['tgl_pinjam']) ?></td>
              <td><?= htmlspecialchars($row['tgl_kembali'] ?: '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Form Pinjam Buku Baru -->
      <h3 class="mb-3">Pinjam Buku Baru</h3>
      <form method="post" action="create_anggota.php" id="formPinjam" class="mb-5">
        <div class="form-group">
          <label for="bibliografi_id">Pilih Buku yang Tersedia:</label>
          <select name="bibliografi_id" id="bibliografi_id" class="form-control select2" required>
            <option value="">-- Pilih Buku --</option>
            <?php foreach ($bibliografiTersedia as $bib): ?>
              <option value="<?= $bib['id'] ?>"><?= htmlspecialchars($bib['judul']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="tgl_pinjam">Tanggal Pinjam:</label>
          <input type="date" name="tgl_pinjam" id="tgl_pinjam" class="form-control" required value="<?= date('Y-m-d') ?>" />
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-book-reader"></i> Pinjam</button>
      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/script.js"></script>
  <script>
    $(document).ready(function() {
      $('#returnedTable').DataTable();
      $('#activeTable').DataTable();

      $('.select2').select2({
        width: '100%',
        placeholder: 'Pilih buku...',
        allowClear: true
      });
    });
  </script>
</body>

</html>