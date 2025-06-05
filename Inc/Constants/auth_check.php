<?php
// Fichier à inclure au début de chaque page protégée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../Traitement/timeout.php';

// Initialiser le timeout si nécessaire
initializeSessionTimeout();

// Vérifier le timeout de session
if (!checkSessionTimeout()) {
    // L'utilisateur a été déconnecté automatiquement
    exit;
}
?>