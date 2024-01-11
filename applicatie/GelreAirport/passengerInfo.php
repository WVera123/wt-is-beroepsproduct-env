<?php
session_start();
if(!$_SESSION['passagier']){
  $melding = 'Log eerst in voordat u deze pagina bezoekt!';
  header("location:home.php");
  die;
}
require_once 'db_connectie.php';

$db = maakVerbinding();
$query = 'SELECT V.vluchtnummer, balienummer, gatecode, stoel, max_gewicht_pp, bestemming, maatschappijcode, vertrektijd
          FROM Vlucht V INNER JOIN Passagier P ON V.vluchtnummer = P.vluchtnummer
          WHERE P.passagiernummer = ' . $_SESSION['passagier'] .
          'ORDER BY vertrektijd ASC';

$data = $db->prepare($query);

$data->execute();

$html_table = '<table>';
$html_table .= '<tr><th>Vluchtnummer</th><th>Balienummer</th><th>Gatecode</th><th>Stoel</th><th>Max. gewicht pp</th><th>Bestemming</th><th>Maatschappijcode</th><th>Vertrektijd</th></tr>';

while ($rij = $data->fetch()) {
  $vluchtnummer = $rij['vluchtnummer'];
  $balienummer = $rij['balienummer'];
  $gatecode = $rij['gatecode'];
  $stoel = $rij['stoel'];
  $maxGewicht = $rij['max_gewicht_pp'];
  $bestemming = $rij['bestemming'];
  $maatschappijcode = $rij['maatschappijcode'];
  $vertrektijd = $rij['vertrektijd'];

  $html_table .= "<tr><th>$vluchtnummer</th><th>$balienummer</th><th>$gatecode</th><th>$stoel</th><th>$maxGewicht kg</th><th>$bestemming</th><th>$maatschappijcode</th><th>$vertrektijd</th></tr>";
}

$html_table .= "</table>";
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
      <h1>Uw vluchtgegevens</h1>
      <p>Ingelogd</p>
    </div>
  </header>
  <main class="container">
  <?php 
    echo ($html_table);
  ?>
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

  