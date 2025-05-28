<?php
function updateUserAndAnggota($conn, $idUser, $username, $role, $idAnggota, $nama, $alamat, $telepon, $tanggal_lahir, $password = null)
{
    $conn->beginTransaction();
    try {
        if ($password) {
            $salt1 = "qm&h*";
            $salt2 = "pg!@";
            $hash = sha1("$salt1$password$salt2");
            $stmtUser = $conn->prepare("UPDATE user SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmtUser->execute([$username, $hash, $role, $idUser]);
        } else {
            $stmtUser = $conn->prepare("UPDATE user SET username = ?, role = ? WHERE id = ?");
            $stmtUser->execute([$username, $role, $idUser]);
        }

        if ($idAnggota) {
            $stmtAnggota = $conn->prepare("UPDATE anggota SET nama = ?, alamat = ?, telepon = ?, tanggal_lahir = ? WHERE id = ?");
            $stmtAnggota->execute([$nama, $alamat, $telepon, $tanggal_lahir, $idAnggota]);
        } else {
            $stmtAnggota = $conn->prepare("INSERT INTO anggota (nama, alamat, telepon, tanggal_lahir, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmtAnggota->execute([$nama, $alamat, $telepon, $tanggal_lahir, $idUser]);
        }

        $conn->commit();
        return "User & anggota berhasil diperbarui.";
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
