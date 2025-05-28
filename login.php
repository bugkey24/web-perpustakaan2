<?php
session_start();
require_once('db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $salt1 = "qm&h*";
  $salt2 = "pg!@";

  $username = $_POST['username'] ?? '';
  $pw_temp = $_POST['password'] ?? '';
  $token = sha1("$salt1$pw_temp$salt2");

  $stmt = $conn->prepare("SELECT * FROM user WHERE username = :username");
  $stmt->bindParam(':username', $username);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && $token === $user['password']) {
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];

    // Jika role anggota, ambil anggota_id dan simpan di session
    if ($user['role'] === 'anggota') {
      $stmt2 = $conn->prepare("SELECT id FROM anggota WHERE user_id = ?");
      $stmt2->execute([$user['id']]);
      $anggota = $stmt2->fetch(PDO::FETCH_ASSOC);
      if ($anggota) {
        $_SESSION['anggota_id'] = $anggota['id'];
      }
    }

    // Redirect sesuai role
    if ($user['role'] === 'pustakawan') {
      header('Location: index.php');
    } else {
      header('Location: index.php');
    }
    exit;
  } else {
    $error = "Username atau password salah.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Perpustakaan POLIWANGI</title>
  <link rel="stylesheet" href="/assets/css/login.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
</head>

<body>
  <div class="container d-flex justify-content-center">
    <div class="login-container">
      <h3 class="mb-4 text-center text-primary font-weight-bold">Login Perpustakaan</h3>

      <?php if ($error) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <div class="form-group">
          <label for="username">Username</label>
          <input autofocus type="text" id="username" name="username" class="form-control" required
            placeholder="Masukkan username" />
          <div class="invalid-feedback">Username harus diisi.</div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" required
            placeholder="Masukkan password" />
          <div class="invalid-feedback">Password harus diisi.</div>
        </div>

        <div class="form-check mb-3">
          <input type="checkbox" class="form-check-input" id="rememberme" name="rememberme" value="1" />
          <label class="form-check-label" for="rememberme">Ingat saya</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
      </form>

      <div class="mt-3 text-center">
        Belum punya akun? <a href="signup.php">Daftar di sini</a>
      </div>
    </div>
  </div>

  <script>
    // Bootstrap form validation
    (function() {
      'use strict';
      window.addEventListener(
        'load',
        function() {
          var forms = document.getElementsByTagName('form');
          Array.prototype.filter.call(forms, function(form) {
            form.addEventListener(
              'submit',
              function(event) {
                if (form.checkValidity() === false) {
                  event.preventDefault();
                  event.stopPropagation();
                }
                form.classList.add('was-validated');
              },
              false
            );
          });
        },
        false
      );
    })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>