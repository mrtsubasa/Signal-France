<?php
session_start();
require_once '../Inc/Constants/db.php';

// Vérifier directement les sessions au lieu d'inclure nav.php
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'moderator'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

header('Content-Type: application/json');

try {
    $conn = connect_db();
    if (!$conn) {
        throw new Exception('Impossible de se connecter à la base de données');
    }
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_users':
            $stmt = $conn->prepare("SELECT id, username, email, role, created_at, last_activity, is_blacklisted, is_verified, is_active FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formater les dates
            foreach ($users as &$user) {
                $user['created_at'] = date('d/m/Y H:i', strtotime($user['created_at']));
                $user['last_activity'] = $user['last_activity'] ? date('d/m/Y H:i', strtotime($user['last_activity'])) : 'Jamais';
            }
            
            echo json_encode(['users' => $users]);
            break;
            
        case 'delete_user':
            $userId = $_POST['user_id'] ?? 0;
            
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            // Empêcher la suppression de son propre compte
            if ($userId == $user_id) {
                throw new Exception('Vous ne pouvez pas supprimer votre propre compte');
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['status' => 'success', 'message' => 'Utilisateur supprimé avec succès']);
            break;
            
            case 'update_user_info': 
                $userId = $_POST['user_id']?? 0;
                $username = $_POST['username']?? '';
                $email = $_POST['email']?? '';
                $smt = $conn->prepare("UPDATE users SET username =?, email =? WHERE id =?");
                $stmt->execute([$username, $email, $userId]);

                echo json_encode(['status' =>'success','message' => 'Informations utilisateur mises à jour avec succès']);
                break;
        case 'update_user_role':
            $userId = $_POST['user_id'] ?? 0;
            $newRole = $_POST['role'] ?? '';
            
            if ($userId <= 0 || empty($newRole)) {
                throw new Exception('Données invalides');
            }
            
            // Vérifier que le rôle existe
            $validRoles = ['admin', 'moderator', 'user', 'opj', 'avocat', 'journaliste', 'magistrat', 'psychologue', 'association', 'rgpd'];
            if (!in_array($newRole, $validRoles)) {
                throw new Exception('Rôle invalide');
            }
            
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            
            echo json_encode(['status' => 'success', 'message' => 'Rôle mis à jour avec succès']);
            break;
            
            case 'update_user_blacklist':
                $userId = $_POST['user_id']?? 0;
                $blacklistStatus = $_POST['blacklist_status']?? '';

                if ($userId <= 0 || empty($blacklistStatus)) {
                    throw new Exception('Données invalides');
                }

                // Vérifier que le statut existe
                $validBlacklistStatuses = ['blacklisted', 'unblacklisted'];
                if (!in_array($blacklistStatus, $validBlacklistStatuses)) {
                    throw new Exception('Statut invalide');
                }

                if ($blacklistStatus === 'blacklisted') {
                    $stmt = $conn->prepare("UPDATE users SET is_blacklisted = 1 WHERE id = ?");
                    $stmt->execute([$userId]);
                    echo json_encode(["status"=> "success", "message"=> "Utilisateur mis en liste noire avec succès"]);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET is_blacklisted = 0 WHERE id =?");
                    $stmt->execute([$userId]);
                    echo json_encode(["status"=> "success", "message"=> "Utilisateur retiré de la liste noire avec succès"]);
                }
                break;

                // Ajouter ce case dans le switch de admin_ajax.php
case 'update_user_verified':
    $user_id = $_POST['user_id'] ?? 0;
    $verifStatus = $_POST['verif_status'] ?? '';
    

    if ($user_id === null || empty($verifStatus)) {
        throw new Exception('Données invalides');
    }

    $validStatus = ["verified", "unverified"];
    if (!in_array($verifStatus, $validStatus)) {
        throw new Exception('Statut invalide');
    }

    if ($verifStatus === 'verified') {
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id =?");
        $stmt->execute([$user_id]);
        echo json_encode(["status"=> "success", "message"=> "Utilisateur vérifié avec succès"]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET is_verified = 0 WHERE id =?");
        $stmt->execute([$user_id]);
        echo json_encode(["status"=> "success", "message"=> "Utilisateur non vérifié avec succès"]);
    }
    break;
        case 'get_user':
            $userId = $_GET['user_id'] ?? 0;
            
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            $stmt = $conn->prepare("SELECT id, username, email, role, created_at, last_activity, access_level, accreditation, organization, is_blacklisted, is_verified FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user_data) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            echo json_encode(['user' => $user_data]);
            break;
            
        case 'create_user':
            // Test de connexion avant de procéder
            $testQuery = $conn->query("SELECT 1");
            if (!$testQuery) {
                throw new Exception('Base de données non accessible');
            }
            
            // Récupérer les données du formulaire
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $newRole = $_POST['role'] ?? '';
            $sendWelcomeEmail = isset($_POST['sendWelcomeEmail']) && $_POST['sendWelcomeEmail'] === 'true';
            $requirePasswordChange = isset($_POST['requirePasswordChange']) && $_POST['requirePasswordChange'] === 'true';
            
            // Validation des données
            if (empty($username) || empty($email) || empty($password) || empty($newRole)) {
                throw new Exception('Tous les champs obligatoires doivent être remplis');
            }
            
            // Validation du nom d'utilisateur
            if (strlen($username) < 3) {
                throw new Exception('Le nom d\'utilisateur doit contenir au moins 3 caractères');
            }
            
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                throw new Exception('Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores');
            }
            
            // Validation de l'email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format d\'email invalide');
            }
            
            // Validation du mot de passe
            if (strlen($password) < 8 || 
                !preg_match('/[A-Z]/', $password) || 
                !preg_match('/[a-z]/', $password) || 
                !preg_match('/[0-9]/', $password) || 
                !preg_match('/[^\w]/', $password)) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial');
            }
            
            // Vérifier que le rôle existe
            $validRoles = ['admin', 'moderator', 'user', 'opj', 'avocat', 'journaliste','developer', 'magistrat', 'psychologue', 'association', 'rgpd'];
            if (!in_array($newRole, $validRoles)) {
                throw new Exception('Rôle invalide');
            }
            
            // Vérifier si l'utilisateur existe déjà
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                throw new Exception('Un utilisateur avec ce nom d\'utilisateur ou cet email existe déjà');
            }
            
            // Hasher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer le nouvel utilisateur avec gestion d'erreur détaillée
            try {
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, password, role, created_at, require_password_change) 
                    VALUES (?, ?, ?, ?, datetime('now'), ?)
                ");
                
                $result = $stmt->execute([
                    $username, 
                    $email, 
                    $hashedPassword, 
                    $newRole,
                    $requirePasswordChange ? 1 : 0
                ]);
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception('Erreur SQL: ' . $errorInfo[2]);
                }
                
                $newUserId = $conn->lastInsertId();
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Utilisateur créé avec succès',
                    'user_id' => $newUserId
                ]);
            } catch (PDOException $e) {
                throw new Exception('Erreur base de données: ' . $e->getMessage());
            }
            break;
            
        // Gestion des signalements
        case 'get_signalements':
            try {
                $filter = $_GET['filter'] ?? '';
                $sql = "SELECT s.*, u.username as auteur_nom FROM signalements s LEFT JOIN users u ON s.user_id = u.id";
                
                if (!empty($filter)) {
                    $sql .= " WHERE s.statut = :filter";
                }
                
                $sql .= " ORDER BY s.date_signalement DESC";
                
                $stmt = $conn->prepare($sql);
                if (!empty($filter)) {
                    $stmt->bindParam(':filter', $filter);
                }
                $stmt->execute();
                
                $signalements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Formater les dates
                foreach ($signalements as &$signalement) {
                    $signalement['date_creation'] = date('d/m/Y H:i', strtotime($signalement['date_signalement']));
                    $signalement['auteur_nom'] = $signalement['auteur_nom'] ?? 'Utilisateur supprimé';
                }
                
                echo json_encode([
                    'status' => 'success',
                    'signalements' => $signalements
                ]);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        
            case 'update_signalement':
                try {
                    $id = $_POST['id'] ?? 0;
                    $titre = $_POST['titre'] ?? '';
                    $description = $_POST['description'] ?? '';
                    $localisation = $_POST['localisation'] ?? '';
                    $type_incident = $_POST['type_incident'] ?? '';
                    $incident_context = $_POST['incident_context'] ?? 'irl';
                    $plateforme = $_POST['plateforme'] ?? '';
                    $lieu = $_POST['lieu'] ?? '';
                    $priorite = $_POST['priorite'] ?? 'normale';
                    $statut = $_POST['statut'] ?? 'en_attente';
                    $commentaire_traitement = $_POST['commentaire_traitement'] ?? '';
                    // SUPPRIMER : $email_contact = $_POST['email_contact'] ?? '';
                    
                    // Validation des champs obligatoires
                    if (empty($titre) || empty($description) || empty($type_incident)) {
                        throw new Exception('Les champs titre, description et type d\'incident sont obligatoires');
                    }
                    
                    // Validation du statut
                    $validStatuses = ['en_attente', 'en_cours', 'resolu', 'rejete'];
                    if (!in_array($statut, $validStatuses)) {
                        throw new Exception('Statut invalide');
                    }
                    
                    // Validation de la priorité
                    $validPriorities = ['faible', 'normale', 'haute', 'critique'];
                    if (!in_array($priorite, $validPriorities)) {
                        throw new Exception('Priorité invalide');
                    }
                    
                    // Mise à jour du signalement (SANS email_contact)
                    $stmt = $conn->prepare("
                        UPDATE signalements SET 
                            titre = ?, 
                            description = ?, 
                            localisation = ?, 
                            type_incident = ?, 
                            incident_context = ?, 
                            plateforme = ?, 
                            lieu = ?, 
                            priorite = ?, 
                            statut = ?, 
                            commentaire_traitement = ?, 
                            updated_at = CURRENT_TIMESTAMP,
                            date_traitement = CASE WHEN statut != ? AND ? IN ('resolu', 'rejete') THEN CURRENT_TIMESTAMP ELSE date_traitement END,
                            traite_par = CASE WHEN statut != ? AND ? IN ('resolu', 'rejete') THEN ? ELSE traite_par END
                        WHERE id = ?
                    ");
                    
                    // Récupérer l'ancien statut pour détecter les changements
                    $oldStatusStmt = $conn->prepare("SELECT statut FROM signalements WHERE id = ?");
                    $oldStatusStmt->execute([$id]);
                    $oldStatus = $oldStatusStmt->fetchColumn();
                    
                    $userId = $_SESSION['user_id'] ?? null;
                    
                    $stmt->execute([
                        $titre, $description, $localisation, $type_incident, $incident_context,
                        $plateforme, $lieu, $priorite, $statut, $commentaire_traitement,
                        $oldStatus, $statut, $oldStatus, $statut, $userId, $id
                    ]);
                    
                    if ($stmt->rowCount() > 0) {
                        echo json_encode([
                            'status' => 'success', 
                            'message' => 'Signalement mis à jour avec succès'
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'error', 
                            'error' => 'Aucune modification effectuée ou signalement introuvable'
                        ]);
                    }
                    
                } catch (Exception $e) {
                    echo json_encode([
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ]);
                }
                break;
            
            case 'get_signalement_details':
                try {
                    $id = $_POST['id'] ?? 0;
                    
                    $stmt = $conn->prepare("
                        SELECT s.*, u.username as auteur_nom 
                        FROM signalements s 
                        LEFT JOIN users u ON s.user_id = u.id 
                        WHERE s.id = ?
                    ");
                    $stmt->execute([$id]);
                    $signalement = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($signalement) {
                        echo json_encode([
                            'status' => 'success',
                            'signalement' => $signalement
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'error' => 'Signalement non trouvé'
                        ]);
                    }
                    
                } catch (Exception $e) {
                    echo json_encode([
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ]);
                }
                break;
            
        
                case 'update_signalement_status':
                    try {
                        $id = $_POST['id'] ?? 0;
                        $status = $_POST['status'] ?? '';
                        
                        $validStatuses = ['en_attente', 'en_cours', 'resolu', 'rejete'];
                        if (!in_array($status, $validStatuses)) {
                            throw new Exception('Statut invalide');
                        }
                        
                        $stmt = $conn->prepare("UPDATE signalements SET statut = ? WHERE id = ?");
                        $stmt->execute([$status, $id]);
                        
                        echo json_encode(['status' => 'success', 'message' => 'Statut mis à jour']);
                    } catch (Exception $e) {
                        echo json_encode(['error' => $e->getMessage()]);
                    }
                    break;
        
        case 'delete_signalement':
            try {
                $id = $_POST['id'] ?? 0;
                
                $stmt = $conn->prepare("DELETE FROM signalements WHERE id = ?");
                $stmt->execute([$id]);
                
                echo json_encode(['status' => 'success', 'message' => 'Signalement supprimé']);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        
        // Gestion des messages de contact
        case 'get_contacts':
            try {
                $filter = $_GET['filter'] ?? '';
                $sql = "SELECT * FROM messages_contact";
                
                if (!empty($filter)) {
                    $sql .= " WHERE statut = :filter";
                }
                
                $sql .= " ORDER BY date_creation DESC";
                
                $stmt = $conn->prepare($sql);
                if (!empty($filter)) {
                    $stmt->bindParam(':filter', $filter);
                }
                $stmt->execute();
                
                $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Formater les dates
                foreach ($contacts as &$contact) {
                    $contact['date_creation'] = date('d/m/Y H:i', strtotime($contact['date_creation']));
                    $contact['nom_affiche'] = $contact['anonyme'] ? 'Utilisateur anonyme' : $contact['nom'];
                    $contact['email_affiche'] = $contact['anonyme'] ? 'Non communiqué' : $contact['email'];
                }
                
                echo json_encode([
                    'status' => 'success',
                    'contacts' => $contacts
                ]);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        
            case 'get_contact_details':
                try {
                    $id = $_POST['id'] ?? 0; // Changé de $_GET à $_POST
                    
                    if (!$id) {
                        throw new Exception('ID manquant');
                    }
                    
                    $stmt = $conn->prepare("SELECT * FROM messages_contact WHERE id = ?");
                    $stmt->execute([$id]);
                    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($contact) {
                        // Formatage des données
                        $contact['date_creation'] = date('d/m/Y H:i', strtotime($contact['date_creation']));
                        $contact['nom_affiche'] = $contact['anonyme'] ? 'Utilisateur anonyme' : $contact['nom'];
                        $contact['email_affiche'] = $contact['anonyme'] ? 'Non communiqué' : $contact['email'];
                        
                        echo json_encode([
                            'status' => 'success',
                            'contact' => $contact
                        ]);
                    } else {
                        echo json_encode(['error' => 'Message non trouvé']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;


                case 'mark_contact_as_replied':
                    try {
                        $id = $_POST['id']?? 0;

                        if (!$id) {
                            throw new Exception('ID manquant');
                        }

                        $db = connect_db();

                        // Mise à jour du statut du message
                        $stmt = $conn->prepare("UPDATE messages_contact SET statut = 'repondu', repondu = 1 WHERE id = ?");
                        $stmt->execute([$id]);

                        echo json_encode(['status' =>'success','message' => 'Message marqué comme répondu']);
                    } catch (Exception $e) {
                        echo json_encode(['error' => $e->getMessage()]);
                    }
                    break;

            
            
            case 'update_contact_status':
                try {
                    $id = $_POST['id'] ?? 0;
                    $status = $_POST['status'] ?? '';
                    
                    if (!$id) {
                        throw new Exception('ID manquant');
                    }
                    
                    // Statuts valides corrigés
                    $validStatuses = ['nouveau', 'en_cours', 'resolu'];
                    if (!in_array($status, $validStatuses)) {
                        throw new Exception('Statut invalide. Statuts autorisés: ' . implode(', ', $validStatuses));
                    }
                    
                    $stmt = $conn->prepare("UPDATE messages_contact SET statut = ?, repondu = ? WHERE id = ?");
                    $repondu = ($status === 'resolu') ? 1 : 0;
                    $result = $stmt->execute([$status, $repondu, $id]);
                    
                    if ($result) {
                        echo json_encode(['status' => 'success', 'message' => 'Statut mis à jour avec succès']);
                    } else {
                        throw new Exception('Erreur lors de la mise à jour');
                    }
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;
        
        case 'delete_contact':
            try {
                $id = $_POST['id'] ?? 0;
                
                $stmt = $conn->prepare("DELETE FROM messages_contact WHERE id = ?");
                $stmt->execute([$id]);
                
                echo json_encode(['status' => 'success', 'message' => 'Message supprimé']);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
        
        // Statistiques pour le tableau de bord
        case 'get_stats':
            try {
                // Statistiques des signalements
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM signalements");
                $stmt->execute();
                $totalSignalements = $stmt->fetchColumn();
                
                $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM signalements WHERE statut = 'en_attente'");
                $stmt->execute();
                $pendingSignalements = $stmt->fetchColumn();
                
                // Statistiques des contacts
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM messages_contact");
                $stmt->execute();
                $totalContacts = $stmt->fetchColumn();
                
                $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages_contact WHERE statut = 'nouveau'");
                $stmt->execute();
                $unreadContacts = $stmt->fetchColumn();
                
                echo json_encode([
                    'status' => 'success',
                    'stats' => [
                        'signalements' => [
                            'total' => $totalSignalements,
                            'pending' => $pendingSignalements
                        ],
                        'contacts' => [
                            'total' => $totalContacts,
                            'unread' => $unreadContacts
                        ]
                    ]
                ]);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
            case 'get_logs':
                try {
                    $logFile = 'log.txt';
                    if (file_exists($logFile)) {
                        $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        $logs = array_reverse($logs); // Afficher les plus récents en premier
                        
                        $stats = [
                            'total' => count($logs),
                            'lastActivity' => count($logs) > 0 ? substr($logs[0], 0, 19) : 'Aucune',
                            'fileSize' => formatBytes(filesize($logFile))
                        ];
                        
                        echo json_encode([
                            'status' => 'success',
                            'logs' => $logs,
                            'stats' => $stats
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'success',
                            'logs' => [],
                            'stats' => ['total' => 0, 'lastActivity' => 'Aucune', 'fileSize' => '0 B']
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;
            
            case 'clear_logs':
                try {
                    $logFile = 'log.txt';
                    if (file_exists($logFile)) {
                        file_put_contents($logFile, '');
                    }
                    echo json_encode(['status' => 'success', 'message' => 'Logs vidés']);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;
            
            case 'download_logs':
                try {
                    $logFile = 'log.txt';
                    if (file_exists($logFile)) {
                        header('Content-Type: text/plain');
                        header('Content-Disposition: attachment; filename="logs_' . date('Y-m-d_H-i-s') . '.txt"');
                        header('Content-Length: ' . filesize($logFile));
                        readfile($logFile);
                        exit;
                    } else {
                        echo json_encode(['error' => 'Fichier de logs non trouvé']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;// Fonction helper pour formater la taille des fichiers

                
                

        default:
            throw new Exception('Action non reconnue: ' . $action);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'details' => 'Erreur lors du traitement de la requête'
    ]);
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
?>