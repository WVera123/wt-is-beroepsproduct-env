<?php

require_once 'db_connectie.php';
require_once 'components/header.php';
require_once 'components/footer.php';
require_once 'components/navigation.php';

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

$html_table = '<table>';
$html_table .= '<tr><th>Vluchtnummer</th><th>Bestemming</th><th>Gatecode</th><th>Vertrektijd</th><th>Maatschappijcode</th></tr>';

while ($rij = $data->fetch()) {
  $vluchtnummer = $rij['vluchtnummer'];
  $bestemming = $rij['bestemming'];
  $gatecode = $rij['gatecode'];
  $vertrektijd = $rij['vertrektijd'];
  $maatschappijcode = $rij['maatschappijcode'];

  $html_table .= "<tr><th>$vluchtnummer</th><th>$bestemming</th><th>$gatecode</th><th>$vertrektijd</th><th>$maatschappijcode</th></tr>";
}

$html_table .= "</table>";
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
    <div class="aankomendeVluchtenUitgebreid">
      <div class="zoekbalk">
        <form action="flights.php" method="POST">
          <label for="zoek">Zoek een vluchtnummer</label>
          <input type="text" name="tekst" id="tekst">
          <button type="submit" name="zoek"><i class="fa fa-search"></i></button>
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

  