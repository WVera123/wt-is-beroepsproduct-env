<?php
session_start();
if (isset($_SESSION['passagier']) || isset($_SESSION['medewerker'])) {
  header("location:home.php?melding=U bent al ingelogd.");
  die;
} else {
  require_once 'db_connectie.php';
  require_once 'components/head.php';
  require_once 'components/footer.php';
  require_once 'components/header.php';

  $melding = '';

  if (isset($_POST['inloggen'])) {
    $choice = $_POST['choice'];
    $nummer = $_POST['nummer'];
    $wachtwoord = $_POST['wachtwoord'];

    if (empty($choice) || empty($nummer) || empty($wachtwoord)) {
      $melding = 'Fout: niet alle velden zijn ingevuld!';
    } else {
      $db = maakVerbinding();
      if ($choice == 'passagier') {
        $passagierQuery = $db->prepare('SELECT passagiernummer, wachtwoord 
                                        FROM Passagier 
                                        WHERE passagiernummer = :passagiernummer');
        $passagierQuery->execute([':passagiernummer' => $nummer]);
        if ($passagierRow = $passagierQuery->fetch()) {
          $wachtwoordHash = $passagierRow['wachtwoord'];

          if (password_verify($wachtwoord, $wachtwoordHash)) {
            $_SESSION['passagier'] = $nummer;
            header("Location: home.php?melding=U bent ingelogd als passagier $nummer.");
          } else {
            $melding = 'Fout: incorrecte inloggegevens.';
          }
        } else {
          $melding = 'Fout: incorrecte inloggegevens.';
        }
      } else if ($choice == 'medewerker') {
        $medewerkerQuery = $db->prepare('SELECT medewerkernummer, wachtwoord 
                                          FROM Medewerker 
                                          WHERE medewerkernummer = :medewerkernummer');
        $medewerkerQuery->execute([':medewerkernummer' => $nummer]);
        if ($medewerkerRow = $medewerkerQuery->fetch()) {
          $wachtwoordHash = $medewerkerRow['wachtwoord'];

          if (password_verify($wachtwoord, $wachtwoordHash)) {
            $_SESSION['medewerker'] = $nummer;
            header("Location: home.php?melding=U bent ingelogd als medewerker $nummer.");
          } else {
            $melding = 'Fout: incorrecte inloggegevens.';
          }
        } else {
          $melding = 'Fout: incorrecte inloggegevens.';
        }
      } else {
        $melding = 'Er is geen geldige keuze gemaakt.';
      }
    }
  }
}

echo genereerHead();
?>

<body>
  <?= genereerNav(); ?>
  <header class="container">
    <div class="header">
      <h1>Login</h1>
    </div>
  </header>
  <main class="container">
    <div class="login">
      <?= $melding ?>
      <form class="loginForm" action="login.php" method="post">
        <div class="formGroup">
          <label for="choice">Wilt u inloggen als passagier of medewerker?</label>
          <select name="choice" id="choice" required>
            <option value="passagier" selected>Passagier</option>
            <option value="medewerker">Medewerker</option>
          </select>
        </div>
        <div class="formGroup">
          <label for="nummer">Nummer:</label>
          <input type="text" id="nummer" name="nummer" required>
        </div>
        <div class="formGroup">
          <label for="wachtwoord">Wachtwoord:</label>
          <input type="password" id="wachtwoord" name="wachtwoord" required>
        </div>
        <div class="formGroup">
          <button type="submit" name="inloggen" class="button">Inloggen</button>
        </div>
      </form>
    </div>
  </main>
  <?= genereerFooter(); ?>
</body>

</html>