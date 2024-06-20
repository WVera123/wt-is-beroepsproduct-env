<?php
session_start();
if (!isset($_SESSION['medewerker'])) {
  header("location:home.php?melding=Deze pagina is alleen zichtbaar voor medewerkers.");
  die;
}
require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';

$db = maakVerbinding();

$query = 'SELECT vluchtnummer, bestemming, gatecode, vertrektijd, V.maatschappijcode, max_objecten_pp, max_gewicht_pp
          FROM Vlucht V
          INNER JOIN Maatschappij M ON V.maatschappijcode = M.maatschappijcode';

$zoekVluchtnummer = isset($_POST['nummer']) ? $_POST['nummer'] : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';

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
$kolommen = ['vluchtnummer', 'bestemming', 'gatecode', 'vertrektijd', 'maatschappijcode', 'max_objecten_pp', 'max_gewicht_pp'];

echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Alle vluchten</h1>
      <?php checkInOfUitgelogd() ?>
    </div>
  </header>
  <main class="container">
    <div class="aankomendeVluchtenUitgebreid">
      <div class="searchContainer">
        <form action="allFlights.php" method="POST">
          <label for="filter">Filter:</label>
          <select name="filter" id="filter">
            <option value="tijdNieuw" <?= (isset($_POST['filter']) && $_POST['filter'] == 'tijdNieuw') ? 'selected' : '' ?>>Tijd nieuw - oud</option>
            <option value="tijdOud" <?= (isset($_POST['filter']) && $_POST['filter'] == 'tijdOud') ? 'selected' : '' ?>>
              Tijd oud - nieuw</option>
            <option value="luchthavenA" <?= (isset($_POST['filter']) && $_POST['filter'] == 'luchthavenA') ? 'selected' : '' ?>>Luchthaven A - Z</option>
            <option value="luchthavenZ" <?= (isset($_POST['filter']) && $_POST['filter'] == 'luchthavenZ') ? 'selected' : '' ?>>Luchthaven Z - A</option>
          </select>
          <div class="zoekbalk">
            <label for="nummer">Zoek een vluchtnummer</label>
            <input type="number" name="nummer" id="nummer" <?php if (isset($_POST['nummer'])): ?>
                value="<?= $_POST['nummer'] ?>" <?php endif ?>>
            <button type="submit" name="zoek"><i class="fa fa-search"></i></button>
          </div>
        </form>
      </div>
      <?= genereerTabel($data, $kolommen) ?>
    </div>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>