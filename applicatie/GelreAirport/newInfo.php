<?php
session_start();
if (!isset($_SESSION['medewerker'])) {
  header("location:home.php?melding=Deze pagina is alleen zichtbaar voor medewerkers.");
  die;
}
require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';

$melding = '';
$fouten = [];

if (isset($_POST['nieuwePassagier'])) {
  $db = maakVerbinding();
  $passagiernummer = $_POST['passagiernummer'];
  $achternaam = $_POST['achternaam'];
  $vluchtnummer = $_POST['vluchtnummer'];
  $geslacht = $_POST['geslacht'];
  $balienummer = $_POST['balienummer'];
  $stoel = $_POST['stoel'];
  $incheckDatum = isset($_POST['incheckdatum']) ? $_POST['incheckdatum'] : NULL; //Als incheckdatum niet leeg is, dan wordt de variable de ingevulde waarde en anders null.
  $incheckTijdstip = isset($_POST['inchecktijdstip']) ? $_POST['inchecktijdstip'] : NULL;
  $incheckDatumTijd = ($incheckDatum && $incheckTijdstip) ? "$incheckDatum $incheckTijdstip" : NULL; //Alleen als ze beiden gevuld zijn dan wordt het in de database gezet.
  $wachtwoord = $_POST['wachtwoord'];
  $wachtwoordCheck = $_POST['wachtwoordCheck'];

  // Check of passagiernummer al bestaat
  $queryCheckPassagierNum = "SELECT COUNT(*) AS count 
                            FROM Passagier 
                            WHERE passagiernummer = :passagiernummer";
  $dataCheckPassagierNum = $db->prepare($queryCheckPassagierNum);
  $dataCheckPassagierNum->execute([':passagiernummer' => $passagiernummer]);
  $resultPassagiernummer = $dataCheckPassagierNum->fetch(PDO::FETCH_ASSOC);

  if ($resultPassagiernummer['count'] > 0) {
    $fouten[] = 'Dit passagiernummer is al in gebruik.';
  }

  // Check of vluchtnummernummer bestaat
  $queryCheckVluchtNum = "SELECT COUNT(*) AS count 
                          FROM Vlucht 
                          WHERE vluchtnummer = :vluchtnummer";
  $dataCheckVluchtNum = $db->prepare($queryCheckVluchtNum);
  $dataCheckVluchtNum->execute([':vluchtnummer' => $vluchtnummer]);
  $resultVluchtnummer = $dataCheckVluchtNum->fetch(PDO::FETCH_ASSOC);

  if ($resultVluchtnummer['count'] <= 0) {
    $fouten[] = 'Deze vlucht bestaat niet.';
  }

  // Check of stoel is bezet
  $queryCheckStoel = "SELECT COUNT(*) AS count 
                            FROM Passagier 
                            WHERE stoel = :stoel AND vluchtnummer = :vluchtnummer";
  $dataCheckStoel = $db->prepare($queryCheckStoel);
  $dataCheckStoel->execute([':stoel' => $stoel, ':vluchtnummer' => $vluchtnummer]);
  $resultStoel = $dataCheckStoel->fetch(PDO::FETCH_ASSOC);

  if ($resultStoel['count'] > 0) {
    $fouten[] = 'Deze stoel is al bezet.';
  }

  if ($geslacht == 'null') {
    $geslacht = NULL;
  }

  if ($balienummer == 'null') {
    $balienummer = NULL;
  }

  if (strlen($wachtwoord) < 8) {
    $fouten[] = 'Het wachtwoord moet minstens 8 karakters zijn!';
  }

  if ($wachtwoord != $wachtwoordCheck) {
    $fouten[] = 'De wachtwoorden komen niet overeen!';
  }

  if (count($fouten) > 0) {
    $melding = "Er waren fouten in de invoer.<ul>";
    foreach ($fouten as $fout) {
      $melding .= "<li>$fout</li>";
    }
    $melding .= "</ul>";

  } else {
    $passwordhash = password_hash($wachtwoord, PASSWORD_DEFAULT);
    $db = maakVerbinding();

    $queryPassagierCount = 'SELECT COUNT(*) AS passagierCount
                            FROM Passagier 
                            WHERE vluchtnummer = :vluchtnummer';

    $dataPassagierCount = $db->prepare($queryPassagierCount);
    $dataPassagierCount->bindParam(':vluchtnummer', $vluchtnummer);
    $dataPassagierCount->execute();
    $resultaatPassengerCount = $dataPassagierCount->fetchColumn();


    $queryMaxAantal = 'SELECT max_aantal 
                      FROM Vlucht 
                      WHERE vluchtnummer = :vluchtnummer';
    $dataMaxAantal = $db->prepare($queryMaxAantal);
    $dataMaxAantal->bindParam(':vluchtnummer', $vluchtnummer);
    $dataMaxAantal->execute();
    $resultaatMaxAantal = $dataMaxAantal->fetchColumn();

    if ($resultaatPassengerCount >= $resultaatMaxAantal) {
      $melding = "Het maximum aantal passagiers voor deze vlucht ($resultaatMaxAantal) is al bereikt.";
    } else {
      $sql = 'INSERT INTO Passagier(passagiernummer, naam, vluchtnummer, geslacht, balienummer, stoel, inchecktijdstip, wachtwoord)
              SELECT :passagiernummer, :naam, :vluchtnummer, :geslacht, :balienummer, :stoel, :inchecktijdstip, :wachtwoord';
      $query = $db->prepare($sql);

      $data_array = [
        ':passagiernummer' => $passagiernummer,
        ':naam' => $achternaam,
        ':vluchtnummer' => $vluchtnummer,
        ':geslacht' => $geslacht,
        ':balienummer' => $balienummer,
        ':stoel' => $stoel,
        ':inchecktijdstip' => $incheckDatumTijd,
        ':wachtwoord' => $passwordhash
      ];
      $succes = $query->execute($data_array);

      if ($succes) {
        $melding = 'Gebruiker is geregistreerd!';
      } else {
        $melding = 'Registratie is mislukt!';
      }
    }
  }
}

