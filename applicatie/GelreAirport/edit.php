<?php
session_start();
if (!$_SESSION['medewerker']) {
  header("location:home.php?melding=Log eerst in als medewerker voordat u deze pagina bezoekt.");
  die;
}

require_once 'db_connectie.php';
require_once 'components/functions.php';
require_once 'components/head.php';
require_once 'components/footer.php';
require_once 'components/header.php';

$melding = '';
$fouten = [];

if (!isset($_GET['passagiernummer'])) {
  header("location:allPassengers.php?melding=Geen passagier geselecteerd.");
}

$medewerkernummer = isset($_GET['passagiernummer']) ? $_GET['passagiernummer'] : '';

$db = maakVerbinding();

$query = 'SELECT passagiernummer, naam, vluchtnummer, balienummer, stoel, inchecktijdstip
          FROM Passagier 
          WHERE passagiernummer = :passagiernummer';

$data = $db->prepare($query);

$data->bindParam(':passagiernummer', $medewerkernummer);

$data->execute();

while ($rij = $data->fetch()) {
  $medewerkernummer = $rij['passagiernummer'];
  $naam = $rij['naam'];
  $vluchtnummer = $rij['vluchtnummer'];
  $balienummer = $rij['balienummer'];
  $stoel = $rij['stoel'];
  $inchecktijdstip = $rij['inchecktijdstip'];
}

if (isset($_POST['update'])) {

  $updatedVluchtnummer = $_POST['vluchtnummer'];
  $updatedBalienummer = $_POST['balienummer'];
  $updatedStoelnummer = $_POST['stoel'];
  if (!checkBestaanKolom($db, 'Vlucht', 'vluchtnummer', $updatedVluchtnummer)) {
    $fouten[] = 'Deze vlucht bestaat niet.';
  }

  if (checkBestaanKolom($db, 'Passagier', 'stoel', $updatedStoelnummer, 'vluchtnummer', $updatedVluchtnummer)) {
    $fouten[] = 'Deze stoel is al bezet.';
  }

  if (count($fouten) > 0) {
    $melding = "Er waren fouten in de invoer.<ul>";
    foreach ($fouten as $fout) {
      $melding .= "<li>$fout</li>";
    }
    $melding .= "</ul>";
  } else {
    $updateQuery = 'UPDATE Passagier 
                    SET vluchtnummer = :vluchtnummer, balienummer = :balienummer, stoel = :stoel
                    WHERE passagiernummer = :passagiernummer';

    $updateData = $db->prepare($updateQuery);
    $updateData->execute([':vluchtnummer' => $updatedVluchtnummer, ':balienummer' => $updatedBalienummer, ':stoel' => $updatedStoelnummer, ':passagiernummer' => $medewerkernummer]);

    header("Location: edit.php?passagiernummer=$medewerkernummer&melding=Informatie is succesvol veranderd.");
  }
}

echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Bewerk passagiers informatie</h1>
      <?php checkInOfUitgelogd() ?>
    </div>
  </header>
  <main class="container">
    <?= $melding ?>
    <?= checkVoorMeldingen(); ?>
    <form action="#" id="passengerForm" method="POST">
      <label for="passagiernummer">Passagiernummer:</label>
      <input type="text" name="passagiernummer" value="<?= $medewerkernummer ?>" readonly>

      <label for="naam">Naam:</label>
      <input type="text" name="naam" value="<?= $naam ?>" readonly>

      <label for="vluchtnummer">Vluchtnummer:</label>
      <input type="text" name="vluchtnummer" value="<?= $vluchtnummer ?>">

      <label for="balienummer">Balienummer:</label>
      <input type="text" name="balienummer" value="<?= $balienummer ?>">

      <label for="stoel">Stoelnummer:</label>
      <input type="text" name="stoel" value="<?= $stoel ?>">

      <label for="inchecktijdstip">Inchecktijdstip:</label>
      <input type="text" name="inchecktijdstip" value="<?= $inchecktijdstip ?>" readonly>

      <button type="submit" name="update">Update informatie</button>
    </form>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>