<?php
session_start();
if(!isset($_SESSION['medewerker'])){
  $melding = 'Log eerst in voordat u deze pagina bezoekt!';
  header("location:home.php");
  die;
}
require_once 'db_connectie.php';
require_once 'components/header.php';
require_once 'components/footer.php';
require_once 'components/navigation.php';

$melding = '';
$fouten = [];

if (isset($_POST['nieuwePassagier'])) {
  $passagiernummer = $_POST['passagiernummer'];
  $achternaam = $_POST['achternaam'];
  $vluchtnummer = $_POST['vluchtnummer'];
  $geslacht = $_POST['geslacht'];
  $balienummer = $_POST['balienummer'];
  $stoel = $_POST['stoel'];
  $incheckDatumTijd = $_POST['incheckdatum'] . " " . $_POST['inchecktijdstip'];
  $wachtwoord = $_POST['wachtwoord'];
  $wachtwoordCheck = $_POST['wachtwoordCheck'];

  if (strlen($geslacht) != 1 || !in_array($geslacht, ['V', 'M', 'x'])) {
    $fouten[] = "Druk het geslacht uit in 'V', 'M' of 'x'!";
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

    if($resultaatPassengerCount >= $resultaatMaxAantal){
      $melding = "Het maximum aantal passagiers voor deze vlucht ($resultaatMaxAantal) is al bereikt.";
    }else{
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

      if($succes)
      {
        $melding = 'Gebruiker is geregistreerd!';
      }
      else
      {
        $melding = 'Registratie is mislukt!';
      }
    }
  }
}

if(isset($_POST['nieuweVlucht'])){
  $vluchtnummer = $_POST['vluchtnummer'];
  $bestemming = $_POST['bestemming'];
  $gatecode = $_POST['gatecode'];
  $maxAantal = $_POST['max_aantal'];
  $maxGewicht = $_POST['max_gewichtpp'];
  $maxTotaalGewicht = $_POST['max_totaalgewicht'];
  $vertrektijd = $_POST['vertrektijd'];
  $maatschappijcode = $_POST['maatschappijcode'];

  if(strlen($maatschappijcode) > 2) {
    $fouten[] = 'De maatschappijcode mag niet langer dan 2 letters zijn.';
  }

  if(count($fouten) > 0) {
    $melding = "Er waren fouten in de invoer.<ul>";
    foreach($fouten as $fout) {
        $melding .= "<li>$fout</li>";
    }
    $melding .= "</ul>";
  }else{
    $db = maakVerbinding();
    $sql = 'INSERT INTO Vlucht (vluchtnummer, bestemming, gatecode, max_aantal, max_gewicht_pp, max_totaalgewicht, vertrektijd, maatschappijcode)
            VALUES(:vluchtnummer, :bestemming, :gatecode, :max_aantal, :max_gewicht_pp, :max_totaalgewicht, :vertrektijd, :maatschappijcode)';
    $query = $db->prepare($sql);

    $data_array = [
      ':vluchtnummer' => $vluchtnummer,
      ':bestemming' => $bestemming,
      ':gatecode' => $gatecode,
      ':max_aantal' => $maxAantal,
      ':max_gewicht_pp' => $maxGewicht,
      ':max_totaalgewicht' => $maxTotaalGewicht,
      ':vertrektijd' => $vertrektijd,
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
  <?= genereerNav();?>
    <header class="container">
      <div class="header">
        <h1>Gegevensinvoer</h1>
        <a href="logout.php">Log uit</a>
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
            <input type="text" name="geslacht" id="geslacht" required>

            <label for="balienummer">Balienummer:</label>
            <input type="number" name="balienummer" id="balienummer" required>

            <label for="stoel">Stoelnummer:</label>
            <input type="text" name="stoel" id="stoel" required>

            <label for="incheckdatum">Incheckdatum:</label>
            <input type="date" name="incheckdatum" id="incheckdatum" required>

            <label for="inchecktijdstip">Inchecktijdstip:</label>
            <input type="time" step="0.001" name="inchecktijdstip" id="inchecktijdstip" required>

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
            <input type="text" name="gatecode" id="gatecode">

            <label for="max_aantal">Max. aantal passagiers*:</label>
            <input type="number" name="max_aantal" id="max_aantal" required>

            <label for="max_gewichtpp">Max. gewicht p.p*:</label>
            <input type="number" name="max_gewichtpp" id="max_gewichtpp" required>

            <label for="max_totaalgewicht">Max. totaal gewicht*:</label>
            <input type="number" name="max_totaalgewicht" id="max_totaalgewicht" required>

            <label for="vertrektijd">Vertrektijd:</label>
            <input type="date" name="vertrektijd" id="vertrektijd">

            <label for="maatschappijcode">Maatschappijcode*:</label>
            <input type="text" name="maatschappijcode" id="maatschappijcode" required>
          </div>
          <input type="submit" name="nieuweVlucht" id="nieuweVlucht" class="button" value="Verzend">
        </form>
      </div>
    </main>
    <?= genereerFooter();?>
  </body>
</html>