<?php
require_once("db_connectie.php");
ini_set('max_execution_time', '0');
$vb = maakVerbinding();
$defaultPassword = "password";

$alter = "ALTER table Balie ALTER COLUMN wachtwoord varchar(255)";
$vb->query($alter);

$balies = "SELECT balienummer from Balie";
$data = $vb->query($balies);

foreach($data as $rij) {
    $balienummer = $rij['balienummer'];
    $sql = "UPDATE balie SET wachtwoord = :wachtwoord WHERE balienummer = :bn";
    $query = $vb->prepare($sql);
    $hashed = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $query->execute([":wachtwoord" => $hashed, ":bn" => $balienummer]);
}

$alter = "ALTER table Passagier ALTER COLUMN wachtwoord varchar(255)";
$vb->query($alter);

$passagiers = "SELECT passagiernummer from Passagier";
$data = $vb->query($passagiers);

foreach($data as $rij) {
    $passasiernummer = $rij['passagiernummer'];
    $sql = "UPDATE Passagier SET wachtwoord = :wachtwoord WHERE passagiernummer = :pn";
    $query = $vb->prepare($sql);
    $hashed = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $query->execute([":wachtwoord" => $hashed, ":pn" => $passasiernummer]);
}

$alter = "ALTER table Medewerker ALTER COLUMN wachtwoord varchar(255)";
$vb->query($alter);

$medewerkers = "SELECT medewerkernummer from Medewerker";
$data = $vb->query($medewerkers);

foreach($data as $rij) {
    $medewerkernummer = $rij['medewerkernummer'];
    $sql = "UPDATE Medewerker SET wachtwoord = :wachtwoord WHERE medewerkernummer = :mn";
    $query = $vb->prepare($sql);
    $hashed = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $query->execute([":wachtwoord" => $hashed, ":mn" => $medewerkernummer]);
}

echo "Wachtwoorden zijn gehashed!";