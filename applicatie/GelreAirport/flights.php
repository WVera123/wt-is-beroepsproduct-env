<?php

require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';

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

$kolommen = ['vluchtnummer', 'bestemming', 'gatecode', 'vertrektijd', 'maatschappijcode'];
echo genereerHead();
?>
<body>
<?= genereerNav();?>
  <header class="container">
    <div class="header">
      <h1>Toekomstige vluchten</h1>
      <?php checkInOfUitgelogd()?>
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
      <?= genereerTabel($data, $kolommen); ?>
    </div>
  </main>
  <?= genereerFooter();?>
</body>
</html>

  