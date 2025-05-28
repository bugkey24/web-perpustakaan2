<?php
session_start();
require_once('../db.php');
require_once('create.php');
require_once('update.php');
require_once('delete.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'pustakawan') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUser = $_POST['id_user'] ?? null;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'anggota';

    $idAnggota = $_POST['id_anggota'] ?? null;
    $nama = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');

    if (!$username || (!$idUser && !$password) || !$nama) {
        $error = "Username, password (untuk user baru), dan nama anggota wajib diisi.";
    } else {
        try {
            if ($idUser) {
                $success = updateUserAndAnggota($conn, (int)$idUser, $username, $role, $idAnggota, $nama, $alamat, $telepon, $tanggal_lahir, $password ?: null);
            } else {
                $success = insertUserAndAnggota($conn, $username, $password, $role, $nama, $alamat, $telepon, $tanggal_lahir);
            }
        } catch (Exception $e) {
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        $success = deleteUserAndAnggota($conn, $deleteId);
    } catch (Exception $e) {
        $error = "Gagal menghapus data: " . $e->getMessage();
    }
}

$stmt = $conn->query("SELECT u.id AS user_id, u.username, u.role, a.id AS anggota_id, a.nama, a.alamat, a.telepon, a.tanggal_lahir
                      FROM user u
                      LEFT JOIN anggota a ON a.user_id = u.id
                      ORDER BY u.id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola User & Anggota - Pustakawan</title>
    <link href="/assets/css/style.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <main class="content" id="mainContent" tabindex="-1" role="main">
        <div class="container-fluid">
            <h1 class="mb-4 font-weight-bold text-primary">Kelola User & Anggota</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Form tetap seperti sebelumnya -->
            <div class="card form-section shadow-sm">
                <div class="card-body">
                    <form method="post" id="formUserAnggota" novalidate>
                        <input type="hidden" name="id_user" id="idUser" />
                        <input type="hidden" name="id_anggota" id="idAnggota" />

                        <div class="form-group">
                            <label for="nama">Nama Lengkap <small class="text-muted">*</small></label>
                            <input type="text" name="nama" id="nama" class="form-control" placeholder="Masukkan nama lengkap" required />
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" rows="3" placeholder="Masukkan alamat"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="telepon">No Telepon</label>
                            <input type="text" name="telepon" id="telepon" class="form-control" placeholder="Masukkan nomor telepon" />
                        </div>

                        <div class="form-group">
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="username">Username <small class="text-muted">*</small></label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required />
                        </div>

                        <div class="form-group">
                            <label for="password">Password <small class="text-muted">(kosongkan jika tidak ingin ubah)</small></label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" />
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control">
                                <option value="anggota" selected>Anggota</option>
                                <option value="pustakawan">Pustakawan</option>
                            </select>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
                            <button type="button" class="btn btn-secondary d-none" id="cancelBtn">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive shadow-sm w-auto mx-auto">
                <table class="table table-striped table-bordered mb-0" id="userTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Nama Anggota</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Tanggal Lahir</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($data): ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row['role'])) ?></td>
                                    <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['alamat'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['telepon'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_lahir'] ?? '-') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info editBtn"
                                            data-id_user="<?= $row['user_id'] ?>"
                                            data-id_anggota="<?= $row['anggota_id'] ?>"
                                            data-username="<?= htmlspecialchars($row['username']) ?>"
                                            data-role="<?= htmlspecialchars($row['role']) ?>"
                                            data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                            data-alamat="<?= htmlspecialchars($row['alamat']) ?>"
                                            data-telepon="<?= htmlspecialchars($row['telepon']) ?>"
                                            data-tanggal_lahir="<?= htmlspecialchars($row['tanggal_lahir']) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?= $row['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/js/script.js"></script>
    <script>
        $(document).ready(function() {
            $('#userTable').DataTable({
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
                $('#idUser').val($(this).data('id_user'));
                $('#idAnggota').val($(this).data('id_anggota'));
                $('#username').val($(this).data('username'));
                $('#role').val($(this).data('role'));
                $('#nama').val($(this).data('nama'));
                $('#alamat').val($(this).data('alamat'));
                $('#telepon').val($(this).data('telepon'));
                $('#tanggal_lahir').val($(this).data('tanggal_lahir'));
                $('#password').val('');
                $('#submitBtn').text('Update User & Anggota');
                $('#cancelBtn').removeClass('d-none');
                $('html, body').animate({
                    scrollTop: 0
                }, 'fast');
            });

            $('#cancelBtn').on('click', function() {
                $('#formUserAnggota')[0].reset();
                $('#idUser').val('');
                $('#idAnggota').val('');
                $('#submitBtn').text('Simpan');
                $(this).addClass('d-none');
            });
        });
    </script>
</body>

</html>