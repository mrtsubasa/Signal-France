<?php
session_start();
require_once '../Inc/Constants/db.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if ($_POST['action'] === 'update_profile') {
    try {
        $db = connect_db();
        
        // Gestion de l'avatar s'il y en a un
        $avatarUpdated = false;
        $newAvatar = null;
        
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $userId = $_SESSION['user_id'];
            
            // Vérifications de sécurité
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Type de fichier non autorisé. Utilisez JPG, JPEG ou PNG.');
            }
            
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB max
                throw new Exception('Le fichier est trop volumineux (2MB maximum)');
            }
            
            // Générer un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
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
            // Vérifications de sécurité
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Type de fichier non autorisé pour la bannière. Utilisez JPG, JPEG ou PNG.');
            }
            
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB max
                throw new Exception('Le fichier bannière est trop volumineux (2MB maximum)');
            }
            
            // Générer un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
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
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ?, avatar = ?, banner = ? WHERE id = ?');
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
                $_SESSION['user_id']
            ]);
        } elseif ($avatarUpdated) {
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ?, avatar = ? WHERE id = ?');
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
                $_SESSION['user_id']
            ]);
        } elseif ($bannerUpdated) {
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ?, banner = ? WHERE id = ?');
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
                $_SESSION['user_id']
            ]);
        } else {
            // Aucun fichier, mise à jour normale
            $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, organization = ?, address = ?, city = ?, accreditation = ?, github = ?, linkedin = ?, website = ? WHERE id = ?');
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
                $_SESSION['user_id']
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>