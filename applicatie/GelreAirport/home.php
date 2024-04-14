<?php
require_once 'db_connectie.php';
require_once 'components/header.php';
require_once 'components/footer.php';
require_once 'components/navigation.php';

$melding = '';

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
echo genereerHead();
?>
<body>
<?= genereerNav();?>
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
      <?= $melding ?>
      <div class="persoon">
        <h2>Voor medewerkers</h2>
        <a href="allFlights.php" class="button">Bekijk alle vluchten</a>
        <a href="newInfo.php" class="button">Voer nieuwe gegevens in</a>
      </div>
    </div>
  </main>
  <?= genereerFooter();?>
</body>
</html>