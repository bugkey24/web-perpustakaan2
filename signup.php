<?php
session_start();
require_once('db.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $salt1 = "qm&h*";
  $salt2 = "pg!@";

  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $password_confirm = $_POST['password_confirm'] ?? '';
  $nama = trim($_POST['nama'] ?? '');
  $alamat = trim($_POST['alamat'] ?? '');
  $telepon = trim($_POST['telepon'] ?? '');
  $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');

  // Validasi sederhana
  if (!$username || !$password || !$password_confirm || !$nama) {
    $error = 'Username, password, konfirmasi password, dan nama wajib diisi.';
  } elseif ($password !== $password_confirm) {
    $error = 'Password dan konfirmasi password tidak cocok.';
  } else {
    // Cek apakah username sudah ada
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    if ($stmt->fetch()) {
      $error = 'Username sudah digunakan, silakan pilih username lain.';
    } else {
      // Hash password dengan salt
      $token = sha1("$salt1$password$salt2");

      try {
        $conn->beginTransaction();

        // Insert ke tabel user (role anggota)
        $stmt = $conn->prepare("INSERT INTO user (username, password, role) VALUES (:username, :password, 'anggota')");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $token);
        $stmt->execute();

        $user_id = $conn->lastInsertId();

        // Insert ke tabel anggota
        $stmt = $conn->prepare("INSERT INTO anggota (nama, alamat, telepon, tanggal_lahir, user_id) VALUES (:nama, :alamat, :telepon, :tanggal_lahir, :user_id)");
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':telepon', $telepon);
        $stmt->bindParam(':tanggal_lahir', $tanggal_lahir);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $conn->commit();

        $success = "Registrasi berhasil! Silakan <a href='login.php'>login di sini</a>.";
      } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Terjadi kesalahan: " . $e->getMessage();
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar Anggota - Perpustakaan POLIWANGI</title>
  <link rel="stylesheet" href="/assets/css/signup.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />

</head>

<body>
  <div class="container d-flex justify-content-center">
    <div class="signup-container w-100">
      <h3 class="mb-4 text-center text-primary font-weight-bold">Daftar Anggota Baru</h3>

      <?php if ($error) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success) : ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>

      <form method="POST" action="signup.php" novalidate>
        <div class="form-group">
          <label for="nama">Nama Lengkap<span class="text-danger">*</span></label>
          <input type="text" id="nama" name="nama" class="form-control" required placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" />
          <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
        </div>

        <div class="form-group">
          <label for="alamat">Alamat</label>
          <textarea id="alamat" name="alamat" class="form-control" placeholder="Masukkan alamat"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label for="telepon">No. Telepon</label>
          <input type="text" id="telepon" name="telepon" class="form-control" placeholder="Masukkan nomor telepon" value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>" />
        </div>

        <div class="form-group">
          <label for="tanggal_lahir">Tanggal Lahir</label>
          <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>" />
        </div>

        <hr>

        <div class="form-group">
          <label for="username">Username<span class="text-danger">*</span></label>
          <input type="text" id="username" name="username" class="form-control" required placeholder="Pilih username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
          <div class="invalid-feedback">Username wajib diisi.</div>
        </div>

        <div class="form-group">
          <label for="password">Password<span class="text-danger">*</span></label>
          <input type="password" id="password" name="password" class="form-control" required placeholder="Masukkan password minimal 6 karakter" minlength="6" />
          <div class="invalid-feedback">Password wajib diisi dan minimal 6 karakter.</div>
        </div>

        <div class="form-group">
          <label for="password_confirm">Konfirmasi Password<span class="text-danger">*</span></label>
          <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="Masukkan ulang password" minlength="6" />
          <div class="invalid-feedback">Konfirmasi password wajib diisi dan harus sama dengan password.</div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Daftar</button>
      </form>

      <div class="mt-3 text-center">
        Sudah punya akun? <a href="login.php">Login di sini</a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/signup.js"></script>
</body>

</html>