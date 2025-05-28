<?php
function insertUserAndAnggota($conn, $username, $password, $role, $nama, $alamat, $telepon, $tanggal_lahir)
{
    $salt1 = "qm&h*";
    $salt2 = "pg!@";
    $hash = sha1("$salt1$password$salt2");

    // Cek username sudah ada
    $checkUser = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $checkUser->execute([$username]);
    if ($checkUser->fetch()) {
        throw new Exception("Username sudah digunakan.");
    }

    $conn->beginTransaction();
    try {
        // Insert user
        $stmtUser = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        $stmtUser->execute([$username, $hash, $role]);
        $newUserId = $conn->lastInsertId();

        // Insert anggota
        $stmtAnggota = $conn->prepare("INSERT INTO anggota (nama, alamat, telepon, tanggal_lahir, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmtAnggota->execute([$nama, $alamat, $telepon, $tanggal_lahir, $newUserId]);

        $conn->commit();
        return "User & anggota berhasil ditambahkan.";
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
