<?php
  session_start();
  if(isset($_SESSION['passagier']) || isset($_SESSION['medewerker'])){
    $melding = 'U bent al ingelogd!';
    header("location:home.php");
    die;
  }else{
    require_once 'db_connectie.php';
    require_once 'components/header.php';
    require_once 'components/footer.php';
    require_once 'components/navigation.php';

    $melding = '';

    if(isset($_POST['inloggen'])) {
      $nummer          = $_POST['nummer'];
      $wachtwoord      = $_POST['wachtwoord'];

      if (empty($nummer) || empty($wachtwoord)) {
          $melding = 'Fout: niet alle velden zijn ingevuld!';
      } else {
          $db = maakVerbinding();

          $passagierQuery = $db->prepare('SELECT passagiernummer, wachtwoord 
                                            FROM Passagier 
                                            WHERE passagiernummer = :passagiernummer');
          $passagierQuery->execute([':passagiernummer' => $nummer]);
          $passagierRow = $passagierQuery->fetch();

          $medewerkerQuery = $db->prepare('SELECT medewerkernummer, wachtwoord 
                                            FROM Medewerker 
                                            WHERE medewerkernummer = :medewerkernummer');
          $medewerkerQuery->execute([':medewerkernummer' => $nummer]);
          $medewerkerRow = $medewerkerQuery->fetch();

          $passagierPasswordhash = $passagierRow; //IMPROVE, NOT HASHED
          $medewerkerPasswordhash = $medewerkerRow; //IMPROVE, NOT HASHED
          if ($passagierRow && $passagierPasswordhash) {
              session_start();
              $_SESSION['passagier'] = $nummer;
              $melding = 'Passagier is ingelogd';
          } elseif ($medewerkerRow && $medewerkerPasswordhash) {
              session_start();
              $_SESSION['medewerker'] = $nummer;
              $melding = 'Medewerker is ingelogd';
          } else {
              $melding = 'Fout: incorrecte inloggegevens!';
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
      <h1>Login</h1>
    </div>
  </header>
  <main class="container">
    <div class="login">
      <?=$melding?>
      <form class="loginForm" action="login.php" method="post">
          <div class="formGroup">
              <label for="nummer">Nummer:</label>
              <input type="text" id="nummer" name="nummer" required>
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
  <?= genereerFooter();?>
</body>
</html>