if (isset($_POST['nieuweVlucht'])) {
  $db = maakVerbinding();
  $vluchtnummer = $_POST['vluchtnummer'];
  $bestemming = $_POST['bestemming'];
  $gatecode = $_POST['gatecode'];
  $maxAantal = $_POST['max_aantal'];
  $maxGewichtPp = $_POST['max_gewichtpp'];
  $maxTotaalGewicht = $_POST['max_totaalgewicht'];
  $vertrekDatum = isset($_POST['vertrekdatum']) ? $_POST['vertrekdatum'] : NULL;
  $vertrekTijd = isset($_POST['vertrektijd']) ? $_POST['vertrektijd'] : NULL;
  $vertrekDatumTijd = ($vertrekDatum && $vertrekTijd) ? "$vertrekDatum $vertrekTijd" : NULL;
  $maatschappijcode = $_POST['maatschappijcode'];

  // Check of passagiernummer al bestaat
  $queryCheckVluchtNum = "SELECT COUNT(*) AS count 
                          FROM Vlucht 
                          WHERE vluchtnummer = :vluchtnummer";
  $dataCheckVluchtNum = $db->prepare($queryCheckVluchtNum);
  $dataCheckVluchtNum->execute([':vluchtnummer' => $vluchtnummer]);
  $resultVluchtnummer = $dataCheckVluchtNum->fetch(PDO::FETCH_ASSOC);

  if ($resultVluchtnummer['count'] > 0) {
    $fouten[] = 'Dit vluchtnummer is al in gebruik.';
  }

  if (strlen($maatschappijcode) > 2) {
    $fouten[] = 'De maatschappijcode mag niet langer dan 2 letters zijn.';
  }

  if ($vertrekDatum != NULL) {
    if ($vertrekDatumTijd < date('Y-m-d H:i:s')) {
      $fouten[] = 'De vertrekdatum mag niet in het verleden liggen..';
    }
  }

  if ($gatecode == 'null') {
    $gatecode = NULL;
  }

  //Voorkomt overtreding totale gewicht aan bagage van vlucht.
  if ($maxAantal * $maxGewichtPp > $maxTotaalGewicht) {
    $fouten[] = "Fout in systeem. Maximaal toegestane gewicht kan worden overschreden.";
  }

  if (count($fouten) > 0) {
    $melding = "Er waren fouten in de invoer.<ul>";
    foreach ($fouten as $fout) {
      $melding .= "<li>$fout</li>";
    }
    $melding .= "</ul>";
  } else {
    $sql = 'INSERT INTO Vlucht (vluchtnummer, bestemming, gatecode, max_aantal, max_gewicht_pp, max_totaalgewicht, vertrektijd, maatschappijcode)
            VALUES(:vluchtnummer, :bestemming, :gatecode, :max_aantal, :max_gewicht_pp, :max_totaalgewicht, :vertrektijd, :maatschappijcode)';
    $query = $db->prepare($sql);

    $data_array = [
      ':vluchtnummer' => $vluchtnummer,
      ':bestemming' => $bestemming,
      ':gatecode' => $gatecode,
      ':max_aantal' => $maxAantal,
      ':max_gewicht_pp' => $maxGewichtPp,
      ':max_totaalgewicht' => $maxTotaalGewicht,
      ':vertrektijd' => $vertrekDatumTijd,
      ':maatschappijcode' => $maatschappijcode
    ];

    $succes = $query->execute($data_array);

    if ($succes) {
      $melding = 'Vlucht is toegevoegd!';
    } else {
      $melding = 'Vlucht toevoegen is mislukt!';
    }
  }
}
echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Gegevensinvoer</h1>
      <?php checkInOfUitgelogd() ?>
    </div>
  </header>
  <main class="container">
    <?= $melding ?>
    <div class="formGrid">
      <form action="newinfo.php" id="passengerForm" method="POST">
        <h2>Voer een nieuwe passagier in</h2>
        <div class="data">
          <label for="passagiernummer">Passagiernummer*:</label>
          <input type="number" name="passagiernummer" id="passagiernummer" required>

          <label for="achternaam">Achternaam*:</label>
          <input type="text" name="achternaam" id="achternaam" required>

          <label for="vluchtnummer">Vluchtnummer*:</label>
          <input type="number" name="vluchtnummer" id="vluchtnummer" required>

          <label for="geslacht">Geslacht</label>
          <select name="geslacht" id="geslacht">
            <option value="null">Kies een geslacht</option>
            <option value="V">V</option>
            <option value="M">M</option>
            <option value="x">x</option>
          </select>

          <label for="balienummer">Balienummer:</label>
          <select name="balienummer" id="balienummer">
            <option value="null">Kies een balienummer</option>
            <?= selecteerBalie($db) ?>
          </select>

          <label for="stoel">Stoelnummer:</label>
          <input type="text" name="stoel" id="stoel">

          <label for="incheckdatum">Incheckdatum:</label>
          <input type="date" name="incheckdatum" id="incheckdatum" value="">

          <label for="inchecktijdstip">Inchecktijdstip:</label>
          <input type="time" name="inchecktijdstip" id="inchecktijdstip">

          <label for="wachtwoord">Wachtwoord:</label>
          <input type="password" id="wachtwoord" name="wachtwoord" required>

          <label for="wachtwoordCheck">Wachtwoord check:</label>
          <input type="password" id="wachtwoordCheck" name="wachtwoordCheck" required>
        </div>
        <input type="submit" name="nieuwePassagier" id="nieuwePassagier" class="button" value="Verzend">
      </form>

      <form action="newinfo.php" id="flightForm" method="POST">
        <h2>Voer een nieuwe vlucht in</h2>
        <div class="data">
          <label for="vluchtnummer">Vluchtnummer*:</label>
          <input type="number" name="vluchtnummer" id="vluchtnummer" required>

          <label for="bestemming">Bestemming*:</label>
          <input type="text" name="bestemming" id="bestemming" required>

          <label for="gatecode">Gatecode:</label>
          <select name="gatecode" id="gatecode">
            <option value="null">Kies een gatecode</option>
            <?= selecteerGate($db) ?>
          </select>

          <label for="max_aantal">Max. aantal passagiers*:</label>
          <input type="number" name="max_aantal" id="max_aantal" required>

          <label for="max_gewichtpp">Max. gewicht p.p*:</label>
          <input type="number" name="max_gewichtpp" id="max_gewichtpp" required>

          <label for="max_totaalgewicht">Max. totaal gewicht*:</label>
          <input type="number" name="max_totaalgewicht" id="max_totaalgewicht" required>

          <label for="vertrekdatum">Vertrekdatum:</label>
          <input type="date" name="vertrekdatum" id="vertrekdatum">

          <label for="vertrektijd">Vertrektijd:</label>
          <input type="time" name="vertrektijd" id="vertrektijd">

          <label for="maatschappijcode">Maatschappijcode*:</label>
          <select name="maatschappijcode" id="maatschappijcode" required>
            <?= selecteerMaatschappij($db) ?>
          </select>
        </div>
        <input type="submit" name="nieuweVlucht" id="nieuweVlucht" class="button" value="Verzend">
      </form>
    </div>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>