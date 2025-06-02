<?php
session_start();
include("../Constants/db.php");
include("../Constants/CookieManager.php");

if (isset($_POST['email'], $_POST['password'])) {
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim(htmlspecialchars($_POST['password']));

    // ❌ SUPPRIMER CETTE VALIDATION - Elle ne doit être que lors de la création/modification de mot de passe
    // La validation du format ne doit PAS être dans le processus de connexion
    
    try {
        $db = connect_db();
        
        // Requête optimisée avec index sur email
        $req = $db->prepare("SELECT id, email, username, password, role, avatar, token, last_activity, is_active FROM users WHERE email = :email LIMIT 1");
        $req->execute(['email' => $email]);
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            
            // Variables de session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'];
            $_SESSION['user_last_activity'] = date('Y-m-d H:i:s');
            $_SESSION['user_active'] = $user['is_active'];
            
            // Gestion des cookies seulement si "Se souvenir de moi" est coché
            if (isset($_POST['remember_me'])) {
                $connectResult = connect($user['username']);
                if (!$connectResult['success']) {
                    error_log("Erreur lors de la création du token: " . $connectResult['message']);
                }
            }
            
            // Mise à jour de la dernière activité
            $updateStmt = $db->prepare("UPDATE users SET last_activity = datetime('now'), is_active=1 WHERE id = :id");
            $updateStmt->execute(['id' => $user['id']]);
            
            // Redirection selon le rôle
            if (in_array($user['role'], ['admin', 'moderator'])) {
                header('Location: ../../Pages/admin.php');
            } else {
                header('Location: ../../index.php');
            }
            exit;
        } else {
            $_SESSION['notification'] = [
                'message' => 'Email ou mot de passe incorrect.',
                'type' => 'error'
            ];
            header('Location: ../../Pages/login.php');
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Erreur de connexion: " . $e->getMessage());
        $_SESSION['notification'] = [
            'message' => 'Erreur de connexion à la base de données.',
            'type' => 'error'
        ];
        header('Location: ../../Pages/login.php');
        exit;
    }
} else {
    header('Location: ../../Pages/login.php');
    exit;
}
?>