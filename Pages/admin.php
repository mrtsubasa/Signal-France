<?php
session_start();
require_once '../Inc/Constants/db.php';
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!$user || ($role !== 'admin' && $role !== 'moderator')) {
    header('Location: login.php');
    exit;
}

// Récupérer les statistiques
try {
    $conn = connect_db();
    
    // Statistiques utilisateurs
    $totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $adminUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch()['count'];
    $activeUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_activity > datetime('now', '-30 days')")->fetch()['count'];
    
    // Statistiques signalements
    $totalSignalements = 0;
    $pendingSignalements = 0;
    $resolvedSignalements = 0;
    try {
        $totalSignalements = $conn->query("SELECT COUNT(*) as count FROM signalements")->fetch()['count'];
        $pendingSignalements = $conn->query("SELECT COUNT(*) as count FROM signalements WHERE statut IN ('nouveau', 'en_cours')")->fetch()['count'];
        $resolvedSignalements = $conn->query("SELECT COUNT(*) as count FROM signalements WHERE statut = 'resolu'")->fetch()['count'];
    } catch (Exception $e) {
        // Table signalements n'existe pas encore
    }
    
    // Statistiques contacts
    $totalContacts = 0;
    $newContacts = 0;
    try {
        $totalContacts = $conn->query("SELECT COUNT(*) as count FROM messages_contact")->fetch()['count'];
        $newContacts = $conn->query("SELECT COUNT(*) as count FROM messages_contact WHERE statut = 'nouveau'")->fetch()['count'];
    } catch (Exception $e) {
        // Table messages_contact n'existe pas encore
    }
    
    // Récupérer les utilisateurs récents
    $recentUsers = $conn->query("SELECT username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
    // Statistiques par rôle
    $roleStats = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll();
    
} catch (Exception $e) {
    $error = "Erreur lors du chargement des données : " . $e->getMessage();
}
?>

<main class="flex-grow container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- En-tête du panel -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-tachometer-alt mr-3 text-france-blue"></i>
                Panel d'Administration
            </h1>
            <p class="text-gray-600">Bienvenue <?php echo htmlspecialchars($username); ?>, gérez votre plateforme Signale France</p>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-2xl text-blue-500"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Utilisateurs</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-shield text-2xl text-green-500"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Administrateurs</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $adminUsers; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-clock text-2xl text-yellow-500"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Actifs (30j)</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $activeUsers; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-flag text-2xl text-red-500"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Signalements</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo  $pendingSignalements; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-envelope text-2xl text-purple-500"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Messages Contact</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $newContacts; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation du panel -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Menu latéral -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cogs mr-2"></i>Gestion
                    </h3>
                    <nav class="space-y-2">
                        <a href="#users" onclick="showSection('users')" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-france-blue transition-colors">
                            <i class="fas fa-users mr-3"></i>Utilisateurs
                            <span class="ml-auto bg-france-blue text-white text-xs font-bold px-2 py-1 rounded"><?php echo  $totalUsers; ?></span>
                        </a>
                        <a href="#signalements" onclick="showSection('signalements')" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-france-blue transition-colors">
                            <i class="fas fa-flag mr-3"></i>Signalements 
                            <span class="ml-auto bg-france-red text-white text-xs font-bold px-2 py-1 rounded-full"><?php echo  $pendingSignalements; ?></span>
                        </a>
                        <a href="#contacts" onclick="showSection('contacts')" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-france-blue transition-colors">
                            <i class="fas fa-envelope mr-3"></i>Messages Contact
                            <span class="ml-auto bg-france-red text-white text-xs font-bold px-2 py-1 rounded-full"><?php echo  $newContacts; ?></span>
                        </a>
                        <a href="#roles" onclick="showSection('roles')" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-france-blue transition-colors">
                            <i class="fas fa-user-tag mr-3"></i>Rôles & Permissions
                        </a>
                        <a href="#settings" onclick="showSection('settings')" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-france-blue transition-colors">
                            <i class="fas fa-cog mr-3"></i>Paramètres
                        </a>
                        <a href="#logs" onclick="showSection('logs')" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-france-blue transition-colors">
                            <i class="fas fa-file-alt mr-3"></i>Logs
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="lg:col-span-3">
                <!-- Section Utilisateurs -->
                <div id="users-section" class="admin-section">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-users mr-2 text-france-blue"></i>Gestion des Utilisateurs
                            </h3>
                            <button onclick="openCreateUserModal()" class="bg-france-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Nouvel Utilisateur
                            </button>
                        </div>

                        <!-- Tableau des utilisateurs -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="users-table-body">
                                    <!-- Les utilisateurs seront chargés ici via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section Signalements -->
                <div id="signalements-section" class="admin-section hidden">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-flag mr-2 text-france-blue"></i>Gestion des Signalements
                            </h3>
                            <div class="flex space-x-2">
                                <select id="signalement-filter" onchange="loadSignalements()" class="px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Tous les statuts</option>
                                    <option value="nouveau">Nouveau</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="resolu">Résolu</option>
                                    <option value="rejete">Rejeté</option>
                                </select>
                                <button onclick="loadSignalements()" class="bg-france-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-sync mr-2"></i>Actualiser
                                </button>
                            </div>
                        </div>

                        <!-- Statistiques rapides -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-yellow-600 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-yellow-600">En attente</p>
                                        <p class="text-xl font-bold text-yellow-800"><?php echo $pendingSignalements; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-green-600">Résolus</p>
                                        <p class="text-xl font-bold text-green-800"><?php echo $resolvedSignalements; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-list text-blue-600 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-blue-600">Total</p>
                                        <p class="text-xl font-bold text-blue-800"><?php echo $totalSignalements; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des signalements -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorité</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auteur</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="signalements-table-body">
                                    <!-- Les signalements seront chargés ici via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section Messages Contact -->
                <div id="contacts-section" class="admin-section hidden">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-envelope mr-2 text-france-blue"></i>Messages de Contact
                            </h3>
                            <div class="flex space-x-2">
                                <select id="contact-filter" onchange="loadContacts()" class="px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Tous les statuts</option>
                                    <option value="nouveau">Nouveau</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="resolu">Résolu</option>
                                </select>
                                <select id="contact-type-filter" onchange="loadContacts()" class="px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Tous les types</option>
                                    <option value="support_technique">Support technique</option>
                                    <option value="question_generale">Question générale</option>
                                    <option value="suggestion">Suggestion</option>
                                    <option value="signalement_probleme">Signaler un problème</option>
                                    <option value="partenariat">Partenariat</option>
                                    <option value="autre">Autre</option>
                                </select>
                                <button onclick="loadContacts()" class="bg-france-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-sync mr-2"></i>Actualiser
                                </button>
                            </div>
                        </div>

                        <!-- Statistiques rapides -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-envelope-open text-purple-600 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-purple-600">Nouveaux messages</p>
                                        <p class="text-xl font-bold text-purple-800"><?php echo $newContacts; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-list text-blue-600 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-blue-600">Total messages</p>
                                        <p class="text-xl font-bold text-blue-800"><?php echo $totalContacts; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des messages contact -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sujet</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="contacts-table-body">
                                    <!-- Les messages contact seront chargés ici via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section Rôles -->
                <div id="roles-section" class="admin-section hidden">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">
                            <i class="fas fa-user-tag mr-2 text-france-blue"></i>Rôles et Permissions
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($roleStats as $roleStat): ?>
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900"><?php echo ucfirst($roleStat['role']); ?></h4>
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        <?php echo $roleStat['count']; ?> utilisateurs
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">Gérer les permissions de ce rôle</p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Section Paramètres -->
                          <!-- Section Paramètres -->
                          <div id="settings-section" class="admin-section hidden">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">
                            <i class="fas fa-cog mr-2 text-france-blue"></i>Paramètres Système
                        </h3>
                        
                        <div class="space-y-6">
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Initialisation des données</h4>
                                <p class="text-sm text-gray-600 mb-3">Créer les utilisateurs par défaut du système</p>
                                <button onclick="createDefaultUsers()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                    <i class="fas fa-user-plus mr-2"></i>Créer Utilisateurs par Défaut
                                </button>
                            </div>
                            
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Tables de base de données</h4>
                                <p class="text-sm text-gray-600 mb-3">Créer les tables nécessaires pour les signalements et contacts</p>
                                <div class="space-x-2">
                                    <button onclick="createSignalTable()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-table mr-2"></i>Créer Table Signalements
                                    </button>
                                    <button onclick="createContactTable()" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                                        <i class="fas fa-table mr-2"></i>Créer Table Contacts
                                    </button>
                                </div>
                            </div>
                            
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Base de données</h4>
                                <p class="text-sm text-gray-600 mb-3">Optimiser et maintenir la base de données</p>
                                <button class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition-colors">
                                    <i class="fas fa-database mr-2"></i>Optimiser BDD
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Logs -->
                <div id="logs-section" class="admin-section hidden">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-file-alt mr-2 text-france-blue"></i>Logs du Système
                            </h3>
                            <div class="flex space-x-2">
                                <button onclick="refreshLogs()" class="bg-france-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                </button>
                                <button onclick="clearLogs()" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-2"></i>Vider les logs
                                </button>
                                <button onclick="downloadLogs()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                    <i class="fas fa-download mr-2"></i>Télécharger
                                </button>
                            </div>
                        </div>

                        <!-- Statistiques des logs -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-list-ol text-blue-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-blue-600">Total des entrées</p>
                                        <p class="text-2xl font-bold text-blue-900" id="total-logs">-</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-green-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-green-600">Dernière activité</p>
                                        <p class="text-sm font-bold text-green-900" id="last-activity">-</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-hdd text-yellow-600 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-600">Taille du fichier</p>
                                        <p class="text-sm font-bold text-yellow-900" id="file-size">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtres -->
                        <div class="mb-4 flex flex-wrap gap-2">
                            <select id="log-filter" onchange="filterLogs()" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">Toutes les pages</option>
                                <option value="admin.php">Admin</option>
                                <option value="signal.php">Signalements</option>
                                <option value="contact.php">Contact</option>
                                <option value="search.php">Recherche</option>
                                <option value="profile.php">Profil</option>
                            </select>
                            <input type="date" id="log-date" onchange="filterLogs()" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <input type="text" id="log-search" placeholder="Rechercher dans les logs..." onkeyup="filterLogs()" class="border border-gray-300 rounded-md px-3 py-2 text-sm flex-1 min-w-64">
                        </div>

                        <!-- Contenu des logs -->
                        <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-auto" style="max-height: 600px;">
                            <div id="logs-content">
                                <div class="text-center text-gray-500 py-8">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                    <p>Chargement des logs...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Affichage de <span id="logs-start">0</span> à <span id="logs-end">0</span> sur <span id="logs-total">0</span> entrées
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="previousLogsPage()" id="prev-logs" class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50 disabled:opacity-50" disabled>
                                    Précédent
                                </button>
                                <button onclick="nextLogsPage()" id="next-logs" class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50 disabled:opacity-50" disabled>
                                    Suivant
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="editSignalementModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Éditer le signalement</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Fermer</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="editSignalementForm" class="space-y-4">
                <input type="hidden" id="editSignalementId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                        <input type="text" id="editTitre" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type d'incident *</label>
                        <select id="editTypeIncident" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Sélectionner un type</option>
                            <option value="Harcèlement">Harcèlement</option>
                            <option value="Discrimination">Discrimination</option>
                            <option value="Violence">Violence</option>
                            <option value="Contenu inapproprié">Contenu inapproprié</option>
                            <option value="Spam">Spam</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea id="editDescription" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                        <input type="text" id="editLocalisation" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                        <input type="text" id="editLieu" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contexte</label>
                        <select id="editIncidentContext" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                            <option value="irl">Dans la vraie vie</option>
                            <option value="online">En ligne</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                        <select id="editPriorite" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                            <option value="faible">Faible</option>
                            <option value="normale">Normale</option>
                            <option value="haute">Haute</option>
                            <option value="critique">Critique</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select id="editStatut" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolu">Résolu</option>
                            <option value="rejete">Rejeté</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plateforme (si en ligne)</label>
                    <input type="text" id="editPlateforme" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
             
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire de traitement</label>
                    <textarea id="editCommentaireTraitement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="Notes internes pour l'équipe..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Annuler
                    </button>
                    <button type="button" onclick="saveSignalementChanges()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<!-- JavaScript pour le panel admin -->
<script>
// Gestion des sections
function showSection(sectionName) {
    // Cacher toutes les sections
    document.querySelectorAll('.admin-section').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Afficher la section demandée
    document.getElementById(sectionName + '-section').classList.remove('hidden');
    
    // Mettre à jour la navigation
    document.querySelectorAll('.admin-nav-link').forEach(link => {
        link.classList.remove('bg-france-blue', 'text-white');
        link.classList.add('text-gray-700');
    });
    
    event.target.classList.add('bg-france-blue', 'text-white');
    event.target.classList.remove('text-gray-700');
    
    // Charger les données selon la section
    switch(sectionName) {
        case 'users':
            loadUsers();
            break;
        case 'signalements':
            loadSignalements();
            break;
        case 'contacts':
            loadContacts();
            break;
        case 'logs':
            loadLogs();
            break;
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('editSignalementModal');
        if (!modal.classList.contains('hidden')) {
            closeEditModal();
        }
    }
});
// Créer les utilisateurs par défaut
function createDefaultUsers() {
    if (confirm('Êtes-vous sûr de vouloir créer les utilisateurs par défaut ?')) {
        fetch('../Inc/Traitement/create_default_users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Utilisateurs créés avec succès!', 'success');
                loadUsers(); // Recharger la liste des utilisateurs
            } else {
                showNotification('Erreur: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur de connexion', 'error');
        });
    }
}

