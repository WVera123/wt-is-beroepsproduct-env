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

$vluchtnummer = isset($_GET['vluchtnummer']) ? $_GET['vluchtnummer'] : '';

$db = maakVerbinding();

$query = 'SELECT passagiernummer, naam, vluchtnummer, balienummer, stoel, inchecktijdstip
          FROM Passagier
          WHERE passagiernummer IS NULL'; //Statement zonder resultaten om ervoor te zorgen dat er pas een passagier wordt laten zien als een gebruiker een passagiernummer of vluchtnummer heeft ingevoerd.

$zoekPassagiernummer = isset($_POST['nummer']) ? $_POST['nummer'] : '';

if (!empty($vluchtnummer)) {
  $query .= ' OR vluchtnummer = :vluchtnummer';
}

if (!empty($zoekPassagiernummer)) {
  $query .= ' OR passagiernummer = :zoek';
}

$data = $db->prepare($query);

if (!empty($vluchtnummer)) {
  $data->bindParam(':vluchtnummer', $vluchtnummer);
}
if (!empty($zoekPassagiernummer)) {
  $data->bindParam(':zoek', $zoekPassagiernummer);
}

$data->execute();

$kolommen = ['passagiernummer', 'naam', 'vluchtnummer', 'balienummer', 'stoel', 'inchecktijdstip'];
echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Alle passagiers</h1>
      <?php checkInOfUitgelogd() ?>
    </div>
    <?= checkVoorMeldingen(); ?>
  </header>
  <main class="container">
    <div class="aankomendeVluchtenUitgebreid">
      <div class="searchContainer">
        <form action="allPassengers.php" method="POST">
          <div class="zoekbalk">
            <label for="zoek">Zoek een passagiernummer</label>
            <input type="number" name="nummer" id="nummer" <?php if (isset($_POST['nummer'])): ?>value="<?= $_POST['nummer'] ?>" <?php endif ?>>
            <button type="submit" name="zoek"><i class="fa fa-search"></i></button>
          </div>
        </form>
      </div>
      <?= genereerTabel($data, $kolommen); ?>
    </div>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>