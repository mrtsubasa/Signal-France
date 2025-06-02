<?php
session_start();
require_once '../Inc/Constants/db.php';
$currentPath = $_SERVER['REQUEST_URI'];
$isInSubfolder = (strpos($currentPath, '/Src/') !== false || strpos($currentPath, '/Pages/') !== false);
$basePath = $isInSubfolder ? '../' : './';
// Variables d'initialisation
$searchResults = [];
$searchPerformed = false;
$searchQuery = '';
$searchType = 'all';
$sortBy = 'date_signalement';
$sortOrder = 'DESC';
$statusFilter = '';
$priorityFilter = '';
$typeFilter = '';
$error = '';
$totalResults = 0;

// Traitement de la recherche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchQuery = trim($_POST['search_query'] ?? '');
    $searchType = $_POST['search_type'] ?? 'all';
    $sortBy = $_POST['sort_by'] ?? 'date_signalement';
    $sortOrder = $_POST['sort_order'] ?? 'DESC';
    $statusFilter = $_POST['status_filter'] ?? '';
    $priorityFilter = $_POST['priority_filter'] ?? '';
    $typeFilter = $_POST['type_filter'] ?? '';
    $searchPerformed = true;
    
    try {
        $pdo = connect_db();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Construction de la requête SQL avec les vrais noms de colonnes
        $sql = "SELECT s.*, 
        u.username, u.email,
        CASE 
            WHEN s.anonyme = 1 THEN 'Anonyme'
            WHEN u.username IS NOT NULL THEN u.username
            ELSE 'Utilisateur supprimé'
        END as auteur_complet
 FROM signalements s 
 LEFT JOIN users u ON s.user_id = u.id 
 WHERE s.statut = 'resolu'";
$params = [];
        
        // Filtres de recherche par texte
        if (!empty($searchQuery)) {
            switch ($searchType) {
                case 'titre':
                    $sql .= " AND s.titre LIKE ?";
                    $params[] = "%$searchQuery%";
                    break;
                case 'description':
                    $sql .= " AND s.description LIKE ?";
                    $params[] = "%$searchQuery%";
                    break;
                case 'localisation':
                    $sql .= " AND (s.localisation LIKE ? OR s.lieu LIKE ?)";
                    $params[] = "%$searchQuery%";
                    $params[] = "%$searchQuery%";
                    break;
                case 'type_incident':
                    $sql .= " AND s.type_incident LIKE ?";
                    $params[] = "%$searchQuery%";
                    break;
                case 'plateforme':
                    $sql .= " AND s.plateforme LIKE ?";
                    $params[] = "%$searchQuery%";
                    break;
                case 'auteur':
                    $sql .= " AND (s.auteur LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
                    $params[] = "%$searchQuery%";
                    $params[] = "%$searchQuery%";
                    $params[] = "%$searchQuery%";
                    break;
                default: // 'all'
                    $sql .= " AND (s.titre LIKE ? OR s.description LIKE ? OR s.localisation LIKE ? OR s.lieu LIKE ? OR s.type_incident LIKE ? OR s.plateforme LIKE ? OR s.auteur LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
                    $searchTerm = "%$searchQuery%";
                    $params = array_fill(0, 9, $searchTerm);
                    break;
            }
        }
        
        // Filtres supplémentaires
        if (!empty($statusFilter)) {
            $sql .= " AND s.statut = ?";
            $params[] = $statusFilter;
        }
        
        if (!empty($priorityFilter)) {
            $sql .= " AND s.priorite = ?";
            $params[] = $priorityFilter;
        }
        
        if (!empty($typeFilter)) {
            $sql .= " AND s.type_incident = ?";
            $params[] = $typeFilter;
        }
        
        // Tri sécurisé
        $allowedSortFields = ['date_signalement', 'titre', 'statut', 'priorite', 'type_incident', 'created_at', 'updated_at'];
        $allowedSortOrders = ['ASC', 'DESC'];
        
        if (in_array($sortBy, $allowedSortFields) && in_array($sortOrder, $allowedSortOrders)) {
            $sql .= " ORDER BY s.$sortBy $sortOrder";
        } else {
            $sql .= " ORDER BY s.date_signalement DESC";
        }
        
        // Exécution de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalResults = count($searchResults);
        
    } catch (PDOException $e) {
        $error = "Erreur lors de la recherche: " . $e->getMessage();
    }
}

