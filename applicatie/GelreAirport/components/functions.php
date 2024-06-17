<?php
function checkInOfUitgelogd(){
  if (isset($_SESSION['passagier']) || isset($_SESSION['medewerker'])){
    echo "<a href='logout.php'>Log uit</a>";
  }else{
    echo "<a href='login.php'>Inloggen</a>";
  }
}

function checkVoorMeldingen(){
  if (isset($_GET['melding'])){
    $melding = $_GET['melding'];
    echo "$melding";
  }
}

function selecteerMaatschappij($db){
  $db = maakVerbinding();

  $query = "SELECT maatschappijcode
            FROM Maatschappij";
  $maatschappijen = $db->prepare($query);
  $maatschappijen->execute();
  $maatschappijen = $maatschappijen->fetchAll();

  foreach($maatschappijen as $maatschappij){
    $maatschappijCode = $maatschappij['maatschappijcode'];
    echo "<option value='$maatschappijCode'>$maatschappijCode</option>";
  }
}

function selecteerGate($db){
  $db = maakVerbinding();

  $query = "SELECT gatecode
            FROM Gate";
  $gates = $db->prepare($query);
  $gates->execute();
  $gates = $gates->fetchAll();

  foreach($gates as $gate){
    $gatecode = $gate['gatecode'];
    echo "<option value='$gatecode'>$gatecode</option>";
  }
}

function selecteerBalie($db){
  $db = maakVerbinding();

  $query = "SELECT balienummer
            FROM Balie";
  $balies = $db->prepare($query);
  $balies->execute();
  $balies = $balies->fetchAll();

  foreach($balies as $balie){
    $balienummer = $balie['balienummer'];
    echo "<option value='$balienummer'>$balienummer</option>";
  }
}

?>