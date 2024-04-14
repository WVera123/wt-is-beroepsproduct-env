<?php
require_once 'db_connectie.php';
require_once 'components/header.php';
require_once 'components/footer.php';
require_once 'components/navigation.php';

$melding = '';

$htmlFormPassagier = '<div class="formGroup">
                        <label for="passagiernummer">Passagiernummer*:</label>
                        <input type="number" id="passagiernummer" name="passagiernummer" required>
                      </div>
                      <div class="formGroup">
                        <label for="passagierNaam">Achternaam*:</label>
                        <input type="text" id="passagierNaam" name="passagierNaam" required>
                      </div>
                      <div class="formGroup">
                        <label for="vluchtnummer">Vluchtnummer*:</label>
                        <input type="number" id="vluchtnummer" name="vluchtnummer" required>
                      </div>
                      <div class="formGroup">
                        <label for="geslacht">Geslacht:</label>
                        <input type="text" id="geslacht" name="geslacht" required>
                      </div>
                      <div class="formGroup">
                        <label for="balienummer">Balienummer:</label>
                        <input type="number" id="balienummer" name="balienummer" required>
                      </div>
                      <div class="formGroup">
                        <label for="stoel">Stoelnummer:</label>
                        <input type="text" id="stoel" name="stoel" required>
                      </div>
                      <div class="formGroup">
                        <label for="incheckdatum">Incheckdatum:</label>
                        <input type="date" id="incheckdatum" name="incheckdatum" required>
                      </div>
                      <div class="formGroup">
                        <label for="inchecktijdstip">Inchecktijdstip:</label>
                        <input type="time" step="0.001" id="inchecktijdstip" name="inchecktijdstip" required>
                      </div>
                      <div class="formGroup">
                        <label for="wachtwoord">Wachtwoord*:</label>
                        <input type="password" id="wachtwoord" name="wachtwoord" required>
                      </div>
                      <div class="formGroup">
                          <label for="wachtwoordCheck">Wachtwoord check*:</label>
                          <input type="password" id="wachtwoordCheck" name="wachtwoordCheck" required>
                      </div>
                      <div class="formGroup">
                          <button type="submit" name="registeren" class="Button">Registreren</button>
                      </div>';

$htmlFormMedewerker = '<div class="formGroup">
                      <label for="medewerkernummer">Medewerkernummer*:</label>
                      <input type="text" id="medewerkernummer" name="medewerkernummer" required>
                      </div>
                      <div class="formGroup">
                        <label for="medewerkerNaam">Achternaam*:</label>
                        <input type="text" id="medewerkerNaam" name="medewerkerNaam" required>
                      </div>
                      <div class="formGroup">
                        <label for="maatschappijcode">Maatschappijcode*:</label>
                        <input type="text" id="maatschappijcode" name="maatschappijcode" required>
                      </div>
                      <div class="formGroup">
                        <label for="geslacht">Geslacht:</label>
                        <input type="text" id="geslacht" name="geslacht" required>
                      </div>
                      <div class="formGroup">
                      <label for="wachtwoord">Wachtwoord:</label>
                      <input type="password" id="wachtwoord" name="wachtwoord" required>
                      </div>
                      <div class="formGroup">
                          <label for="wachtwoordCheck">Wachtwoord check:</label>
                          <input type="password" id="wachtwoordCheck" name="wachtwoordCheck" required>
                      </div>
                      <div class="formGroup">
                          <button type="submit" name="registeren" class="Button">Registreren</button>
                      </div>';
