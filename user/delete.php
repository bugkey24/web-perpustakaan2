<?php
function deleteUserAndAnggota($conn, $idUser)
{
    $conn->beginTransaction();
    try {
        $stmtAnggota = $conn->prepare("DELETE FROM anggota WHERE user_id = ?");
        $stmtAnggota->execute([$idUser]);

        $stmtUser = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmtUser->execute([$idUser]);

        $conn->commit();
        return "User & anggota berhasil dihapus.";
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
