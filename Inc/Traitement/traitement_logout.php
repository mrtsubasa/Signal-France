<?php
session_start();
require_once '../Constants/CookieManager.php';

// Déconnecter via CookieManager
$result = disconnect();

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: ../../index.php');
exit;
?>