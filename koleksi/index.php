<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['username'])) {
  header('Location: ../login.php');
  exit;
}

$role = $_SESSION['role'] ?? 'anggota';

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

$stmtBib = $conn->query("SELECT id, judul FROM bibliografi ORDER BY judul");
$bibliografiList = $stmtBib->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT k.*, b.judul FROM koleksi k LEFT JOIN bibliografi b ON k.bibliografi_id = b.id ORDER BY k.id DESC";
$stmt = $conn->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kelola Koleksi</title>
  <link href="/assets/css/style.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
  <!-- Tambahkan CSS Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .form-section {
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 3rem;
    }

    .table-responsive {
      background: #fff;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
    }

    .form-group label {
      font-weight: bold;
    }

    .btn {
      margin: 0.2rem;
    }

    .table th,
    .table td {
      vertical-align: middle;
      text-align: center;
    }
  </style>
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/sidebar.php'; ?>

  <main class="content" id="mainContent" tabindex="-1" role="main">
    <div class="container-fluid">
      <h1 class="mb-4 font-weight-bold text-primary">
        <?php if ($role === 'pustakawan'): ?>
          KELOLA KOLEKSI
        <?php else: ?>
          DAFTAR KOLEKSI
        <?php endif; ?>
      </h1>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if ($role === 'pustakawan'): ?>
        <div class="card form-section shadow-sm">
          <div class="card-body">
            <form method="post" id="formKoleksi" novalidate action="create.php">
              <input type="hidden" name="id" id="idKoleksi" />
              <div class="form-group">
                <label for="bibliografi_id">Bibliografi <small class="text-muted">*</small></label>
                <select name="bibliografi_id" id="bibliografi_id" class="form-control" required>
                  <option value="">-- Pilih Bibliografi --</option>
                  <?php foreach ($bibliografiList as $bib): ?>
                    <option value="<?= $bib['id'] ?>"><?= htmlspecialchars($bib['judul']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="tanggal_beli">Tanggal Beli</label>
                <input type="date" name="tanggal_beli" id="tanggal_beli" class="form-control" />
              </div>

              <div class="form-group">
                <label for="harga">Harga</label>
                <input type="number" name="harga" id="harga" class="form-control" placeholder="Masukkan harga" />
              </div>

              <div class="form-group">
                <label for="status">Status Koleksi</label>
                <select name="status" id="status" class="form-control">
                  <option value="tersedia" selected>Tersedia</option>
                  <option value="dipinjam">Dipinjam</option>
                  <option value="rusak">Rusak</option>
                  <option value="hilang">Hilang</option>
                </select>
              </div>

              <div class="form-group text-right">
                <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
                <button type="reset" class="btn btn-secondary d-none" id="cancelBtn">Batal</button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="table-responsive shadow-sm">
        <table class="table table-striped table-bordered" id="koleksiTable" style="width:100%">
          <thead class="thead-dark">
            <tr>
              <th>Bibliografi</th>
              <th>Tanggal Beli</th>
              <th>Harga</th>
              <th>Status</th>
              <?php if ($role === 'pustakawan'): ?>
                <th>Aksi</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($data as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_beli'] ?: '-') ?></td>
                <td><?= htmlspecialchars($row['harga'] ?: '-') ?></td>
                <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                <?php if ($role === 'pustakawan'): ?>
                  <td>
                    <button class="btn btn-sm btn-info editBtn"
                      data-id="<?= $row['id'] ?>"
                      data-bibliografi_id="<?= $row['bibliografi_id'] ?>"
                      data-tanggal_beli="<?= $row['tanggal_beli'] ?>"
                      data-harga="<?= $row['harga'] ?>"
                      data-status="<?= $row['status'] ?>"
                      title="Edit Data">
                      <i class="fas fa-edit"></i>
                    </button>
                    <a href="delete.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus Data">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
  <script src="/assets/js/script.js"></script>

  <script>
    $(document).ready(function() {
      $('#koleksiTable').DataTable({
        language: {
          search: "Cari:",
          lengthMenu: "Tampilkan _MENU_ entri",
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
          paginate: {
            first: "Awal",
            last: "Akhir",
            next: "Berikutnya",
            previous: "Sebelumnya"
          },
          zeroRecords: "Tidak ditemukan data yang cocok",
          infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
          infoFiltered: "(disaring dari _MAX_ total entri)"
        }
      });

      // Inisialisasi Select2 untuk bibliografi
      $('#bibliografi_id').select2({
        placeholder: '-- Pilih Bibliografi --',
        allowClear: true,
        width: '100%'
      });

      // Tombol edit
      $('.editBtn').on('click', function() {
        $('#formKoleksi').attr('action', 'update.php');
        $('#idKoleksi').val($(this).data('id'));
        $('#bibliografi_id').val(String($(this).data('bibliografi_id'))).trigger('change');
        $('#tanggal_beli').val($(this).data('tanggal_beli'));
        $('#harga').val($(this).data('harga'));
        $('#status').val($(this).data('status'));
        $('#submitBtn').text('Update Koleksi');
        $('#cancelBtn').removeClass('d-none');
        $('html, body').animate({
          scrollTop: 0
        }, 'fast');
      });

      // Tombol batal
      $('#cancelBtn').on('click', function() {
        $('#formKoleksi').attr('action', 'create.php');
        $('#formKoleksi')[0].reset();
        $('#idKoleksi').val('');
        $('#submitBtn').text('Simpan');
        $(this).addClass('d-none');
        $('#bibliografi_id').val(null).trigger('change'); // reset select2 bibliografi
      });
    });
  </script>
</body>

</html>