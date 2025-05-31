<?php
session_start();
require_once '../Inc/Constants/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Fonction pour compresser les images
function compressImage($source, $destination, $quality = 80) {
    $info = getimagesize($source);
    
    if (!$info) return false;
    
    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    if (!$image) return false;
    
    // Redimensionner si trop grande
    $width = imagesx($image);
    $height = imagesy($image);
    
    if ($width > 1920 || $height > 1080) {
        $ratio = min(1920/$width, 1080/$height);
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Préserver la transparence pour PNG
        if ($info['mime'] == 'image/png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefill($newImage, 0, 0, $transparent);
        }
        
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $newImage;
    }
    
    $result = false;
    switch ($info['mime']) {
        case 'image/jpeg':
            $result = imagejpeg($image, $destination, $quality);
            break;
        case 'image/png':
            $result = imagepng($image, $destination, 8);
            break;
        case 'image/gif':
            $result = imagegif($image, $destination);
            break;
    }
    
    imagedestroy($image);
    return $result;
}

// Fonction pour valider les fichiers
function validateFile($file, $allowedTypes, $maxSize) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Erreur lors du téléchargement'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['valid' => false, 'error' => 'Type de fichier non autorisé'];
    }
    
    if ($file['size'] > $maxSize) {
        $maxSizeMB = round($maxSize / (1024 * 1024), 1);
        return ['valid' => false, 'error' => "Fichier trop volumineux (max {$maxSizeMB}MB)"];
    }
    
    return ['valid' => true];
}

