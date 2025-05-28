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

// Data anggota
$stmtAnggota = $conn->query("SELECT id, nama FROM anggota ORDER BY nama");
$anggotaList = $stmtAnggota->fetchAll(PDO::FETCH_ASSOC);

// Data bibliografi
$stmtBib = $conn->query("SELECT id, judul FROM bibliografi ORDER BY judul");
$bibliografiList = $stmtBib->fetchAll(PDO::FETCH_ASSOC);

// Data peminjaman dengan koleksi dan judul buku
$sql = "SELECT
          p.id AS peminjaman_id,
          p.anggota_id,
          a.nama AS anggota_nama,
          p.tgl_pinjam,
          p.tgl_kembali,
          GROUP_CONCAT(k.id ORDER BY k.id) AS koleksi_ids,
          GROUP_CONCAT(b.judul ORDER BY b.judul) AS judul_buku,
          b.id AS bibliografi_id -- tambahan agar bisa kirim ke tombol edit
        FROM peminjaman p
        JOIN anggota a ON p.anggota_id = a.id
        JOIN peminjaman_koleksi pk ON pk.peminjaman_id = p.id
        JOIN koleksi k ON k.id = pk.koleksi_id
        JOIN bibliografi b ON b.id = k.bibliografi_id
        GROUP BY p.id, p.anggota_id, a.nama, p.tgl_pinjam, p.tgl_kembali, b.id
        ORDER BY p.id DESC";
