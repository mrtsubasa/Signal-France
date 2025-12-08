<?php
// Fonction pour vérifier le timeout de session (30 minutes)
function checkSessionTimeout() {
    $timeout_duration = 30 * 60; // 30 minutes en secondes
    
    // Vérifier si l'utilisateur est connecté
    if (isset($_SESSION['user_id'])) {
        // Vérifier si last_activity existe
        if (isset($_SESSION['user_last_activity'])) {
            $time_since_last_activity = time() - $_SESSION['user_last_activity'];
            
            // Si plus de 30 minutes d'inactivité
            if ($time_since_last_activity > $timeout_duration) {
                // Déconnecter l'utilisateur
                logoutUser();
                return false;
            }
        }
        
        // Mettre à jour la dernière activité
        $_SESSION['user_last_activity'] = time();
        
        // Mettre à jour en base de données (optionnel, pour tracking)
        updateLastActivityInDB();
    }
    
    return true;
}

// Fonction pour déconnecter l'utilisateur
function logoutUser() {
    // Inclure le CookieManager pour la déconnexion complète
    require_once __DIR__ . '/CookieManager.php';
    
    // Déconnecter via CookieManager
    disconnect();
    
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire le cookie de session si il existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page de connexion avec message
    header('Location: /Tsubi_Psycho/Signale_France/Pages/login.php?timeout=1');
    exit;
}

// Fonction pour mettre à jour la dernière activité en base
function updateLastActivityInDB() {
    if (isset($_SESSION['user_id'])) {
        try {
            require_once __DIR__ . '/db.php';
            $db = connect_db();
            
            $stmt = $db->prepare("UPDATE users SET last_activity = datetime('now') WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (Exception $e) {
            // Log l'erreur mais ne pas interrompre l'exécution
            error_log("Erreur mise à jour last_activity: " . $e->getMessage());
        }
    }
}

// Fonction pour vérifier et initialiser la session si nécessaire
function initializeSessionTimeout() {
    if (isset($_SESSION['user_id']) && !isset($_SESSION['user_last_activity'])) {
        $_SESSION['user_last_activity'] = time();
    }
}
?>