<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once('db.php');

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'anggota';

try {
    if ($role === 'pustakawan') {
        $totalKategori = $conn->query("SELECT COUNT(*) FROM bibliografi_kategori")->fetchColumn();
        $totalBibliografi = $conn->query("SELECT COUNT(*) FROM bibliografi")->fetchColumn();
        $totalKoleksi = $conn->query("SELECT COUNT(*) FROM koleksi")->fetchColumn();
        $totalAnggota = $conn->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
        $totalPeminjaman = $conn->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
        // Untuk pustakawan, user spesifik tidak perlu
        $totalPeminjamanUser = null;
    } else if ($role === 'anggota') {
        $totalKategori = null;
        $totalBibliografi = null;
        $totalAnggota = null;
        $totalPeminjaman = null;
        $totalKoleksi = $conn->query("SELECT COUNT(*) FROM koleksi")->fetchColumn();

        // Hitung peminjaman aktif user berdasar username session
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM peminjaman p
            JOIN anggota a ON p.anggota_id = a.id
            JOIN user u ON a.user_id = u.id
            WHERE u.username = :username AND p.tgl_kembali IS NULL
        ");
        $stmt->execute([':username' => $username]);
        $totalPeminjamanUser = $stmt->fetchColumn();
    } else {
        // Role lain, set semua ke null atau 0 agar aman
        $totalKategori = $totalBibliografi = $totalKoleksi = $totalAnggota = $totalPeminjaman = $totalPeminjamanUser = 0;
    }
} catch (PDOException $e) {
    $totalKategori = $totalBibliografi = $totalKoleksi = $totalAnggota = $totalPeminjamanUser = $totalPeminjaman = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Perpustakaan POLIWANGI - Dashboard</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="content" id="mainContent" tabindex="-1" role="main">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div>
                    <h1 class="font-weight-bold text-primary mb-1"> DASHBOARD <?= strtoupper(htmlspecialchars($role)) ?></h1>
                    <p class="text-muted mb-0">
                        <i>Selamat datang <?= htmlspecialchars($username) ?>! Anda sedang berada di halaman utama aplikasi perpustakaan POLIWANGI.</i>
                    </p>
                </div>
            </div>
            <div class="row">
                <?php if ($role === 'pustakawan'): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100" role="button" tabindex="0" onclick="location.href='/bibliografi_kategori/index.php'" onkeypress="if(event.key==='Enter' || event.key===' ') this.click()">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><i class="fas fa-th-list"></i> Kategori Bibliografi</h5>
                                <p class="card-text">Kelola kategori-kategori bibliografi perpustakaan.</p>
                                <span class="badge badge-warning"><?= $totalKategori ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100" role="button" tabindex="0" onclick="location.href='/bibliografi/index.php'" onkeypress="if(event.key==='Enter' || event.key===' ') this.click()">
                            <div class="card-body">
                                <h5 class="card-title text-success"><i class="fas fa-book"></i> Bibliografi</h5>
                                <p class="card-text">Kelola data bibliografi perpustakaan.</p>
                                <span class="badge badge-success"><?= $totalBibliografi ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100" role="button" tabindex="0" onclick="location.href='/koleksi/index.php'" onkeypress="if(event.key==='Enter' || event.key===' ') this.click()">
                            <div class="card-body">
                                <h5 class="card-title text-info"><i class="fas fa-archive"></i> Koleksi</h5>
                                <p class="card-text">Kelola koleksi buku perpustakaan.</p>
                                <span class="badge badge-info"><?= $totalKoleksi ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100" role="button" tabindex="0" onclick="location.href='/user/index.php'" onkeypress="if(event.key==='Enter' || event.key===' ') this.click()">
                            <div class="card-body">
                                <h5 class="card-title text-secondary"><i class="fas fa-users"></i> Anggota</h5>
                                <p class="card-text">Kelola data anggota perpustakaan.</p>
                                <span class="badge badge-secondary"><?= $totalAnggota ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100" role="button" tabindex="0" onclick="location.href='/peminjaman/index.php'" onkeypress="if(event.key==='Enter' || event.key===' ') this.click()">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><i class="fas fa-book-reader"></i> Peminjaman</h5>
                                <p class="card-text">Kelola data peminjaman buku perpustakaan.</p>
                                <span class="badge badge-warning"><?= $totalPeminjaman ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-6 col-lg-6 mb-4">
                        <div class="card h-100" role="button" tabindex="0" onclick="location.href='/koleksi/index.php'" onkeypress="if(event.key==='Enter' || event.key===' ') this.click()">
                            <div class="card-body">
                                <h5 class="card-title text-info"><i class="fas fa-archive"></i> Koleksi Buku</h5>
                                <p class="card-text">Lihat daftar koleksi buku yang tersedia.</p>
                                <span class="badge badge-info"><?= $totalKoleksi ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 mb-4">
                        <div class="card h-100" role="button" tabindex="0" onclick="location.href='/peminjaman/anggota.php'" onkeypress="if(event.key==='Enter' || event.key===' ') this.click()">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><i class="fas fa-book-reader"></i> Peminjaman Saya</h5>
                                <p class="card-text">Lihat dan kelola peminjaman Anda.</p>
                                <span class="badge badge-warning"><?= $totalPeminjamanUser ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info mt-4" role="alert">
                <i class="fas fa-info-circle"></i> Sistem perpustakaan siap membantu Anda mengelola data dengan mudah dan cepat.
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
</body>

</html>