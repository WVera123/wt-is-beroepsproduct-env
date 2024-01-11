<?php

require_once 'db_connectie.php';

$db = maakVerbinding();

$query = 'SELECT vluchtnummer, bestemming, gatecode, vertrektijd, maatschappijcode
        FROM Vlucht
        WHERE vertrektijd > CURRENT_TIMESTAMP';

$zoekVluchtnummer = isset($_POST['tekst']) ? $_POST['tekst'] : '';

if (!empty($zoekVluchtnummer)) {
  $query .= ' AND vluchtnummer = :zoek';
}

$query .= ' ORDER BY vertrektijd ASC';

$data = $db->prepare($query);

if (!empty($zoekVluchtnummer)) {
  $data->bindParam(':zoek', $zoekVluchtnummer);
}

$data->execute();

$html_table = '<table>';
$html_table .= '<tr><th>Vluchtnummer</th><th>Bestemming</th><th>Gatecode</th><th>Vertrektijd</th><th>Maatschappijcode</th></tr>';

while ($rij = $data->fetch()) {
  $vluchtnummer = $rij['vluchtnummer'];
  $bestemming = $rij['bestemming'];
  $gatecode = $rij['gatecode'];
  $vertrektijd = $rij['vertrektijd'];
  $maatschappijcode = $rij['maatschappijcode'];

  $html_table .= "<tr><th>$vluchtnummer</th><th>$bestemming</th><th>$gatecode</th><th>$vertrektijd</th><th>$maatschappijcode</th></tr>";
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
      <h1>Home</h1>
      <a href="login.php">Inloggen</a>
    </div>
  </header>
  <main class="container">
    <div class="aankomendeVluchtenUitgebreid">
      <div class="zoekbalk">
        <form action="flights.php" method="POST">
          <label for="zoek">Zoek een vluchtnummer</label>
          <input type="text" name="tekst" id="tekst">
          <button type="submit" name="zoek"><i class="fa fa-search"></i></button>
        </form>
      </div>
      <?php 
        echo ($html_table);
       ?>
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

  