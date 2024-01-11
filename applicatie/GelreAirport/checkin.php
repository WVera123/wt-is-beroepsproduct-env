<?php
session_start();
if(!$_SESSION['passagier'] || !$_SESSION['medewerker']){
  $melding = 'Log eerst in voordat u deze pagina bezoekt!';
  header("location:home.php");
  die;
}

require_once 'db_connectie.php';
$melding = '';
$fouten = [];

if(isset($_POST['verzend'])){
  $fouten = [];
  $passagiernummer = $_POST['passagiernummer'];
  $achternaam = $_POST['naam'];
  $vluchtnummer = $_POST['vluchtnummer'];
  $hoeveelheid = $_POST['hoeveelheidKoffer'] + $_POST['hoeveelheidHandbagage'] + $_POST['hoeveelheidRugzak'];
  $gewicht = 0;
  if(isset($_POST['objectKoffer'])){
    $gewicht += $_POST['gewichtKoffer'];
  }

  if(isset($_POST['objectHandbagage'])){
    $gewicht += $_POST['gewichtHandbagage'];
  }

  if(isset($_POST['objectRugzak'])){
    $gewicht += $_POST['gewichtRugzak'];
  }

  if( $_SESSION['inlog'] != $passagiernummer){
    $fouten[] = 'U heeft onjuiste gegevens ingevoerd!';
  }
  if(count($fouten) > 0) {
    $melding = "Er waren fouten in de invoer.<ul>";
    foreach($fouten as $fout) {
      $melding .= "<li>$fout</li>";
    }
    $melding .= "</ul>";
  } 
  else {
    $db = maakVerbinding();
    $sqlObjectnummer = 'SELECT MAX(objectvolgnummer) AS huidigNummer
                        FROM BagageObject
                        WHERE passagiernummer =  '. $_SESSION['inlog'];
    $queryObjectnummer = $db->prepare($sqlObjectnummer);

    $queryObjectnummer->execute();

    $resultaatObjectnummer = $queryObjectnummer->fetch();
    $objectvolgnummer = $resultaatObjectnummer['huidigNummer'];
        
    $sqlMax = 'SELECT max_objecten_pp, max_gewicht_pp 
    FROM Maatschappij M
    INNER JOIN Vlucht V ON M.maatschappijcode = V.maatschappijcode
    INNER JOIN Passagier P ON V.vluchtnummer = P.vluchtnummer
    WHERE P.passagiernummer = '. $_SESSION['inlog'];

    $queryMax = $db->prepare($sqlMax);

    $queryMax->execute();

    $resultaatMax = $queryMax->fetch();

    $maxObjecten = $resultaatMax['max_objecten_pp'];
    $maxGewicht = $resultaatMax['max_gewicht_pp'];

    if ($objectvolgnummer + 1 >= $maxObjecten) {
    $fouten[] = 'U heeft uw bagage limiet overschreden! De limiet is ' . $maxObjecten . '!';
    }

    if ($gewicht > $maxGewicht) {
    $fouten[] = 'Uw bagage is te zwaar! De limiet is ' . $maxGewicht . 'kg!';
    }

    if (count($fouten) > 0) {
    $melding = "Er waren fouten in de invoer.<ul>";
    foreach ($fouten as $fout) {
    $melding .= "<li>$fout</li>";
    }
    $melding .= "</ul>";
    } else {

      $sqlInsert = 'INSERT INTO BagageObject (passagiernummer, objectvolgnummer, gewicht) 
        VALUES (:passagiernummer, :objectvolgnummer, :gewicht)';

      $queryInsert = $db->prepare($sqlInsert);

      $data_array = [
      ':passagiernummer' => $passagiernummer,
      ':objectvolgnummer' => $objectvolgnummer + 1,
      ':gewicht' => $gewicht,
      ];

      $success = $queryInsert->execute($data_array);

      if ($success) {
      $melding = 'Bagage is ingecheckt.';
      } else {
      $melding = 'Bagage inchecken is mislukt.';
      }
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
      <h1>Bagage check-in</h1>
      <p>Ingelogd</p>
    </div>
  </header>
  <main class="container">
    <form action="#" id="checkInForm" method="POST">
      <h2>Selecteer de bagage die ingecheckt moet worden</h2>
      <div class="bagage">
        <div class="bagageInhoud">
          <img src="images/trolley.png" alt="trolley">
          <p>Max. 80 x 60 x 25cm</p>
          <label for="object">Koffer</label>
          <input type="checkbox" name="objectKoffer" id="objectKoffer">

          <label for="hoeveelheidKoffer">Aantal: </label>
          <input type="number" id="hoeveelheidKoffer" name="hoeveelheidKoffer" value="1" min="1">   
          
          <label for="gewichtKoffer">Gewicht in kg: </label>
          <input type="number" id="gewichtKoffer" name="gewichtKoffer" value="1" min="1">
        </div>
        <div class="bagageInhoud">
          <img src="images/suitcase.png" alt="suitcase">
          <p>Max. 56 x 45 x 25 cm</p>
          <label for="object" class="custom-checkbox-label">Handbagage koffer</label>
          <input type="checkbox" name="objectHandbagage" id="objectHandbagage" class="customCheckbox">
          
          <label for="hoeveelheidHandbagage">Aantal: </label>
          <input type="number" id="hoeveelheidHandbagage" name="hoeveelheidHandbagage" value="1" min="1">      
          
          <label for="gewichtHandbagage">Gewicht in kg: </label>
          <input type="number" id="gewichtHandbagage" name="gewichtHandbagage" value="1" min="1">
        </div>
        <div class="bagageInhoud">
          <img src="images/backpack.png" alt="backpack">
          <p>Max. 45 x 36 x 20 cm</p>
          <label for="object">Rugzak</label>
          <input type="checkbox" name="objectRugzak" id="objectRugzak">
          
          <label for="hoeveelheidRugzak">Aantal: </label>
          <input type="number" id="hoeveelheidRugzak" name="hoeveelheidRugzak" value="1" min="1">

          <label for="gewicht">Gewicht in kg: </label>
          <input type="number" id="gewichtRugzak" name="gewichtRugzak" value="1" min="1">
        </div>
      </div>
      <h2>Voer uw gegevens in ter controle</h2>
      <div class="luggageGrid">
        <div class="gegevens">
          <label for="passagiernummer">Passagiernummer:</label>
          <input type="number" name="passagiernummer" id="passagiernummer" required>

          <label for="naam">Achternaam:</label>
          <input type="text" name="naam" id="naam" required>

          <label for="vluchtnummer">Vluchtnummer:</label>
          <input type="number" name="vluchtnummer" id="vluchtnummer">

          <input type="submit" name="verzend" id="verzend" class="button" value="Verzend">
        </div>
        <img src="images/luggage.png" alt="luggage">
      </div>
    </form>
    <?php echo $melding;?>
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

  