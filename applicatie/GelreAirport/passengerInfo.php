<?php
session_start();
if(!isset($_SESSION['passagier'])){
  header("location:home.php?melding=Log eerst in als passagier voordat u deze pagina bezoekt.");
  die;
}
require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';

$db = maakVerbinding();
$query = 'SELECT V.vluchtnummer, balienummer, gatecode, stoel, max_gewicht_pp, bestemming, maatschappijcode, vertrektijd
          FROM Vlucht V INNER JOIN Passagier P ON V.vluchtnummer = P.vluchtnummer
          WHERE P.passagiernummer = :passagiernummer';
          'ORDER BY vertrektijd ASC';
$data = $db->prepare($query);

$data->execute([':passagiernummer' => $_SESSION['passagier']]);

$kolommen = ['vluchtnummer', 'balienummer', 'gatecode', 'stoel', 'max_gewicht_pp', 'bestemming', 'maatschappijcode', 'vertrektijd'];
echo genereerHead();
?>
<body>
<?= genereerNav();?>
  <header class="container">
    <div class="header">
      <h1>Uw vluchtgegevens</h1>
      <?php checkInOfUitgelogd()?>
    </div>
    <h2>Passagier <?= $_SESSION['passagier']?></h2>
  </header>
  <main class="container">
    <?= genereerTabel($data, $kolommen); ?>
  </main>
  <?= genereerFooter();?>
</body>
</html>