// Créer la table des signalements
function createSignalTable() {
    if (confirm('Êtes-vous sûr de vouloir créer la table des signalements ?')) {
        fetch('../Inc/Traitement/create_signal_table.php', {
            method: 'POST'
        })
        .then(response => response.text())
        .then(data => {
            showNotification('Table signalements créée avec succès!', 'success');
            location.reload(); // Recharger pour mettre à jour les statistiques
        })
        .catch(error => {
            showNotification('Erreur lors de la création de la table', 'error');
        });
    }
}

// Créer la table des contacts
function createContactTable() {
    if (confirm('Êtes-vous sûr de vouloir créer la table des contacts ?')) {
        fetch('../Inc/Traitement/create_contact_table.php', {
            method: 'POST'
        })
        .then(response => response.text())
        .then(data => {
            showNotification('Table contacts créée avec succès!', 'success');
            location.reload(); // Recharger pour mettre à jour les statistiques
        })
       .catch(error => {
            showNotification('Erreur lors de la création de la table', 'error');
        });
    }
}


function editSignalement(signalementId) {
    // Récupérer les détails du signalement
    fetch('admin_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_signalement_details&id=${signalementId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            populateEditForm(data.signalement);
            document.getElementById('editSignalementModal').classList.remove('hidden');
        } else {
            alert('Erreur lors du chargement des détails: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion lors du chargement des détails');
    });
}

