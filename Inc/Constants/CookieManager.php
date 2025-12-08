<?php
$rootPath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
include_once($rootPath . "Inc" . DIRECTORY_SEPARATOR . "Constants" . DIRECTORY_SEPARATOR . "db.php");

// Fonction simplifiée pour définir un cookie sécurisé
function setSecureCookie($name, $value, $expire = null) {
    $expire = $expire ?? time() + (7 * 24 * 60 * 60); // 7 jours par défaut
    
    $cookieOptions = [
        'expires' => $expire,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    
    return setcookie($name, $value, $cookieOptions);
}

// Fonction optimisée pour la connexion (appelée seulement si nécessaire)
function connect($username) {
    try {
        $conn = connect_db();
        
        // Générer un token sécurisé
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        // Mise à jour optimisée avec une seule requête
        $stmt = $conn->prepare("UPDATE users SET token = ?, token_expiry = ?, last_activity = datetime('now') WHERE username = ?");
        $result = $stmt->execute([$hashedToken, $expiry, $username]);
        
        if ($result && $stmt->rowCount() > 0) {
            // Cookies sécurisés
            setSecureCookie('user_token', $token);
            setSecureCookie('user_pseudo', $username);
            
            return [
                'success' => true, 
                'message' => 'Connexion réussie',
                'token' => $token
            ];
        } else {
            return [
                'success' => false, 
                'message' => 'Utilisateur non trouvé'
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Database error in connect(): " . $e->getMessage());
        return [
            'success' => false, 
            'message' => 'Erreur de connexion à la base de données'
        ];
    } catch (Exception $e) {
        error_log("General error in connect(): " . $e->getMessage());
        return [
            'success' => false, 
            'message' => 'Une erreur est survenue'
        ];
    }
}

// Fonction pour déconnecter l'utilisateur
function disconnect() {
    try {
        if (isset($_COOKIE['user_token'])) {
            $token = $_COOKIE['user_token'];
            $hashedToken = hash('sha256', $token);
            
            $conn = connect_db();
            
            // Invalider le token en base de données
            $stmt = $conn->prepare("UPDATE users SET token = NULL, token_expiry = NULL WHERE token = ?");
            $stmt->execute([$hashedToken]);
        }
        
        // Supprimer les cookies
        setcookie('user_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        setcookie('user_pseudo', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        
        return ['success' => true, 'message' => 'Déconnexion réussie'];
        
    } catch (Exception $e) {
        error_log("Error in disconnect(): " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la déconnexion'];
    }
}

// Fonction corrigée pour valider un token (SQLite compatible)
function validateToken($token) {
    try {
        $conn = connect_db();
        $hashedToken = hash('sha256', $token);
        
        // Correction pour SQLite : utiliser datetime('now') au lieu de NOW()
        $stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND token_expiry > datetime('now')");
        $stmt->execute([$hashedToken]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error in validateToken(): " . $e->getMessage());
        return false;
    }
}

// Fonction pour vérifier l'authentification automatique
function checkAutoLogin() {
    if (isset($_COOKIE['user_token']) && !isset($_SESSION['user_id'])) {
        $user = validateToken($_COOKIE['user_token']);
        if ($user) {
            // Restaurer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'];
            return $user;
        } else {
            // Token invalide, supprimer le cookie
            setcookie('user_token', '', time() - 3600, '/');
            setcookie('user_pseudo', '', time() - 3600, '/');
        }
    }
    return false;
}

?>