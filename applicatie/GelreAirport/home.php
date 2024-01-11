<?php
require_once 'db_connectie.php';

$db = maakVerbinding();

$query = 'SELECT TOP 5 vluchtnummer, bestemming, vertrektijd, maatschappijcode
          FROM Vlucht
          ORDER BY vertrektijd ASC';

$data = $db->query($query);

$html_table = '<table>';
$html_table = $html_table . '<tr><th>Vluchtnummer</th><th>Bestemming</th><th>Vertrektijd</th><th>Maatschappijcode</th></tr>';

while($rij = $data->fetch()) {
  $vluchtnummer = $rij['vluchtnummer'];
  $bestemming = $rij['bestemming'];
  $vertrektijd = $rij['vertrektijd'];
  $maatschappijcode = $rij['maatschappijcode'];
  
  $html_table = $html_table . "<tr><th>$vluchtnummer</th><th>$bestemming</th><th>$vertrektijd</th><th>$maatschappijcode</th></tr>";
}

$html_table = $html_table . "</table>";
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
      <h1>Home</h1>
      <a href="login.php">Inloggen</a>
    </div>
  </header>
  <main class="container">
    <a href="checkin.php" class="button">Ga naar de koffer check-in</a>
    <div class="homegrid">
      <div class="persoon">
        <h2>Voor passagiers</h2>
        <a href="passengerInfo.php" class="button">Bekijk uw vluchtgegevens</a>
      </div>
      <div class="aankomendeVluchten">
       <?php 
        echo ($html_table);
       ?>
        <a href="flights.php" class="button">Bekijk alle toekomstige vluchten</a>
      </div>
      <div class="persoon">
        <h2>Voor medewerkers</h2>
        <a href="allFlights.php" class="button">Bekijk alle vluchten</a>
        <a href="newInfo.php" class="button">Voer nieuwe gegevens in</a>
      </div>
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

  