// Fonction pour remplir le formulaire d'édition
function populateEditForm(signalement) {
    document.getElementById('editSignalementId').value = signalement.id;
    document.getElementById('editTitre').value = signalement.titre || '';
    document.getElementById('editDescription').value = signalement.description || '';
    document.getElementById('editLocalisation').value = signalement.localisation || '';
    document.getElementById('editTypeIncident').value = signalement.type_incident || '';
    document.getElementById('editIncidentContext').value = signalement.incident_context || 'irl';
    document.getElementById('editPlateforme').value = signalement.plateforme || '';
    document.getElementById('editLieu').value = signalement.lieu || '';
    document.getElementById('editPriorite').value = signalement.priorite || 'normale';
    document.getElementById('editStatut').value = signalement.statut || 'en_attente';
    document.getElementById('editCommentaireTraitement').value = signalement.commentaire_traitement || '';
}

// Fonction pour sauvegarder les modifications
function saveSignalementChanges() {
    const formData = new FormData();
    formData.append('action', 'update_signalement');
    formData.append('id', document.getElementById('editSignalementId').value);
    formData.append('titre', document.getElementById('editTitre').value);
    formData.append('description', document.getElementById('editDescription').value);
    formData.append('localisation', document.getElementById('editLocalisation').value);
    formData.append('type_incident', document.getElementById('editTypeIncident').value);
    formData.append('incident_context', document.getElementById('editIncidentContext').value);
    formData.append('plateforme', document.getElementById('editPlateforme').value);
    formData.append('lieu', document.getElementById('editLieu').value);
    formData.append('priorite', document.getElementById('editPriorite').value);
    formData.append('statut', document.getElementById('editStatut').value);
    formData.append('commentaire_traitement', document.getElementById('editCommentaireTraitement').value);
    
    fetch('admin_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Signalement mis à jour avec succès!');
            document.getElementById('editSignalementModal').classList.add('hidden');
            location.reload(); // Recharger pour voir les changements
        } else {
            alert('Erreur lors de la mise à jour: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion lors de la mise à jour');
    });
}

// Fonction pour fermer le modal
function closeEditModal() {
    const modal = document.getElementById('editSignalementModal');
    if (modal) {
        modal.classList.add('hidden');
        // Réinitialiser le formulaire
        const form = document.getElementById('editSignalementForm');
        if (form) {
            form.reset();
        }
    }
}



