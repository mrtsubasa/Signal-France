<?php
session_start();
require_once '../Inc/Constants/db.php';

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
 WHERE s.statut = 'resolu'"; // AJOUTER CETTE CONDITION
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
        case 'en_attente': return 'bg-yellow-100 text-yellow-800';
        case 'en_cours': return 'bg-blue-100 text-blue-800';
        case 'resolu': return 'bg-green-100 text-green-800';
        case 'rejete': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

// Fonction pour obtenir la classe CSS de la priorité
function getPriorityClass($priority) {
    switch (strtolower($priority)) {
        case 'faible': return 'bg-green-100 text-green-800';
        case 'normale': return 'bg-blue-100 text-blue-800';
        case 'elevee': return 'bg-orange-100 text-orange-800';
        case 'critique': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche - Signale France</title>
    <link rel="stylesheet" href="../Assets/Css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include '../Inc/Components/header.php'; ?>
    <?php include '../Inc/Components/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">
                <i class="fas fa-search mr-3"></i>Recherche de Signalements
            </h1>

            <!-- Formulaire de recherche -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form method="POST" class="space-y-6">
                    <!-- Barre de recherche principale -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" 
                                   name="search_query" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   placeholder="Rechercher des signalements..."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <button type="submit" name="search" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Rechercher
                        </button>
                    </div>

                    <!-- Filtres avancés -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                            <select name="status_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Tous les statuts</option>
                                <?php foreach ($availableStatuses as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                            <select name="priority_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Toutes les priorités</option>
                                <?php foreach ($availablePriorities as $priority): ?>
                                    <option value="<?php echo htmlspecialchars($priority); ?>" <?php echo $priorityFilter === $priority ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($priority); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type d'incident</label>
                            <select name="type_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Tous les types</option>
                                <?php foreach ($availableTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $typeFilter === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                            <div class="flex gap-2">
                                <select name="sort_by" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                    <option value="date_signalement" <?php echo $sortBy === 'date_signalement' ? 'selected' : ''; ?>>Date signalement</option>
                                    <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Date création</option>
                                    <option value="updated_at" <?php echo $sortBy === 'updated_at' ? 'selected' : ''; ?>>Dernière modification</option>
                                    <option value="titre" <?php echo $sortBy === 'titre' ? 'selected' : ''; ?>>Titre</option>
                                    <option value="statut" <?php echo $sortBy === 'statut' ? 'selected' : ''; ?>>Statut</option>
                                    <option value="priorite" <?php echo $sortBy === 'priorite' ? 'selected' : ''; ?>>Priorité</option>
                                </select>
                                <select name="sort_order" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                    <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>↓</option>
                                    <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>↑</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Messages d'erreur -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Résultats de recherche -->
            <?php if ($searchPerformed): ?>
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            Résultats de recherche
                            <?php if (!empty($searchQuery)): ?>
                                pour "<?php echo htmlspecialchars($searchQuery); ?>"
                            <?php endif; ?>
                            (<?php echo $totalResults; ?> résultat<?php echo $totalResults > 1 ? 's' : ''; ?>)
                        </h2>
                    </div>

                    <?php if (empty($searchResults)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-xl font-medium text-gray-600 mb-2">Aucun signalement trouvé</h3>
                            <p class="text-gray-500">Essayez de modifier vos critères de recherche ou vos filtres.</p>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($searchResults as $signalement): ?>
                                <div class="p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between mb-2">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                    <?php 
                                                    $titre = htmlspecialchars($signalement['titre']);
                                                    if ($searchType === 'titre' && !empty($searchQuery)) {
                                                        $titre = str_ireplace($searchQuery, '<mark class="bg-yellow-200">' . htmlspecialchars($searchQuery) . '</mark>', $titre);
                                                    }
                                                    echo $titre;
                                                    ?>
                                                </h3>
                                                <span class="text-sm text-gray-500 ml-4">
                                                    #<?php echo $signalement['id']; ?>
                                                </span>
                                            </div>
                                            
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <?php if (!empty($signalement['localisation'])): ?>
                                                    <p><i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($signalement['localisation']); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($signalement['lieu'])): ?>
                                                    <p><i class="fas fa-location-dot mr-1"></i><?php echo htmlspecialchars($signalement['lieu']); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($signalement['plateforme'])): ?>
                                                    <p><i class="fas fa-globe mr-1"></i><?php echo htmlspecialchars($signalement['plateforme']); ?></p>
                                                <?php endif; ?>
                                                <p><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($signalement['auteur_complet']); ?></p>
                                                <p><i class="fas fa-calendar mr-1"></i><?php echo date('d/m/Y H:i', strtotime($signalement['date_signalement'])); ?></p>
                                                <?php if (!empty($signalement['email_contact'])): ?>
                                                    <p><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($signalement['email_contact']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-gray-700 mt-2 line-clamp-2">
                                                <?php echo htmlspecialchars(substr($signalement['description'], 0, 200)) . (strlen($signalement['description']) > 200 ? '...' : ''); ?>
                                            </p>
                                            
                                            <?php if (!empty($signalement['preuves'])): ?>
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                                                        <i class="fas fa-paperclip mr-1"></i>Preuves attachées
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="flex gap-2 mt-4 lg:mt-0 lg:ml-6">
                                            <button onclick="showSignalementDetails(<?php echo $signalement['id']; ?>)" 
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                                <i class="fas fa-eye mr-1"></i>Voir détails
                                            </button>
                                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'moderator')): ?>
                                                <a href="admin.php?edit=<?php echo $signalement['id']; ?>" 
                                                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                                    <i class="fas fa-edit mr-1"></i>Modifier
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour les détails -->
    <div id="signalementModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-semibold">Détails du signalement</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="modalContent" class="p-6">
                    <!-- Contenu chargé dynamiquement -->
                </div>
            </div>
        </div>
    </div>

    <script>
    function showSignalementDetails(id) {
        const modal = document.getElementById('signalementModal');
        const content = document.getElementById('modalContent');
        
        content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="mt-2">Chargement...</p></div>';
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
                content.innerHTML = `<div class="text-center py-4 text-red-600"><i class="fas fa-exclamation-triangle text-2xl"></i><p class="mt-2">${data.error || 'Erreur lors du chargement'}</p></div>`;
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="text-center py-4 text-red-600"><i class="fas fa-exclamation-triangle text-2xl"></i><p class="mt-2">Erreur de connexion</p></div>';
        });
    }
    
    function displaySignalementDetails(signalement) {
        const content = document.getElementById('modalContent');
        const statusClass = getStatusClass(signalement.statut);
        const priorityClass = getPriorityClass(signalement.priorite);
        
        content.innerHTML = `    
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Informations générales</h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Auteur:</strong> ${signalement.anonyme == 1 ? 'Anonyme' : (signalement.auteur ? `${signalement.auteur}` : 'Utilisateur supprimé?')}</p>
                            ${signalement.email_contact ? `<p><strong>Email contact:</strong> ${escapeHtml(signalement.email_contact)}</p>` : ''}
                            ${signalement.localisation ? `<p><strong>Localisation:</strong> ${escapeHtml(signalement.localisation)}</p>` : ''}
                            ${signalement.lieu ? `<p><strong>Lieu:</strong> ${escapeHtml(signalement.lieu)}</p>` : ''}
                            ${signalement.plateforme ? `<p><strong>Plateforme:</strong> ${escapeHtml(signalement.plateforme)}</p>` : ''}
                            ${signalement.incident_context ? `<p><strong>Contexte:</strong> ${escapeHtml(signalement.incident_context)}</p>` : ''}
                            ${signalement.latitude && signalement.longitude ? `<p><strong>Coordonnées:</strong> ${signalement.latitude}, ${signalement.longitude}</p>` : ''}
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Traitement</h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Date de création:</strong> ${new Date(signalement.created_at).toLocaleString('fr-FR')}</p>
                            <p><strong>Dernière modification:</strong> ${new Date(signalement.updated_at).toLocaleString('fr-FR')}</p>
                            ${signalement.date_traitement ? `<p><strong>Date de traitement:</strong> ${new Date(signalement.date_traitement).toLocaleString('fr-FR')}</p>` : ''}
                            ${signalement.traite_par_nom ? `<p><strong>Traité par:</strong> ${signalement.traite_par_nom} ${signalement.traite_par_prenom}</p>` : ''}
                            ${signalement.commentaire_traitement ? `<p><strong>Commentaire:</strong> ${escapeHtml(signalement.commentaire_traitement)}</p>` : ''}
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Description</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">${escapeHtml(signalement.description)}</p>
                </div>
                
                ${signalement.preuves ? `
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Preuves attachées</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        ${signalement.preuves.split(',').map(preuve => `
                            <div class="border rounded-lg p-2">
                                <img src="../uploads/${preuve.trim()}" alt="Preuve" class="w-full h-32 object-cover rounded cursor-pointer" onclick="window.open(this.src, '_blank')">
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
                
                ${signalement.images ? `
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Images attachées</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        ${signalement.images.split(',').map(image => `
                            <div class="border rounded-lg p-2">
                                <img src="../uploads/${image.trim()}" alt="Image" class="w-full h-32 object-cover rounded cursor-pointer" onclick="window.open(this.src, '_blank')">
                            </div>
                        `).join('')}
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
            case 'en_attente': return 'bg-yellow-100 text-yellow-800';
            case 'en_cours': return 'bg-blue-100 text-blue-800';
            case 'resolu': return 'bg-green-100 text-green-800';
            case 'rejete': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function getPriorityClass(priority) {
        switch (priority.toLowerCase()) {
            case 'faible': return 'bg-green-100 text-green-800';
            case 'normale': return 'bg-blue-100 text-blue-800';
            case 'elevee': return 'bg-orange-100 text-orange-800';
            case 'critique': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('signalementModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>

    <?php include '../Inc/Components/footer.php'; ?>
</body>
</html>