<?php
  require_once 'db_connectie.php';

  $melding = '';

  if(isset($_POST['inloggen'])) {
      $nummer          = $_POST['nummer'];
      $naamPassagier   = $_POST['passagierNaam'];
      $wachtwoord      = $_POST['wachtwoord'];

      if ( !isset($_POST['nummer'], $_POST['passagierNaam'], $_POST['wachtwoord']) ) {
        $melding = 'fout: niet alle velden zijn ingevuld!';
      }

      $db = maakVerbinding();

      $sql = 'SELECT wachtwoord, passagiernummer, NULL AS medewerkernummer
              FROM Passagier
              WHERE passagiernummer = :passagiernummer
              UNION
              SELECT wachtwoord, NULL AS passagiernummer, medewerkernummer
              FROM Medewerker
              WHERE medewerkernummer = :medewerkernummer';
      $query = $db->prepare($sql);
  
      $data_array = [
        ':passagiernummer' => $nummer,
        ':medewerkernummer' => $nummer
      ];
      $query->execute($data_array);
  
      if ($rij = $query->fetch()) {
          $passwordhash = $rij['wachtwoord'];
          if (password_verify($wachtwoord, $passwordhash)) {
              session_start();
              header('location: home.php');
              if ($rij['passagiernummer'] !== null) {
                $_SESSION['passagier'] = $nummer;
              } elseif ($rij['medewerkernummer'] !== null) {
                $_SESSION['medewerker'] = $nummer;
              }
              $melding = 'Gebruiker is ingelogd';
          } else {
              $melding = 'fout: incorrecte inloggegevens!!';
          }
      } else {
          $melding = 'Incorrecte inloggegevens';
      }
  }
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Gelre Airport</title>
</head>
<body>
<nav class="navbar">
    <h1 class="logo">Gelre Airport</h1>
    <ul>
      <li><a href="home.php">Home</a></li>
      <li><a href="flights.php">Vluchten</a></li>
      <li><a href="checkin.php">Bagage check-in</a></li>
      <li>
        <a href="#">Passagier</a>
        <ul>
          <li><a href="passengerInfo.php">Gegevens</a></li>
        </ul>
      </li>
      <li>
        <a href="#">Medewerker</a>
        <ul>
          <li><a href="allFlights.php">Alle vluchten</a></li>
          <li><a href="newInfo.php">Gegevensinvoer</a></li>
        </ul>
      </li>
    </ul>
  </nav>
  <header class="container">
    <div class="header">
      <h1>Login</h1>
      <a href="register.php">Registreren</a>
    </div>
  </header>
  <main class="container">
    <div class="login">
      <form class="loginForm" action="login.php" method="post">
          <div class="formGroup">
              <label for="nummer">Nummer:</label>
              <input type="text" id="nummer" name="nummer" required>
          </div>
              <label for="passagierNaam">Achternaam:</label>
              <input type="text" id="passagierNaam" name="passagierNaam" required>
          </div>
          <div class="formGroup">
              <label for="wachtwoord">Wachtwoord:</label>
              <input type="password" id="wachtwoord" name="wachtwoord" required>
          </div>
          <div class="formGroup">
              <button type="submit" name="inloggen" class="Button">Inloggen</button>
          </div>
      </form>
      <?=$melding?>
  </div>
  </main>
  <footer>
  <div class="footer">
      <div>
        <h1>Gelre Airport</h1>
        <p>Copyright &copy; 2023</p>
      </div>
      <nav>
        <ul>
          <li><a href="home.php">Home</a></li>
          <li><a href="flights.php">Vluchten</a></li>
          <li><a href="checkin.php">Check-in</a></li>
          <li><a href="passengerInfo.php">Gegevens</a></li>
          <li><a href="newInfo.php">Nieuwe gegevens</a></li>
          <li><a href="allFlights.php">Alle vluchten</a></li>
        </ul>
      </nav>
      <div class="social">
        <a href="#" target="_blank" ><i class="fa fa-facebook"></i></a>
        <a href="#" target="_blank"><i class="fa fa-twitter"></i></a>
        <a href="#" target="_blank"><i class="fa fa-instagram"></i></a>
      </div>
    </div>
  </footer>
</body>
</html>

  