<?php
function genereerNav(){
  $html = <<<NAVIGATION
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
          <li><a href="allPassengers.php">Alle passagiers</a></li>
          <li><a href="newPassenger.php">Passagier invoer</a></li>
          <li><a href="newFlight.php">Vlucht invoer</a></li>
        </ul>
      </li>
    </ul>
  </nav>
NAVIGATION;
  return $html;
}


?>