// Variables pour la pagination des logs
let currentLogsPage = 1;
let logsPerPage = 50;
let allLogs = [];
let filteredLogs = [];

// Charger les logs
function loadLogs() {
    fetch('admin_ajax.php?action=get_logs')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                allLogs = data.logs;
                filteredLogs = [...allLogs];
                updateLogsStats(data.stats);
                displayLogs();
            } else {
                document.getElementById('logs-content').innerHTML = 
                    '<div class="text-center text-red-400 py-8"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Erreur lors du chargement des logs</p></div>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('logs-content').innerHTML = 
                '<div class="text-center text-red-400 py-8"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Erreur de connexion</p></div>';
        });
}

// Actualiser les logs
function refreshLogs() {
    loadLogs();
}

// Mettre à jour les statistiques
function updateLogsStats(stats) {
    document.getElementById('total-logs').textContent = stats.total;
    document.getElementById('last-activity').textContent = stats.lastActivity;
    document.getElementById('file-size').textContent = stats.fileSize;
}

// Afficher les logs avec pagination
function displayLogs() {
    const startIndex = (currentLogsPage - 1) * logsPerPage;
    const endIndex = startIndex + logsPerPage;
    const logsToShow = filteredLogs.slice(startIndex, endIndex);
    
    let html = '';
    if (logsToShow.length === 0) {
        html = '<div class="text-center text-gray-500 py-8"><p>Aucun log trouvé</p></div>';
    } else {
        logsToShow.forEach((log, index) => {
            const logClass = getLogClass(log);
            html += `<div class="${logClass} py-1 border-l-2 border-transparent hover:border-blue-400 hover:bg-gray-800 px-2 rounded">${log}</div>`;
        });
    }
    
    document.getElementById('logs-content').innerHTML = html;
    
    // Mettre à jour les informations de pagination
    document.getElementById('logs-start').textContent = filteredLogs.length > 0 ? startIndex + 1 : 0;
    document.getElementById('logs-end').textContent = Math.min(endIndex, filteredLogs.length);
    document.getElementById('logs-total').textContent = filteredLogs.length;
    
    // Mettre à jour les boutons de pagination
    document.getElementById('prev-logs').disabled = currentLogsPage <= 1;
    document.getElementById('next-logs').disabled = endIndex >= filteredLogs.length;
}

// Obtenir la classe CSS pour un log
function getLogClass(log) {
    if (log.includes('admin.php')) return 'text-yellow-400';
    if (log.includes('signal.php')) return 'text-blue-400';
    if (log.includes('contact.php')) return 'text-purple-400';
    if (log.includes('search.php')) return 'text-cyan-400';
    if (log.includes('profile.php')) return 'text-pink-400';
    return 'text-green-400';
}

// Filtrer les logs
function filterLogs() {
    const pageFilter = document.getElementById('log-filter').value;
    const dateFilter = document.getElementById('log-date').value;
    const searchFilter = document.getElementById('log-search').value.toLowerCase();
    
    filteredLogs = allLogs.filter(log => {
        let matches = true;
        
        if (pageFilter && !log.includes(pageFilter)) {
            matches = false;
        }
        
        if (dateFilter) {
            const logDate = log.substring(0, 10); // YYYY-MM-DD
            if (logDate !== dateFilter) {
                matches = false;
            }
        }
        
        if (searchFilter && !log.toLowerCase().includes(searchFilter)) {
            matches = false;
        }
        
        return matches;
    });
    
    currentLogsPage = 1;
    displayLogs();
}

// Navigation pagination
function previousLogsPage() {
    if (currentLogsPage > 1) {
        currentLogsPage--;
        displayLogs();
    }
}

function nextLogsPage() {
    if ((currentLogsPage * logsPerPage) < filteredLogs.length) {
        currentLogsPage++;
        displayLogs();
    }
}

// Vider les logs
function clearLogs() {
    if (confirm('Êtes-vous sûr de vouloir vider tous les logs ? Cette action est irréversible.')) {
        fetch('admin_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_logs'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Logs vidés avec succès', 'success');
                loadLogs();
            } else {
                showNotification('Erreur lors de la suppression des logs', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur de connexion', 'error');
        });
    }
}

// Télécharger les logs
function downloadLogs() {
    window.open('admin_ajax.php?action=download_logs', '_blank');
}

