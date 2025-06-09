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

$stmt = $conn->query("SELECT * FROM bibliografi_kategori ORDER BY deskripsi");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Kategori Bibliografi</title>
    <link href="/assets/css/style.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
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
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <main class="content" id="mainContent" tabindex="-1" role="main">
        <div class="container-fluid">
            <h1 class="mb-4 font-weight-bold text-primary">Kelola Kategori Bibliografi</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($role === 'pustakawan'): ?>
                <div class="card form-section shadow-sm">
                    <div class="card-body">
                        <form method="post" id="formKategori" novalidate action="create.php">
                            <input type="hidden" name="id" id="idKategori" />

                            <div class="form-group">
                                <label for="deskripsi">Deskripsi Kategori <small class="text-muted">*</small></label>
                                <input type="text" name="deskripsi" id="deskripsi" class="form-control" placeholder="Masukkan deskripsi kategori" required />
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
                <table class="table table-striped table-bordered" id="kategoriTable" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>Deskripsi Kategori</th>
                            <?php if ($role === 'pustakawan'): ?>
                                <th style="width: 120px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <?php if ($role === 'pustakawan'): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info editBtn"
                                            data-id="<?= $row['id'] ?>"
                                            data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                                            title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="delete.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?')" title="Hapus Data">
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
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
    <script>
        $(document).ready(function() {
            $('#kategoriTable').DataTable({
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

            $('.editBtn').on('click', function() {
                $('#formKategori').attr('action', 'update.php');
                $('#idKategori').val($(this).data('id'));
                $('#deskripsi').val($(this).data('deskripsi'));
                $('#submitBtn').text('Update Kategori');
                $('#cancelBtn').removeClass('d-none');
                $('html, body').animate({
                    scrollTop: 0
                }, 'fast');
            });

            $('#cancelBtn').on('click', function() {
                $('#formKategori').attr('action', 'create.php');
                $('#formKategori')[0].reset();
                $('#idKategori').val('');
                $('#submitBtn').text('Simpan');
                $(this).addClass('d-none');
            });
        });
    </script>
</body>

</html>