try {
    $conn = connect_db();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'validate_signal':
            $titre = trim($_POST['titre'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $type_incident = trim($_POST['type_incident'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $email_contact = trim($_POST['email_contact'] ?? '');
            // NOUVEAUX CHAMPS
            $incident_context = trim($_POST['incident_context'] ?? '');
            $plateforme = trim($_POST['plateforme'] ?? '');
            $lieu = trim($_POST['lieu'] ?? '');
            
            $errors = [];
            
            if (empty($titre) || strlen($titre) < 3) {
                $errors[] = 'Le titre doit contenir au moins 3 caractères';
            }
            
            if (empty($type)) {
                $errors[] = 'Le type de signalement est obligatoire';
            }
            
            if (empty($type_incident)) {
                $errors[] = 'Le type d\'incident est obligatoire';
            }
            
            if (empty($description) || strlen($description) < 10) {
                $errors[] = 'La description doit contenir au moins 10 caractères';
            }
            
            // VALIDATION DES NOUVEAUX CHAMPS
            if (empty($incident_context)) {
                $errors[] = 'Le contexte de l\'incident est obligatoire';
            }
            
            if ($incident_context === 'virtuel' && empty($plateforme)) {
                $errors[] = 'La plateforme est obligatoire pour un incident virtuel';
            }
            
            if ($incident_context === 'irl' && empty($lieu)) {
                $errors[] = 'Le lieu est obligatoire pour un incident IRL';
            }
            
            if (!empty($email_contact) && !filter_var($email_contact, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'adresse email n\'est pas valide';
            }
            
            echo json_encode([
                'valid' => empty($errors),
                'errors' => $errors
            ]);
            break;
            
        case 'upload_files':
            $uploadDir = '../uploads/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadedFiles = [];
            $errors = [];
            
            // Configuration des types de fichiers et tailles
            $fileConfigs = [
                'photo_personne' => [
                    'types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
                    'maxSize' => 2 * 1024 * 1024 // 2MB
                ],
                'depot_plainte' => [
                    'types' => ['application/pdf', 'image/jpeg', 'image/png'],
                    'maxSize' => 5 * 1024 * 1024 // 5MB
                ],
                'autres_preuves' => [
                    'types' => ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'application/pdf'],
                    'maxSize' => 10 * 1024 * 1024 // 10MB
                ]
            ];
            
            foreach ($fileConfigs as $fieldName => $config) {
                if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES[$fieldName];
                    
                    $validation = validateFile($file, $config['types'], $config['maxSize']);
                    if (!$validation['valid']) {
                        $errors[] = $validation['error'] . " pour {$fieldName}";
                        continue;
                    }
                    
                    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $uniqueFileName;
                    
                    $success = false;
                    
                    // Compresser les images
                    if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                        $success = compressImage($file['tmp_name'], $uploadPath);
                    } else {
                        $success = move_uploaded_file($file['tmp_name'], $uploadPath);
                    }
                    
                    if ($success) {
                        $uploadedFiles[$fieldName] = [
                            'original_name' => $file['name'],
                            'file_name' => $uniqueFileName,
                            'file_path' => $uploadPath,
                            'file_type' => $file['type'],
                            'file_size' => filesize($uploadPath)
                        ];
                    } else {
                        $errors[] = "Erreur lors du téléchargement de: {$file['name']}";
                    }
                }
            }
            
            echo json_encode([
                'success' => empty($errors),
                'files' => $uploadedFiles,
                'errors' => $errors
            ]);
            break;
            
        case 'submit_signal':
            // Validation complète avant insertion
            $titre = trim($_POST['titre'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $type_incident = trim($_POST['type_incident'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priorite = $_POST['priorite'] ?? 'normale';
            $email_contact = trim($_POST['email_contact'] ?? '');
            $anonyme = isset($_POST['anonyme']) ? 1 : 0;
            $preuves = $_POST['preuves'] ?? '{}';
            // NOUVEAUX CHAMPS
            $incident_context = trim($_POST['incident_context'] ?? 'irl');
            $plateforme = trim($_POST['plateforme'] ?? '');
            $lieu = trim($_POST['lieu'] ?? '');
            
            $errors = [];
            
            if (empty($titre) || strlen($titre) < 3) {
                $errors[] = 'Le titre doit contenir au moins 3 caractères';
            }
            
            if (empty($type)) {
                $errors[] = 'Le type de signalement est obligatoire';
            }
            
            if (empty($type_incident)) {
                $errors[] = 'Le type d\'incident est obligatoire';
            }
            
            if (empty($description) || strlen($description) < 10) {
                $errors[] = 'La description doit contenir au moins 10 caractères';
            }
            
            // VALIDATION DES NOUVEAUX CHAMPS
            if ($incident_context === 'virtuel' && empty($plateforme)) {
                $errors[] = 'La plateforme est obligatoire pour un incident virtuel';
            }
            
            if ($incident_context === 'irl' && empty($lieu)) {
                $errors[] = 'Le lieu est obligatoire pour un incident IRL';
            }
            
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                break;
            }
            
            // Insertion en base avec les nouveaux champs
            $sql = "INSERT INTO signalements (user_id, titre, type, description, type_incident, priorite, email_contact, anonyme, preuves, incident_context, plateforme, lieu, statut, date_signalement) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', datetime('now'))";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $_SESSION['user_id'],
                $titre,
                $type,
                $description,
                $type_incident,
                $priorite,
                $email_contact,
                $anonyme,
                $preuves,
                $incident_context,
                $plateforme,
                $lieu
            ]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Signalement créé avec succès',
                    'signal_id' => $conn->lastInsertId()
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erreur lors de l\'enregistrement'
                ]);
            }
            break;
            
        case 'get_user_stats':
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
                    SUM(CASE WHEN statut = 'resolu' THEN 1 ELSE 0 END) as resolu,
                    SUM(CASE WHEN statut = 'rejete' THEN 1 ELSE 0 END) as rejete
                FROM signalements 
                WHERE user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        case 'delete_file':
            $fileName = $_POST['file_name'] ?? '';
            $filePath = '../uploads/' . basename($fileName);
            
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    echo json_encode(['success' => true, 'message' => 'Fichier supprimé']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Fichier non trouvé']);
            }
            break;
            
            case 'check_upload_progress':
                // Modern upload progress tracking using sessions
                $uploadKey = $_GET['upload_key'] ?? '';
                
                if (empty($uploadKey)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Upload key required'
                    ]);
                    break;
                }
                
                // Check if upload progress is stored in session
                $progress = $_SESSION["upload_progress_$uploadKey"] ?? null;
                
                if ($progress) {
                    echo json_encode([
                        'success' => true,
                        'progress' => $progress
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'progress' => 0
                    ]);
                }
                break;
            
            case 'upload_proof_files':
                $uploadDir = '../uploads/';
                
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $uploadedFiles = [];
                $errors = [];
                
                // Configuration pour les nouvelles catégories de preuves
                $proofConfigs = [
                    'photos' => [
                        'types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
                        'maxSize' => 5 * 1024 * 1024 // 5MB
                    ],
                    'videos' => [
                        'types' => ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'],
                        'maxSize' => 50 * 1024 * 1024 // 50MB
                    ],
                    'documents' => [
                        'types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
                        'maxSize' => 10 * 1024 * 1024 // 10MB
                    ]
                ];
                
                foreach ($proofConfigs as $category => $config) {
                    if (isset($_FILES[$category])) {
                        $files = $_FILES[$category];
                        
                        // Gérer les uploads multiples
                        if (is_array($files['name'])) {
                            for ($i = 0; $i < count($files['name']); $i++) {
                                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                                    $file = [
                                        'name' => $files['name'][$i],
                                        'type' => $files['type'][$i],
                                        'tmp_name' => $files['tmp_name'][$i],
                                        'error' => $files['error'][$i],
                                        'size' => $files['size'][$i]
                                    ];
                                    
                                    $validation = validateFile($file, $config['types'], $config['maxSize']);
                                    if (!$validation['valid']) {
                                        $errors[] = $validation['error'] . " pour {$file['name']}";
                                        continue;
                                    }
                                    
                                    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                    $uniqueFileName = $category . '_' . uniqid() . '_' . time() . '.' . $fileExtension;
                                    $uploadPath = $uploadDir . $uniqueFileName;
                                    
                                    $success = false;
                                    
                                    // Compresser les images
                                    if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                                        $success = compressImage($file['tmp_name'], $uploadPath);
                                    } else {
                                        $success = move_uploaded_file($file['tmp_name'], $uploadPath);
                                    }
                                    
                                    if ($success) {
                                        $uploadedFiles[] = [
                                            'category' => $category,
                                            'original_name' => $file['name'],
                                            'file_name' => $uniqueFileName,
                                            'file_path' => $uploadPath,
                                            'file_type' => $file['type'],
                                            'file_size' => filesize($uploadPath)
                                        ];
                                    } else {
                                        $errors[] = "Erreur lors du téléchargement de: {$file['name']}";
                                    }
                                }
                            }
                        }
                    }
                }
                
                echo json_encode([
                    'success' => empty($errors),
                    'files' => $uploadedFiles,
                    'errors' => $errors
                ]);
                break;

            case 'update_signal_status':
                $signalId = $_POST['signal_id']?? '';
                $newStatus = $_POST['new_status']?? '';

                if (empty($signalId) || empty($newStatus)) {
                    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
                    break;
                }

                $stmt = $conn->prepare("UPDATE signalements SET statut = ? WHERE id = ?");
                $result = $stmt->execute([$newStatus, $signalId]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Statut du signalement mis à jour']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour du statut']);
                }
                break;
        default:
            echo json_encode(['error' => 'Action non reconnue']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erreur signal_ajax.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>