function loadSignalements() {
    const filter = document.getElementById('signalement-filter').value;
    
    fetch(`admin_ajax.php?action=get_signalements&filter=${filter}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showNotification('Erreur: ' + data.error, 'error');
                return;
            }
            
            const tbody = document.getElementById('signalements-table-body');
            tbody.innerHTML = '';
            
            if (data.signalements && data.signalements.length > 0) {
                data.signalements.forEach(signalement => {
                    const statusClass = getStatusClass(signalement.statut);
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${signalement.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${signalement.titre}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${signalement.type_incident || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                                    ${signalement.statut}
                                </span>
                            </td>
                               <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${signalement.priorite}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${signalement.auteur}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${signalement.date_creation}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewSignalement(${signalement.id})" class="text-france-blue hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editSignalement(${signalement.id})" class="text-blue-600 hover:text-blue-900 mr-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
    </svg>
</button>

                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun signalement trouvé</td></tr>';
            }
        })
        .catch(error => {
            showNotification('Erreur de connexion', 'error');
        });
}

// Charger les messages de contact
function loadContacts() {
    const filter = document.getElementById('contact-filter').value;
    
    fetch(`admin_ajax.php?action=get_contacts&filter=${filter}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showNotification('Erreur: ' + data.error, 'error');
                return;
            }
            
            const tbody = document.getElementById('contacts-table-body');
            tbody.innerHTML = '';
            
            if (data.contacts && data.contacts.length > 0) {
                data.contacts.forEach(contact => {
                    const statusClass = getStatusClass(contact.statut);
                    const anonymeIcon = contact.anonyme ? '<i class="fas fa-user-secret text-purple-500" title="Message anonyme"></i>' : '';
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${contact.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${contact.nom} ${anonymeIcon}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${contact.email || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${contact.type_demande}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${contact.sujet}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                                    ${contact.statut}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${contact.message}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${contact.date_creation}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="markAsReplied(${contact.id})" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Aucun message trouvé</td></tr>';
            }
        })
        .catch(error => {
            showNotification('Erreur de connexion', 'error');
        });
}

// Marquer un message comme répondu
function markAsReplied(contactId) {
    if (confirm('Êtes-vous sûr de vouloir marquer ce message comme répondu?')) {
        fetch(`admin_ajax.php?action=mark_contact_as_replied&contact_id=${contactId}`)
           .then(response => response.json())
           .then(data => {
                if (data.status ==='success') {
                    showNotification('Message marqué comme répondu', 'success');
                    loadContacts();
                }
           })
          .catch(error => {
                showNotification('Erreur lors de la mise à jour du statut', 'error');
          })
    }
}
// Fonction utilitaire pour les classes de statut
function getStatusClass(statut) {
    switch(statut) {
        case 'en_attente':
        case 'nouveau':
            return 'bg-yellow-100 text-yellow-800';
        case 'en_cours':
            return 'bg-blue-100 text-blue-800';
        case 'resolu':
            return 'bg-green-100 text-green-800';
        case 'rejete':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadSignalements();
    loadContacts();
});

// Charger les utilisateurs 
// Créer les utilisateurs par défaut
function createDefaultUsers() {
    if (confirm('Êtes-vous sûr de vouloir créer les utilisateurs par défaut ?')) {
        fetch('../Inc/Traitement/create_default_users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Utilisateurs créés avec succès!', 'success');
                loadUsers(); // Recharger la liste des utilisateurs
            } else {
                showNotification('Erreur: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur de connexion', 'error');
        });
    }
}

// Charger les utilisateurs
function loadUsers() {
    fetch('admin_ajax.php?action=get_users')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                showNotification('Erreur: ' + data.error, 'error');
                return;
            }
            
            const tbody = document.getElementById('users-table-body');
            tbody.innerHTML = '';
            
            if (data.users && data.users.length > 0) {
                data.users.forEach(user => {
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-france-blue flex items-center justify-center text-white font-medium">
                                            ${user.username.charAt(0).toUpperCase()}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${user.username}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.email}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    ${user.role}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.created_at}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editUser(${user.id})" class="text-france-blue hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Aucun utilisateur trouvé</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur de chargement des utilisateurs', 'error');
        });
}

// Charger les utilisateurs au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    showSection('users'); // Afficher la section utilisateurs par défaut
});

function deleteUser(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        fetch('admin_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_user&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Utilisateur supprimé avec succès!', 'success');
                loadUsers(); // Recharger la liste
            } else {
                showNotification('Erreur: ' + data.error, 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur de connexion', 'error');
        });
    }
}

