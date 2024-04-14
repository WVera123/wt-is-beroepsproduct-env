<?php
session_start();
if(!isset($_SESSION['medewerker'])){
  $melding = 'Log eerst in als medewerkervoordat u deze pagina bezoekt!';
  header("location:home.php");
  die;
}
require_once 'db_connectie.php';
require_once 'components/header.php';
require_once 'components/footer.php';
require_once 'components/navigation.php';

$db = maakVerbinding();

$query = 'SELECT vluchtnummer, bestemming, gatecode, vertrektijd, maatschappijcode
          FROM Vlucht';

$zoekVluchtnummer = isset($_POST['nummer']) ? $_POST['nummer'] : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] :'';

if (!empty($zoekVluchtnummer)) {
  $query .= ' WHERE vluchtnummer = :zoek';
}

switch ($filter) {
  case 'tijdNieuw':
    $query .= ' ORDER BY vertrektijd DESC, vluchtnummer ASC';
    break;
    case 'tijdOud':
      $query .= ' ORDER BY vertrektijd ASC, vluchtnummer ASC';
      break;
    case 'luchthavenA':
      $query .= ' ORDER BY bestemming ASC, vluchtnummer ASC';
      break;
    case 'luchthavenZ':
      $query .= ' ORDER BY bestemming DESC, vluchtnummer ASC';
      break;
    default:
      $query .= ' ORDER BY vertrektijd DESC, vluchtnummer ASC';
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

  $passagiernummerLink = "<a href='allPassengers.php?vluchtnummer=$vluchtnummer'>$vluchtnummer</a>";

  $html_table .= "<tr><th>$passagiernummerLink</th><th>$bestemming</th><th>$gatecode</th><th>$vertrektijd</th><th>$maatschappijcode</th></tr>";
}

$html_table .= "</table>";
echo genereerHead();
?>
<body>
<?= genereerNav();?>
  <header class="container">
    <div class="header">
      <h1>Alle vluchten</h1>
      <a href="logout.php">Log uit</a>
    </div>
  </header>
  <main class="container">
    <div class="aankomendeVluchtenUitgebreid">
      <div class="searchContainer">
        <form action="allFlights.php" method="POST">
          <label for="filter">Filter:</label>
          <select name="filter" id="filter">
            <option value="tijdNieuw">Tijd nieuw - oud</option>
            <option value="tijdOud">Tijd oud - nieuw</option>
            <option value="luchthavenA">Luchthaven A - Z</option>
            <option value="luchthavenZ">Luchthaven Z - A</option>
          </select>
          
          <div class="zoekbalk">
            <label for="zoek">Zoek een vluchtnummer</label>
            <input type="number" name="nummer" id="nummer">
            <button type="submit" name="zoek"><i class="fa fa-search"></i></button>
          </div>
        </form>
      </div>
      <?php
      echo ($html_table);
      ?>
    </div>
  </main>
  <?= genereerFooter();?>
</body>
</html>

  