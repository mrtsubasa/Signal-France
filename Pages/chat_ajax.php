<?php
// Ajouter ces headers au début du fichier
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Si c'est une requête OPTIONS, arrêter ici
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
include("../Inc/Constants/db.php");
include("../Inc/Constants/functions.php");

// Vérification CSRF pour toutes les requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Erreur de sécurité : Jeton CSRF invalide']);
        exit;
    }
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_username'];
$user_avatar = $_SESSION['user_avatar'] ?? 'default.png';

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Action manquante']);
    exit;
}

try {
    $db = connect_db();

    switch ($_POST['action']) {
        case 'get_messages':
            $last_id = $_POST['last_id'] ?? 0;

            $stmt = $db->prepare("
                SELECT 
                    cm.id, cm.user_id, cm.message, cm.files, cm.created_at, cm.edited_at, cm.reply_to_id,
                    u.username, u.avatar,
                    reply_msg.message as reply_message,
                    reply_user.username as reply_username
                FROM chat_messages cm 
                JOIN users u ON cm.user_id = u.id 
                LEFT JOIN chat_messages reply_msg ON cm.reply_to_id = reply_msg.id
                LEFT JOIN users reply_user ON reply_msg.user_id = reply_user.id
                WHERE cm.id > ? 
                ORDER BY cm.created_at ASC
            ");
            $stmt->execute([$last_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Traiter les messages
            foreach ($messages as &$message) {
                if ($message['files']) {
                    $message['files'] = json_decode($message['files'], true);
                }

                // Ajouter les informations de réponse si disponibles
                if ($message['reply_to_id'] && $message['reply_message']) {
                    $message['reply_to'] = [
                        'id' => $message['reply_to_id'],
                        'message' => $message['reply_message'],
                        'username' => $message['reply_username']
                    ];
                }
            }

            echo json_encode(['success' => true, 'messages' => $messages]);
            break;

        case 'send_message':
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            $files = [];

            // Traitement des fichiers uploadés
            if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                $uploadDir = '../uploads/chat/';

                // Créer le dossier s'il n'existe pas
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                        $originalName = $_FILES['files']['name'][$i];
                        $tmpName = $_FILES['files']['tmp_name'][$i];
                        $fileSize = $_FILES['files']['size'][$i];
                        $fileType = $_FILES['files']['type'][$i];

                        // Vérifier la taille (10MB max)
                        if ($fileSize > 10 * 1024 * 1024) {
                            continue;
                        }

                        // Générer un nom unique
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                        $fileName = uniqid() . '_' . time() . '.' . $extension;
                        $filePath = $uploadDir . $fileName;

                        if (move_uploaded_file($tmpName, $filePath)) {
                            $files[] = [
                                'name' => $fileName,
                                'original_name' => $originalName,
                                'type' => $fileType,
                                'size' => $fileSize
                            ];
                        }
                    }
                }
            }

            // Vérifier qu'il y a au moins un message ou un fichier
            if (empty($message) && empty($files)) {
                echo json_encode(['success' => false, 'message' => 'Message ou fichier requis']);
                break;
            }

            // Limiter la longueur du message
            if (strlen($message) > 2000) {
                echo json_encode(['success' => false, 'message' => 'Message trop long']);
                break;
            }

            // Insérer le message
            $stmt = $db->prepare("
                INSERT INTO chat_messages (user_id, message, files, created_at)
                VALUES (?, ?, ?, datetime('now'))
            ");
            $stmt->execute([$user_id, $message, json_encode($files)]);

            echo json_encode([
                'success' => true,
                'message' => 'Message envoyé'
            ]);
            break;

        case 'get_online_users':
            $stmt = $db->prepare("
                SELECT id, username, avatar, role
                FROM users
                WHERE is_active = 1 AND id != ?
                ORDER BY username ASC
            ");
            $stmt->execute([$user_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
            break;

        case 'delete_message':
            $messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;

            if ($messageId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de message invalide']);
                break;
            }

            // Vérifier que l'utilisateur peut supprimer ce message
            $stmt = $db->prepare("SELECT user_id FROM chat_messages WHERE id = ?");
            $stmt->execute([$messageId]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$message) {
                echo json_encode(['success' => false, 'message' => 'Message introuvable']);
                break;
            }

            // Vérifier les permissions (propriétaire du message ou admin/modérateur)
            $userStmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $userStmt->execute([$user_id]);
            $userRole = $userStmt->fetchColumn();

            if ($message['user_id'] != $user_id && !in_array($userRole, ['admin', 'moderator'])) {
                echo json_encode(['success' => false, 'message' => 'Permission refusée']);
                break;
            }

            // Supprimer le message
            $deleteStmt = $db->prepare("DELETE FROM chat_messages WHERE id = ?");
            $deleteStmt->execute([$messageId]);

            echo json_encode([
                'success' => true,
                'message' => 'Message supprimé'
            ]);
            break;

        case 'search_messages':
            $searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';

            if (empty($searchTerm)) {
                echo json_encode(['success' => false, 'message' => 'Terme de recherche requis']);
                break;
            }

            $stmt = $db->prepare("
                    SELECT m.id, m.message, m.files, m.created_at, m.user_id,
                           u.username, u.avatar
                    FROM chat_messages m
                    JOIN users u ON m.user_id = u.id
                    WHERE m.message LIKE ?
                    ORDER BY m.created_at DESC
                    LIMIT 20
                ");
            $stmt->execute(['%' . $searchTerm . '%']);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Décoder les fichiers JSON
            foreach ($messages as &$message) {
                $message['files'] = $message['files'] ? json_decode($message['files'], true) : [];
            }

            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            break;

        case 'edit_message':
            $message_id = $_POST['message_id'] ?? '';
            $new_message = trim($_POST['message'] ?? '');

            if (empty($message_id) || empty($new_message)) {
                echo json_encode(['success' => false, 'message' => 'ID du message et nouveau contenu requis']);
                exit;
            }

            // Vérifier que l'utilisateur est propriétaire du message
            $checkStmt = $db->prepare("SELECT user_id FROM chat_messages WHERE id = ?");
            $checkStmt->execute([$message_id]);
            $messageData = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$messageData) {
                echo json_encode(['success' => false, 'message' => 'Message non trouvé']);
                exit;
            }

            if ($messageData['user_id'] != $user_id && $userRole !== 'admin' && $userRole !== 'moderator') {
                echo json_encode(['success' => false, 'message' => 'Permission refusée']);
                exit;
            }

            // Mettre à jour le message
            $updateStmt = $db->prepare("UPDATE chat_messages SET message = ?, edited_at = CURRENT_TIMESTAMP WHERE id = ?");
            $result = $updateStmt->execute([$new_message, $message_id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Message modifié avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
            }
            break;

        case 'reply_to_message':
            $reply_to_id = $_POST['reply_to_id'] ?? '';
            $message = trim($_POST['message'] ?? '');

            if (empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Message vide']);
                exit;
            }

            if (strlen($message) > 1000) {
                echo json_encode(['success' => false, 'message' => 'Message trop long (max 1000 caractères)']);
                exit;
            }

            // Vérifier que le message auquel on répond existe
            if (!empty($reply_to_id)) {
                $checkReply = $db->prepare("SELECT id FROM chat_messages WHERE id = ?");
                $checkReply->execute([$reply_to_id]);
                if (!$checkReply->fetch()) {
                    $reply_to_id = null;
                }
            }

            // Insérer le message de réponse
            $stmt = $db->prepare("INSERT INTO chat_messages (user_id, message, reply_to_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
            $result = $stmt->execute([$user_id, $message, $reply_to_id ?: null]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Réponse envoyée']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action inconnue']);
            break;

    }

} catch (PDOException $e) {
    error_log("Erreur chat AJAX: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données'
    ]);
}
?>