// Éditer un utilisateur
function editUser(userId) {
    fetch(`admin_ajax.php?action=get_user&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.user) {
                openEditUserModal(data.user);
            } else {
                showNotification('Erreur: ' + data.error, 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur de connexion', 'error');
        });
}

// Ouvrir le modal d'édition
function openEditUserModal(user) {
    // Créer le modal dynamiquement
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.id = 'editUserModal';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Éditer l'utilisateur</h3>
                <form id="editUserForm">
                    <input type="hidden" id="editUserId" value="${user.id}">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                        <input type="text" id="editUsername" value="${user.username}" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="editEmail" value="${user.email}" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Rôle</label>
                        <select id="editRole" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="user" ${user.role === 'user' ? 'selected' : ''}>Utilisateur</option>
                            <option value="moderator" ${user.role === 'moderator' ? 'selected' : ''}>Modérateur</option>
                            <option value="developer" ${user.role === 'developer'?'selected' : ''}>Developpeur</option>
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Administrateur</option>
                            <option value="opj" ${user.role === 'opj' ? 'selected' : ''}>OPJ</option>
                            <option value="avocat" ${user.role === 'avocat' ? 'selected' : ''}>Avocat</option>
                            <option value="journaliste" ${user.role === 'journaliste' ? 'selected' : ''}>Journaliste</option>
                            <option value="magistrat" ${user.role === 'magistrat' ? 'selected' : ''}>Magistrat</option>
                            <option value="psychologue" ${user.role === 'psychologue' ? 'selected' : ''}>Psychologue</option>
                            <option value="association" ${user.role === 'association' ? 'selected' : ''}>Association</option>
                            <option value="rgpd" ${user.role === 'rgpd' ? 'selected' : ''}>RGPD</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-france-blue text-white rounded-md hover:bg-blue-700">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Gérer la soumission du formulaire
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateUserRole();
    });
    
    // Fermer le modal en cliquant à l'extérieur
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeEditModal();
        }
    });
}

// Mettre à jour le rôle d'un utilisateur
function updateUserRole() {
    const userId = document.getElementById('editUserId').value;
    const newRole = document.getElementById('editRole').value;
    
    fetch('admin_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_user_role&user_id=${userId}&role=${newRole}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('Rôle mis à jour avec succès!', 'success');
            closeEditModal();
            loadUsers(); // Recharger la liste
        } else {
            showNotification('Erreur: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Erreur de connexion', 'error');
    });
}

// Fermer le modal
// Supprimer la deuxième fonction closeModal() (ligne 795) et garder seulement celle-ci :
function closeEditModal() {
    const modal = document.querySelector('#editUserModal');
    if (modal) {
        modal.remove();
    }
}
function openCreateUserModal() {
    const modal = document.createElement('div');
    modal.id = 'createUserModal'; // ✅ Ajouter cette ligne
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center';
    modal.innerHTML = `
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user-plus text-france-blue mr-2"></i>
                    Créer un nouvel utilisateur
                </h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <form id="createUserForm" class="space-y-4">
                    <!-- Nom d'utilisateur -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-user text-gray-400 mr-1"></i>
                            Nom d'utilisateur *
                        </label>
                        <input type="text" id="newUsername" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all"
                               placeholder="Entrez le nom d'utilisateur">
                        <div id="usernameError" class="text-red-500 text-xs mt-1 hidden"></div>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-envelope text-gray-400 mr-1"></i>
                            Adresse email *
                        </label>
                        <input type="email" id="newEmail" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all"
                               placeholder="exemple@email.com">
                        <div id="emailError" class="text-red-500 text-xs mt-1 hidden"></div>
                    </div>
                    
                    <!-- Mot de passe -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-lock text-gray-400 mr-1"></i>
                            Mot de passe *
                        </label>
                        <div class="relative">
                            <input type="password" id="newPassword" required 
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all"
                                   placeholder="Minimum 8 caractères">
                            <button type="button" onclick="togglePassword('newPassword')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="newPassword-icon"></i>
                            </button>
                        </div>
                        <div class="mt-1">
                            <div class="text-xs text-gray-500">Le mot de passe doit contenir :</div>
                            <ul class="text-xs text-gray-500 ml-4 mt-1">
                                <li id="length-check" class="flex items-center"><i class="fas fa-times text-red-500 mr-1"></i>Au moins 8 caractères</li>
                                <li id="upper-check" class="flex items-center"><i class="fas fa-times text-red-500 mr-1"></i>Une majuscule</li>
                                <li id="lower-check" class="flex items-center"><i class="fas fa-times text-red-500 mr-1"></i>Une minuscule</li>
                                <li id="number-check" class="flex items-center"><i class="fas fa-times text-red-500 mr-1"></i>Un chiffre</li>
                                <li id="special-check" class="flex items-center"><i class="fas fa-times text-red-500 mr-1"></i>Un caractère spécial</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Confirmation mot de passe -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-lock text-gray-400 mr-1"></i>
                            Confirmer le mot de passe *
                        </label>
                        <div class="relative">
                            <input type="password" id="confirmPassword" required 
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all"
                                   placeholder="Confirmez le mot de passe">
                            <button type="button" onclick="togglePassword('confirmPassword')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="confirmPassword-icon"></i>
                            </button>
                        </div>
                        <div id="confirmPasswordError" class="text-red-500 text-xs mt-1 hidden"></div>
                    </div>
                    
                    <!-- Rôle -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-user-tag text-gray-400 mr-1"></i>
                            Rôle *
                        </label>
                        <select id="newRole" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all">
                            <option value="">Sélectionnez un rôle</option>
                            <option value="user">👤 Utilisateur</option>
                            <option value="moderator">🛡️ Modérateur</option>
                            <option value="developer">💻 Développeur</option>
                            <option value="admin">👑 Administrateur</option>
                            <option value="opj">🚔 OPJ</option>
                            <option value="avocat">⚖️ Avocat</option>
                            <option value="journaliste">📰 Journaliste</option>
                            <option value="magistrat">🏛️ Magistrat</option>
                            <option value="psychologue">🧠 Psychologue</option>
                            <option value="association">🤝 Association</option>
                            <option value="rgpd">🔒 RGPD</option>
                        </select>
                    </div>
                    
                    <!-- Options avancées -->
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" id="sendWelcomeEmail" checked 
                                       class="rounded border-gray-300 text-france-blue focus:ring-france-blue">
                                <span class="ml-2 text-sm text-gray-700">Envoyer un email de bienvenue</span>
                            </label>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="requirePasswordChange" 
                                       class="rounded border-gray-300 text-france-blue focus:ring-france-blue">
                                <span class="ml-2 text-sm text-gray-700">Forcer le changement de mot de passe à la première connexion</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-1"></i>Annuler
                </button>
                <button type="submit" form="createUserForm" id="createUserBtn"
                        class="px-4 py-2 bg-france-blue text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-user-plus mr-1"></i>Créer l'utilisateur
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Validation en temps réel du mot de passe
    const passwordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    
    // Validation du nom d'utilisateur
    document.getElementById('newUsername').addEventListener('input', validateUsername);
    
    // Validation de l'email
    document.getElementById('newEmail').addEventListener('input', validateEmail);

    // Gérer la soumission du formulaire
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            createNewUser();
        }
    });

    // Fermer le modal en cliquant à l'extérieur
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Focus sur le premier champ
    setTimeout(() => {
        document.getElementById('newUsername').focus();
    }, 100);
}

// Fonction pour basculer la visibilité du mot de passe
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Validation du mot de passe en temps réel
function validatePassword() {
    const password = document.getElementById('newPassword').value;
    
    const checks = {
        'length-check': password.length >= 8,
        'upper-check': /[A-Z]/.test(password),
        'lower-check': /[a-z]/.test(password),
        'number-check': /[0-9]/.test(password),
        'special-check': /[^\w]/.test(password)
    };
    
    Object.keys(checks).forEach(checkId => {
        const element = document.getElementById(checkId);
        const icon = element.querySelector('i');
        
        if (checks[checkId]) {
            icon.className = 'fas fa-check text-green-500 mr-1';
            element.classList.remove('text-red-500');
            element.classList.add('text-green-500');
        } else {
            icon.className = 'fas fa-times text-red-500 mr-1';
            element.classList.remove('text-green-500');
            element.classList.add('text-red-500');
        }
    });
    
    validatePasswordMatch();
}

// Validation de la correspondance des mots de passe
function validatePasswordMatch() {
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorDiv = document.getElementById('confirmPasswordError');
    
    if (confirmPassword && password !== confirmPassword) {
        errorDiv.textContent = 'Les mots de passe ne correspondent pas';
        errorDiv.classList.remove('hidden');
        return false;
    } else {
        errorDiv.classList.add('hidden');
        return true;
    }
}

