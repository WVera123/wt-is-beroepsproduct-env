<?php
session_start();
if (!isset($_SESSION['passagier']) && !isset($_SESSION['medewerker'])) {
  header("location:home.php?melding=Log eerst in voordat u deze pagina bezoekt.");
  die;
}

require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';
$melding = '';
$fouten = [];
$success = '';

if (isset($_POST['verzend'])) {
  if (isset($_SESSION['passagier']) && ($_POST['passagiernummer'] != $_SESSION['passagier'])) {
    $melding = "U kunt alleen uw eigen bagage inchecken, het ingevoerde passagiernummer is onjuist.";
  } else {
    $totaleGewicht = 0;
    $passagiernummer = $_POST['passagiernummer'];
    $gewicht = 0;
    $bagageObjecten = [];
    if (isset($_POST['objectKoffer'])) {
      $aantalKoffer = $_POST['hoeveelheidKoffer'];
      for ($i = 0; $i < $aantalKoffer; $i++) {
        $gewichtKoffer = $_POST['gewichtKoffer'] / $aantalKoffer; //Zorgt ervoor dat als er verschillende koffers worden ingecheckt voor elk object het gemiddelde gewicht wordt genomen.
        $bagageObjecten[] = $gewichtKoffer;
        $totaleGewicht += $gewichtKoffer;
      }
    }

    if (isset($_POST['objectHandbagage'])) {
      $aantalHandbagage = $_POST['hoeveelheidHandbagage'];
      for ($i = 0; $i < $aantalHandbagage; $i++) {
        $gewichtHandbagage = $_POST['gewichtHandbagage'] / $aantalHandbagage;
        $bagageObjecten[] = $gewichtHandbagage;
        $totaleGewicht += $gewichtHandbagage;
      }
    }

    if (isset($_POST['objectRugzak'])) {
      $aantalRugzak = $_POST['hoeveelheidRugzak'];
      for ($i = 0; $i < $aantalRugzak; $i++) {
        $gewichtRugzak = $_POST['gewichtRugzak'] / $aantalRugzak;
        $bagageObjecten[] = $gewichtRugzak;
        $totaleGewicht += $gewichtRugzak;
      }
    }

    if (count($fouten) > 0) {
      $melding = "Er waren fouten in de invoer.<ul>";
      foreach ($fouten as $fout) {
        $melding .= "<li>$fout</li>";
      }
      $melding .= "</ul>";
    } else {
      $db = maakVerbinding();
      //Query om huidige hoogste objectnummer te krijgen.
      $sqlObjectnummer = 'SELECT MAX(objectvolgnummer) AS huidigNummer
                          FROM BagageObject
                          WHERE passagiernummer =  ' . $passagiernummer;
      $queryObjectnummer = $db->prepare($sqlObjectnummer);

      $queryObjectnummer->execute();

      $resultaatObjectnummer = $queryObjectnummer->fetch();
      $objectvolgnummer = $resultaatObjectnummer['huidigNummer'];

      $objectvolgnummer = $resultaatObjectnummer['huidigNummer'];

      if ($objectvolgnummer == NULL) {
        $objectvolgnummer = -1; // Initialiseer naar -1 zodat als er 1 bij op wordt geteld het resultaat 0 is.
      }

      //Query om huidige incheckte gewicht te krijgen.
      $sqlGewicht = 'SELECT SUM(gewicht) AS huidigGewicht
                     FROM BagageObject
                     WHERE passagiernummer =  ' . $passagiernummer;
      $queryGewicht = $db->prepare($sqlGewicht);

      $queryGewicht->execute();

      $resultaatGewicht = $queryGewicht->fetch();
      $totaleGewicht += $resultaatGewicht['huidigGewicht'];

      //Query om maximale objecten en maximale gewichten te krigjen.
      $sqlMax = 'SELECT max_objecten_pp, max_aantal, max_gewicht_pp , max_totaalgewicht
                  FROM Maatschappij M
                  INNER JOIN Vlucht V ON M.maatschappijcode = V.maatschappijcode
                  INNER JOIN Passagier P ON V.vluchtnummer = P.vluchtnummer
                  WHERE P.passagiernummer = ' . $passagiernummer;

      $queryMax = $db->prepare($sqlMax);

      $queryMax->execute();

      $resultaatMax = $queryMax->fetch();
      $maxObjecten = $resultaatMax['max_objecten_pp'];
      $maxPassagiers = $resultaatMax['max_aantal'];
      $maxGewichtPp = $resultaatMax['max_gewicht_pp'];
      $maxGewicht = $resultaatMax['max_totaalgewicht'];

      if (($objectvolgnummer + 1) + count($bagageObjecten) > $maxObjecten) {
        $fouten[] = "U heeft uw bagage limiet overschreden. De limiet is $maxObjecten. ";
      }

      if ($totaleGewicht > $maxGewichtPp) {
        $fouten[] = "Uw bagage is te zwaar. De totale limiet is $maxGewichtPp kg.";
      }

      if (count($fouten) > 0) {
        $melding = "Er waren fouten in de invoer.<ul>";
        foreach ($fouten as $fout) {
          $melding .= "<li>$fout</li>";
        }
        $melding .= "</ul>";
      } else {
        foreach ($bagageObjecten as $bagageObject) {
            $objectvolgnummer += 1;
          $sqlInsert = 'INSERT INTO BagageObject (passagiernummer, objectvolgnummer, gewicht) 
                        VALUES (:passagiernummer, :objectvolgnummer, :gewicht)';

          $queryInsert = $db->prepare($sqlInsert);

          $data_array = [
            ':passagiernummer' => $passagiernummer,
            ':objectvolgnummer' => $objectvolgnummer,
            ':gewicht' => $bagageObject,
          ];

          $success = $queryInsert->execute($data_array);
        }

        if ($success) {
          $melding = 'Bagage is ingecheckt.';
        } else {
          $melding = 'Bagage inchecken is mislukt.';
        }
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
      <h1>Bagage check-in</h1>
      <?php checkInOfUitgelogd() ?>
    </div>
  </header>
  <main class="container">
    <form action="#" id="checkInForm" method="POST">
      <h2>Selecteer de bagage die ingecheckt moet worden</h2>
      <?php echo $melding; ?>
      <div class="bagage">
        <div class="bagageInhoud">
          <img src="images/trolley.png" alt="trolley">
          <p>Max. 80 x 60 x 25cm</p>
          <label for="object">Koffer</label>
          <input type="checkbox" name="objectKoffer" id="objectKoffer">

          <label for="hoeveelheidKoffer">Aantal: </label>
          <input type="number" id="hoeveelheidKoffer" name="hoeveelheidKoffer" value="1" min="1">

          <label for="gewichtKoffer">Gewicht in kg: </label>
          <input type="number" id="gewichtKoffer" name="gewichtKoffer" value="1" min="1" step="0.01">
        </div>
        <div class="bagageInhoud">
          <img src="images/suitcase.png" alt="suitcase">
          <p>Max. 56 x 45 x 25 cm</p>
          <label for="object" class="custom-checkbox-label">Handbagage koffer</label>
          <input type="checkbox" name="objectHandbagage" id="objectHandbagage" class="customCheckbox">

          <label for="hoeveelheidHandbagage">Aantal: </label>
          <input type="number" id="hoeveelheidHandbagage" name="hoeveelheidHandbagage" value="1" min="1">

          <label for="gewichtHandbagage">Gewicht in kg: </label>
          <input type="number" id="gewichtHandbagage" name="gewichtHandbagage" value="1" min="1" step="0.01">
        </div>
        <div class="bagageInhoud">
          <img src="images/backpack.png" alt="backpack">
          <p>Max. 45 x 36 x 20 cm</p>
          <label for="object">Rugzak</label>
          <input type="checkbox" name="objectRugzak" id="objectRugzak">

          <label for="hoeveelheidRugzak">Aantal: </label>
          <input type="number" id="hoeveelheidRugzak" name="hoeveelheidRugzak" value="1" min="1">

          <label for="gewicht">Gewicht in kg: </label>
          <input type="number" id="gewichtRugzak" name="gewichtRugzak" value="1" min="1" step="0.01">
        </div>
      </div>
      <?php if (isset($_SESSION['passagier'])): ?>
        <h2>Voer uw passagiernummer in</h2>
      <?php elseif (isset($_SESSION['medewerker'])): ?>
        <h2>Voer een passagiernummer in.</h2>
      <?php endif ?>
      <div class="luggageGrid">
        <div class="gegevens">
          <label for="passagiernummer">Passagiernummer:</label>
          <input type="number" name="passagiernummer" id="passagiernummer" required>

          <input type="submit" name="verzend" id="verzend" class="button" value="Verzend">
        </div>
        <img src="images/luggage.png" alt="luggage">
      </div>
    </form>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>