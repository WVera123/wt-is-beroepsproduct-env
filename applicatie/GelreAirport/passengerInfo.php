<?php
session_start();
if(!isset($_SESSION['passagier'])){
  $melding = 'Log eerst in voordat u deze pagina bezoekt!';
  header("location:home.php");
  die;
}
require_once 'db_connectie.php';
require_once 'components/header.php';
require_once 'components/footer.php';
require_once 'components/navigation.php';

$db = maakVerbinding();
$query = 'SELECT V.vluchtnummer, balienummer, gatecode, stoel, max_gewicht_pp, bestemming, maatschappijcode, vertrektijd
          FROM Vlucht V INNER JOIN Passagier P ON V.vluchtnummer = P.vluchtnummer
          WHERE P.passagiernummer = ' . $_SESSION['passagier'] .
          'ORDER BY vertrektijd ASC';
$data = $db->prepare($query);

$data->execute();

$html_table = '<table>';
$html_table .= '<tr><th>Vluchtnummer</th><th>Balienummer</th><th>Gatecode</th><th>Stoel</th><th>Max. gewicht pp</th><th>Bestemming</th><th>Maatschappijcode</th><th>Vertrektijd</th></tr>';

while ($rij = $data->fetch()) {
  $vluchtnummer = $rij['vluchtnummer'];
  $balienummer = $rij['balienummer'];
  $gatecode = $rij['gatecode'];
  $stoel = $rij['stoel'];
  $maxGewicht = $rij['max_gewicht_pp'];
  $bestemming = $rij['bestemming'];
  $maatschappijcode = $rij['maatschappijcode'];
  $vertrektijd = $rij['vertrektijd'];

  $html_table .= "<tr><th>$vluchtnummer</th><th>$balienummer</th><th>$gatecode</th><th>$stoel</th><th>$maxGewicht kg</th><th>$bestemming</th><th>$maatschappijcode</th><th>$vertrektijd</th></tr>";
}

$html_table .= "</table>";
echo genereerHead();
?>
<body>
<?= genereerNav();?>
  <header class="container">
    <div class="header">
      <h1>Uw vluchtgegevens</h1>
      <a href="logout.php">Log uit</a>
    </div>
    <h2>Passagier <?= $_SESSION['passagier']?></h2>
  </header>
  <main class="container">
  <?php 
    echo ($html_table);
  ?>
  </main>
  <?= genereerFooter();?>
</body>
</html>

  