// Zorgt ervoor dat de juiste formulier op het scherm komt
$formulier = '';
if(isset($_POST['keuze'])){
  $rolOptie = $_POST['optie'];

  if($rolOptie == 'passagier') {
    $formulier = $htmlFormPassagier;
  }
  else if($rolOptie  == 'medewerker') {
    $formulier = $htmlFormMedewerker;
  }
}
if(isset($_POST['registeren'])) {
  $fouten = [];
  if($formulier == $htmlFormPassagier){
  $passagiernummer     = $_POST['passagiernummer'];
  $naamPassagier       = $_POST['passagierNaam'];
  $vluchtnummer        = $_POST['vluchtnummer'];
  $balienummer         = $_POST['balienummer'];
  $stoel               = $_POST['stoel'];
  $incheckDatumTijd    = $_POST['incheckdatum'] . " " . $_POST['inchecktijdstip'];
  }
  if($formulier == $htmlFormMedewerker){
  $medewerkernummer    = $_POST['medewerkernummer'];
  $naamMedewerker      = $_POST['medewerkerNaam'];
  $maatschappijcode    = $_POST['maatschappijcode'];
  }
  $geslacht            = $_POST['geslacht'];
  $wachtwoord          = $_POST['wachtwoord'];
  $wachtwoordCheck     = $_POST['wachtwoordCheck'];


  if (isset($wachtwoord) && strlen($wachtwoord) < 8) {
    $fouten[] = 'Wachtwoord moet minstens 8 karakters zijn.';
  }
  
  if (isset($wachtwoord, $wachtwoordCheck) && strlen($wachtwoord) != strlen($wachtwoordCheck)) {
    $fouten[] = 'De wachtwoorden komen niet overeen.';
  }
  
  if (isset($maatschappijcode) && strlen($maatschappijcode) > 2) {
    $fouten[] = 'De maatschappijcode mag niet langer dan 2 letters zijn.';
  }
  

  if(count($fouten) > 0) {
    $melding = "Er waren fouten in de invoer.<ul>";
    foreach($fouten as $fout) {
        $melding .= "<li>$fout</li>";
    }
    $melding .= "</ul>";

  } else {
    $passwordhash = password_hash($wachtwoord, PASSWORD_DEFAULT);
    $db = maakVerbinding();
    if($formulier == $htmlFormPassagier) {
      $sql = 'INSERT INTO Passagier(passagiernummer, naam, vluchtnummer, geslacht, balienummer, stoel, inchecktijdstip, wachtwoord)
      VALUES (:passagiernummer, :naam, :vluchtnummer, :geslacht, :balienummer, :stoel, :incheckdatumtijd, :wachtwoord)';

      $query = $db->prepare($sql);

      $data_array = [
        ':passagiernummer' => $passagiernummer,
        ':naam' => $naamPassagier,
        ':vluchtnummer' => $vluchtnummer,
        ':geslacht' => $geslacht,
        ':balienummer' => $balienummer,
        ':stoel' => $stoel,
        ':incheckdatumtijd' => $incheckDatumTijd,
        ':wachtwoord' => $passwordhash
      ];

      $succes = $query->execute($data_array);

      if($succes)
      {
        $melding = 'Gebruiker is geregistreerd.';
      }
      else
      {
        $melding = 'Registratie is mislukt.';
      }
    }
    else if($formulier == $htmlFormMedewerker) {
      $insertSql = 'CREATE TABLE Medewerker(
        medewerkernummer		numeric(6)   not null,
        naam					      varchar(35)  not null,
        maatschappijcode		char(2)   not null,
        geslacht				    char(1)              ,
        wachtwoord			    varchar(200) not null,
        constraint pk_medewerker primary key (medewerkernummer),
        constraint ak_medewerker unique(maatschappijcode)
      )';
      $insertQuery = $db->prepare($insertSql);
      $insertQuery->execute();
      $sql = 'INSERT INTO Medewerker(medewerkernummer, naam, maatschappijcode, geslacht, wachtwoord)
              values (:medewerkernummer, :naam, :maatschappijcode, :geslacht, :wachtwoord)';
      $query = $db->prepare($sql);

      $data_array = [
        ':medewerkernummer' => $medewerkernummer,
        ':naam' => $naamMedewerker,
        ':maatschappijcode' => $maatschappijcode,
        ':geslacht' => $geslacht,
        ':wachtwoord' => $passwordhash
      ];
    
      $succes = $query->execute($data_array);

      if($succes)
      {
          $melding = 'Gebruiker is geregistreerd.';
      }
      else
      {
          $melding = 'Registratie is mislukt.';
      }
    }
  }
}
echo genereerHead();
?>
<body>
<?= genereerNav();?>
  <header class="container">
    <div class="header">
      <h1>Registreren</h1>
      <a href="login.php">Inloggen</a>
    </div>
  </header>
  <main class="container">
    <div class="keuze">
      <form class="keuzeForm" action="register.php" method="POST">
        <div class="formGroup">
          <label for="optie">Wat bent u: </label>
          <select name="optie" id="optie" required>
            <option value="passagier">Een passagier</option>
            <option value="medewerker">Een medewerker</option>
          </select>
        </div>
        <div class="formGroup">
          <button type="submit" name="keuze" class="button">Maak een keuze</button>
        </div>
      </form>
    </div>
    <div class="registerForm">
      <form class="registerForm" action="register.php" method="POST">
        <?php 
        echo $formulier;
        ?>
      </form>
      <?=$melding?>
    </div>
  </main>
  <?= genereerFooter();?>
</body>
</html>

