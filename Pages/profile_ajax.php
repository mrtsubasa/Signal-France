<?php
// Démarrer la mise en mémoire tampon de sortie
ob_start();
// Désactiver l'affichage des erreurs en production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once '../Inc/Constants/db.php';
// Nettoyer toute sortie précédente
ob_clean();
header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_member') {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID du membre manquant']);
        exit;
    }
    try {
        $db = connect_db();
        $stmt = $db->prepare('SELECT * FROM users WHERE id =?');
        $stmt->execute([$_GET['id']]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($member) {
            echo json_encode(['success' => true, 'member' => $member]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}


if ($_POST['action'] === 'update_profile') {
    try {
        $db = connect_db();

        // Gestion de l'avatar s'il y en a un
        $avatarUpdated = false;
        $newAvatar = null;

        // Function locale de validation (ou inclure une librairie commune)
        function checkFile($file, $allowedMimes, $allowedExts, $maxSize)
        {
            if ($file['error'] !== UPLOAD_ERR_OK)
                return "Erreur upload";
            if ($file['size'] > $maxSize)
                return "Fichier trop volumineux";

            // Check Extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts))
                return "Extension non autorisée";

            // Check Magic Bytes
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file['tmp_name']);
            if (!in_array($realMime, $allowedMimes))
                return "Type MIME invalide ($realMime)";

            return true;
        }

        $is_public = isset($_POST['is_public']) ? (int) $_POST['is_public'] : 0;

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $userId = $_SESSION['user_id'];

            // Validations de sécurité
            $valid = checkFile($file, ['image/jpeg', 'image/png', 'image/gif'], ['jpg', 'jpeg', 'png', 'gif'], 2 * 1024 * 1024);
            if ($valid !== true)
                throw new Exception("Avatar: " . $valid);

            // Générer un nom de fichier unique avec extension sûre
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = '../Assets/Images/avatars/' . $filename;

            // Créer le dossier s'il n'existe pas
            $avatarDir = '../Assets/Images/avatars';
            if (!is_dir($avatarDir)) {
                mkdir($avatarDir, 0755, true);
            }

            // Déplacer le fichier téléchargé
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $newAvatar = $filename;
                $avatarUpdated = true;
            }
        }

        $bannerUpdated = false;
        $newBanner = null;

        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['banner'];
            $userId = $_SESSION['user_id'];

            // Validations de sécurité
            $valid = checkFile($file, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], ['jpg', 'jpeg', 'png', 'gif', "webp"], 10 * 1024 * 1024);
            if ($valid !== true)
                throw new Exception("Bannière: " . $valid);

            // Générer un nom de fichier unique
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'banner_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = '../Assets/Images/banners/' . $filename;

            // Créer le dossier s'il n'existe pas
            $bannerDir = '../Assets/Images/banners';
            if (!is_dir($bannerDir)) {
                mkdir($bannerDir, 0755, true);
            }

            // Déplacer le fichier téléchargé
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $newBanner = $filename;
                $bannerUpdated = true;
            }
        }

        // Préparer la requête selon qu'on met à jour l'avatar ou non
        if ($avatarUpdated && $bannerUpdated) {
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ?, avatar = ?, banner = ?, is_public = ? WHERE id = ?');
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $_POST['bio'],
                $_POST['phone'],
                $_POST['organization'],
                $_POST['address'],
                $_POST['city'],
                $_POST['accreditation'],
                $_POST['github'],
                $_POST['linkedin'],
                $_POST['website'],
                $newAvatar,
                $newBanner,
                $is_public,
                $_SESSION['user_id']
            ]);
        } elseif ($avatarUpdated) {
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ?, avatar = ?, is_public = ? WHERE id = ?');
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $_POST['bio'],
                $_POST['phone'],
                $_POST['organization'],
                $_POST['address'],
                $_POST['city'],
                $_POST['accreditation'],
                $_POST['github'],
                $_POST['linkedin'],
                $_POST['website'],
                $newAvatar,
                $is_public,
                $_SESSION['user_id']
            ]);
        } elseif ($bannerUpdated) {
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ?, banner = ?, is_public = ? WHERE id = ?');
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $_POST['bio'],
                $_POST['phone'],
                $_POST['organization'],
                $_POST['address'],
                $_POST['city'],
                $_POST['accreditation'],
                $_POST['github'],
                $_POST['linkedin'],
                $_POST['website'],
                $newBanner,
                $is_public,
                $_SESSION['user_id']
            ]);
        } else {
            // Aucun fichier, mise à jour normale
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ?, is_public = ? WHERE id = ?');
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $_POST['bio'],
                $_POST['phone'],
                $_POST['organization'],
                $_POST['address'],
                $_POST['city'],
                $_POST['accreditation'],
                $_POST['github'],
                $_POST['linkedin'],
                $_POST['website'],
                $is_public,
                $_SESSION['user_id']
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif ($_POST['action'] === 'delete_account') {
    try {
        $db = connect_db();

        // Commencer une transaction
        $db->beginTransaction();

        // Vérifier que l'utilisateur existe
        $checkStmt = $db->prepare('SELECT id FROM users WHERE id = ?');
        $checkStmt->execute([$_SESSION['user_id']]);

        if (!$checkStmt->fetch()) {
            throw new Exception('Utilisateur non trouvé');
        }

        // Supprimer l'utilisateur
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $result = $stmt->execute([$_SESSION['user_id']]);

        if ($result && $stmt->rowCount() > 0) {
            // Valider la transaction
            $db->commit();

            // Détruire la session
            session_destroy();
            // Rediriger vers la page d'accueil
            header('Location: ../index.php');
        } else {
            $db->rollback();
            throw new Exception('Aucune ligne supprimée');
        }
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        throw new Exception('Erreur lors de la suppression : ' . $e->getMessage());
    }
} elseif ($_POST['action'] === 'fetch_user_account') {
    try {
        $db = connect_db();

        // Récupérer les informations de l'utilisateur
        $stmt = $db->prepare('SELECT * FROM users WHERE id =?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif ($_POST['action'] === 'update_password') {
    try {
        $db = connect_db();
        $stmt = $db->prepare('SELECT password FROM users WHERE id =?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($_POST['current_password'], $user['password'])) {
            $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $updateStmt = $db->prepare('UPDATE users SET password =? WHERE id =?');
            $updateStmt->execute([$newPassword, $_SESSION['user_id']]);

            echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mot de passe actuel incorrect']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Vider et arrêter la mise en mémoire tampon
ob_end_flush();
exit;
?>