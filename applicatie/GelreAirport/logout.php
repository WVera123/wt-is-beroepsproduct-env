<?php
session_start();

session_destroy();

header("Location: home.php?melding=U bent uitgelogd.");
exit;
?>