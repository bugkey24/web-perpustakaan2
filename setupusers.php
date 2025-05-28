<?php
require_once 'db.php';
$salt1 = "qm&h*";
$salt2 = "pg!@";

$username = 'bsmith';
$password = 'mysecret';
$role = 'pustakawan';
$token = sha1("$salt1$password$salt2");

try {
  $sql = "INSERT INTO user (username,password,role) 
          VALUES ('$username','$token','$role')";
  $conn->exec($sql);
  //$id_terakhir = $conn->lastInsertId();
  echo "Data user pertama telah ditambahkan. <br>";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}

$username = 'pjones';
$password = 'acrobat';
$role = 'anggota';
$token = sha1("$salt1$password$salt2");

try {
  $sql = "INSERT INTO user (username,password,role) 
          VALUES ('$username','$token','$role')";
  $conn->exec($sql);
  $id_terakhir = $conn->lastInsertId();
  echo "Data user kedua telah ditambahkan";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}

?>
