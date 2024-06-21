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
  $stoel = !empty($_POST['stoel']) ? $_POST['stoel'] : NULL; //Als stoel niet leeg is, dan wordt de variable de ingevulde waarde en anders null.
  $incheckDatum = isset($_POST['incheckdatum']) ? $_POST['incheckdatum'] : NULL; //Als incheckdatum gevuld is, dan wordt de variable de ingevulde waarde en anders null.
  $incheckTijdstip = isset($_POST['inchecktijdstip']) ? $_POST['inchecktijdstip'] : NULL;
  $incheckDatumTijd = ($incheckDatum && $incheckTijdstip) ? "$incheckDatum $incheckTijdstip" : NULL; //Alleen als ze beiden gevuld zijn dan wordt het in de database gezet.
  $wachtwoord = $_POST['wachtwoord'];
  $wachtwoordCheck = $_POST['wachtwoordCheck'];

  // Check of passagiernummer al bestaat
  if (checkBestaanKolom($db, 'Passagier', 'passagiernummer', $passagiernummer)) {
    $fouten[] = 'Dit passagiernummer is al in gebruik.';
  }

  // Check of vluchtnummernummer bestaat
  if (!checkBestaanKolom($db, 'Vlucht', 'vluchtnummer', $vluchtnummer)) {
    $fouten[] = 'Deze vlucht bestaat niet.';
  }

  // Check of stoel is bezet
  if (checkBestaanKolom($db, 'Passagier', 'stoel', $stoel, 'vluchtnummer', $vluchtnummer)) {
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

      $dataArray = [
        ':passagiernummer' => $passagiernummer,
        ':naam' => $achternaam,
        ':vluchtnummer' => $vluchtnummer,
        ':geslacht' => $geslacht,
        ':balienummer' => $balienummer,
        ':stoel' => $stoel,
        ':inchecktijdstip' => $incheckDatumTijd,
        ':wachtwoord' => $passwordhash
      ];
      $succes = $query->execute($dataArray);

      if ($succes) {
        $melding = 'Passasier is ingevoerd.';
      } else {
        $melding = 'Invoeren is mislukt.';
      }
    }
  }
}
echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Voer een nieuwe passagier in</h1>
      <?php checkInOfUitgelogd() ?>
    </div>
  </header>
  <main class="container">
    <?= $melding ?>
    <div class="formGrid">
      <form action="newPassenger.php" id="passengerForm" method="POST">
        <h2>Vul de gegevens in</h2>
        <div class="data">
          <label for="passagiernummer">Passagiernummer*:</label>
          <input type="number" name="passagiernummer" id="passagiernummer"
            value="<?= isset($_POST['passagiernummer']) ? $_POST['passagiernummer'] : '' ?>" required>

          <label for="achternaam">Achternaam*:</label>
          <input type="text" name="achternaam" id="achternaam"
            value="<?= isset($_POST['achternaam']) ? $_POST['achternaam'] : '' ?>" required>

          <label for="vluchtnummer">Vluchtnummer*:</label>
          <input type="number" name="vluchtnummer" id="vluchtnummer"
            value="<?= isset($_POST['vluchtnummer']) ? $_POST['vluchtnummer'] : '' ?>" required>

          <label for="geslacht">Geslacht</label>
          <select name="geslacht" id="geslacht">
            <option value="null">Kies een geslacht</option>
            <option value="V" <?php echo (isset($_POST['geslacht']) && $_POST['geslacht'] == 'V') ? 'selected' : ''; ?>>V
            </option>
            <option value="M" <?php echo (isset($_POST['geslacht']) && $_POST['geslacht'] == 'M') ? 'selected' : ''; ?>>M
            </option>
            <option value="x" <?php echo (isset($_POST['geslacht']) && $_POST['geslacht'] == 'x') ? 'selected' : ''; ?>>x
            </option>
          </select>

          <label for="balienummer">Balienummer:</label>
          <select name="balienummer" id="balienummer">
            <option value="null">Kies een balienummer</option>
            <?= selecteerBalie($db) ?>
          </select>

          <label for="stoel">Stoelnummer:</label>
          <input type="text" name="stoel" id="stoel" value="<?= isset($_POST['stoel']) ? $_POST['stoel'] : '' ?>">

          <label for="incheckdatum">Incheckdatum:</label>
          <input type="date" name="incheckdatum" id="incheckdatum"
            value="<?= isset($_POST['incheckdatum']) ? $_POST['incheckdatum'] : '' ?>">

          <label for="inchecktijdstip">Inchecktijdstip:</label>
          <input type="time" name="inchecktijdstip" id="inchecktijdstip"
            value="<?= isset($_POST['inchecktijdstip']) ? $_POST['inchecktijdstip'] : '' ?>">

          <label for="wachtwoord">Wachtwoord:</label>
          <input type="password" id="wachtwoord" name="wachtwoord" required>

          <label for="wachtwoordCheck">Wachtwoord check:</label>
          <input type="password" id="wachtwoordCheck" name="wachtwoordCheck" required>
        </div>
        <input type="submit" name="nieuwePassagier" id="nieuwePassagier" class="button" value="Verzend">
      </form>
    </div>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>