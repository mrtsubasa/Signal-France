<?php
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';
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

        // Construction de la requête SQL - CORRECTION: Supprimer le filtre statut = 'resolu'
        $sql = "SELECT s.*, 
                u.username, u.email,
                CASE 
                    WHEN s.anonyme = 1 THEN 'Anonyme'
                    WHEN u.username IS NOT NULL THEN u.username
                    ELSE 'Utilisateur supprimé'
                END as auteur_complet
                FROM signalements s 
                LEFT JOIN users u ON s.user_id = u.id 
                WHERE 1=1";
        $params = [];

        // Filtres de recherche par texte
        if (!empty($searchQuery)) {
            switch ($searchType) {
                case 'nom_prenom':
                    $sql .= " AND (s.nom LIKE ? OR s.prenom LIKE ? OR CONCAT(s.nom, ' ', s.prenom) LIKE ? OR CONCAT(s.prenom, ' ', s.nom) LIKE ?)";
                    $params[] = "%$searchQuery%";
                    $params[] = "%$searchQuery%";
                    $params[] = "%$searchQuery%";
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
                    $sql .= " AND (s.nom LIKE ? OR s.prenom LIKE ? OR CONCAT(s.nom, ' ', s.prenom) LIKE ? OR s.description LIKE ? OR s.localisation LIKE ? OR s.lieu LIKE ? OR s.type_incident LIKE ? OR s.plateforme LIKE ? OR s.auteur LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
                    $searchTerm = "%$searchQuery%";
                    $params = array_fill(0, 11, $searchTerm);
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
        $allowedSortFields = ['date_signalement', 'nom', 'prenom', 'statut', 'priorite', 'type_incident', 'created_at', 'updated_at'];
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
function getStatusClass($status)
{
    switch (strtolower($status)) {
        case 'en_attente':
            return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'en_cours':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'resolu':
            return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'rejete':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

// Fonction pour obtenir la classe CSS de la priorité
function getPriorityClass($priority)
{
    switch (strtolower($priority)) {
        case 'faible':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'normale':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'elevee':
            return 'bg-orange-100 text-orange-800 border-orange-200';
        case 'critique':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

// Fonction pour obtenir l'icône du statut
function getStatusIcon($status)
{
    switch (strtolower($status)) {
        case 'en_attente':
            return 'fas fa-clock';
        case 'en_cours':
            return 'fas fa-spinner';
        case 'resolu':
            return 'fas fa-check-circle';
        case 'rejete':
            return 'fas fa-times-circle';
        default:
            return 'fas fa-question-circle';
    }
}

// Fonction pour obtenir l'icône de la priorité
function getPriorityIcon($priority)
{
    switch (strtolower($priority)) {
        case 'faible':
            return 'fas fa-arrow-down';
        case 'normale':
            return 'fas fa-minus';
        case 'elevee':
            return 'fas fa-arrow-up';
        case 'critique':
            return 'fas fa-exclamation-triangle';
        default:
            return 'fas fa-question';
    }
}
?>



<main>
    <!-- En-tête avec animation -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 min-h-[60vh]">
        <!-- Particules animées en arrière-plan -->
        <div class="absolute inset-0">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-blue-400 rounded-full animate-pulse opacity-60"></div>
            <div class="absolute top-1/3 right-1/3 w-1 h-1 bg-white rounded-full animate-ping opacity-40"></div>
            <div class="absolute bottom-1/4 left-1/3 w-3 h-3 bg-indigo-400 rounded-full animate-bounce opacity-50">
            </div>
            <div class="absolute top-1/2 right-1/4 w-1.5 h-1.5 bg-cyan-400 rounded-full animate-pulse opacity-70"></div>
        </div>

        <!-- Mesh gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 via-transparent to-purple-600/20"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 flex items-center min-h-[60vh]">
            <div class="w-full">
                <!-- Badge de statut -->
                <div class="flex justify-center mb-8">
                    <div
                        class="inline-flex items-center px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-white text-sm font-medium">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                        Base de données active
                    </div>
                </div>

                <div class="text-center">
                    <h1 class="text-6xl md:text-7xl font-black text-white mb-6 tracking-tight">
                        <span class="bg-white bg-clip-text text-transparent">
                            Recherche
                        </span>
                        <br>
                        <span class="text-white">Intelligente</span>
                    </h1>
                    <p class="text-xl md:text-2xl text-white/80 max-w-4xl mx-auto leading-relaxed mb-12">
                        Explorez notre base de données avec des outils de recherche
                        <span class="text-blue-300 font-semibold">puissants</span> et
                        <span class="text-purple-300 font-semibold">intuitifs</span>
                    </p>

                    <!-- Stats cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                        <div
                            class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/15 transition-all duration-300">
                            <div class="text-3xl font-bold text-blue-300 mb-2">
                                <?php echo $totalResults > 0 ? $totalResults : '∞'; ?>
                            </div>
                            <div class="text-white/80 text-sm font-medium">Signalements disponibles</div>
                        </div>
                        <div
                            class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/15 transition-all duration-300">
                            <div class="text-3xl font-bold text-purple-300 mb-2">
                                < 1s</div>
                                    <div class="text-white/80 text-sm font-medium">Temps de recherche</div>
                            </div>
                            <div
                                class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/15 transition-all duration-300">
                                <div class="text-3xl font-bold text-cyan-300 mb-2">24/7</div>
                                <div class="text-white/80 text-sm font-medium">Disponibilité</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vague en bas -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                        fill="rgb(249 250 251)" />
                </svg>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-30 relative z-20">
            <!-- Formulaire de recherche moderne -->
            <form method="POST"
                class="bg-white rounded-3xl shadow-2xl p-8 mb-12 border border-gray-100 backdrop-blur-sm">
                <!-- Barre de recherche principale -->
                <div class="mb-8">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Que recherchez-vous ?</h2>
                        <p class="text-gray-600">Utilisez notre moteur de recherche intelligent</p>
                    </div>

                    <div class="relative w-full max-w-4xl mx-auto px-4 sm:px-0">
                        <!-- Version Desktop/Tablet -->
                        <div class="hidden sm:block relative">
                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search_query" id="search_query"
                                value="<?php echo htmlspecialchars($searchQuery); ?>"
                                placeholder="Rechercher par nom, description, localisation..."
                                class="w-full pl-14 pr-32 py-6 text-lg rounded-2xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 bg-gray-50 focus:bg-white shadow-sm hover:shadow-md">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                <button type="submit" name="search"
                                    class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        Rechercher
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Version Mobile -->
                        <div class="block sm:hidden space-y-4">
                            <!-- Champ de recherche mobile -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="search_query" id="search_query_mobile"
                                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                                    placeholder="Que recherchez-vous ?"
                                    class="w-full pl-12 pr-4 py-4 text-base rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-3 focus:ring-blue-500/20 transition-all duration-300 bg-gray-50 focus:bg-white shadow-sm">
                            </div>

                            <!-- Bouton de recherche mobile -->
                            <button type="submit" name="search"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg active:scale-95">
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </span>
                            </button>

                            <!-- Suggestions rapides mobile -->
                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="text-xs text-gray-500 font-medium">Suggestions :</span>
                                <button type="button"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200"
                                    onclick="document.getElementById('search_query_mobile').value = 'accident'; this.form.submit();">
                                    Accident
                                </button>
                                <button type="button"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 hover:bg-green-200 transition-colors duration-200"
                                    onclick="document.getElementById('search_query_mobile').value = 'vol'; this.form.submit();">
                                    Vol
                                </button>
                                <button type="button"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors duration-200"
                                    onclick="document.getElementById('search_query_mobile').value = 'urgence'; this.form.submit();">
                                    Urgence
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


        </div>
    </div>
    </form>

    <!-- Section des résultats de recherche -->
    <?php if ($searchPerformed): ?>
        <div class="container mx-auto px-4 py-8">
            <?php if (!empty($error)): ?>
                <!-- Message d'erreur -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        <span class="text-red-700"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
            <?php elseif (!empty($searchResults)): ?>
                <!-- En-tête des résultats -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                Résultats de recherche
                            </h2>
                            <p class="text-gray-600">
                                <?php echo $totalResults; ?> signalement<?php echo $totalResults > 1 ? 's' : ''; ?>
                                trouvé<?php echo $totalResults > 1 ? 's' : ''; ?>
                                <?php if (!empty($searchQuery)): ?>
                                    pour "<span
                                        class="font-semibold text-blue-600"><?php echo htmlspecialchars($searchQuery); ?></span>"
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Options de tri -->
                        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
                            <form method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="search" value="1">
                                <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <input type="hidden" name="search_type" value="<?php echo htmlspecialchars($searchType); ?>">
                                <input type="hidden" name="status_filter"
                                    value="<?php echo htmlspecialchars($statusFilter); ?>">
                                <input type="hidden" name="priority_filter"
                                    value="<?php echo htmlspecialchars($priorityFilter); ?>">
                                <input type="hidden" name="type_filter" value="<?php echo htmlspecialchars($typeFilter); ?>">

                                <select name="sort_by"
                                    class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="date_signalement" <?php echo $sortBy === 'date_signalement' ? 'selected' : ''; ?>>Date</option>
                                    <option value="nom" <?php echo $sortBy === 'nom' ? 'selected' : ''; ?>>Nom</option>
                                    <option value="statut" <?php echo $sortBy === 'statut' ? 'selected' : ''; ?>>Statut</option>
                                    <option value="priorite" <?php echo $sortBy === 'priorite' ? 'selected' : ''; ?>>Priorité
                                    </option>
                                </select>
                                <select name="sort_order"
                                    class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Décroissant
                                    </option>
                                    <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                                </select>
                                <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <i class="fas fa-sort mr-1"></i>Trier
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Grille des résultats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($searchResults as $index => $signalement): ?>
                        <div
                            class="group relative bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">

                            <?php if ($user): ?>
                                <!-- AFFICHAGE COMPLET POUR UTILISATEURS CONNECTÉS -->
                                <!-- Image du signalement -->
                                <div class="relative h-48 overflow-hidden rounded-t-xl">
                                    <img src="<?php echo !empty($signalement['photo']) ? htmlspecialchars($signalement['photo']) : '../Assets/Images/IMG_5652.jpg'; ?>"
                                        alt="Photo du signalement"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                                </div>

                                <!-- Contenu de la carte -->
                                <div class="p-6">
                                    <!-- Titre et ID -->
                                    <div class="mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                                            <?php echo htmlspecialchars($signalement['nom'] . ' ' . $signalement['prenom']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            Signalement #<?php echo $signalement['id']; ?>
                                        </p>
                                    </div>

                                    <!-- Informations principales -->
                                    <div class="space-y-2 mb-4">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                            <span><?php echo date('d/m/Y à H:i', strtotime($signalement['date_signalement'])); ?></span>
                                        </div>

                                        <?php if (!empty($signalement['localisation'])): ?>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                                <span class="truncate"><?php echo htmlspecialchars($signalement['localisation']); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($signalement['type_incident'])): ?>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-exclamation-triangle mr-2 text-gray-400"></i>
                                                <span><?php echo htmlspecialchars($signalement['type_incident']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-700 line-clamp-3">
                                            <?php echo htmlspecialchars(substr($signalement['description'], 0, 150)) . (strlen($signalement['description']) > 150 ? '...' : ''); ?>
                                        </p>
                                    </div>

                                    <!-- Bouton d'action -->
                                    <div class="flex justify-between items-center">
                                        <button onclick="showSignalementDetails(<?php echo $signalement['id']; ?>)"
                                            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                                            <i class="fas fa-eye mr-2"></i>Voir détails
                                        </button>

                                        <div class="text-xs text-gray-500">
                                            <?php if (!empty($signalement['updated_at']) && $signalement['updated_at'] !== $signalement['created_at']): ?>
                                                Modifié le <?php echo date('d/m/Y', strtotime($signalement['updated_at'])); ?>
                                            <?php else: ?>
                                                Créé le <?php echo date('d/m/Y', strtotime($signalement['created_at'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- AFFICHAGE SIMPLIFIÉ POUR UTILISATEURS NON CONNECTÉS -->
                                <div class="p-8 text-center">
                                    <!-- Icône de confirmation -->
                                    <div
                                        class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                                        <i class="fas fa-check text-white text-2xl"></i>
                                    </div>

                                    <!-- Message de confirmation -->
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Résultat trouvé</h3>
                                    <p class="text-lg font-bold text-green-600 mb-3">OUI</p>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Un signalement correspond à votre recherche.
                                    </p>

                                    <!-- Informations limitées -->
                                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <p><strong>ID:</strong> #<?php echo $signalement['id']; ?></p>
                                            <p><strong>Date:</strong>
                                                <?php echo date('d/m/Y', strtotime($signalement['date_signalement'])); ?></p>
                                            <?php if (!empty($signalement['type_incident'])): ?>
                                                <p><strong>Type:</strong> <?php echo htmlspecialchars($signalement['type_incident']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Bouton de connexion -->
                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-600">Pour voir les détails complets :</p>
                                        <a href="login.php"
                                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                                            <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- Message d'erreur -->
                <div class="text-center py-12">
                    <div
                        class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-red-400 to-pink-500 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun résultat trouvé</h3>
                    <p class="text-lg font-bold text-red-600 mb-3">NON</p>
                    <p class="text-sm text-gray-600 mb-4">
                        Aucun signalement ne correspond à votre recherche.
                    </p>

                    <!-- Suggestions -->
                    <div class="bg-gray-50 rounded-lg p-6 max-w-md mx-auto">
                        <h4 class="font-semibold text-gray-900 mb-3">Suggestions :</h4>
                        <ul class="text-sm text-gray-600 space-y-1 text-left">
                            <li>• Vérifiez l'orthographe des mots-clés</li>
                            <li>• Essayez des termes plus généraux</li>
                            <li>• Utilisez moins de filtres</li>
                            <li>• Essayez une recherche dans "Tout"</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>



            <?php if (!$isLoggedIn): ?>
                <!-- Bouton pour afficher/masquer le message -->
                <div class="text-center mt-8 mb-4">
                    <button id="toggleInfoBtn"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        Afficher les informations de connexion
                    </button>
                </div>

                <!-- Message d'information (initialement caché) -->
                <div id="infoMessage" class="relative mx-auto mt-8 mb-8 max-w-4xl overflow-hidden hidden">
                    <div class="relative mx-4 mb-8 overflow-hidden">
                        <!-- Fond avec gradient et effet glassmorphism -->
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-amber-50 via-orange-50 to-yellow-50 rounded-2xl opacity-80">
                        </div>
                        <div class="absolute inset-0 bg-white/30 backdrop-blur-sm rounded-2xl border border-amber-200/50"></div>

                        <!-- Contenu du message -->
                        <div class="relative p-6 rounded-2xl">
                            <div class="flex items-start space-x-4">
                                <!-- Icône avec animation -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-300">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>




                                <!-- Contenu textuel -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-800">Information importante</h3>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                            Accès limité
                                        </span>
                                    </div>

                                    <p class="text-gray-700 leading-relaxed mb-4">
                                        Vous pouvez effectuer des recherches et consulter les résultats, mais une connexion est
                                        requise pour accéder aux détails complets des signalements.
                                    </p>

                                    <!-- Bouton de connexion stylisé -->
                                    <div class="flex items-center space-x-3">
                                        <a href="login.php"
                                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-medium rounded-lg hover:from-amber-600 hover:to-orange-600 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1">
                                                </path>
                                            </svg>
                                            Se connecter
                                        </a>

                                        <span class="text-sm text-gray-500">ou</span>

                                        <a href="#"
                                            class="text-sm text-amber-600 hover:text-amber-700 font-medium hover:underline transition-colors duration-200">
                                            Créer un compte
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Décoration subtile -->
                            <div
                                class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-200/20 to-orange-200/20 rounded-full -translate-y-10 translate-x-10">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 w-16 h-16 bg-gradient-to-tr from-yellow-200/20 to-amber-200/20 rounded-full translate-y-8 -translate-x-8">
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.getElementById('toggleInfoBtn').addEventListener('click', function () {
                        const message = document.getElementById('infoMessage');
                        const btn = this;

                        if (message.classList.contains('hidden')) {
                            message.classList.remove('hidden');
                            message.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            btn.textContent = 'Masquer les informations';
                        } else {
                            message.classList.add('hidden');
                            btn.textContent = 'Afficher les informations de connexion';
                        }
                    });
                </script>
            <?php endif; ?>
        </div>

    <?php endif; ?>
    </div>
</main>

<!-- Modal pour les détails -->
<div id="signalementModal" class="fixed inset-0 modal-backdrop hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div id="modalContent">
                <!-- Le contenu sera chargé ici via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour afficher les détails d'un signalement
    function showSignalementDetails(id) {
        const modal = document.getElementById('signalementModal');
        const modalContent = document.getElementById('modalContent');

        // Afficher le modal avec un loader
        modalContent.innerHTML = `
        <div class="p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Chargement des détails...</p>
        </div>
    `;
        modal.classList.remove('hidden');

        // Charger les détails via AJAX
        fetch('search_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_details&id=' + id
        })
            .then(response => response.text())
            .then(data => {
                modalContent.innerHTML = data;
            })
            .catch(error => {
                modalContent.innerHTML = `
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Erreur</h3>
                <p class="text-gray-600">Impossible de charger les détails du signalement.</p>
                <button onclick="closeModal()" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    Fermer
                </button>
            </div>
        `;
            });
    }

    // Fonction pour fermer le modal
    function closeModal() {
        document.getElementById('signalementModal').classList.add('hidden');
    }

    // Fermer le modal en cliquant sur l'arrière-plan
    document.getElementById('signalementModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Animation des cartes au chargement
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.animate-fade-in');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>

<style>
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .line-clamp-2 {
        display: -webkit-box;
        --webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        --webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .animate-fade-in {
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
</style>

<?php include '../Inc/Components/footer.php'; ?>
<?php include '../Inc/Components/footers.php'; ?>
<?php include('../Inc/Traitement/create_log.php'); ?>