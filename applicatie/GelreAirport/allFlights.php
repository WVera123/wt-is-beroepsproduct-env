<?php
session_start();
if(!$_SESSION['medewerker']){
  $melding = 'Log eerst in voordat u deze pagina bezoekt!';
  header("location:home.php");
  die;
}
require_once 'db_connectie.php';

$db = maakVerbinding();

$query = 'SELECT vluchtnummer, bestemming, gatecode, vertrektijd, maatschappijcode
          FROM Vlucht';

$zoekVluchtnummer = isset($_POST['tekst']) ? $_POST['tekst'] : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] :'';

if (!empty($zoekVluchtnummer)) {
  $query .= ' WHERE vluchtnummer = :zoek';
}

switch ($filter) {
  case 'tijdNieuw':
    $query .= ' ORDER BY vertektijd ASC';
    break;
    case 'tijdOud':
      $query .= ' ORDER BY vertrektijd DESC';
      break;
    case 'luchthavenA':
      $query .= ' ORDER BY bestemming ASC, vluchtnummer ASC';
      break;
    case 'luchthavenZ':
      $query .= ' ORDER BY bestemming DESC, vluchtnummer ASC';
      break;
    default:
      $query .= ' ORDER BY vertrektijd ASC';
}

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
      <h1>Alle vluchten</h1>
      <p>Ingelogd</p>
    </div>
  </header>
  <main class="container">
    <div class="aankomendeVluchtenUitgebreid">
      <div class="searchContainer">
        <form action="allFlights.php" method="POST">
          <label for="filter">Filter:</label>
          <select name="filter" id="filter">
            <option value="tijdNiew">Tijd nieuw - oud</option>
            <option value="tijdOud">Tijd oud - nieuw</option>
            <option value="luchthavenA">Luchthaven A - Z</option>
            <option value="luchthavenZ">Luchthaven Z - A</option>
          </select>
          <div class="zoekbalk">
            <label for="zoek">Zoek een vluchtnummer</label>
            <input type="text" name="tekst" id="tekst">
            <button type="submit" name="zoek"><i class="fa fa-search"></i></button>
          </div>
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

  