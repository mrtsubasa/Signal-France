<?php
session_start();
include("../Constants/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage des données
    $username = trim(htmlspecialchars($_POST['username']));
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim(htmlspecialchars($_POST['password']));
    $confirm_password = trim(htmlspecialchars($_POST['confirm_password']));

    // Validation des champs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['notification'] = [
            'message' => 'Veuillez remplir tous les champs.',
            'type' => 'error'
        ];
        header('Location: ../../Pages/register.php');
        exit;
    }

    // Vérification des mots de passe
    if ($password !== $confirm_password) {
        $_SESSION['notification'] = [
            'message' => 'Les mots de passe ne correspondent pas.',
            'type' => 'error'
        ];
        header('Location: ../../Pages/register.php');
        exit;
    }

    // Validation email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['notification'] = [
            'message' => 'Format d\'email invalide.',
            'type' => 'error'
        ];
        header('Location: ../../Pages/register.php');
        exit;
    }

    try {
        $db = connect_db();

        // Vérifier si l'utilisateur ou l'email existe déjà
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
        $checkStmt->execute(['username' => $username, 'email' => $email]);

        if ($checkStmt->fetch()) {
            $_SESSION['notification'] = [
                'message' => 'Un compte existe déjà avec ce nom d\'utilisateur ou cet email.',
                'type' => 'error'
            ];
            header('Location: ../../Pages/register.php');
            exit;
        }

        // Hashage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insertion du nouvel utilisateur
        // Rôle par défaut: 'user', access_level: 'basic'
        $insertStmt = $db->prepare("INSERT INTO users (username, password, email, role, access_level, created_at) VALUES (:username, :password, :email, 'user', 'basic', datetime('now'))");

        if (
            $insertStmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'email' => $email
            ])
        ) {
            $_SESSION['notification'] = [
                'message' => 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.',
                'type' => 'success'
            ];
            header('Location: ../../Pages/login.php');
            exit;
        } else {
            $_SESSION['notification'] = [
                'message' => 'Erreur lors de la création du compte.',
                'type' => 'error'
            ];
            header('Location: ../../Pages/register.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log("Erreur d'inscription: " . $e->getMessage());
        $_SESSION['notification'] = [
            'message' => 'Une erreur technique est survenue.',
            'type' => 'error'
        ];
        header('Location: ../../Pages/register.php');
        exit;
    }
} else {
    header('Location: ../../Pages/register.php');
    exit;
}
?>