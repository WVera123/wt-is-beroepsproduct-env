<?php
require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';
session_start();
$melding = '';

$db = maakVerbinding();

$query = 'SELECT TOP 5 vluchtnummer, bestemming, vertrektijd, maatschappijcode
          FROM Vlucht
          WHERE vertrektijd > CURRENT_TIMESTAMP
          ORDER BY vertrektijd ASC';

$data = $db->prepare($query);

$data->execute();
$kolommen = ['vluchtnummer', 'bestemming', 'vertrektijd', 'maatschappijcode'];
echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Home</h1>
      <?= checkInOfUitgelogd()?>
    </div>
    <?=checkVoorMeldingen(); ?>
  </header>
  <main class="container">
    <a href="checkin.php" class="button">Ga naar de koffer check-in</a>
    <div class="homegrid">
      <div class="persoon">
        <h2>Voor passagiers</h2>
        <a href="passengerInfo.php" class="button">Bekijk uw vluchtgegevens</a>
      </div>
      <div class="aankomendeVluchten">
        <?= genereerTabel($data, $kolommen); ?>
        <a href="flights.php" class="button">Bekijk alle toekomstige vluchten</a>
      </div>
      <?= $melding ?>
      <div class="persoon">
        <h2>Voor medewerkers</h2>
        <a href="allFlights.php" class="button">Bekijk alle vluchten</a>
        <a href="newInfo.php" class="button">Voer nieuwe gegevens in</a>
      </div>
    </div>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>