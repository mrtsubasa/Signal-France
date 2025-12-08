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
            try {
                $stmt = $conn->prepare("SELECT id, username, email, role, created_at, last_activity, is_blacklisted, is_verified, is_active FROM users ORDER BY created_at DESC");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Formater les dates
                foreach ($users as &$user) {
                    $user['created_at'] = date('d/m/Y H:i', strtotime($user['created_at']));
                    $user['last_activity'] = $user['last_activity'] ? date('d/m/Y H:i', strtotime($user['last_activity'])) : 'Jamais';
                }
                
                echo json_encode(['success' => true, 'users' => $users]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
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
            $userId = $_POST['id'] ?? 0;
            
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            $stmt = $conn->prepare("SELECT id, username, email, role, is_verified, is_active FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
            }
            break;
            
        case 'update_user':
            $userId = $_POST['user_id'] ?? 0;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? '';
            $isVerified = $_POST['is_verified'] ?? '0';
            
            if ($userId <= 0 || empty($username) || empty($email) || empty($role)) {
                throw new Exception('Données invalides');
            }
            
            // Vérifier que le rôle existe
            $validRoles = ['admin', 'moderator', 'user', 'opj', 'avocat', 'journaliste', 'magistrat', 'psychologue', 'association', 'rgpd'];
            if (!in_array($role, $validRoles)) {
                throw new Exception('Rôle invalide');
            }
            
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Cet email est déjà utilisé par un autre utilisateur');
            }
            
            // Vérifier si le nom d'utilisateur existe déjà pour un autre utilisateur
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Ce nom d\'utilisateur est déjà utilisé');
            }
            
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_verified = ? WHERE id = ?");
            $stmt->execute([$username, $email, $role, $isVerified, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
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
                $filter = $_POST['filter'] ?? '';
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
                    'success' => true,
                    'signalements' => $signalements
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
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
                        // Décoder les preuves JSON en tableau
                        if ($signalement['preuves']) {
                            $signalement['preuves'] = json_decode($signalement['preuves'], true) ?: [];
                        } else {
                            $signalement['preuves'] = [];
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'signalement' => $signalement
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Signalement non trouvé'
                        ]);
                    }
                    
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
                break;
            

                case 'update_signalement_status':
                    try {
                        $id = $_POST['id'] ?? 0;
                        $status = $_POST['status'] ?? '';
                        
                        // Validation des données
                        if (empty($id) || empty($status)) {
                            throw new Exception('ID et statut requis');
                        }
                        
                        // Validation du statut
                        $validStatuses = ['en_attente', 'en_cours', 'resolu', 'rejete'];
                        if (!in_array($status, $validStatuses)) {
                            throw new Exception('Statut invalide');
                        }
                        
                        // Préparer les champs à mettre à jour
                        $updateFields = ['statut = ?'];
                        $params = [$status];
                        
                        // Si le statut change vers résolu ou rejeté, mettre à jour la date de traitement et qui l'a traité
                        if (in_array($status, ['resolu', 'rejete'])) {
                            $updateFields[] = 'date_traitement = datetime(\'now\')';
                            $updateFields[] = 'traite_par = ?';
                            $params[] = $_SESSION['user_id'];
                        }
                        
                        $params[] = $id; // ID pour la clause WHERE
                        
                        $sql = "UPDATE signalements SET " . implode(', ', $updateFields) . " WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute($params);
                        
                        if ($stmt->rowCount() > 0) {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Statut mis à jour avec succès'
                            ]);
                        } else {
                            throw new Exception('Aucune modification effectuée');
                        }
                        
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => $e->getMessage()
                        ]);
                    }
                    break;

                    case 'initialize_tables':
                        try {
                            $results = [];
                            $errors = [];
                            
                            // Liste des scripts d'initialisation
                            $initScripts = [
                                '../Inc/Traitement/create_contact_table.php',
                                '../Inc/Traitement/create_signal_table.php', 
                                '../Inc/Traitement/create_chat_table.php',
                                '../Inc/Traitement/create_default_users.php',
                                '../Inc/Traitement/create_log.php'
                            ];
                            
                            foreach ($initScripts as $script) {
                                if (file_exists($script)) {
                                    ob_start();
                                    include $script;
                                    $output = ob_get_clean();
                                    $results[] = basename($script) . ' exécuté';
                                } else {
                                    $errors[] = 'Script non trouvé : ' . basename($script);
                                }
                            }
                            
                            if (empty($errors)) {
                                echo json_encode([
                                    'success' => true,
                                    'message' => 'Toutes les tables ont été initialisées',
                                    'details' => $results
                                ]);
                            } else {
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'Erreurs lors de l\'initialisation',
                                    'errors' => $errors,
                                    'completed' => $results
                                ]);
                            }
                        } catch (Exception $e) {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Erreur : ' . $e->getMessage()
                            ]);
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
                            
                            // Récupérer l'ancien statut pour détecter les changements
                            $oldStatusStmt = $conn->prepare("SELECT statut FROM signalements WHERE id = ?");
                            $oldStatusStmt->execute([$id]);
                            $oldStatus = $oldStatusStmt->fetchColumn();
                            
                            // Gestion des fichiers uploadés
                            $uploadedFiles = [];
                            $uploadDir = '../uploads/';
                            
                            // Créer le dossier uploads s'il n'existe pas
                            if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            
                            // Traitement des nouveaux fichiers
                            if (isset($_FILES['preuves']) && !empty($_FILES['preuves']['name'][0])) {
                                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
                                $maxFileSize = 5 * 1024 * 1024; // 5MB
                                
                                for ($i = 0; $i < count($_FILES['preuves']['name']); $i++) {
                                    if ($_FILES['preuves']['error'][$i] === UPLOAD_ERR_OK) {
                                        $fileName = $_FILES['preuves']['name'][$i];
                                        $fileType = $_FILES['preuves']['type'][$i];
                                        $fileSize = $_FILES['preuves']['size'][$i];
                                        $fileTmpName = $_FILES['preuves']['tmp_name'][$i];
                                        
                                        // Validation du type de fichier
                                        if (!in_array($fileType, $allowedTypes)) {
                                            throw new Exception("Type de fichier non autorisé: $fileName");
                                        }
                                        
                                        // Validation de la taille
                                        if ($fileSize > $maxFileSize) {
                                            throw new Exception("Fichier trop volumineux: $fileName (max 5MB)");
                                        }
                                        
                                        // Générer un nom unique
                                        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                                        $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
                                        $filePath = $uploadDir . $uniqueFileName;
                                        
                                        // Déplacer le fichier
                                        if (move_uploaded_file($fileTmpName, $filePath)) {
                                            $uploadedFiles[] = [
                                                'original_name' => $fileName,
                                                'file_path' => $uniqueFileName,
                                                'file_type' => $fileType,
                                                'file_size' => $fileSize
                                            ];
                                        } else {
                                            throw new Exception("Erreur lors de l'upload du fichier: $fileName");
                                        }
                                    }
                                }
                            }
                            
                            // Récupérer les preuves existantes
                            $existingProofsStmt = $conn->prepare("SELECT preuves FROM signalements WHERE id = ?");
                            $existingProofsStmt->execute([$id]);
                            $existingProofs = $existingProofsStmt->fetchColumn();
                            $existingProofsArray = $existingProofs ? json_decode($existingProofs, true) : [];
                            
                            // Gestion de la suppression de fichiers existants
                            if (isset($_POST['deleted_files']) && !empty($_POST['deleted_files'])) {
                                $deletedFiles = json_decode($_POST['deleted_files'], true);
                                if (is_array($deletedFiles)) {
                                    foreach ($deletedFiles as $fileToDelete) {
                                        // Supprimer le fichier du serveur
                                        $filePath = $uploadDir . $fileToDelete;
                                        if (file_exists($filePath)) {
                                            unlink($filePath);
                                        }
                                        
                                        // Retirer de la liste des preuves existantes
                                        $existingProofsArray = array_filter($existingProofsArray, function($proof) use ($fileToDelete) {
                                            return $proof['file_path'] !== $fileToDelete;
                                        });
                                    }
                                }
                            }
                            
                            // Fusionner les preuves existantes avec les nouvelles
                            $allProofs = array_merge($existingProofsArray, $uploadedFiles);
                            $preuves = !empty($allProofs) ? json_encode($allProofs) : null;
                            
                            // Mise à jour du signalement
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
                                    preuves = ?,
                                    updated_at = CURRENT_TIMESTAMP,
                                    date_traitement = CASE WHEN statut != ? AND ? IN ('resolu', 'rejete') THEN CURRENT_TIMESTAMP ELSE date_traitement END,
                                    traite_par = CASE WHEN statut != ? AND ? IN ('resolu', 'rejete') THEN ? ELSE traite_par END
                                WHERE id = ?
                            ");
                            
                            $userId = $_SESSION['user_id'] ?? null;
                            
                            $stmt->execute([
                                $titre, $description, $localisation, $type_incident, $incident_context,
                                $plateforme, $lieu, $priorite, $statut, $commentaire_traitement, $preuves,
                                $oldStatus, $statut, $oldStatus, $statut, $userId, $id
                            ]);
                            
                            if ($stmt->rowCount() > 0) {
                                echo json_encode([
                                    'success' => true, 
                                    'message' => 'Signalement mis à jour avec succès',
                                    'uploaded_files' => count($uploadedFiles)
                                ]);
                            } else {
                                echo json_encode([
                                    'success' => false, 
                                    'message' => 'Aucune modification effectuée ou signalement introuvable'
                                ]);
                            }
                            
                        } catch (Exception $e) {
                            echo json_encode([
                                'success' => false,
                                'message' => $e->getMessage()
                            ]);
                        }
                        break;

                        case 'delete_signalement_file':
                            try {
                                $signalementId = $_POST['signalement_id'] ?? 0;
                                $fileName = $_POST['file_name'] ?? '';
                                
                                if (empty($signalementId) || empty($fileName)) {
                                    throw new Exception('ID du signalement et nom du fichier requis');
                                }
                                
                                // Récupérer les preuves actuelles
                                $stmt = $conn->prepare("SELECT preuves FROM signalements WHERE id = ?");
                                $stmt->execute([$signalementId]);
                                $preuves = $stmt->fetchColumn();
                                
                                if ($preuves) {
                                    $preuvesArray = json_decode($preuves, true);
                                    
                                    // Filtrer pour retirer le fichier à supprimer
                                    $newPreuvesArray = array_filter($preuvesArray, function($proof) use ($fileName) {
                                        return $proof['file_path'] !== $fileName;
                                    });
                                    
                                    // Supprimer le fichier physique
                                    $filePath = '../uploads/' . $fileName;
                                    if (file_exists($filePath)) {
                                        unlink($filePath);
                                    }
                                    
                                    // Mettre à jour la base de données
                                    $newPreuves = !empty($newPreuvesArray) ? json_encode(array_values($newPreuvesArray)) : null;
                                    $updateStmt = $conn->prepare("UPDATE signalements SET preuves = ? WHERE id = ?");
                                    $updateStmt->execute([$newPreuves, $signalementId]);
                                    
                                    echo json_encode([
                                        'success' => true,
                                        'message' => 'Fichier supprimé avec succès'
                                    ]);
                                } else {
                                    throw new Exception('Aucune preuve trouvée pour ce signalement');
                                }
                                
                            } catch (Exception $e) {
                                echo json_encode([
                                    'success' => false,
                                    'message' => $e->getMessage()
                                ]);
                            }
                            break;
        
                    case 'delete_signalement':
                        try {
                            $id = $_POST['id'] ?? 0;
                            
                            $stmt = $conn->prepare("DELETE FROM signalements WHERE id = ?");
                            $stmt->execute([$id]);
                            
                            echo json_encode([
                                'success' => true, 
                                'message' => 'Signalement supprimé'
                            ]);
                        } catch (Exception $e) {
                            echo json_encode([
                                'success' => false,
                                'message' => $e->getMessage()
                            ]);
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
                    $logs = [];
                    $stats = [
                        'total' => 0,
                        'today' => 0,
                        'lastHour' => 0,
                        'fileSize' => '0 KB'
                    ];
                    
                    // Lire les logs du dossier Pages
                    $logFile = __DIR__ . '/log.txt';
                    if (file_exists($logFile)) {
                        $content = file_get_contents($logFile);
                        $lines = array_filter(explode("\n", $content));
                        $stats['total'] = count($lines);
                        $stats['fileSize'] = formatBytes(filesize($logFile));
                        
                        $today = date('Y-m-d');
                        $lastHour = date('Y-m-d H:i:s', strtotime('-1 hour'));
                        
                        foreach ($lines as $line) {
                            if (empty(trim($line))) continue;
                            
                            // Parser la ligne de log
                            $parts = explode(' - ', $line, 4);
                            if (count($parts) >= 4) {
                                $timestamp = $parts[0];
                                $ip = $parts[1];
                                $page = $parts[2];
                                $userAgent = $parts[3];
                                
                                $logs[] = [
                                    'timestamp' => $timestamp,
                                    'ip' => $ip,
                                    'page' => $page,
                                    'userAgent' => $userAgent,
                                    'content' => $line,
                                    'date' => substr($timestamp, 0, 10)
                                ];
                                
                                // Statistiques
                                if (strpos($timestamp, $today) === 0) {
                                    $stats['today']++;
                                }
                                if ($timestamp >= $lastHour) {
                                    $stats['lastHour']++;
                                }
                            }
                        }
                        
                        // Trier par timestamp décroissant
                        usort($logs, function($a, $b) {
                            return strcmp($b['timestamp'], $a['timestamp']);
                        });
                    }
                    
                    // Lire aussi les logs du dossier racine
                    $rootLogFile = dirname(__DIR__) . '/log.txt';
                    if (file_exists($rootLogFile)) {
                        $content = file_get_contents($rootLogFile);
                        $lines = array_filter(explode("\n", $content));
                        $stats['total'] += count($lines);
                        
                        foreach ($lines as $line) {
                            if (empty(trim($line))) continue;
                            
                            $parts = explode(' - ', $line, 4);
                            if (count($parts) >= 4) {
                                $timestamp = $parts[0];
                                $ip = $parts[1];
                                $page = $parts[2];
                                $userAgent = $parts[3];
                                
                                $logs[] = [
                                    'timestamp' => $timestamp,
                                    'ip' => $ip,
                                    'page' => $page,
                                    'userAgent' => $userAgent,
                                    'content' => $line,
                                    'date' => substr($timestamp, 0, 10)
                                ];
                            }
                        }
                        
                        // Re-trier après ajout
                        usort($logs, function($a, $b) {
                            return strcmp($b['timestamp'], $a['timestamp']);
                        });
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'logs' => $logs,
                        'stats' => $stats
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur lors de la lecture des logs: ' . $e->getMessage()
                    ]);
                }
                break;
                
            case 'clear_logs':
                try {
                    $logFile = __DIR__ . '/log.txt';
                    $rootLogFile = dirname(__DIR__) . '/log.txt';
                    
                    if (file_exists($logFile)) {
                        file_put_contents($logFile, '');
                    }
                    if (file_exists($rootLogFile)) {
                        file_put_contents($rootLogFile, '');
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Logs vidés avec succès'
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur lors de la suppression des logs: ' . $e->getMessage()
                    ]);
                }
                break;
                
            case 'download_logs':
                try {
                    $logs = '';
                    
                    // Combiner les logs des deux fichiers
                    $logFile = __DIR__ . '/log.txt';
                    $rootLogFile = dirname(__DIR__) . '/log.txt';
                    
                    if (file_exists($logFile)) {
                        $logs .= "=== LOGS PAGES ===\n";
                        $logs .= file_get_contents($logFile);
                        $logs .= "\n\n";
                    }
                    
                    if (file_exists($rootLogFile)) {
                        $logs .= "=== LOGS RACINE ===\n";
                        $logs .= file_get_contents($rootLogFile);
                    }
                    
                    header('Content-Type: text/plain');
                    header('Content-Disposition: attachment; filename="logs_' . date('Y-m-d_H-i-s') . '.txt"');
                    header('Content-Length: ' . strlen($logs));
                    echo $logs;
                    exit;
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
                    ]);
                }
                break;


                case 'get_database_info':
                    try {
                        $conn = connect_db();
                        
                        // Obtenir la liste des tables
                        $tablesQuery = $conn->query("SELECT name, type FROM sqlite_master WHERE type='table' ORDER BY name");
                        $tables = [];
                        
                        while ($table = $tablesQuery->fetch()) {
                            $tableName = $table['name'];
                            
                            // Compter les lignes de chaque table
                            try {
                                $countQuery = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
                                $count = $countQuery->fetch()['count'];
                            } catch (Exception $e) {
                                $count = 0;
                            }
                            
                            $tables[] = [
                                'name' => $tableName,
                                'type' => $table['type'],
                                'rows' => $count
                            ];
                        }
                        
                        // Statistiques de la base de données
                        $dbPath = dirname(__DIR__) . '/Inc/Db/db.sqlite';
                        $stats = [
                            'tablesCount' => count($tables),
                            'size' => file_exists($dbPath) ? formatBytes(filesize($dbPath)) : '0 KB',
                            'lastModified' => file_exists($dbPath) ? date('d/m/Y H:i', filemtime($dbPath)) : 'N/A',
                            'sqliteVersion' => $conn->query('SELECT sqlite_version()')->fetch()['sqlite_version()']
                        ];
                        
                        echo json_encode([
                            'success' => true,
                            'tables' => $tables,
                            'stats' => $stats
                        ]);
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Erreur lors de la lecture de la base de données: ' . $e->getMessage()
                        ]);
                    }
                    break;
                    
                case 'get_table_details':
                    try {
                        $tableName = $_POST['table'] ?? '';
                        if (empty($tableName)) {
                            throw new Exception('Nom de table manquant');
                        }
                        
                        $conn = connect_db();
                        
                        // Obtenir la structure de la table
                        $columnsQuery = $conn->query("PRAGMA table_info(`$tableName`)");
                        $columns = [];
                        
                        while ($column = $columnsQuery->fetch()) {
                            $columns[] = [
                                'name' => $column['name'],
                                'type' => $column['type'],
                                'notnull' => $column['notnull'],
                                'pk' => $column['pk']
                            ];
                        }
                        
                        // Compter les lignes
                        $countQuery = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
                        $rowCount = $countQuery->fetch()['count'];
                        
                        echo json_encode([
                            'success' => true,
                            'details' => [
                                'columns' => $columns,
                                'rowCount' => $rowCount,
                                'size' => 'N/A' // SQLite ne fournit pas facilement la taille par table
                            ]
                        ]);
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Erreur lors de la lecture des détails: ' . $e->getMessage()
                        ]);
                    }
                    break;
                    
                case 'execute_sql':
                    try {
                        $query = $_POST['query'] ?? '';
                        if (empty($query)) {
                            throw new Exception('Requête SQL manquante');
                        }
                        
                        $conn = connect_db();
                        
                        // Déterminer le type de requête
                        $queryType = strtoupper(trim(explode(' ', $query)[0]));
                        
                        if ($queryType === 'SELECT') {
                            $stmt = $conn->query($query);
                            $results = $stmt->fetchAll();
                            
                            echo json_encode([
                                'success' => true,
                                'results' => $results,
                                'type' => 'SELECT'
                            ]);
                        } else {
                            // Pour les requêtes de modification
                            $stmt = $conn->exec($query);
                            
                            echo json_encode([
                                'success' => true,
                                'results' => [
                                    'message' => "Requête exécutée. Lignes affectées: $stmt"
                                ],
                                'type' => $queryType
                            ]);
                        }
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => $e->getMessage()
                        ]);
                    }
                    break;
                    
                case 'backup_database':
                    try {
                        $dbPath = dirname(__DIR__) . '/Inc/Db/db.sqlite';
                        
                        if (!file_exists($dbPath)) {
                            throw new Exception('Fichier de base de données introuvable');
                        }
                        
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="database_backup_' . date('Y-m-d_H-i-s') . '.sqlite"');
                        header('Content-Length: ' . filesize($dbPath));
                        readfile($dbPath);
                        exit;
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
                        ]);
                    }
                    break;
                    
                case 'export_table':
                    try {
                        $tableName = $_POST['table'] ?? '';
                        if (empty($tableName)) {
                            throw new Exception('Nom de table manquant');
                        }
                        
                        $conn = connect_db();
                        $stmt = $conn->query("SELECT * FROM `$tableName`");
                        $data = $stmt->fetchAll();
                        
                        if (empty($data)) {
                            throw new Exception('Aucune donnée à exporter');
                        }
                        
                        // Générer le CSV
                        $csv = '';
                        
                        // En-têtes
                        $headers = array_keys($data[0]);
                        $csv .= implode(',', $headers) . "\n";
                        
                        // Données
                        foreach ($data as $row) {
                            $csvRow = [];
                            foreach ($row as $value) {
                                // Échapper les guillemets et entourer de guillemets si nécessaire
                                $value = str_replace('"', '""', $value);
                                if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
                                    $value = '"' . $value . '"';
                                }
                                $csvRow[] = $value;
                            }
                            $csv .= implode(',', $csvRow) . "\n";
                        }
                        
                        header('Content-Type: text/csv; charset=utf-8');
                        header('Content-Disposition: attachment; filename="' . $tableName . '_export_' . date('Y-m-d_H-i-s') . '.csv"');
                        header('Content-Length: ' . strlen($csv));
                        echo $csv;
                        exit;
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Erreur lors de l\'exportation: ' . $e->getMessage()
                        ]);
                    }
                    break;


                    case 'get_adhesions':
                        try {
                            $db = connect_db();
                            
                            // Récupérer toutes les demandes d'adhésion
                            $stmt = $db->prepare("SELECT * FROM adhesion_requests ORDER BY created_at DESC");
                            $stmt->execute();
                            $adhesions = $stmt->fetchAll();
                            
                            // Calculer les statistiques
                            $stats = [
                                'pending' => 0,
                                'approved' => 0,
                                'rejected' => 0,
                                'weekly' => 0
                            ];
                            
                            $weekAgo = date('Y-m-d H:i:s', strtotime('-1 week'));
                            
                            foreach ($adhesions as $adhesion) {
                                $stats[$adhesion['status']]++;
                                if ($adhesion['created_at'] >= $weekAgo) {
                                    $stats['weekly']++;
                                }
                            }
                            
                            echo json_encode([
                                'success' => true,
                                'adhesions' => $adhesions,
                                'stats' => $stats
                            ]);
                            
                        } catch (Exception $e) {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Erreur lors du chargement des demandes: ' . $e->getMessage()
                            ]);
                        }
                        break;
                        
                    case 'approve_adhesion':
                        try {
                            $id = $_POST['id'] ?? null;
                            if (!$id) {
                                throw new Exception('ID manquant');
                            }
                            
                            $db = connect_db();
                            
                            // Récupérer la demande
                            $stmt = $db->prepare("SELECT * FROM adhesion_requests WHERE id = ? AND status = 'pending'");
                            $stmt->execute([$id]);
                            $request = $stmt->fetch();
                            
                            if (!$request) {
                                throw new Exception('Demande non trouvée ou déjà traitée');
                            }
                            
                            // Créer l'utilisateur
                            $stmt = $db->prepare("INSERT INTO users (username, email, password, role, organization, accreditation, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $request['username'],
                                $request['email'],
                                $request['password'], // Déjà hashé
                                $request['role'],
                                $request['organization'],
                                $request['accreditation'],
                                date('Y-m-d H:i:s')
                            ]);
                            
                            // Mettre à jour le statut de la demande
                            $stmt = $db->prepare("UPDATE adhesion_requests SET status = 'approved', reviewed_by = ?, reviewed_at = ? WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id'], date('Y-m-d H:i:s'), $id]);
                            
                            echo json_encode(['success' => true, 'message' => 'Demande approuvée et utilisateur créé']);
                            
                        } catch (Exception $e) {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Erreur lors de l\'approbation: ' . $e->getMessage()
                            ]);
                        }
                        break;
                        
                    case 'reject_adhesion':
                        try {
                            $id = $_POST['id'] ?? null;
                            $reason = $_POST['reason'] ?? null;
                            
                            if (!$id) {
                                throw new Exception('ID manquant');
                            }
                            
                            $db = connect_db();
                            
                            // Mettre à jour le statut de la demande
                            $stmt = $db->prepare("UPDATE adhesion_requests SET status = 'rejected', reviewed_by = ?, reviewed_at = ?, rejection_reason = ? WHERE id = ? AND status = 'pending'");
                            $stmt->execute([$_SESSION['user_id'], date('Y-m-d H:i:s'), $reason, $id]);
                            
                            if ($stmt->rowCount() === 0) {
                                throw new Exception('Demande non trouvée ou déjà traitée');
                            }
                            
                            echo json_encode(['success' => true, 'message' => 'Demande rejetée']);
                            
                        } catch (Exception $e) {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Erreur lors du rejet: ' . $e->getMessage()
                            ]);
                        }
                        break;

                
                

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