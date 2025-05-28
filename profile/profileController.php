<?php
// profileController.php
require_once('../db.php');

function getUserData($conn, $username)
{
  $stmt = $conn->prepare("
        SELECT u.id AS user_id, u.username, u.role,
               a.id AS anggota_id, a.nama, a.alamat, a.telepon, a.tanggal_lahir, a.foto
        FROM user u
        LEFT JOIN anggota a ON a.user_id = u.id
        WHERE u.username = ?
    ");
  $stmt->execute([$username]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function uploadFotoProfil($file, $userId, $uploadDir, $fotoLama)
{
  $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

  if ($file['error'] !== UPLOAD_ERR_OK) {
    return [null, "Gagal upload file"];
  }

  $fileTmpPath = $file['tmp_name'];
  $fileName = basename($file['name']);
  $fileSize = $file['size'];
  $fileType = mime_content_type($fileTmpPath);

  if (!in_array($fileType, $allowedTypes)) {
    return [null, "Format foto harus JPG, PNG, atau GIF."];
  }

  if ($fileSize > 2 * 1024 * 1024) {
    return [null, "Ukuran foto maksimal 2MB."];
  }

  $ext = pathinfo($fileName, PATHINFO_EXTENSION);
  $newFileName = 'profile_' . $userId . '_' . time() . '.' . $ext;

  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }

  $destPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFileName;

  if (!move_uploaded_file($fileTmpPath, $destPath)) {
    return [null, "Gagal mengupload foto profil."];
  }

  // Hapus file lama jika ada dan berbeda
  $oldFilePath = realpath(__DIR__ . '/../' . $fotoLama);
  if (!empty($fotoLama) && $oldFilePath && file_exists($oldFilePath) && $fotoLama !== 'uploads/foto_profile/' . $newFileName) {
    unlink($oldFilePath);
  }

  // Return path relatif untuk simpan di DB dan pakai di src img
  return ['uploads/foto_profile/' . $newFileName, null];
}

function updateProfil($conn, $userData, $dataPost, $uploadDir)
{
  $fotoPath = $userData['foto'];

  // Hapus foto jika checkbox dicentang
  if (!empty($dataPost['hapus_foto'])) {
    $oldFilePath = realpath(__DIR__ . '/../' . $fotoPath);
    if (!empty($fotoPath) && $oldFilePath && file_exists($oldFilePath)) {
      unlink($oldFilePath);
    }
    $fotoPath = null;
  }

  // Upload foto baru jika ada
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    list($newFotoPath, $uploadError) = uploadFotoProfil($_FILES['foto'], $userData['user_id'], $uploadDir, $fotoPath);
    if ($uploadError) {
      return [$uploadError, null];
    }
    $fotoPath = $newFotoPath;
  }

  $fields = [];
  $params = [];

  if (isset($dataPost['nama']) && trim($dataPost['nama']) !== '') {
    $fields[] = "nama = ?";
    $params[] = trim($dataPost['nama']);
  }

  if (isset($dataPost['alamat'])) {
    $fields[] = "alamat = ?";
    $params[] = trim($dataPost['alamat']);
  }

  if (isset($dataPost['telepon'])) {
    $fields[] = "telepon = ?";
    $params[] = trim($dataPost['telepon']);
  }

  if (isset($dataPost['tanggal_lahir'])) {
    $fields[] = "tanggal_lahir = ?";
    $params[] = trim($dataPost['tanggal_lahir']);
  }

  $fields[] = "foto = ?";
  $params[] = $fotoPath;

  if (!empty($fields)) {
    if ($userData['anggota_id']) {
      $sql = "UPDATE anggota SET " . implode(", ", $fields) . " WHERE id = ?";
      $params[] = $userData['anggota_id'];
      $stmt = $conn->prepare($sql);
      $updatedAnggota = $stmt->execute($params);
    } else {
      $columns = [];
      $placeholders = [];
      $values = [];
      foreach ($fields as $i => $field) {
        $columns[] = explode(' ', $field)[0];
        $placeholders[] = '?';
        $values[] = $params[$i];
      }
      $columns[] = "user_id";
      $placeholders[] = "?";
      $values[] = $userData['user_id'];
      $sql = "INSERT INTO anggota (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
      $stmt = $conn->prepare($sql);
      $updatedAnggota = $stmt->execute($values);
    }
  } else {
    $updatedAnggota = true;
  }

  $updatedUser = true;
  if (!empty($dataPost['password'])) {
    if ($dataPost['password'] !== $dataPost['password_confirm']) {
      return ["Password dan konfirmasi tidak cocok", null];
    }
    $passwordHash = password_hash($dataPost['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
    $updatedUser = $stmt->execute([$passwordHash, $userData['user_id']]);
  }

  if ($updatedAnggota && $updatedUser) {
    return [null, "Profil berhasil diperbarui"];
  }
  return ["Gagal memperbarui profil", null];
}
