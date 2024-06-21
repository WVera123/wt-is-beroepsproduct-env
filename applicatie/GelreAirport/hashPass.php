<?php

require_once ("db_connectie.php");
$vb = maakVerbinding();

$alter = "ALTER table Passagier ALTER COLUMN wachtwoord varchar(255)";
$vb->query($alter);

$balies = "SELECT passagiernummer from Passagier";
$data = $vb->query($balies);

foreach ($data as $rij) {
  $balienummer = $rij['passagiernummer'];
  $sql = "UPDATE Passagier SET wachtwoord = :wachtwoord WHERE passagiernummer = :bn";
  $query = $vb->prepare($sql);
  $hashed = password_hash("password", PASSWORD_DEFAULT);
  $query->execute([":wachtwoord" => $hashed, ":bn" => $balienummer]);
}

?>