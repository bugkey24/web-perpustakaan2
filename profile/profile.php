<?php
session_start();
require_once('../db.php');
require_once(__DIR__ . '/profileController.php');

if (!isset($_SESSION['username'])) {
  header('Location: ../login.php');
  exit;
}

$username = $_SESSION['username'];
$userData = getUserData($conn, $username);

if (!$userData) {
  die("Data user tidak ditemukan.");
}

// Folder upload foto profil di luar folder profile
$uploadDir = realpath(__DIR__ . '/../uploads/foto_profile/') . '/';
if (!file_exists($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  list($error, $success) = updateProfil($conn, $userData, $_POST, $uploadDir);
  if (!$error) {
    $userData = getUserData($conn, $username);
    $_SESSION['foto_profil'] = $userData['foto'] ?? null; // update session foto profil
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profil Saya - Perpustakaan POLIWANGI</title>
  <link href="/assets/css/style.css" rel="stylesheet" />
  <link href="/assets/css/profile.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/sidebar.php'; ?>

  <main class="content" id="mainContent" tabindex="-1" role="main">
    <div class="container-fluid">
      <h1 class="mb-4 font-weight-bold text-primary">Profil Saya</h1>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="post" action="profile.php" enctype="multipart/form-data" novalidate>
        <div class="form-group">
          <label>Username</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($userData['username']) ?>" readonly>
        </div>

        <div class="form-group">
          <label>Foto Profil Saat Ini</label><br />
          <?php
          $fotoPath = $userData['foto'] ?? '';
          $fotoFullPath = realpath(__DIR__ . '/../' . $fotoPath);
          ?>
          <?php if (!empty($fotoPath) && $fotoFullPath && file_exists($fotoFullPath)): ?>
            <img src="/<?= htmlspecialchars($fotoPath) ?>" alt="Foto Profil" class="profile-img mb-3" id="currentFotoProfil">
          <?php else: ?>
            <img src="/assets/img/default-profile.png" alt="Foto Profil Default" class="profile-img mb-3" id="currentFotoProfil">
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="foto">Unggah Foto Profil (JPG, PNG, GIF max 2MB)</label>
          <div class="d-flex align-items-center">
            <input type="file" id="foto" name="foto" class="form-control-file" accept="image/jpeg,image/png,image/gif" style="max-width: 300px;" />
            <button type="button" id="btnClearFoto" class="btn btn-outline-danger btn-sm ml-2" title="Hapus file terpilih" style="display:none;">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <small class="form-text text-muted">Pilih file gambar untuk foto profil, kosongkan jika tidak ingin mengubah.</small>
          <div id="previewFoto" style="margin-top:10px;"></div>
        </div>

        <div class="form-group form-check">
          <input type="checkbox" class="form-check-input" id="hapus_foto" name="hapus_foto" value="1" />
          <label class="form-check-label" for="hapus_foto">Hapus Foto Profil Saat Ini</label>
        </div>

        <div class="form-group">
          <label for="nama">Nama Lengkap</label>
          <input type="text" id="nama" name="nama" class="form-control" value="<?= htmlspecialchars($userData['nama'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="alamat">Alamat</label>
          <input type="text" id="alamat" name="alamat" class="form-control" value="<?= htmlspecialchars($userData['alamat'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="telepon">Telepon</label>
          <input type="text" id="telepon" name="telepon" class="form-control" value="<?= htmlspecialchars($userData['telepon'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="tanggal_lahir">Tanggal Lahir</label>
          <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($userData['tanggal_lahir'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="password">Password Baru <small class="text-muted">(kosongkan jika tidak ingin diubah)</small></label>
          <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label for="password_confirm">Konfirmasi Password Baru</label>
          <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary">Perbarui Profil</button>
      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/script.js"></script>
  <script src="/assets/js/profile.js"></script>
</body>

</html>