// Validation du nom d'utilisateur
function validateUsername() {
    const username = document.getElementById('newUsername').value;
    const errorDiv = document.getElementById('usernameError');
    
    if (username.length < 3) {
        errorDiv.textContent = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
        errorDiv.classList.remove('hidden');
        return false;
    } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        errorDiv.textContent = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores';
        errorDiv.classList.remove('hidden');
        return false;
    } else {
        errorDiv.classList.add('hidden');
        return true;
    }
}

// Validation de l'email
function validateEmail() {
    const email = document.getElementById('newEmail').value;
    const errorDiv = document.getElementById('emailError');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        errorDiv.textContent = 'Format d\'email invalide';
        errorDiv.classList.remove('hidden');
        return false;
    } else {
        errorDiv.classList.add('hidden');
        return true;
    }
}

// Validation complète du formulaire
function validateForm() {
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();
    const isPasswordMatchValid = validatePasswordMatch();
    
    const password = document.getElementById('newPassword').value;
    const isPasswordStrong = password.length >= 8 && 
                           /[A-Z]/.test(password) && 
                           /[a-z]/.test(password) && 
                           /[0-9]/.test(password) && 
                           /[^\w]/.test(password);
    
    return isUsernameValid && isEmailValid && isPasswordStrong && isPasswordMatchValid;
}

// Fonction pour créer un nouvel utilisateur
function createNewUser() {
    const createBtn = document.getElementById('createUserBtn');
    createBtn.disabled = true;
    createBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Création...';
    
    const formData = {
        username: document.getElementById('newUsername').value,
        email: document.getElementById('newEmail').value,
        password: document.getElementById('newPassword').value,
        role: document.getElementById('newRole').value,
        sendWelcomeEmail: document.getElementById('sendWelcomeEmail').checked,
        requirePasswordChange: document.getElementById('requirePasswordChange').checked
    };
    
    fetch('admin_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=create_user&${Object.keys(formData).map(key => 
            `${key}=${encodeURIComponent(formData[key])}`
        ).join('&')}`
    })
    .then(response => {
    if (!response.ok) {
        throw new Error('Erreur réseau: ' + response.status);
    }
    return response.json();
})
.then(data => {
    if (data.status === 'success') {
        showNotification('Utilisateur créé avec succès!', 'success');
        closeModal();
        loadUsers();
    } else {
        showNotification('Erreur: ' + (data.error || 'Erreur inconnue'), 'error');
        console.error('Détails de l\'erreur:', data);
    }
})
.catch(error => {
    console.error('Erreur lors de la création:', error);
    showNotification('Erreur de connexion: ' + error.message, 'error');
})
    .finally(() => {
        createBtn.disabled = false;
        createBtn.innerHTML = '<i class="fas fa-user-plus mr-1"></i>Créer l\'utilisateur';
    });
}

// Fonction pour fermer le modal
function closeModal() {
    const modal = document.getElementById('createUserModal');
    if (modal) {
        modal.remove();
    }
}

// Fonction pour afficher une notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.classList.add('notification', type);
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    })
}

function viewSignalement(signalementId) {
    // Validation de l'ID
    if (!signalementId || signalementId <= 0) {
        showNotification('ID de signalement invalide', 'error');
        return;
    }

    // Fermer le modal existant s'il y en a un
    const existingModal = document.getElementById('signalementModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Afficher un indicateur de chargement
    showLoadingModal();

    // Récupérer les détails du signalement
    fetch('admin_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_signalement_details&id=${encodeURIComponent(signalementId)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Fermer l'indicateur de chargement
        hideLoadingModal();
        
        if (data.status === 'success' && data.signalement) {
            showSignalementDetails(data.signalement);
        } else {
            const errorMsg = data.error || 'Signalement non trouvé';
            showNotification(errorMsg, 'error');
            console.error('Erreur:', errorMsg);
        }
    })
    .catch(error => {
        hideLoadingModal();
        const errorMsg = 'Erreur de connexion au serveur';
        showNotification(errorMsg, 'error');
        console.error('Erreur fetch:', error);
    });
}

// Fonction pour afficher un modal de chargement
function showLoadingModal() {
    const loadingModal = document.createElement('div');
    loadingModal.id = 'loadingModal';
    loadingModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loadingModal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span>Chargement des détails...</span>
            </div>
        </div>
    `;
    document.body.appendChild(loadingModal);
}

// Fonction pour masquer le modal de chargement
function hideLoadingModal() {
    const loadingModal = document.getElementById('loadingModal');
    if (loadingModal) {
        loadingModal.remove();
    }
}

