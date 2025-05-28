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

$stmtKat = $conn->query("SELECT id, deskripsi FROM bibliografi_kategori ORDER BY deskripsi");
$kategoriList = $stmtKat->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT b.*, k.deskripsi AS kategori FROM bibliografi b LEFT JOIN bibliografi_kategori k ON b.bibliografi_kategori_id = k.id ORDER BY b.id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Bibliografi</title>
    <link href="/assets/css/style.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <!-- Tambahkan Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-section {
            background: #fff;
            padding: 1rem;
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
        }

        .table td.abstrak-cell {
            text-align: left;
            max-width: 300px;
            white-space: normal;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <main class="content" id="mainContent" tabindex="-1" role="main">
        <div class="container-fluid">
            <h1 class="mb-4 font-weight-bold text-primary">Kelola Bibliografi</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($role === 'pustakawan'): ?>
                <div class="card form-section shadow-sm">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data" id="formBibliografi" novalidate action="create.php">
                            <input type="hidden" name="id" id="idBibliografi" />
                            <div class="form-group">
                                <label for="judul">Judul <small class="text-muted">*</small></label>
                                <input type="text" name="judul" id="judul" class="form-control" placeholder="Masukkan judul" required />
                            </div>

                            <div class="form-group">
                                <label for="kategori_id">Kategori <small class="text-muted">*</small></label>
                                <select name="kategori_id" id="kategori_id" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($kategoriList as $kat): ?>
                                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['deskripsi']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="penerbit">Penerbit</label>
                                <input type="text" name="penerbit" id="penerbit" class="form-control" placeholder="Masukkan penerbit" />
                            </div>

                            <div class="form-group">
                                <label for="tahun_terbit">Tahun Terbit</label>
                                <input type="text" name="tahun_terbit" id="tahun_terbit" class="form-control" placeholder="Masukkan tahun terbit" />
                            </div>

                            <div class="form-group">
                                <label for="jumlah_halaman">Jumlah Halaman</label>
                                <input type="number" name="jumlah_halaman" id="jumlah_halaman" class="form-control" placeholder="Masukkan jumlah halaman" />
                            </div>

                            <div class="form-group">
                                <label for="jilid_ke">Jilid Ke</label>
                                <input type="text" name="jilid_ke" id="jilid_ke" class="form-control" placeholder="Masukkan jilid ke" />
                            </div>

                            <div class="form-group">
                                <label for="abstrak">Abstrak</label>
                                <textarea name="abstrak" id="abstrak" class="form-control" rows="3" placeholder="Masukkan abstrak"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="sampul">Sampul (Upload Gambar)</label>
                                <div class="d-flex align-items-center">
                                    <input type="file" name="sampul" id="sampul" class="form-control-file" accept="image/*" style="max-width: 300px;" />
                                    <button type="button" id="btnClearSampul" class="btn btn-outline-danger btn-sm ml-2" title="Hapus file terpilih" style="display:none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Unggah gambar sampul (jpg, png, dll). Kosongkan jika tidak ingin mengubah.</small>
                                <div id="previewSampul" style="margin-top:10px;"></div>
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
                <table class="table table-striped table-bordered" id="bibliografiTable" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Penerbit</th>
                            <th>Tahun Terbit</th>
                            <th>Halaman</th>
                            <th>Jilid</th>
                            <th>Abstrak</th>
                            <th>Sampul</th>
                            <?php if ($role === 'pustakawan'): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                <td><?= htmlspecialchars($row['penerbit'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($row['tahun_terbit'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($row['jumlah_halaman'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($row['jilid_ke'] ?: '-') ?></td>
                                <td class="abstrak-cell"><?= nl2br(htmlspecialchars($row['abstrak'] ?: '-')) ?></td>
                                <td>
                                    <?php if ($row['sampul'] && file_exists("../" . $row['sampul'])): ?>
                                        <img src="<?= htmlspecialchars('../' . $row['sampul']) ?>" alt="Sampul" style="max-height:80px; max-width:60px;" />
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <?php if ($role === 'pustakawan'): ?>
                                    <td>
                                        <button class="btn btn-sm btn-info editBtn"
                                            data-id="<?= $row['id'] ?>"
                                            data-judul="<?= htmlspecialchars($row['judul']) ?>"
                                            data-kategori_id="<?= $row['bibliografi_kategori_id'] ?>"
                                            data-penerbit="<?= htmlspecialchars($row['penerbit']) ?>"
                                            data-tahun_terbit="<?= htmlspecialchars($row['tahun_terbit']) ?>"
                                            data-jumlah_halaman="<?= htmlspecialchars($row['jumlah_halaman']) ?>"
                                            data-jilid_ke="<?= htmlspecialchars($row['jilid_ke']) ?>"
                                            data-abstrak="<?= htmlspecialchars($row['abstrak']) ?>"
                                            data-sampul="<?= htmlspecialchars($row['sampul']) ?>"
                                            title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus Data">
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#bibliografiTable').DataTable({
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

            // Inisialisasi Select2 pada kategori (opsional)
            $('#kategori_id').select2({
                placeholder: '-- Pilih Kategori --',
                allowClear: true,
                width: '100%'
            });

            // Event tombol edit
            $('.editBtn').on('click', function() {
                $('#formBibliografi').attr('action', 'update.php');
                $('#idBibliografi').val($(this).data('id'));
                $('#judul').val($(this).data('judul'));
                $('#kategori_id').val(String($(this).data('kategori_id'))).trigger('change');
                $('#penerbit').val($(this).data('penerbit'));
                $('#tahun_terbit').val($(this).data('tahun_terbit'));
                $('#jumlah_halaman').val($(this).data('jumlah_halaman'));
                $('#jilid_ke').val($(this).data('jilid_ke'));
                $('#abstrak').val($(this).data('abstrak'));
                $('#sampul').val('');
                $('#submitBtn').text('Update Bibliografi');
                $('#cancelBtn').removeClass('d-none');
                $('html, body').animate({
                    scrollTop: 0
                }, 'fast');

                var sampul = $(this).data('sampul');
                if (sampul) {
                    $('#previewSampul').html('<img src="../' + sampul + '" style="max-height:100px;" />');
                    $('#btnClearSampul').show();
                } else {
                    $('#previewSampul').html('');
                    $('#btnClearSampul').hide();
                }
            });

            // Tombol batal reset form
            $('#cancelBtn').on('click', function() {
                $('#formBibliografi').attr('action', 'create.php');
                $('#formBibliografi')[0].reset();
                $('#idBibliografi').val('');
                $('#submitBtn').text('Simpan');
                $(this).addClass('d-none');
                $('#previewSampul').html('');
                $('#btnClearSampul').hide();
                $('#sampul').val('');
                // Reset select2 kategori jika dipakai
                $('#kategori_id').val(null).trigger('change');
            });

            // Preview gambar sampul saat upload file baru
            $('#sampul').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewSampul').html('<img src="' + e.target.result + '" style="max-height:100px;">');
                    }
                    reader.readAsDataURL(file);
                    $('#btnClearSampul').show();
                } else {
                    $('#previewSampul').html('');
                    $('#btnClearSampul').hide();
                }
            });

            // Tombol hapus preview sampul
            $('#btnClearSampul').on('click', function() {
                $('#sampul').val('');
                $('#previewSampul').html('');
                $(this).hide();
            });
        });
    </script>
</body>

</html>