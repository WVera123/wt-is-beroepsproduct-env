<?php
function checkInOfUitgelogd()
{
  if (isset($_SESSION['passagier']) || isset($_SESSION['medewerker'])) {
    echo "<a href='logout.php'>Log uit</a>";
  } else {
    echo "<a href='login.php'>Inloggen</a>";
  }
}

function checkVoorMeldingen()
{
  if (isset($_GET['melding'])) {
    $melding = $_GET['melding'];
    echo "$melding";
  }
}

function genereerTabel($data, $kolommen)
{
  //https://www.tutorialrepublic.com/faq/how-to-get-current-page-url-in-php.php
  $huidigePagina = $_SERVER['PHP_SELF'];
  $htmlTabel = '<table>';

  $htmlTabel .= '<tr>';
  foreach ($kolommen as $kolom) {
    //Changes display name
    if ($kolom == 'max_objecten_pp') {
      $kolom = 'Max objecten pp';
    } else if ($kolom == 'max_gewicht_pp') {
      $kolom = 'Max kg pp';
    }
    $htmlTabel .= '<th>' . ucfirst($kolom) . '</th>';
  }
  $htmlTabel .= '</tr>';

  while ($rij = $data->fetch()) {
    $htmlTabel .= '<tr>';
    foreach ($kolommen as $kolom) {
      if (isset($rij[$kolom])) {
        if ($kolom == 'vertrektijd' || $kolom == 'inchecktijdstip') {
          $datetime = new DateTime($rij[$kolom]);
          $formattedDateTime = $datetime->format('d-M-Y H:i');
          $htmlTabel .= '<td>' . $formattedDateTime . '</td>';
        }else if($kolom == 'passagiernummer'){
          $editPassagierLink = "<a href='edit.php?passagiernummer=$rij[$kolom]'>$rij[$kolom]</a>";
          $htmlTabel .= '<td>' . $editPassagierLink . '</td>';
        }else if($kolom == 'vluchtnummer' && $huidigePagina == '/GelreAirport/allFlights.php'){
          $passagierLink = "<a href='allPassengers.php?vluchtnummer=$rij[$kolom]'>$rij[$kolom]</a>";
          $htmlTabel .= '<td>' . $passagierLink . '</td>';
        } 
        else {
          $htmlTabel .= '<td>' . $rij[$kolom] . '</td>';
        }
      } else {
        $htmlTabel .= '<td>' . '-' . '</td>';
      }
    }
    $htmlTabel .= '</tr>';
  }

  $htmlTabel .= '</table>';

  return $htmlTabel;
}

function bepaalBagageType($objectType, $quantityField, $weightField) {
  $bagageObjecten = [];
  $totaleGewicht = 0;
  if (isset($_POST[$objectType])) {
    $quantity = $_POST[$quantityField];
    $weight = $_POST[$weightField];
    for ($i = 0; $i < $quantity; $i++) {
      $individualWeight = $weight / $quantity;
      $bagageObjecten[] = $individualWeight;
      $totaleGewicht += $individualWeight;
    }
  }
  return [$bagageObjecten, $totaleGewicht];
}

function checkBestaanKolom($db, $tabelnaam, $kolomnaam, $waarde) {
  $query = "SELECT COUNT(*) AS aantal 
            FROM $tabelnaam 
            WHERE $kolomnaam = :value";
  $data = $db->prepare($query);
  $data->execute([':value' => $waarde]);
  $resultaat = $data->fetch(PDO::FETCH_ASSOC);
  return $resultaat['aantal'] > 0;
}

function selecteerMaatschappij($db)
{
  $db = maakVerbinding();

  $query = "SELECT maatschappijcode
            FROM Maatschappij";
  $maatschappijen = $db->prepare($query);
  $maatschappijen->execute();
  $maatschappijen = $maatschappijen->fetchAll();

  foreach ($maatschappijen as $maatschappij) {
    $maatschappijCode = $maatschappij['maatschappijcode'];
    echo "<option value='$maatschappijCode'>$maatschappijCode</option>";
  }
}

function selecteerGate($db)
{
  $db = maakVerbinding();

  $query = "SELECT gatecode
            FROM Gate";
  $gates = $db->prepare($query);
  $gates->execute();
  $gates = $gates->fetchAll();

  foreach ($gates as $gate) {
    $gatecode = $gate['gatecode'];
    echo "<option value='$gatecode'>$gatecode</option>";
  }
}

function selecteerBalie($db)
{
  $db = maakVerbinding();

  $query = "SELECT balienummer
            FROM Balie";
  $balies = $db->prepare($query);
  $balies->execute();
  $balies = $balies->fetchAll();

  foreach ($balies as $balie) {
    $balienummer = $balie['balienummer'];
    echo "<option value='$balienummer'>$balienummer</option>";
  }
}

?>