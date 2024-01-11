<?php
session_start();
if(!$_SESSION['medewerker']){
  $melding = 'Log eerst in voordat u deze pagina bezoekt!';
  header("location:home.php");
  die;
}
require_once 'db_connectie.php';
$melding = '';
$fouten = [];
$query = null;
if (isset($_POST['nieuwePassagier'])) {
    $passagiernummer = $_POST['passagiernummer'];
    $achternaam = $_POST['achternaam'];
    $vluchtnummer = $_POST['vluchtnummer'];
    $geslacht = $_POST['geslacht'];
    $balienummer = $_POST['stoel'];
    $stoel = $_POST['stoel'];
    $incheckDatumTijd = $_POST['incheckdatum'] . " " . $_POST['inchecktijdstip'];
    $rolOptie = 'passagier';
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
        $sql = 'INSERT INTO Passagier (passagiernummer, naam, vluchtnummer, geslacht, balienummer, stoel, inchecktijdstip, wachtwoord)
                SELECT :passagiernummer, :naam, :vluchtnummer, :geslacht, :balienummer, :stoel, :inchecktijdstip, :wachtwoord
                FROM Passagier P
                INNER JOIN Vlucht V ON P.vluchtnummer = V.vluchtnummer
                GROUP BY passagiernummer, naam, P.vluchtnummer, geslacht, balienummer, stoel, inchecktijdstip, wachtwoord, max_aantal
                HAVING COUNT(passagiernummer) < max_aantal';
        $query = $db->prepare($sql);

        $data_array = [
            ':passagiernummer' => (int)$passagiernummer,
            ':naam' => $achternaam,
            ':vluchtnummer' => (int)$vluchtnummer,
            ':geslacht' => $geslacht,
            ':balienummer' => (int)$balienummer,
            ':stoel' => $stoel,
            ':inchecktijdstip' => $incheckDatumTijd,
            ':wachtwoord' => $passwordhash
        ];
    }
    $succes = $query->execute($data_array);

    if($succes)
    {
        $melding = 'Gebruiker is geregistreerd.';
    }
    else
    {
        $melding = 'Registratie is mislukt.';
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

    $success = $query->execute($data_array);

    if ($success) {
      $melding = 'Vlucht is toegevoegd!.';
    } else {
      $melding = 'Vlucht toevoegen is mislukt.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="nl">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="css/style.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
      <title>Gelre Airport</title>
  </head>
  <body>
  <nav class="navbar">
    <h1 class="logo">Gelre Airport</h1>
    <ul>
      <li><a href="home.php">Home</a></li>
      <li><a href="flights.php">Vluchten</a></li>
      <li><a href="checkin.php">Bagage check-in</a></li>
      <li>
        <a href="#">Passagier</a>
        <ul>
          <li><a href="passengerInfo.php">Gegevens</a></li>
        </ul>
      </li>
      <li>
        <a href="#">Medewerker</a>
        <ul>
          <li><a href="allFlights.php">Alle vluchten</a></li>
          <li><a href="newInfo.php">Gegevensinvoer</a></li>
        </ul>
      </li>
    </ul>
  </nav>
    <header class="container">
      <div class="header">
        <h1>Gegevensinvoer</h1>
        <p>Ingelogd</p>
      </div>
    </header>
    <main class="container">
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
    <footer>
    <div class="footer">
      <div>
        <h1>Gelre Airport</h1>
        <p>Copyright &copy; 2023</p>
      </div>
      <nav>
        <ul>
          <li><a href="home.php">Home</a></li>
          <li><a href="flights.php">Vluchten</a></li>
          <li><a href="checkin.php">Check-in</a></li>
          <li><a href="passengerInfo.php">Gegevens</a></li>
          <li><a href="newInfo.php">Nieuwe gegevens</a></li>
          <li><a href="allFlights.php">Alle vluchten</a></li>
        </ul>
      </nav>
      <div class="social">
        <a href="#" target="_blank" ><i class="fa fa-facebook"></i></a>
        <a href="#" target="_blank"><i class="fa fa-twitter"></i></a>
        <a href="#" target="_blank"><i class="fa fa-instagram"></i></a>
      </div>
    </div>
    </footer>
  </body>
</html>