// Version améliorée de showSignalementDetails
function showSignalementDetails(signalement) {
    // Validation des données
    if (!signalement) {
        showNotification('Données du signalement invalides', 'error');
        return;
    }

    const modal = document.createElement('div');
    modal.id = 'signalementModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    
    // Fonction pour échapper le HTML et éviter les injections XSS
    const escapeHtml = (text) => {
        if (!text) return 'Non spécifié';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Détails du signalement #${escapeHtml(signalement.id)}</h2>
                <button onclick="closeSignalementModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Titre</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(signalement.titre)}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type d'incident</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(signalement.type_incident)}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                            signalement.statut === 'resolu' ? 'bg-green-100 text-green-800' :
                            signalement.statut === 'en_cours' ? 'bg-yellow-100 text-yellow-800' :
                            signalement.statut === 'en_attente' ? 'bg-blue-100 text-blue-800' :
                            'bg-red-100 text-red-800'
                        }">${escapeHtml(signalement.statut)}</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priorité</label>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                            signalement.priorite === 'Urgente' ? 'bg-red-100 text-red-800' :
                            signalement.priorite === 'Haute' ? 'bg-orange-100 text-orange-800' :
                            'bg-gray-100 text-gray-800'
                        }">${escapeHtml(signalement.priorite)}</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lieu</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(signalement.lieu)}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de création</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(signalement.date_creation || signalement.date_signalement)}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Auteur</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(signalement.auteur_nom || signalement.auteur || 'Anonyme')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Anonyme</label>
                        <p class="mt-1 text-sm text-gray-900">${signalement.anonyme == 1 ? 'Oui' : 'Non'}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="mt-1 p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">${escapeHtml(signalement.description)}</p>
                    </div>
                </div>
                ${signalement.photo ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Photo</label>
                        <img src="../uploads/${escapeHtml(signalement.photo)}" alt="Photo du signalement" class="mt-2 max-w-full h-auto rounded-lg shadow-md" onerror="this.style.display='none'">
                    </div>
                ` : ''}
            </div>
            <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
                <button onclick="closeSignalementModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    Fermer
                </button>
                <button onclick="updateSignalementStatus(${signalement.id})" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Modifier le statut
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Fermer le modal en cliquant à l'extérieur
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeSignalementModal();
        }
    });
    
    // Fermer le modal avec la touche Échap
    document.addEventListener('keydown', handleEscapeKey);
}

// Fonction pour fermer le modal de signalement
function closeSignalementModal() {
    const modal = document.getElementById('signalementModal');
    if (modal) {
        modal.remove();
        document.removeEventListener('keydown', handleEscapeKey);
    }
}

// Gestionnaire pour la touche Échap
function handleEscapeKey(e) {
    if (e.key === 'Escape') {
        closeSignalementModal();
    }
}

// Fonction pour mettre à jour le statut d'un signalement
function updateSignalementStatus(signalementId) {
    // Validation de l'ID
    if (!signalementId || signalementId <= 0) {
        showNotification('ID de signalement invalide', 'error');
        return;
    }

    // Créer le modal de sélection de statut
    const modal = document.createElement('div');
    modal.id = 'statusModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Modifier le statut</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">Sélectionnez le nouveau statut pour ce signalement :</p>
                <div class="space-y-3">
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="radio" name="status" value="en_attente" class="mr-3 text-blue-600">
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-3">En attente</span>
                            <span class="text-sm text-gray-700">Le signalement est en attente de traitement</span>
                        </div>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="radio" name="status" value="en_cours" class="mr-3 text-yellow-600">
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mr-3">En cours</span>
                            <span class="text-sm text-gray-700">Le signalement est en cours de traitement</span>
                        </div>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="radio" name="status" value="resolu" class="mr-3 text-green-600">
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mr-3">Résolu</span>
                            <span class="text-sm text-gray-700">Le signalement a été résolu</span>
                        </div>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="radio" name="status" value="rejete" class="mr-3 text-red-600">
                        <div class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 mr-3">Rejeté</span>
                            <span class="text-sm text-gray-700">Le signalement a été rejeté</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
                <button onclick="closeStatusModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                    Annuler
                </button>
                <button onclick="confirmStatusUpdate(${signalementId})" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Confirmer
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Fermer le modal en cliquant à l'extérieur
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeStatusModal();
        }
    });
    
    // Fermer le modal avec la touche Échap
    document.addEventListener('keydown', handleStatusEscapeKey);
}

// Fonction pour confirmer la mise à jour du statut
function confirmStatusUpdate(signalementId) {
    const selectedStatus = document.querySelector('input[name="status"]:checked');
    
    if (!selectedStatus) {
        showNotification('Veuillez sélectionner un statut', 'error');
        return;
    }
    
    const newStatus = selectedStatus.value;
    
    // Afficher un indicateur de chargement
    const confirmButton = document.querySelector('#statusModal button[onclick*="confirmStatusUpdate"]');
    const originalText = confirmButton.textContent;
    confirmButton.disabled = true;
    confirmButton.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';
    
    // Envoyer la requête de mise à jour
    fetch('admin_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_signalement_status&id=${encodeURIComponent(signalementId)}&status=${encodeURIComponent(newStatus)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            showNotification('Statut mis à jour avec succès', 'success');
            closeStatusModal();
            
            // Actualiser la liste des signalements
            if (typeof loadSignalements === 'function') {
                loadSignalements();
            } else {
                // Recharger la page si la fonction loadSignalements n'existe pas
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } else {
            const errorMsg = data.error || 'Erreur lors de la mise à jour du statut';
            showNotification(errorMsg, 'error');
            console.error('Erreur:', errorMsg);
        }
    })
    .catch(error => {
        const errorMsg = 'Erreur de connexion au serveur';
        showNotification(errorMsg, 'error');
        console.error('Erreur fetch:', error);
    })
    .finally(() => {
        // Restaurer le bouton
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.textContent = originalText;
        }
    });
}

// Fonction pour fermer le modal de statut
function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    if (modal) {
        modal.remove();
        document.removeEventListener('keydown', handleStatusEscapeKey);
    }
}

// Gestionnaire pour la touche Échap du modal de statut
function handleStatusEscapeKey(e) {
    if (e.key === 'Escape') {
        closeStatusModal();
    }
}



// Fonctions utilitaires pour les badges
function getStatusBadgeClass(status) {
    switch(status) {
        case 'nouveau': return 'bg-primary';
        case 'en_cours': return 'bg-warning';
        case 'resolu': return 'bg-success';
        default: return 'bg-secondary';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'nouveau': return 'Nouveau';
        case 'en_cours': return 'En cours';
        case 'resolu': return 'Résolu';
        default: return status;
    }
}

// Charger les utilisateurs au chargement de la page
document.addEventListener('DOMContentLoaded', loadUsers);
</script>


<?php include_once('../Inc/Components/footers.php'); ?>
<?php include_once('../Inc/Components/footer.php'); ?>
<?php include('../Inc/Traitement/create_log.php'); ?>
