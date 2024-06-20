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
          $geformatteerdDateTime = $datetime->format('d-M-Y H:i');
          $htmlTabel .= '<td>' . $geformatteerdDateTime . '</td>';
        } else if ($kolom == 'passagiernummer') {
          $bewerkPassagierLink = "<a href='edit.php?passagiernummer=$rij[$kolom]'>$rij[$kolom]</a>";
          $htmlTabel .= '<td>' . $bewerkPassagierLink . '</td>';
        } else if ($kolom == 'vluchtnummer' && $huidigePagina == '/GelreAirport/allFlights.php') {
          $vluchtPassagierLink = "<a href='allPassengers.php?vluchtnummer=$rij[$kolom]'>$rij[$kolom]</a>";
          $htmlTabel .= '<td>' . $vluchtPassagierLink . '</td>';
        } else {
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

function bepaalBagageType($typeObject, $hoeveelheidObjecten, $gewichtObject)
{
  $bagageObjecten = [];
  $totaleGewicht = 0;
  if (isset($_POST[$typeObject])) {
    $hoeveelheid = $_POST[$hoeveelheidObjecten];
    $gewicht = $_POST[$gewichtObject];
    for ($i = 0; $i < $hoeveelheid; $i++) {
      $gewichtPs = $gewicht / $hoeveelheid;
      $bagageObjecten[] = $gewichtPs;
      $totaleGewicht += $gewichtPs;
    }
  }
  return [$bagageObjecten, $totaleGewicht];
}

function checkBestaanKolom($db, $tabelnaam, $kolomnaam, $waarde, $kolomnaam2 = null, $waarde2 = null)
{
  $query = "SELECT COUNT(*) AS aantal 
              FROM $tabelnaam 
              WHERE $kolomnaam = :waarde";

  if (!empty($kolomnaam2) && !empty($waarde2)) {
    $query .= " AND $kolomnaam2 = :waardeTwee";
  }

  $data = $db->prepare($query);

  $data->bindParam(':waarde', $waarde);

  if (!empty($kolomnaam2) && !empty($waarde2)) {
    $data->bindParam(':waardeTwee', $waarde2);
  }

  $data->execute();

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
    $geselecteerd = (isset($_POST['gatecode']) && $_POST['gatecode'] == $gatecode) ? 'selected' : '';
    echo "<option value='$gatecode' $geselecteerd>$gatecode</option>";
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
    $geselecteerd = (isset($_POST['balienummer']) && $_POST['balienummer'] == $balienummer) ? 'selected' : '';
    echo "<option value='$balienummer' $geselecteerd>$balienummer</option>";
  }
}

?>