$stmt = $conn->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kelola Peminjaman</title>
  <link href="/assets/css/style.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
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

  <main id="mainContent" class="content" tabindex="-1" role="main">
    <div class="container-fluid">
      <h1 class="mb-4 font-weight-bold text-primary">Kelola Peminjaman</h1>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if ($role === 'pustakawan'): ?>
        <div class="card form-section shadow-sm">
          <div class="card-body">
            <form method="post" id="formPeminjaman" novalidate action="create.php">
              <input type="hidden" name="id" id="idPeminjaman" />
              <div class="form-group">
                <label for="anggota_id">Anggota <small class="text-muted">*</small></label>
                <select name="anggota_id" id="anggota_id" class="form-control select2" required>
                  <option value="">-- Pilih Anggota --</option>
                  <?php foreach ($anggotaList as $anggota): ?>
                    <option value="<?= $anggota['id'] ?>"><?= htmlspecialchars($anggota['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="bibliografi_id">Bibliografi <small class="text-muted">*</small></label>
                <select name="bibliografi_id" id="bibliografi_id" class="form-control select2" required>
                  <option value="">-- Pilih Bibliografi --</option>
                  <?php foreach ($bibliografiList as $bib): ?>
                    <option value="<?= $bib['id'] ?>"><?= htmlspecialchars($bib['judul']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="koleksi_ids">Koleksi <small class="text-muted">*</small></label>
                <select name="koleksi_ids[]" id="koleksi_ids" class="form-control select2" multiple required>
                  <!-- Opsi akan dimuat via AJAX -->
                </select>
                <small class="form-text text-muted">Pilih satu atau lebih koleksi yang akan dipinjam</small>
              </div>
              <div class="form-group">
                <label for="tgl_pinjam">Tanggal Pinjam <small class="text-muted">*</small></label>
                <input type="date" name="tgl_pinjam" id="tgl_pinjam" class="form-control" required />
              </div>
              <div class="form-group">
                <label for="tgl_kembali">Tanggal Kembali</label>
                <input type="date" name="tgl_kembali" id="tgl_kembali" class="form-control" />
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
        <table class="table table-striped table-bordered" id="peminjamanTable" style="width:100%">
          <thead class="thead-dark">
            <tr>
              <th>ID</th>
              <th>Anggota</th>
              <th>Judul Buku</th>
              <th>Koleksi IDs</th>
              <th>Tanggal Pinjam</th>
              <th>Tanggal Kembali</th>
              <?php if ($role === 'pustakawan'): ?>
                <th>Aksi</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($data as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['peminjaman_id']) ?></td>
                <td><?= htmlspecialchars($row['anggota_nama']) ?></td>
                <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                <td><?= htmlspecialchars($row['koleksi_ids']) ?></td>
                <td><?= htmlspecialchars($row['tgl_pinjam']) ?></td>
                <td><?= htmlspecialchars($row['tgl_kembali'] ?: '-') ?></td>
                <?php if ($role === 'pustakawan'): ?>
                  <td>
                    <button class="btn btn-sm btn-info editBtn"
                      data-id="<?= htmlspecialchars($row['peminjaman_id']) ?>"
                      data-anggota_id="<?= htmlspecialchars($row['anggota_id']) ?>"
                      data-bibliografi_id="<?= htmlspecialchars($row['bibliografi_id']) ?>"
                      data-koleksi_ids="<?= htmlspecialchars($row['koleksi_ids']) ?>"
                      data-tgl_pinjam="<?= htmlspecialchars($row['tgl_pinjam']) ?>"
                      data-tgl_kembali="<?= htmlspecialchars($row['tgl_kembali']) ?>"
                      title="Edit Data">
                      <i class="fas fa-edit"></i>
                    </button>
                    <a href="delete.php?delete=<?= htmlspecialchars($row['peminjaman_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus Data">
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="/assets/js/script.js"></script>

  <script>
    $(document).ready(function() {
      $('.select2').select2({
        width: '100%',
        placeholder: 'Pilih...',
        allowClear: true
      });

      function loadKoleksi(bibliografiId, selectedIds = [], currentPeminjamanId = 0) {
        if (!bibliografiId) {
          $('#koleksi_ids').html('').trigger('change');
          return;
        }
        $.ajax({
          url: 'get_koleksi.php',
          method: 'GET',
          data: {
            bibliografi_id: bibliografiId,
            peminjaman_id: currentPeminjamanId
          },
          success: function(html) {
            $('#koleksi_ids').html(html).select2({
              width: '100%',
              placeholder: 'Pilih...',
              allowClear: true
            });
            setTimeout(() => {
              $('#koleksi_ids').val(selectedIds.map(String)).trigger('change');
            }, 100);
          },
          error: function() {
            alert('Gagal mengambil data koleksi.');
          }
        });
      }

      $('#bibliografi_id').on('change', function() {
        loadKoleksi($(this).val());
      });

      $('.editBtn').on('click', function() {
        const bibId = String($(this).data('bibliografi_id') || '');
        const koleksiRaw = $(this).data('koleksi_ids') || '';
        const koleksiArr = koleksiRaw ? koleksiRaw.toString().split(',').map(x => x.trim()) : [];
        const peminjamanId = Number($(this).data('id')) || 0;

        $('#formPeminjaman').attr('action', 'update.php');
        $('#idPeminjaman').val(String(peminjamanId));
        $('#anggota_id').val(String($(this).data('anggota_id') || '')).trigger('change');
        $('#tgl_pinjam').val($(this).data('tgl_pinjam') || '');
        $('#tgl_kembali').val($(this).data('tgl_kembali') || '');
        $('#bibliografi_id').val(bibId).trigger('change');

        loadKoleksi(bibId, koleksiArr, peminjamanId);

        $('#submitBtn').text('Update Peminjaman');
        $('#cancelBtn').removeClass('d-none');
        $('html, body').animate({
          scrollTop: 0
        }, 'fast');
      });


      $('#cancelBtn').on('click', function() {
        $('#formPeminjaman').attr('action', 'create.php');
        $('#formPeminjaman')[0].reset();

        // Reset input hidden id
        $('#idPeminjaman').val('');

        // Reset semua select2 yang ada di form
        $('#anggota_id').val(null).trigger('change');
        $('#bibliografi_id').val(null).trigger('change');
        $('#koleksi_ids').val(null).trigger('change');

        $('#submitBtn').text('Simpan');
        $(this).addClass('d-none');
      });


      $('#peminjamanTable').DataTable({
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
    });
  </script>

</body>

</html>