// Récupération des options pour les filtres
try {
    $pdo = connect_db();
    
    // Statuts disponibles
    $stmt = $pdo->query("SELECT DISTINCT statut FROM signalements WHERE statut IS NOT NULL AND statut != '' ORDER BY statut");
    $availableStatuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Priorités disponibles
    $stmt = $pdo->query("SELECT DISTINCT priorite FROM signalements WHERE priorite IS NOT NULL AND priorite != '' ORDER BY priorite");
    $availablePriorities = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Types d'incidents disponibles
    $stmt = $pdo->query("SELECT DISTINCT type_incident FROM signalements WHERE type_incident IS NOT NULL AND type_incident != '' ORDER BY type_incident");
    $availableTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $availableStatuses = [];
    $availablePriorities = [];
    $availableTypes = [];
}

// Fonction pour obtenir la classe CSS du statut
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'en_attente': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'en_cours': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'resolu': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'rejete': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

// Fonction pour obtenir la classe CSS de la priorité
function getPriorityClass($priority) {
    switch (strtolower($priority)) {
        case 'faible': return 'bg-green-100 text-green-800 border-green-200';
        case 'normale': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'elevee': return 'bg-orange-100 text-orange-800 border-orange-200';
        case 'critique': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

// Fonction pour obtenir l'icône du statut
function getStatusIcon($status) {
    switch (strtolower($status)) {
        case 'en_attente': return 'fas fa-clock';
        case 'en_cours': return 'fas fa-spinner';
        case 'resolu': return 'fas fa-check-circle';
        case 'rejete': return 'fas fa-times-circle';
        default: return 'fas fa-question-circle';
    }
}

// Fonction pour obtenir l'icône de la priorité
function getPriorityIcon($priority) {
    switch (strtolower($priority)) {
        case 'faible': return 'fas fa-arrow-down';
        case 'normale': return 'fas fa-minus';
        case 'elevee': return 'fas fa-arrow-up';
        case 'critique': return 'fas fa-exclamation-triangle';
        default: return 'fas fa-question';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Avancée - Signale France</title>
    <link rel="stylesheet" href="../Assets/Css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
                 .french-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
        }
       
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(30, 58, 138, 0.1), 0 10px 10px -5px rgba(30, 58, 138, 0.04);
        }
        .search-highlight {
            background-color: #fef3c7;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        .modal-backdrop {
            backdrop-filter: blur(8px);
            background-color: rgba(30, 58, 138, 0.3);
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .slide-in {
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <?php include '../Inc/Components/header.php'; ?>
    <?php include '../Inc/Components/nav.php'; ?>

    <!-- Hero Section -->
    <div class="french-gradient text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <i class="fas fa-search mr-4"></i>Recherche Avancée
                </h1>
                <p class="text-xl opacity-90 mb-8">Explorez notre base de données de signalements résolus</p>
                
                <!-- Barre de recherche principale -->
                <form method="POST" class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input type="text" 
                               name="search_query" 
                               value="<?php echo htmlspecialchars($searchQuery); ?>"
                               placeholder="Rechercher par titre, description, localisation..."
                               class="search-input w-full px-6 py-4 pr-16 text-gray-800 rounded-2xl border-0 focus:ring-4 focus:ring-white/30 focus:outline-none text-lg">
                        <button type="submit" name="search" 
                                class="absolute right-2 top-2 bottom-2 px-6 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-300 flex items-center">
                            <i class="fas fa-search text-lg"></i>
                        </button>
                    </div>
                    
                    <!-- Filtres rapides -->
                    <div class="flex flex-wrap justify-center gap-3 mt-6">
                        <select name="search_type" class="px-4 py-2 rounded-lg bg-white/20 text-white border border-white/30 focus:ring-2 focus:ring-white/50">
                            <option value="all" <?php echo $searchType === 'all' ? 'selected' : ''; ?>>Recherche globale</option>
                            <option value="titre" <?php echo $searchType === 'titre' ? 'selected' : ''; ?>>Titre uniquement</option>
                            <option value="description" <?php echo $searchType === 'description' ? 'selected' : ''; ?>>Description</option>
                            <option value="localisation" <?php echo $searchType === 'localisation' ? 'selected' : ''; ?>>Localisation</option>
                            <option value="type_incident" <?php echo $searchType === 'type_incident' ? 'selected' : ''; ?>>Type d'incident</option>
                            <option value="plateforme" <?php echo $searchType === 'plateforme' ? 'selected' : ''; ?>>Plateforme</option>
                            <option value="auteur" <?php echo $searchType === 'auteur' ? 'selected' : ''; ?>>Auteur</option>
                        </select>
                        
                        <select name="sort_by" class="px-4 py-2 rounded-lg bg-white/20 text-white border border-white/30 focus:ring-2 focus:ring-white/50">
                            <option value="date_signalement" <?php echo $sortBy === 'date_signalement' ? 'selected' : ''; ?>>Date signalement</option>
                            <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Date création</option>
                            <option value="updated_at" <?php echo $sortBy === 'updated_at' ? 'selected' : ''; ?>>Dernière modification</option>
                            <option value="titre" <?php echo $sortBy === 'titre' ? 'selected' : ''; ?>>Titre</option>
                            <option value="priorite" <?php echo $sortBy === 'priorite' ? 'selected' : ''; ?>>Priorité</option>
                        </select>
                        
                        <select name="sort_order" class="px-4 py-2 rounded-lg bg-white/20 text-white border border-white/30 focus:ring-2 focus:ring-white/50">
                            <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Plus récent</option>
                            <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Plus ancien</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Filtres avancés -->
            <div class="filter-card rounded-2xl shadow-lg p-6 mb-8 animate-fade-in">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>Filtres avancés
                </h2>
                
                <form method="POST">
                    <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <input type="hidden" name="search_type" value="<?php echo htmlspecialchars($searchType); ?>">
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sortBy); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sortOrder); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag mr-1"></i>Statut
                            </label>
                            <select name="status_filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tous les statuts</option>
                                <?php foreach ($availableStatuses as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Priorité
                            </label>
                            <select name="priority_filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Toutes les priorités</option>
                                <?php foreach ($availablePriorities as $priority): ?>
                                    <option value="<?php echo htmlspecialchars($priority); ?>" <?php echo $priorityFilter === $priority ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($priority); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tags mr-1"></i>Type d'incident
                            </label>
                            <select name="type_filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tous les types</option>
                                <?php foreach ($availableTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $typeFilter === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-center mt-6">
                        <button type="submit" name="search" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 flex items-center shadow-lg">
                            <i class="fas fa-search mr-2"></i>Appliquer les filtres
                        </button>
                    </div>
                </form>
            </div>

            <!-- Messages d'erreur -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-lg animate-fade-in">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

                                     <?php foreach ($searchResults as $index => $signalement): ?>
                            
                            <div class="group relative bg-white rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-500 border-2 border-gray-100 hover:border-[#000091] animate-fade-in transform hover:-translate-y-1" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                <!-- Effet de bordure animée -->
                                <div class="absolute inset-0 bg-gradient-to-r from-[#000091] via-[#6a6af4] to-[#e1000f] rounded-3xl opacity-0 group-hover:opacity-20 transition-opacity duration-500 -z-10"></div>
                                
                                <div class="flex flex-col lg:flex-row gap-6">
                                    <!-- Informations principales à gauche -->
                                    <div class="flex-1 space-y-5">
                                        <!-- Badge ID en haut -->
                                        <div class="flex items-start justify-between mb-4">
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-gradient-to-r from-[#000091] to-[#6a6af4] text-white shadow-lg">
                                                <i class="fas fa-hashtag mr-2"></i><?php echo $signalement['id']; ?>
                                            </span>
                                        </div>

                                        <!-- Titre principal -->
                                        <h3 class="text-2xl font-bold text-gray-900 mb-4 hover:bg-gradient-to-r hover:from-[#000091] hover:to-[#e1000f] hover:bg-clip-text hover:text-transparent transition-all duration-300 cursor-pointer leading-tight" onclick="showSignalementDetails(<?php echo $signalement['id']; ?>)">
                                            <?php 
                                            $titre = htmlspecialchars($signalement['titre']);
                                            if ($searchType === 'titre' && !empty($searchQuery)) {
                                                $titre = str_ireplace($searchQuery, '<mark class="bg-yellow-200 px-2 py-1 rounded-lg font-semibold">' . htmlspecialchars($searchQuery) . '</mark>', $titre);
                                            }
                                            echo $titre;
                                            ?>
                                        </h3>

                                        <!-- Grille d'informations compacte -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <!-- Localisation -->
                                            <?php if (!empty($signalement['localisation'])): ?>
                                                <div class="flex items-center p-3 bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl border border-red-100 hover:shadow-md transition-shadow">
                                                    <div class="w-8 h-8 bg-gradient-to-r from-[#e1000f] to-red-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                                                        <i class="fas fa-map-marker-alt text-white text-sm"></i>
                                                    </div>
                                                    <span class="text-gray-700 font-medium text-sm truncate"><?php echo htmlspecialchars($signalement['localisation']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Auteur -->
                                            <div class="flex items-center p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 hover:shadow-md transition-shadow">
                                                <div class="w-8 h-8 bg-gradient-to-r from-[#6a6af4] to-indigo-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                                                    <i class="fas fa-user text-white text-sm"></i>
                                                </div>
                                                <span class="text-gray-700 font-medium text-sm truncate"><?php echo htmlspecialchars($signalement['auteur_complet']); ?></span>
                                            </div>
                                            
                                            <!-- Date -->
                                            <div class="flex items-center p-3 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-100 hover:shadow-md transition-shadow">
                                                <div class="w-8 h-8 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                                                    <i class="fas fa-calendar text-white text-sm"></i>
                                                </div>
                                                <span class="text-gray-700 font-medium text-sm"><?php echo date('d/m/Y', strtotime($signalement['date_signalement'])); ?></span>
                                            </div>
                                            
                                            <!-- Type d'incident -->
                                            <?php if (!empty($signalement['type_incident'])): ?>
                                                <div class="flex items-center p-3 bg-gradient-to-r from-slate-50 to-gray-50 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow">
                                                    <div class="w-8 h-8 bg-gradient-to-r from-slate-600 to-gray-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                                                        <i class="fas fa-tag text-white text-sm"></i>
                                                    </div>
                                                    <span class="text-gray-700 font-medium text-sm truncate"><?php echo htmlspecialchars($signalement['type_incident']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Bouton d'action principal -->
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <button onclick="showSignalementDetails(<?php echo $signalement['id']; ?>)" 
                                                    class="group relative w-full lg:w-auto px-8 py-4 bg-gradient-to-r from-[#000091] to-[#6a6af4] text-white rounded-2xl hover:from-[#6a6af4] hover:to-[#000091] transition-all duration-500 flex items-center justify-center shadow-xl hover:shadow-2xl transform hover:-translate-y-1 overflow-hidden font-semibold">
                                                <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                                <i class="fas fa-eye mr-3 text-lg"></i>
                                                <span>View details</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Photo rectangulaire moderne à droite -->
                                    <div class="lg:w-80 lg:flex-shrink-0">
                                        <div class="relative group">
                                            <!-- Conteneur avec bordure tricolore moderne -->
                                            <div class="relative bg-gradient-to-br from-blue-500 via-blue-600 to-blue-800 p-1 rounded-3xl shadow-2xl group-hover:shadow-3xl transition-all duration-500">
                                                <!-- Image avec coins arrondis -->
                                                <div class="bg-white p-2 rounded-[22px] shadow-inner">
                                                    <?php if (!empty($signalement['photo'])): ?>
                                                        <img src="<?php echo htmlspecialchars($signalement['photo']); ?>" alt="Photo du signalement" class="w-full h-64 object-cover rounded-2xl group-hover:scale-105 transition-transform duration-500">
                                                    <?php else: ?>
                                                        <img src="<?php echo $basePath; ?>Assets/Images/IMG_5652.jpg" alt="Photo par défaut" class="w-full h-64 object-cover rounded-2xl group-hover:scale-105 transition-transform duration-500">
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Badge photo moderne -->
                                                <div class="absolute bottom-4 right-4 w-12 h-12 bg-white rounded-full shadow-xl flex items-center justify-center border-2 border-[#000091] group-hover:scale-110 transition-transform duration-300">
                                                    <i class="fas fa-camera text-[#000091] text-lg"></i>
                                                </div>
                                                
                                                <!-- Effet de brillance subtil -->
                                                <div class="absolute top-4 left-6 w-8 h-8 bg-white/40 rounded-full blur-md opacity-70"></div>
                                                
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Section description moderne -->
                                <div class="mt-6 bg-gradient-to-r from-gray-50 via-slate-50 to-gray-50 rounded-3xl p-6 border border-gray-100 shadow-inner">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-r from-[#000091] to-[#6a6af4] rounded-2xl flex items-center justify-center mr-3 shadow-lg">
                                            <i class="fas fa-file-text text-white"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800">Description</h4>
                                    </div>
                                    <p class="text-gray-700 leading-relaxed text-lg font-medium">
                                        <?php echo htmlspecialchars(substr($signalement['description'], 0, 200)) . (strlen($signalement['description']) > 200 ? '...' : ''); ?>
                                    </p>
                                    
                                    <!-- Badges d'informations supplémentaires -->
                                    <div class="flex flex-wrap gap-3 mt-4">
                                        <?php if (!empty($signalement['preuves'])): ?>
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-[#000091] to-[#6a6af4] text-white shadow-lg">
                                                <i class="fas fa-paperclip mr-2"></i>Preuves
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($signalement['images'])): ?>
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg">
                                                <i class="fas fa-images mr-2"></i>Images
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($signalement['email_contact'])): ?>
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-purple-500 to-violet-500 text-white shadow-lg">
                                                <i class="fas fa-envelope mr-2"></i>Contact
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                                       
                                    
                        <?php endforeach; ?>
                    </div>
         
        </div>
    </div>

    <!-- Modal pour les détails -->
    <div id="signalementModal" class="fixed inset-0 modal-backdrop hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden shadow-2xl">
                <div class="flex justify-between items-center p-6 border-b bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                    <h3 class="text-xl font-bold"><i class="fas fa-file-alt mr-2"></i>Détails du signalement</h3>
                    <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <div id="modalContent" class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                    <!-- Contenu chargé dynamiquement -->
                </div>
            </div>
        </div>
    </div>

    <script>
    function showSignalementDetails(id) {
        const modal = document.getElementById('signalementModal');
        const content = document.getElementById('modalContent');
        
        content.innerHTML = `
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <p class="text-gray-600">Chargement des détails...</p>
            </div>
        `;
        modal.classList.remove('hidden');
        
        fetch('search_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get_signalement&signalement_id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySignalementDetails(data.signalement);
            } else {
                content.innerHTML = `
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Erreur de chargement</h3>
                        <p class="text-gray-600">${data.error || 'Une erreur est survenue'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-wifi text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Erreur de connexion</h3>
                    <p class="text-gray-600">Impossible de charger les détails</p>
                </div>
            `;
        });
    }
    
    function displaySignalementDetails(signalement) {
        const content = document.getElementById('modalContent');
        const statusClass = getStatusClass(signalement.statut);
        const priorityClass = getPriorityClass(signalement.priorite);
        const statusIcon = getStatusIcon(signalement.statut);
        const priorityIcon = getPriorityIcon(signalement.priorite);
        
        content.innerHTML = `
            <div class="space-y-8">
                <!-- En-tête -->
                <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2 md:mb-0">${escapeHtml(signalement.titre)}</h2>
                     <img src="${signalement.photo ? signalement.photo : '../Assets/Images/IMG_5652.jpg'}" alt="Photo du signalement" class="w-16 h-16 rounded-lg object-cover">
                    </div>
                    <p class="text-gray-600">Signalement #${signalement.id}</p>
                </div>
                
                <!-- Informations principales -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>Informations générales
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex items-center">
                                    <i class="fas fa-user w-5 text-purple-500 mr-3"></i>
                                    <span class="font-medium text-gray-700">Auteur:</span>
                                    <span class="ml-2">${signalement.anonyme == 1 ? 'Anonyme' : (signalement.auteur || 'Utilisateur supprimé')}</span>
                                </div>
                                ${signalement.email_contact ? `
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope w-5 text-blue-500 mr-3"></i>
                                        <span class="font-medium text-gray-700">Email:</span>
                                        <span class="ml-2">${escapeHtml(signalement.email_contact)}</span>
                                    </div>
                                ` : ''}
                                ${signalement.localisation ? `
                                    <div class="flex items-center">
                                        <i class="fas fa-map-marker-alt w-5 text-red-500 mr-3"></i>
                                        <span class="font-medium text-gray-700">Localisation:</span>
                                        <span class="ml-2">${escapeHtml(signalement.localisation)}</span>
                                    </div>
                                ` : ''}
                                ${signalement.lieu ? `
                                    <div class="flex items-center">
                                        <i class="fas fa-location-dot w-5 text-green-500 mr-3"></i>
                                        <span class="font-medium text-gray-700">Lieu:</span>
                                        <span class="ml-2">${escapeHtml(signalement.lieu)}</span>
                                    </div>
                                ` : ''}
                                ${signalement.plateforme ? `
                                    <div class="flex items-center">
                                        <i class="fas fa-globe w-5 text-indigo-500 mr-3"></i>
                                        <span class="font-medium text-gray-700">Plateforme:</span>
                                        <span class="ml-2">${escapeHtml(signalement.plateforme)}</span>
                                    </div>
                                ` : ''}
                                ${signalement.type_incident ? `
                                    <div class="flex items-center">
                                        <i class="fas fa-tag w-5 text-orange-500 mr-3"></i>
                                        <span class="font-medium text-gray-700">Type:</span>
                                        <span class="ml-2">${escapeHtml(signalement.type_incident)}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-clock mr-2 text-green-600"></i>Chronologie
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex items-center">
                                    <i class="fas fa-plus-circle w-5 text-blue-500 mr-3"></i>
                                    <span class="font-medium text-gray-700">Créé le:</span>
                                    <span class="ml-2">${new Date(signalement.created_at).toLocaleString('fr-FR')}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-edit w-5 text-orange-500 mr-3"></i>
                                    <span class="font-medium text-gray-700">Modifié le:</span>
                                    <span class="ml-2">${new Date(signalement.updated_at).toLocaleString('fr-FR')}</span>
                                </div>
                                ${signalement.date_traitement ? `
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle w-5 text-green-500 mr-3"></i>
                                        <span class="font-medium text-gray-700">Traité le:</span>
                                        <span class="ml-2">${new Date(signalement.date_traitement).toLocaleString('fr-FR')}</span>
                                    </div>
                                ` : ''}
                                ${signalement.traite_par_nom ? `
                                    <div class="flex items-center">
                                        <i class="fas fa-user-check w-5 text-purple-500 mr-3"></i>
                                        <span class="font-medium text-gray-700">Traité par:</span>
                                        <span class="ml-2">${signalement.traite_par_nom} ${signalement.traite_par_prenom || ''}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-file-text mr-2 text-blue-600"></i>Description détaillée
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">${escapeHtml(signalement.description)}</p>
                    </div>
                </div>
                
                ${signalement.commentaire_traitement ? `
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-comment mr-2 text-green-600"></i>Commentaire de traitement
                        </h3>
                        <div class="bg-green-50 border-l-4 border-green-400 rounded-lg p-6">
                            <p class="text-gray-700 whitespace-pre-wrap">${escapeHtml(signalement.commentaire_traitement)}</p>
                        </div>
                    </div>
                ` : ''}
                
                ${signalement.preuves || signalement.images ? `
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-paperclip mr-2 text-purple-600"></i>Fichiers attachés
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            ${signalement.preuves ? signalement.preuves.split(',').map(preuve => `
                                <div class="group relative bg-white rounded-lg border-2 border-gray-200 hover:border-blue-400 transition-all duration-300 overflow-hidden">
                                    <img src="../uploads/${preuve.trim()}" alt="Preuve" class="w-full h-32 object-cover cursor-pointer group-hover:scale-105 transition-transform duration-300" onclick="window.open(this.src, '_blank')">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                        <i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                                    </div>
                                </div>
                            `).join('') : ''}
                            ${signalement.images ? signalement.images.split(',').map(image => `
                                <div class="group relative bg-white rounded-lg border-2 border-gray-200 hover:border-blue-400 transition-all duration-300 overflow-hidden">
                                    <img src="../uploads/${image.trim()}" alt="Image" class="w-full h-32 object-cover cursor-pointer group-hover:scale-105 transition-transform duration-300" onclick="window.open(this.src, '_blank')">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                        <i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                                    </div>
                                </div>
                            `).join('') : ''}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    function closeModal() {
        document.getElementById('signalementModal').classList.add('hidden');
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'en_attente': return 'bg-amber-100 text-amber-800 border-amber-200';
            case 'en_cours': return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'resolu': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
            case 'rejete': return 'bg-red-100 text-red-800 border-red-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }
    
    function getPriorityClass(priority) {
        switch (priority.toLowerCase()) {
            case 'faible': return 'bg-green-100 text-green-800 border-green-200';
            case 'normale': return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'elevee': return 'bg-orange-100 text-orange-800 border-orange-200';
            case 'critique': return 'bg-red-100 text-red-800 border-red-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }
    
    function getStatusIcon(status) {
        switch (status.toLowerCase()) {
            case 'en_attente': return 'fas fa-clock';
            case 'en_cours': return 'fas fa-spinner';
            case 'resolu': return 'fas fa-check-circle';
            case 'rejete': return 'fas fa-times-circle';
            default: return 'fas fa-question-circle';
        }
    }
    
    function getPriorityIcon(priority) {
        switch (priority.toLowerCase()) {
            case 'faible': return 'fas fa-arrow-down';
            case 'normale': return 'fas fa-minus';
            case 'elevee': return 'fas fa-arrow-up';
            case 'critique': return 'fas fa-exclamation-triangle';
            default: return 'fas fa-question';
        }
    }
    
    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('signalementModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Animation d'apparition des cartes
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.animate-fade-in');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    </script>

    <?php include '../Inc/Components/footer.php'; ?>
</body>
</html>