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

  // Check of vluchtnummer al bestaat
  if (checkBestaanKolom($db, 'Vlucht', 'vluchtnummer', $vluchtnummer)) {
    $fouten[] = 'Dit vluchtnummer is al in gebruik.';
  }

  if ($vertrekDatum != NULL) {
    if ($vertrekDatumTijd < date('Y-m-d H:i:s')) {
      $fouten[] = 'De vertrekdatum mag niet in het verleden liggen.';
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

    $dataArray = [
      ':vluchtnummer' => $vluchtnummer,
      ':bestemming' => $bestemming,
      ':gatecode' => $gatecode,
      ':max_aantal' => $maxAantal,
      ':max_gewicht_pp' => $maxGewichtPp,
      ':max_totaalgewicht' => $maxTotaalGewicht,
      ':vertrektijd' => $vertrekDatumTijd,
      ':maatschappijcode' => $maatschappijcode
    ];

    $succes = $query->execute($dataArray);

    if ($succes) {
      $melding = 'Vlucht is toegevoegd.';
    } else {
      $melding = 'Vlucht toevoegen is mislukt.';
    }
  }
}
echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Voer een nieuwe vlucht in</h1>
      <?php checkInOfUitgelogd() ?>
    </div>
  </header>
  <main class="container">
    <?= $melding ?>
    <div class="formGrid">
      <form action="newFlight.php" id="flightForm" method="POST">
        <h2>Vul de gegevens in</h2>
        <div class="data">
          <label for="vluchtnummer">Vluchtnummer*:</label>
          <input type="number" name="vluchtnummer" id="vluchtnummer" value="<?= isset($_POST['vluchtnummer']) ? $_POST['vluchtnummer'] : '' ?>" required>

          <label for="bestemming">Bestemming*:</label>
          <input type="text" name="bestemming" id="bestemming" value="<?= isset($_POST['bestemming']) ? $_POST['bestemming'] : '' ?>" required>

          <label for="gatecode">Gatecode:</label>
          <select name="gatecode" id="gatecode">
            <option value="null">Kies een gatecode</option>
            <?= selecteerGate($db) ?>
          </select>

          <label for="max_aantal">Max. aantal passagiers*:</label>
          <input type="number" name="max_aantal" id="max_aantal" value="<?= isset($_POST['max_aantal']) ? $_POST['max_aantal'] : '' ?>" required>

          <label for="max_gewichtpp">Max. gewicht p.p*:</label>
          <input type="number" name="max_gewichtpp" id="max_gewichtpp" value="<?= isset($_POST['max_gewichtpp']) ? $_POST['max_gewichtpp'] : '' ?>" required>

          <label for="max_totaalgewicht">Max. totaal gewicht*:</label>
          <input type="number" name="max_totaalgewicht" id="max_totaalgewicht" value="<?= isset($_POST['max_totaalgewicht']) ? $_POST['max_totaalgewicht'] : '' ?>" required>

          <label for="vertrekdatum">Vertrekdatum:</label>
          <input type="date" name="vertrekdatum" id="vertrekdatum" value="<?= isset($_POST['vertrekdatum']) ? $_POST['vertrekdatum'] : '' ?>">

          <label for="vertrektijd">Vertrektijd:</label>
          <input type="time" name="vertrektijd" id="vertrektijd" value="<?= isset($_POST['vertrektijd']) ? $_POST['vertrektijd'] : '' ?>">

          <label for="maatschappijcode">Maatschappijcode*:</label>
          <select name="maatschappijcode" id="maatschappijcode" value="<?= isset($_POST['maatschappijcode']) ? $_POST['maatschappijcode'] : '' ?>" required>
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