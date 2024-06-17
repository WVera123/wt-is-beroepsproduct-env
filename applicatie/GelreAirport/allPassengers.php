<?php
session_start();
if(!isset($_SESSION['medewerker'])){
  header("location:home.php?melding=Deze pagina is alleen zichtbaar voor medewerkers.");
  die;
}
require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';

$vluchtnummer = isset($_GET['vluchtnummer']) ? $_GET['vluchtnummer'] : '';

$db = maakVerbinding();

$query = 'SELECT passagiernummer, naam, vluchtnummer, balienummer, stoel, inchecktijdstip
          FROM Passagier
          WHERE 1 = 1'; //IMPROVE THIS

$zoekPassagiernummer = isset($_POST['nummer']) ? $_POST['nummer'] : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] :'';

if (!empty($vluchtnummer)) {
  $query .= ' AND vluchtnummer = :vluchtnummer';
}

if (!empty($zoekPassagiernummer)) {
  $query .= ' AND passagiernummer = :zoek';
}

switch ($filter) {
  case 'tijdNieuw':
    $query .= ' ORDER BY inchecktijdstip ASC';
    break;
    case 'tijdOud':
      $query .= ' ORDER BY inchecktijdstip DESC';
      break;
    case 'naamA':
      $query .= ' ORDER BY naam ASC, passagiernummer ASC';
      break;
    case 'naamZ':
      $query .= ' ORDER BY naam DESC, passagiernummer ASC';
      break;
    case 'vluchtnummerL':
      $query .= ' ORDER BY vluchtnummer ASC';
      break;
    case 'vluchtnummerH':
      $query .= ' ORDER BY vluchtnummer DESC';
      break;
    default:
      $query .= ' ORDER BY inchecktijdstip ASC';
}

$data = $db->prepare($query);

if (!empty($vluchtnummer)) {
  $data->bindParam(':vluchtnummer', $vluchtnummer);
}
if (!empty($zoekPassagiernummer)) {
  $data->bindParam(':zoek', $zoekPassagiernummer);
}

$data->execute();

$html_table = '<table>';
$html_table .= '<tr><th>Passagiernummer</th><th>Naam</th><th>Vluchtnummer</th><th>Balienummer</th><th>Stoelnummer</th><th>Inchecktijdstip</th></tr>';

while ($rij = $data->fetch()) {
  $passagiernummer = $rij['passagiernummer'];
  $naam = $rij['naam'];
  $vluchtnummer = $rij['vluchtnummer'];
  $balienummer = $rij['balienummer'];
  $stoel = $rij['stoel'];
  $inchecktijdstip = $rij['inchecktijdstip'];

  $editpassagierLink = "<a href='edit.php?passagiernummer=$passagiernummer'>$passagiernummer</a>";

  $html_table .= "<tr><th>$editpassagierLink</th><th>$naam</th><th>$vluchtnummer</th><th>$balienummer</th><th>$stoel</th><th>$inchecktijdstip</th></tr>";

}

$html_table .= "</table>";
echo genereerHead();
?>
<body>
<?= genereerNav();?>
  <header class="container">
    <div class="header">
      <h1>Alle passagiers</h1>
      <?php checkInOfUitgelogd()?>
    </div>
  </header>
  <main class="container">
    <div class="aankomendeVluchtenUitgebreid">
      <div class="searchContainer">
        <form action="allPassengers.php" method="POST">
          <label for="filter">Filter:</label>
          <select name="filter" id="filter">
            <option value="tijdNieuw">Tijd nieuw - oud</option>
            <option value="tijdOud">Tijd oud - nieuw</option>
            <option value="naamA">Naam A - Z</option>
            <option value="naamZ">Naam Z - A</option>
            <option value="vluchtnummerL">Vluchtnummer laag - hoog</option>
            <option value="vluchtnummerH">Vluchtnummer hoog - laag</option>
          </select>
          <button type="submit" name="filter">Filter</button>
          <div class="zoekbalk">
            <label for="zoek">Zoek een passagiernummer</label>
            <input type="number" name="nummer" id="nummer">
            <button type="submit" name="zoek"><i class="fa fa-search"></i></button>
          </div>
        </form>
      </div>
      <?php echo ($html_table); ?>
    </div>
  </main>
  <?= genereerFooter();?>
</body>
</html>