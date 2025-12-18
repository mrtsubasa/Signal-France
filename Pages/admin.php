<?php
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';

// Access control using globals from nav.php
if (!$user || !in_array($role, ['admin', 'moderator'])) {
    header('Location: login.php');
    exit;
}

// Générer un token CSRF pour les actions AJAX
$csrf_token = generate_csrf_token();

// Initialiser toutes les variables avec des valeurs par défaut
$totalUsers = 0;
$adminUsers = 0;
$activeUsers = 0;
$verifiedUsers = 0;
$totalSignalements = 0;
$pendingSignalements = 0;
$resolvedSignalements = 0;
$rejectedSignalements = 0;
$signalementsByPerson = 0;
$totalContacts = 0;
$newContacts = 0;
$recentUsers = [];
$roleStats = [];

// Récupérer les statistiques
try {
    $conn = connect_db();

    // Statistiques utilisateurs
    $totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $adminUsers = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
    $adminUsers->execute(['admin']);
    $adminUsers = $adminUsers->fetchColumn();
    $activeUsers = $conn->prepare("SELECT COUNT(*) FROM users WHERE last_activity > ?");
    $activeUsers->execute([date('Y-m-d H:i:s', strtotime('-30 days'))]);
    $activeUsers = $activeUsers->fetchColumn();

    // Vérifier si la colonne email_verified existe
    try {
        $verifiedUsersCount = $conn->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn();
        $verifiedUsers = $verifiedUsersCount;
    } catch (Exception $e) {
        // Si la colonne n'existe pas, on considère tous les utilisateurs comme vérifiés
        $verifiedUsers = $totalUsers;
    }

    // Statistiques signalements avec nouvelles colonnes
    try {
        $totalSignalements = $conn->query("SELECT COUNT(*) as count FROM signalements")->fetch()['count'];
        $pendingSignalements = $conn->query("SELECT COUNT(*) as count FROM signalements WHERE statut IN ('nouveau', 'en_cours')")->fetch()['count'];
        $resolvedSignalements = $conn->query("SELECT COUNT(*) as count FROM signalements WHERE statut = 'resolu'")->fetch()['count'];
        $rejectedSignalements = $conn->query("SELECT COUNT(*) as count FROM signalements WHERE statut = 'rejete'")->fetch()['count'];
        $signalementsByPerson = $conn->query("SELECT COUNT(*) as count FROM signalements WHERE nom IS NOT NULL AND prenom IS NOT NULL")->fetch()['count'];
    } catch (Exception $e) {
        // Table signalements n'existe pas encore - les valeurs par défaut sont déjà définies
    }

    // Statistiques contacts
    try {
        $totalContacts = $conn->query("SELECT COUNT(*) as count FROM messages_contact")->fetch()['count'];
        $newContacts = $conn->query("SELECT COUNT(*) as count FROM messages_contact WHERE statut = 'nouveau'")->fetch()['count'];
    } catch (Exception $e) {
        // Table messages_contact n'existe pas encore - les valeurs par défaut sont déjà définies
    }

    // Récupérer les utilisateurs récents
    try {
        $recentUsers = $conn->query("SELECT username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
    } catch (Exception $e) {
        $recentUsers = [];
    }

    // Statistiques par rôle
    try {
        $roleStats = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll();
    } catch (Exception $e) {
        $roleStats = [];
    }

} catch (Exception $e) {
    $error = "Erreur lors du chargement des données : " . $e->getMessage();
    // Toutes les variables sont déjà initialisées avec des valeurs par défaut
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - E Conscience</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 3s infinite'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .admin-section {
            display: none;
        }

        .admin-section.active {
            display: block;
            animation: fade-in 0.5s ease-in-out;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>

<body class="bg-gray-50">
    <main class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header moderne avec gradient -->
            <div class="mb-8 relative overflow-hidden">
                <div
                    class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-10 rounded-full -ml-24 -mb-24">
                    </div>
                    <div class="relative z-10">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h1 class="text-4xl lg:text-5xl font-bold mb-2 flex items-center">
                                    <div class="bg-white bg-opacity-20 p-4 rounded-2xl mr-4">
                                        <i class="fas fa-shield-alt text-3xl"></i>
                                    </div>
                                    Panel d'Administration
                                </h1>
                                <p class="text-blue-100 text-lg lg:text-xl">Bienvenue
                                    <?php echo htmlspecialchars($username); ?>, gérez votre plateforme E Conscience
                                </p>
                            </div>
                            <div class="mt-6 lg:mt-0">
                                <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-2xl p-6">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold"><?php echo date('H:i'); ?></div>
                                        <div class="text-sm text-blue-200"><?php echo date('d/m/Y'); ?></div>
                                        <div class="text-xs text-blue-300 mt-1">Dernière connexion</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques modernes et responsives -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
                <!-- Total Utilisateurs -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-100 relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full -mr-10 -mt-10 opacity-10">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-xl shadow-lg">
                                    <i class="fas fa-users text-xl text-white"></i>
                                </div>
                                <span class="text-2xl font-bold text-gray-900"><?php echo $totalUsers; ?></span>
                            </div>
                            <p class="text-sm font-medium text-gray-600">Total Utilisateurs</p>
                            <div class="flex items-center mt-2">
                                <i class="fas fa-arrow-up text-green-500 text-xs mr-1"></i>
                                <span class="text-xs text-green-500 font-medium">+12% ce mois</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Utilisateurs Vérifiés -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-100 relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full -mr-10 -mt-10 opacity-10">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-3 rounded-xl shadow-lg">
                                    <i class="fas fa-user-check text-xl text-white"></i>
                                </div>
                                <span class="text-2xl font-bold text-gray-900"><?php echo $verifiedUsers; ?></span>
                            </div>
                            <p class="text-sm font-medium text-gray-600">Utilisateurs Vérifiés</p>
                            <div class="flex items-center mt-2">
                                <i class="fas fa-shield-check text-emerald-500 text-xs mr-1"></i>
                                <span class="text-xs text-emerald-500 font-medium">Vérifiés</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signalements Actifs -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-100 relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-400 to-red-600 rounded-full -mr-10 -mt-10 opacity-10">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-xl shadow-lg">
                                    <i class="fas fa-flag text-xl text-white"></i>
                                </div>
                                <span
                                    class="text-2xl font-bold text-gray-900"><?php echo $pendingSignalements; ?></span>
                            </div>
                            <p class="text-sm font-medium text-gray-600">En Attente</p>
                            <div class="flex items-center mt-2">
                                <?php if ($pendingSignalements > 0): ?>
                                    <i class="fas fa-exclamation-triangle text-red-500 text-xs mr-1"></i>
                                    <span class="text-xs text-red-500 font-medium">Attention requise</span>
                                <?php else: ?>
                                    <i class="fas fa-check text-green-500 text-xs mr-1"></i>
                                    <span class="text-xs text-green-500 font-medium">Tout traité</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signalements Résolus -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-100 relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-full -mr-10 -mt-10 opacity-10">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl shadow-lg">
                                    <i class="fas fa-check-circle text-xl text-white"></i>
                                </div>
                                <span
                                    class="text-2xl font-bold text-gray-900"><?php echo $resolvedSignalements; ?></span>
                            </div>
                            <p class="text-sm font-medium text-gray-600">Résolus</p>
                            <div class="flex items-center mt-2">
                                <i class="fas fa-thumbs-up text-green-500 text-xs mr-1"></i>
                                <span class="text-xs text-green-500 font-medium">Traités</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signalements par Personne -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-100 relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full -mr-10 -mt-10 opacity-10">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-3 rounded-xl shadow-lg">
                                    <i class="fas fa-user-tag text-xl text-white"></i>
                                </div>
                                <span
                                    class="text-2xl font-bold text-gray-900"><?php echo $signalementsByPerson; ?></span>
                            </div>
                            <p class="text-sm font-medium text-gray-600">Avec Nom/Prénom</p>
                            <div class="flex items-center mt-2">
                                <i class="fas fa-id-card text-purple-500 text-xs mr-1"></i>
                                <span class="text-xs text-purple-500 font-medium">Identifiés</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Contact -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-gray-100 relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full -mr-10 -mt-10 opacity-10">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-gradient-to-br from-amber-500 to-amber-600 p-3 rounded-xl shadow-lg">
                                    <i class="fas fa-envelope text-xl text-white"></i>
                                </div>
                                <span class="text-2xl font-bold text-gray-900"><?php echo $newContacts; ?></span>
                            </div>
                            <p class="text-sm font-medium text-gray-600">Nouveaux Messages</p>
                            <div class="flex items-center mt-2">
                                <?php if ($newContacts > 0): ?>
                                    <i class="fas fa-bell text-amber-500 text-xs mr-1"></i>
                                    <span class="text-xs text-amber-500 font-medium">À traiter</span>
                                <?php else: ?>
                                    <i class="fas fa-check text-green-500 text-xs mr-1"></i>
                                    <span class="text-xs text-green-500 font-medium">À jour</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout principal responsive -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                <!-- Navigation latérale -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden sticky top-8">
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-6 border-b border-gray-100">
                            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-cogs text-blue-600"></i>
                                </div>
                                Gestion
                            </h3>
                        </div>
                        <nav class="p-4 space-y-2">
                            <a href="#" onclick="showSection('users')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-700 transition-all duration-200 border border-transparent hover:border-blue-200">
                                <div class="bg-gray-100 group-hover:bg-blue-100 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-users group-hover:text-blue-600"></i>
                                </div>
                                <span class="flex-1">Utilisateurs</span>
                                <span
                                    class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm"><?php echo $totalUsers; ?></span>
                            </a>

                            <a href="#" onclick="showSection('signalements')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 hover:text-red-700 transition-all duration-200 border border-transparent hover:border-red-200">
                                <div class="bg-gray-100 group-hover:bg-red-100 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-flag group-hover:text-red-600"></i>
                                </div>
                                <span class="flex-1">Signalements</span>
                                <?php if ($pendingSignalements > 0): ?>
                                    <span
                                        class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm animate-pulse"><?php echo $pendingSignalements; ?></span>
                                <?php else: ?>
                                    <span
                                        class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">0</span>
                                <?php endif; ?>
                            </a>

                            <a href="#" onclick="showSection('contacts')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-indigo-50 hover:text-purple-700 transition-all duration-200 border border-transparent hover:border-purple-200">
                                <div
                                    class="bg-gray-100 group-hover:bg-purple-100 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-envelope group-hover:text-purple-600"></i>
                                </div>
                                <span class="flex-1">Messages Contact</span>
                                <?php if ($newContacts > 0): ?>
                                    <span
                                        class="bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm animate-pulse"><?php echo $newContacts; ?></span>
                                <?php else: ?>
                                    <span
                                        class="bg-gray-400 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">0</span>
                                <?php endif; ?>
                            </a>

                            <a href="#" onclick="showSection('analytics')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-700 transition-all duration-200 border border-transparent hover:border-emerald-200">
                                <div
                                    class="bg-gray-100 group-hover:bg-emerald-100 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-chart-bar group-hover:text-emerald-600"></i>
                                </div>
                                <span class="flex-1">Analytiques</span>
                            </a>

                            <a href="#" onclick="showSection('settings')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-amber-50 hover:to-yellow-50 hover:text-amber-700 transition-all duration-200 border border-transparent hover:border-amber-200">
                                <div class="bg-gray-100 group-hover:bg-amber-100 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-cog group-hover:text-amber-600"></i>
                                </div>
                                <span class="flex-1">Paramètres</span>
                            </a>

                            <a href="#" onclick="showSection('logs')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:text-gray-700 transition-all duration-200 border border-transparent hover:border-gray-200">
                                <div class="bg-gray-100 group-hover:bg-gray-200 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-file-alt group-hover:text-gray-600"></i>
                                </div>
                                <span class="flex-1">Logs Système</span>
                            </a>
                            <a href="#" onclick="showSection('database')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-700 transition-all duration-200 border border-transparent hover:border-indigo-200">
                                <div
                                    class="bg-gray-100 group-hover:bg-indigo-100 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-database group-hover:text-indigo-600"></i>
                                </div>
                                <span class="flex-1">Base de Données</span>
                            </a>

                            <a href="#" onclick="showSection('adhesions')"
                                class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-orange-50 hover:to-amber-50 hover:text-orange-700 transition-all duration-200 border border-transparent hover:border-orange-200">
                                <div
                                    class="bg-gray-100 group-hover:bg-orange-100 p-2 rounded-lg mr-3 transition-colors">
                                    <i class="fas fa-user-plus group-hover:text-orange-600"></i>
                                </div>
                                <span class="flex-1">Demandes d'Adhésion</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="xl:col-span-3">
                    <!-- Section Utilisateurs -->
                    <div id="users-section" class="admin-section active">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-100">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                    <div class="mb-4 sm:mb-0">
                                        <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                            <div class="bg-blue-100 p-3 rounded-xl mr-3">
                                                <i class="fas fa-users text-blue-600"></i>
                                            </div>
                                            Gestion des Utilisateurs
                                        </h3>
                                        <p class="text-gray-600 mt-1">Gérez les comptes utilisateurs et leurs
                                            permissions</p>
                                    </div>
                                    <button onclick="openCreateUserModal()"
                                        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                        <i class="fas fa-plus mr-2"></i>Nouvel Utilisateur
                                    </button>
                                </div>
                            </div>




                            <!-- Tableau responsive -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Utilisateur</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                                                Email</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Rôle</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                                                Créé le</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Statut</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Certification</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100" id="users-table-body">
                                        <!-- Les utilisateurs seront chargés ici via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Signalements -->
                    <div id="signalements-section" class="admin-section">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-red-50 to-pink-50 p-6 border-b border-gray-100">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                    <div class="mb-4 sm:mb-0">
                                        <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                            <div class="bg-red-100 p-3 rounded-xl mr-3">
                                                <i class="fas fa-flag text-red-600"></i>
                                            </div>
                                            Gestion des Signalements
                                        </h3>
                                        <p class="text-gray-600 mt-1">Traitez les signalements avec nom et prénom des
                                            personnes signalées</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tableau des signalements -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Personne Signalée</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                                                Type</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                                                Contexte</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Priorité</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Statut</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                                                Date</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100" id="signalements-table-body">
                                        <!-- Les signalements seront chargés ici via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Messages Contact -->
                    <div id="contacts-section" class="admin-section">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-6 border-b border-gray-100">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                    <div class="mb-4 sm:mb-0">
                                        <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                            <div class="bg-purple-100 p-3 rounded-xl mr-3">
                                                <i class="fas fa-envelope text-purple-600"></i>
                                            </div>
                                            Messages de Contact
                                        </h3>
                                        <p class="text-gray-600 mt-1">Gérez les messages reçus via le formulaire de
                                            contact</p>
                                    </div>
                                    <button onclick="markAllAsRead()"
                                        class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-check-double mr-2"></i>Tout marquer lu
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Expéditeur</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                                                Sujet</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                                                Date</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Statut</th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100" id="contacts-table-body">
                                        <!-- Les messages seront chargés ici via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Analytiques -->
                    <div id="analytics-section" class="admin-section">
                        <div class="space-y-6">
                            <!-- Graphiques et statistiques -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                                        Évolution des Signalements
                                    </h4>
                                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-xl">
                                        <p class="text-gray-500">Graphique à implémenter</p>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                                        Répartition par Type
                                    </h4>
                                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-xl">
                                        <p class="text-gray-500">Graphique à implémenter</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Paramètres -->
                    <div id="settings-section" class="admin-section">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 p-6 border-b border-gray-100">
                                <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                    <div class="bg-amber-100 p-3 rounded-xl mr-3">
                                        <i class="fas fa-cog text-amber-600"></i>
                                    </div>
                                    Paramètres du Système
                                </h3>
                                <p class="text-gray-600 mt-1">Configurez les paramètres de votre plateforme</p>
                            </div>

                            <div class="p-6">
                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="bg-gray-50 rounded-xl p-6">
                                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Paramètres Généraux
                                            </h4>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du
                                                        site</label>
                                                    <input type="text" value="E Conscience"
                                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email
                                                        administrateur</label>
                                                    <input type="email" value="admin@signalefrance.fr"
                                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 rounded-xl p-6">
                                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Sécurité</h4>
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-700">Vérification email
                                                        obligatoire</span>
                                                    <button
                                                        class="bg-green-500 relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                        <span
                                                            class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                                    </button>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-700">Modération
                                                        automatique</span>
                                                    <button
                                                        class="bg-gray-200 relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                        <span
                                                            class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button
                                            class="bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white px-8 py-3 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                                            <i class="fas fa-save mr-2"></i>Sauvegarder
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Section Logs -->
                    <div id="logs-section" class="admin-section">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-slate-50 p-6 border-b border-gray-100">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                    <div class="mb-4 sm:mb-0">
                                        <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                            <div class="bg-gray-100 p-3 rounded-xl mr-3">
                                                <i class="fas fa-file-alt text-gray-600"></i>
                                            </div>
                                            Logs Système
                                        </h3>
                                        <p class="text-gray-600 mt-2">Surveillance et analyse des activités du système
                                        </p>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button onclick="refreshLogs()"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                            <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                        </button>
                                        <button onclick="clearLogs()"
                                            class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>Vider les logs
                                        </button>
                                        <button onclick="downloadLogs()"
                                            class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                            <i class="fas fa-download mr-2"></i>Télécharger
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtres -->
                            <div class="p-6 border-b border-gray-100 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                                        <input type="text" id="log-search" placeholder="Rechercher dans les logs..."
                                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de
                                            début</label>
                                        <input type="date" id="log-date-start"
                                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                                        <input type="date" id="log-date-end"
                                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de log</label>
                                        <select id="log-type-filter"
                                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="all">Tous les logs</option>
                                            <option value="access">Logs d'accès</option>
                                            <option value="backup">Logs de sauvegarde</option>
                                            <option value="error">Logs d'erreur</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques des logs -->
                            <div class="p-6 border-b border-gray-100">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                                <i class="fas fa-list text-blue-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-blue-600 font-medium">Total Entrées</p>
                                                <p class="text-2xl font-bold text-blue-700" id="total-logs">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="bg-green-100 p-2 rounded-lg mr-3">
                                                <i class="fas fa-calendar-day text-green-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-green-600 font-medium">Aujourd'hui</p>
                                                <p class="text-2xl font-bold text-green-700" id="today-logs">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-yellow-50 p-4 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                                                <i class="fas fa-clock text-yellow-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-yellow-600 font-medium">Dernière heure</p>
                                                <p class="text-2xl font-bold text-yellow-700" id="hour-logs">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-purple-50 p-4 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                                <i class="fas fa-hdd text-purple-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-purple-600 font-medium">Taille fichier</p>
                                                <p class="text-2xl font-bold text-purple-700" id="log-size">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenu des logs -->
                            <div class="p-6">
                                <div class="bg-gray-900 rounded-xl p-4 max-h-96 overflow-y-auto" id="logs-container">
                                    <div class="text-center py-8">
                                        <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-3"></i>
                                        <p class="text-gray-400">Chargement des logs...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div class="p-6 border-t border-gray-100 bg-gray-50">
                                <div class="flex flex-col sm:flex-row justify-between items-center">
                                    <div class="mb-4 sm:mb-0">
                                        <p class="text-sm text-gray-600">
                                            Affichage de <span id="logs-start">0</span> à <span id="logs-end">0</span>
                                            sur <span id="logs-total">0</span> entrées
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="previousLogsPage()" id="prev-logs-btn"
                                            class="px-4 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                            <i class="fas fa-chevron-left mr-2"></i>Précédent
                                        </button>
                                        <button onclick="nextLogsPage()" id="next-logs-btn"
                                            class="px-4 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                            Suivant<i class="fas fa-chevron-right ml-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Database -->
                    <div id="database-section" class="admin-section">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-6 border-b border-gray-100">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                    <div class="mb-4 sm:mb-0">
                                        <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                            <div class="bg-indigo-100 p-3 rounded-xl mr-3">
                                                <i class="fas fa-database text-indigo-600"></i>
                                            </div>
                                            Gestion Base de Données
                                        </h3>
                                        <p class="text-gray-600 mt-2">Administration et maintenance de la base de
                                            données SQLite</p>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button onclick="refreshDatabase()"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                            <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                        </button>
                                        <button onclick="initializeTables()"
                                            class="bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                            <i class="fas fa-cogs mr-2"></i>Initialiser Tables
                                        </button>
                                        <button onclick="backupDatabase()"
                                            class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                            <i class="fas fa-download mr-2"></i>Sauvegarde
                                        </button>
                                        <button onclick="openSqlModal()"
                                            class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                            <i class="fas fa-code mr-2"></i>Requête SQL
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Informations de la base de données -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-blue-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-table text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-blue-600 font-medium">Tables</p>
                                            <p class="text-2xl font-bold text-blue-700" id="db-tables-count">-</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-hdd text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-green-600 font-medium">Taille DB</p>
                                            <p class="text-2xl font-bold text-green-700" id="db-size">-</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-clock text-yellow-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-yellow-600 font-medium">Dernière MAJ</p>
                                            <p class="text-lg font-bold text-yellow-700" id="db-last-modified">-</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-server text-purple-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-purple-600 font-medium">Version SQLite</p>
                                            <p class="text-lg font-bold text-purple-700" id="sqlite-version">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des tables -->
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-list mr-2 text-indigo-600"></i>
                                Tables de la Base de Données
                            </h4>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <h5 class="font-medium text-gray-900 mb-3">Tables Système</h5>
                                        <div id="system-tables" class="space-y-2">
                                            <div class="text-center py-4">
                                                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                                <p class="text-gray-500 mt-2">Chargement...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <h5 class="font-medium text-gray-900 mb-3">Détails de la Table Sélectionnée</h5>
                                        <div id="table-details" class="space-y-2">
                                            <div class="text-center py-4">
                                                <i class="fas fa-mouse-pointer text-gray-400 text-2xl mb-2"></i>
                                                <p class="text-gray-500">Sélectionnez une table pour voir ses détails
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Résultats de requête -->
                        <div class="p-6 border-t border-gray-100" id="query-results-section" style="display: none;">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-search mr-2 text-green-600"></i>
                                Résultats de la Requête
                            </h4>
                            <div class="bg-gray-50 rounded-xl p-4 max-h-96 overflow-auto">
                                <div id="query-results"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Demandes d'Adhésion -->
                <div id="adhesions-section" class="admin-section">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                        <div class="bg-orange-100 p-3 rounded-xl mr-3">
                                            <i class="fas fa-user-plus text-orange-600"></i>
                                        </div>
                                        Demandes d'Adhésion
                                    </h3>
                                    <p class="text-gray-600 mt-1">Gérez les demandes d'inscription en attente</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="refreshAdhesions()"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all duration-200">
                                        <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-orange-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-orange-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-clock text-orange-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">En attente</p>
                                            <p class="text-xl font-bold text-gray-900" id="pending-requests">0</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-check text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Approuvées</p>
                                            <p class="text-xl font-bold text-gray-900" id="approved-requests">0</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-red-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-red-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-times text-red-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Rejetées</p>
                                            <p class="text-xl font-bold text-gray-900" id="rejected-requests">0</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-xl">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-calendar text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Cette semaine</p>
                                            <p class="text-xl font-bold text-gray-900" id="weekly-requests">0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtres -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <input type="text" id="adhesion-search" placeholder="Rechercher par nom, email..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="flex gap-2">
                                    <select id="status-filter"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="approved">Approuvées</option>
                                        <option value="rejected">Rejetées</option>
                                    </select>
                                    <select id="role-filter-adhesion"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="">Tous les rôles</option>
                                        <option value="user">Utilisateur</option>
                                        <option value="moderator">Modérateur</option>
                                        <option value="admin">Administrateur</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des demandes -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Demandeur</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                                            Organisation</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Rôle Demandé</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Statut</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                                            Date</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100" id="adhesions-table-body">
                                    <!-- Les demandes seront chargées ici via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        </div>
        </div>
    </main>

    <!-- Modales -->
    <!-- Modal Création Utilisateur -->
    <div id="createUserModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                    Créer un Utilisateur
                </h3>
            </div>
            <form id="createUserForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                    <select name="role" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="user">Utilisateur</option>
                        <option value="moderator">Modérateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeCreateUserModal()"
                        class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Requête SQL -->
    <div id="sqlModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-code text-purple-600 mr-3"></i>
                    Exécuter une Requête SQL
                </h3>
                <p class="text-gray-600 mt-1">Attention : Les requêtes de modification sont irréversibles</p>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Requête SQL</label>
                    <textarea id="sql-query" rows="8" placeholder="SELECT * FROM users LIMIT 10;"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"></textarea>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="insertSqlTemplate('SELECT * FROM users LIMIT 10;')"
                            class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm hover:bg-blue-200 transition-colors">
                            SELECT users
                        </button>
                        <button onclick="insertSqlTemplate('SELECT * FROM signalements LIMIT 10;')"
                            class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200 transition-colors">
                            SELECT signalements
                        </button>
                        <button onclick="insertSqlTemplate('SELECT name FROM sqlite_master WHERE type=\'table\';')"
                            class="px-3 py-1 bg-purple-100 text-purple-700 rounded-lg text-sm hover:bg-purple-200 transition-colors">
                            Liste tables
                        </button>
                        <button onclick="insertSqlTemplate('PRAGMA table_info(users);')"
                            class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-sm hover:bg-yellow-200 transition-colors">
                            Structure table
                        </button>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeSqlModal()"
                        class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button onclick="executeSqlQuery()"
                        class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200">
                        <i class="fas fa-play mr-2"></i>Exécuter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'édition des signalements -->
    <div id="editSignalementModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-red-600 to-pink-700 text-white p-6 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-bold">Modifier le Signalement</h3>
                    <button onclick="closeEditSignalementModal()" class="text-white hover:text-gray-200 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <form id="editSignalementForm" class="p-6 space-y-4">
                <input type="hidden" id="edit_signalement_id" name="id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                        <input type="text" id="edit_titre" name="titre"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type d'incident</label>
                        <select id="edit_type_incident" name="type_incident"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            required>
                            <option value="harcelement">Harcèlement</option>
                            <option value="discrimination">Discrimination</option>
                            <option value="violence">Violence</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="edit_description" name="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                        required></textarea>
                </div>

                <!-- Section pour les preuves-->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preuves</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600 mb-2">Glissez-déposez vos fichiers ici ou cliquez pour
                                sélectionner</p>
                            <input type="file" id="edit_preuves" name="preuves[]" multiple
                                accept="image/*,.pdf,.doc,.docx" class="hidden">
                            <button type="button" onclick="document.getElementById('edit_preuves').click()"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                Sélectionner des fichiers
                            </button>
                        </div>
                        <div id="edit_files_preview" class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-2"></div>
                        <div id="edit_existing_files" class="mt-4"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF, PDF, DOC, DOCX (max 5MB par
                        fichier)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photos</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600 mb-2">Glissez-déposez vos fichiers ici ou cliquez pour
                                sélectionner</p>
                            <input type="file" id="edit_preuves" name="preuves[]" multiple
                                accept="image/*,.pdf,.doc,.docx" class="hidden">
                            <button type="button" onclick="document.getElementById('edit_photos').click()"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                Sélectionner des fichiers
                            </button>
                        </div>
                        <div id="edit_files_preview" class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-2"></div>
                        <div id="edit_existing_files" class="mt-4"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF, PDF, DOC, DOCX (max 5MB par
                        fichier)</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select id="edit_status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            required>
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolu">Résolu</option>
                            <option value="rejete">Rejeté</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                        <select id="edit_priorite" name="priorite"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            required>
                            <option value="basse">Basse</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                            <option value="critique">Critique</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="closeEditSignalementModal()"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <span id="edit-save-text">Sauvegarder</span>
                        <span id="edit-save-loading" class="hidden">Sauvegarde...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Variables globales
        const csrf_token = '<?php echo $csrf_token; ?>';
        let currentSection = 'users';

        function initializeTables() {
            if (confirm('Êtes-vous sûr de vouloir initialiser toutes les tables ? Cette action peut écraser les données existantes.')) {
                const loadingToast = showToast('Initialisation des tables en cours...', 'info');

                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=initialize_tables&csrf_token=${csrf_token}`
                })
                    .then(response => response.json())
                    .then(data => {
                        loadingToast.remove();
                        if (data.success) {
                            showToast('Tables initialisées avec succès !', 'success');
                            refreshDatabase(); // Actualiser l'affichage
                        } else {
                            showToast('Erreur lors de l\'initialisation : ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        loadingToast.remove();
                        showToast('Erreur de connexion', 'error');
                        console.error('Erreur:', error);
                    });
            }
        }
        // Fonction pour afficher une section
        function showSection(section) {
            // Masquer toutes les sections
            document.querySelectorAll('.admin-section').forEach(el => {
                el.classList.remove('active');
            });

            // Retirer la classe active de tous les liens
            document.querySelectorAll('.nav-link').forEach(el => {
                el.classList.remove('active');
            });

            // Afficher la section demandée
            const targetSection = document.getElementById(section + '-section');
            if (targetSection) {
                targetSection.classList.add('active');
            }

            // Ajouter la classe active au lien correspondant
            event.target.closest('.nav-link').classList.add('active');

            currentSection = section;

            // Charger les données selon la section
            switch (section) {
                case 'users':
                    loadUsers();
                    break;
                case 'signalements':
                    loadSignalements();
                    break;
                case 'contacts':
                    loadContacts();
                    break;
                case 'analytics':
                    // loadAnalytics();
                    break;
                case 'logs':
                    loadLogs();
                    break;
                case 'database':
                    loadDatabase();
                case 'adhesions':
                    loadAdhesions();
                    break;
                case 'settings':
                    // loadSettings();
                    break;
            }
        }


        let allLogs = [];
        let currentLogsPage = 1;
        const logsPerPage = 50;



        // Variables globales pour la database
        let databaseTables = [];
        let selectedTable = null;


        // Variables globales pour les demandes d'adhésion
        let allAdhesions = [];
        let currentAdhesionPage = 1;
        const adhesionsPerPage = 10;

        // Charger les demandes d'adhésion
        function loadAdhesions() {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_adhesions'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allAdhesions = data.adhesions;
                        updateAdhesionStats(data.stats);
                        displayAdhesions();
                    } else {
                        console.error('Erreur:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        }

        // Afficher les demandes d'adhésion
        function displayAdhesions() {
            const filteredAdhesions = filterAdhesions();
            const startIndex = (currentAdhesionPage - 1) * adhesionsPerPage;
            const endIndex = startIndex + adhesionsPerPage;
            const adhesionsToShow = filteredAdhesions.slice(startIndex, endIndex);

            const tbody = document.getElementById('adhesions-table-body');
            tbody.innerHTML = '';

            adhesionsToShow.forEach(adhesion => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';

                const statusBadge = getStatusBadge(adhesion.status);
                const roleBadge = getRoleBadge(adhesion.role);

                row.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-orange-400 to-amber-500 flex items-center justify-center text-white font-semibold">
                                    ${adhesion.username.charAt(0).toUpperCase()}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${adhesion.username}</div>
                                <div class="text-sm text-gray-500">${adhesion.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <div class="text-sm text-gray-900">${adhesion.organization || 'Non spécifiée'}</div>
                        <div class="text-sm text-gray-500">${adhesion.accreditation || ''}</div>
                    </td>
                    <td class="px-6 py-4">
                        ${roleBadge}
                    </td>
                    <td class="px-6 py-4">
                        ${statusBadge}
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <div class="text-sm text-gray-900">${new Date(adhesion.created_at).toLocaleDateString('fr-FR')}</div>
                        <div class="text-sm text-gray-500">${new Date(adhesion.created_at).toLocaleTimeString('fr-FR')}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button onclick="viewAdhesionDetails(${adhesion.id})" class="text-blue-600 hover:text-blue-900 transition-colors" title="Voir détails">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${adhesion.status === 'pending' ? `
                                <button onclick="approveAdhesion(${adhesion.id})" class="text-green-600 hover:text-green-900 transition-colors" title="Approuver">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="rejectAdhesion(${adhesion.id})" class="text-red-600 hover:text-red-900 transition-colors" title="Rejeter">
                                    <i class="fas fa-times"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                `;

                tbody.appendChild(row);
            });
        }

        // Filtrer les demandes d'adhésion
        function filterAdhesions() {
            const searchTerm = document.getElementById('adhesion-search').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;
            const roleFilter = document.getElementById('role-filter-adhesion').value;

            return allAdhesions.filter(adhesion => {
                const matchesSearch = adhesion.username.toLowerCase().includes(searchTerm) ||
                    adhesion.email.toLowerCase().includes(searchTerm) ||
                    (adhesion.organization && adhesion.organization.toLowerCase().includes(searchTerm));
                const matchesStatus = !statusFilter || adhesion.status === statusFilter;
                const matchesRole = !roleFilter || adhesion.role === roleFilter;

                return matchesSearch && matchesStatus && matchesRole;
            });
        }

        // Mettre à jour les statistiques
        function updateAdhesionStats(stats) {
            document.getElementById('pending-requests').textContent = stats.pending || 0;
            document.getElementById('approved-requests').textContent = stats.approved || 0;
            document.getElementById('rejected-requests').textContent = stats.rejected || 0;
            document.getElementById('weekly-requests').textContent = stats.weekly || 0;
        }

        // Approuver une demande
        function approveAdhesion(id) {
            if (confirm('Êtes-vous sûr de vouloir approuver cette demande ?')) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=approve_adhesion&id=${id}&csrf_token=${csrf_token}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadAdhesions();
                            alert('Demande approuvée avec succès!');
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de l\'approbation');
                    });
            }
        }

        // Rejeter une demande
        function rejectAdhesion(id) {
            const reason = prompt('Raison du rejet (optionnel):');
            if (reason !== null) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=reject_adhesion&id=${id}&reason=${encodeURIComponent(reason)}&csrf_token=${csrf_token}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadAdhesions();
                            alert('Demande rejetée.');
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors du rejet');
                    });
            }
        }

        // Voir les détails d'une demande
        function viewAdhesionDetails(id) {
            const adhesion = allAdhesions.find(a => a.id === id);
            if (adhesion) {
                alert(`Détails de la demande:\n\nNom: ${adhesion.username}\nEmail: ${adhesion.email}\nOrganisation: ${adhesion.organization || 'Non spécifiée'}\nAccréditation: ${adhesion.accreditation || 'Non spécifiée'}\nMotivation: ${adhesion.motivation || 'Non spécifiée'}\nRôle demandé: ${adhesion.role}\nStatut: ${adhesion.status}\nDate: ${new Date(adhesion.created_at).toLocaleString('fr-FR')}`);
            }
        }

        // Actualiser les demandes
        function refreshAdhesions() {
            loadAdhesions();
        }



        // Fonction pour charger les informations de la database
        async function loadDatabase() {
            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_database_info&csrf_token=${csrf_token}`
                });

                const data = await response.json();

                if (data.success) {
                    updateDatabaseStats(data.stats);
                    databaseTables = data.tables;
                    displayTables();
                } else {
                    showNotification('Erreur lors du chargement de la base de données', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            }
        }

        // Fonction pour éditer un signalement
        // Fonction pour éditer un signalement
        function editSignalement(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_signalement_details&id=${id}&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const signalement = data.signalement;

                        // Vérifier que tous les éléments existent avant de les remplir
                        const editId = document.getElementById('edit_signalement_id');
                        const editTitre = document.getElementById('edit_titre');
                        const editType = document.getElementById('edit_type_incident');
                        const editDescription = document.getElementById('edit_description');
                        const editStatus = document.getElementById('edit_status');
                        const editPriorite = document.getElementById('edit_priorite');

                        if (editId) editId.value = signalement.id || '';
                        if (editTitre) editTitre.value = signalement.titre || '';
                        if (editType) editType.value = signalement.type_incident || '';
                        if (editDescription) editDescription.value = signalement.description || '';
                        if (editStatus) editStatus.value = signalement.statut || 'en_attente';
                        if (editPriorite) editPriorite.value = signalement.priorite || 'normale';

                        // Afficher les preuves existantes
                        const existingFilesContainer = document.getElementById('edit_existing_files');
                        if (existingFilesContainer) {
                            let preuves = [];

                            // Vérifier si les preuves existent et les décoder si nécessaire
                            if (signalement.preuves) {
                                if (typeof signalement.preuves === 'string') {
                                    try {
                                        preuves = JSON.parse(signalement.preuves);
                                    } catch (e) {
                                        console.error('Erreur parsing preuves:', e);
                                        preuves = [];
                                    }
                                } else if (Array.isArray(signalement.preuves)) {
                                    preuves = signalement.preuves;
                                }
                            }

                            if (preuves.length > 0) {
                                existingFilesContainer.innerHTML = `
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Preuves existantes :</h5>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            ${preuves.map(preuve => {
                                    const isImage = /\.(jpg|jpeg|png|gif)$/i.test(preuve.file_path);
                                    return `
                                    <div class="relative border rounded-lg p-2 bg-white">
                                        ${isImage ?
                                            `<img src="../uploads/${preuve.file_path}" alt="${preuve.original_name}" class="w-full h-16 object-cover rounded cursor-pointer" onclick="openImageModal('../uploads/${preuve.file_path}')">` :
                                            `<div class="w-full h-16 bg-gray-100 rounded flex items-center justify-center">
                                                <i class="fas fa-file text-lg text-gray-400"></i>
                                            </div>`
                                        }
                                        <p class="text-xs text-gray-600 mt-1 truncate" title="${preuve.original_name}">${preuve.original_name}</p>
                                        <button type="button" onclick="deleteExistingFile('${preuve.file_path}', this)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                            ×
                                        </button>
                                    </div>
                                `;
                                }).join('')}
                        </div>
                    `;
                            } else {
                                existingFilesContainer.innerHTML = '<p class="text-sm text-gray-500">Aucune preuve existante</p>';
                            }
                        }

                        // Réinitialiser la zone de prévisualisation des nouveaux fichiers
                        const newFilePreview = document.getElementById('edit_file_preview');
                        if (newFilePreview) {
                            newFilePreview.innerHTML = '';
                        }

                        // Fermer la modal de détails si elle est ouverte
                        const detailsModal = document.getElementById('signalementDetailsModal');
                        if (detailsModal) {
                            detailsModal.classList.add('hidden');
                        }

                        // Ouvrir la modal d'édition
                        const editModal = document.getElementById('editSignalementModal');
                        if (editModal) {
                            editModal.classList.remove('hidden');
                        }

                    } else {
                        console.error('Erreur lors du chargement des détails:', data.message);
                        alert('Erreur lors du chargement des détails du signalement: ' + (data.message || 'Erreur inconnue'));
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des détails du signalement:', error);
                    alert('Erreur lors du chargement des détails du signalement');
                });
        }




        // Fonction pour supprimer un fichier existant
        function deleteExistingFile(fileName, buttonElement) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette preuve ?')) {
                const signalementId = document.getElementById('edit_signalement_id').value;

                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_signalement_file&signalement_id=${signalementId}&file_name=${fileName}&csrf_token=${csrf_token}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            buttonElement.closest('.relative').remove();
                            showNotification('Fichier supprimé avec succès', 'success');
                        } else {
                            alert('Erreur lors de la suppression du fichier: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la suppression du fichier');
                    });
            }
        }

        // Fonction pour fermer la modal d'édition
        function closeEditSignalementModal() {
            document.getElementById('editSignalementModal').classList.add('hidden');
            document.getElementById('editSignalementForm').reset();
        }

        // Gestionnaire de soumission du formulaire d'édition
        document.addEventListener('DOMContentLoaded', function () {
            const editForm = document.getElementById('editSignalementForm');
            if (editForm) {
                editForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const saveText = document.getElementById('edit-save-text');
                    const saveLoading = document.getElementById('edit-save-loading');

                    // Afficher l'indicateur de chargement
                    saveText.classList.add('hidden');
                    saveLoading.classList.remove('hidden');

                    const formData = new FormData(this);
                    formData.append('action', 'update_signalement');
                    formData.append('csrf_token', csrf_token);

                    fetch('admin_ajax.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                closeEditSignalementModal();
                                loadSignalements();
                                showNotification('Signalement modifié avec succès', 'success');
                            } else {
                                alert(data.message || 'Erreur lors de la modification du signalement');
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            alert('Erreur lors de la modification du signalement');
                        })
                        .finally(() => {
                            // Masquer l'indicateur de chargement
                            saveText.classList.remove('hidden');
                            saveLoading.classList.add('hidden');
                        });
                });
            }
        });

        // ... existing code ...
        // Fonction pour afficher les tables
        function displayTables() {
            const container = document.getElementById('system-tables');

            if (databaseTables.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                        <p class="text-gray-500 mt-2">Aucune table trouvée</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = databaseTables.map(table => `
                <div class="table-item flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200 hover:border-indigo-300 cursor-pointer transition-all duration-200" onclick="selectTable('${table.name}')">
                    <div class="flex items-center">
                        <i class="fas fa-table text-indigo-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-gray-900">${table.name}</p>
                            <p class="text-sm text-gray-500">${table.rows} lignes</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full">${table.type}</span>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </div>
            `).join('');
        }

        // Fonction pour sélectionner une table
        async function selectTable(tableName) {
            selectedTable = tableName;

            // Mettre à jour l'apparence
            document.querySelectorAll('.table-item').forEach(item => {
                item.classList.remove('border-indigo-500', 'bg-indigo-50');
                item.classList.add('border-gray-200');
            });

            event.currentTarget.classList.remove('border-gray-200');
            event.currentTarget.classList.add('border-indigo-500', 'bg-indigo-50');

            // Charger les détails de la table
            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_table_details&table=${tableName}&csrf_token=${csrf_token}`
                });

                const data = await response.json();

                if (data.success) {
                    displayTableDetails(data.details);
                } else {
                    showNotification('Erreur lors du chargement des détails', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            }
        }

        // Fonction pour afficher les détails d'une table
        function displayTableDetails(details) {
            const container = document.getElementById('table-details');

            container.innerHTML = `
                <div class="space-y-4">
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <h6 class="font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-columns text-blue-600 mr-2"></i>
                            Colonnes (${details.columns.length})
                        </h6>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            ${details.columns.map(col => `
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <div>
                                        <span class="font-medium text-gray-900">${col.name}</span>
                                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">${col.type}</span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        ${col.pk ? '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded">PK</span>' : ''}
                                        ${col.notnull ? '<span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded">NOT NULL</span>' : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <h6 class="font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-info-circle text-green-600 mr-2"></i>
                            Informations
                        </h6>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Nombre de lignes:</span>
                                <span class="font-medium ml-2">${details.rowCount}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Taille estimée:</span>
                                <span class="font-medium ml-2">${details.size || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="viewTableData('${selectedTable}')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-eye mr-1"></i>Voir données
                        </button>
                        <button onclick="exportTable('${selectedTable}')" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
            `;
        }

        // Fonction pour mettre à jour les statistiques
        function updateDatabaseStats(stats) {
            document.getElementById('db-tables-count').textContent = stats.tablesCount || 0;
            document.getElementById('db-size').textContent = stats.size || '0 KB';
            document.getElementById('db-last-modified').textContent = stats.lastModified || 'N/A';
            document.getElementById('sqlite-version').textContent = stats.sqliteVersion || 'N/A';
        }

        // Fonction pour actualiser la database
        function refreshDatabase() {
            loadDatabase();
        }

        // Fonction pour sauvegarder la database
        async function backupDatabase() {
            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=backup_database&csrf_token=${csrf_token}`
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `database_backup_${new Date().toISOString().split('T')[0]}.sqlite`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                    showNotification('Sauvegarde téléchargée avec succès', 'success');
                } else {
                    showNotification('Erreur lors de la sauvegarde', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            }
        }

        // Fonctions pour le modal SQL
        function openSqlModal() {
            document.getElementById('sqlModal').classList.remove('hidden');
        }

        function closeSqlModal() {
            document.getElementById('sqlModal').classList.add('hidden');
            document.getElementById('sql-query').value = '';
        }

        function insertSqlTemplate(template) {
            document.getElementById('sql-query').value = template;
        }

        // Fonction pour exécuter une requête SQL
        async function executeSqlQuery() {
            const query = document.getElementById('sql-query').value.trim();

            if (!query) {
                showNotification('Veuillez saisir une requête SQL', 'error');
                return;
            }

            // Vérification de sécurité pour les requêtes dangereuses
            const dangerousKeywords = ['DROP', 'DELETE', 'TRUNCATE', 'ALTER'];
            const upperQuery = query.toUpperCase();

            if (dangerousKeywords.some(keyword => upperQuery.includes(keyword))) {
                if (!confirm('Cette requête peut modifier ou supprimer des données. Êtes-vous sûr de vouloir continuer ?')) {
                    return;
                }
            }

            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=execute_sql&query=${encodeURIComponent(query)}&csrf_token=${csrf_token}`
                });

                const data = await response.json();

                if (data.success) {
                    displayQueryResults(data.results, data.type);
                    closeSqlModal();
                    showNotification('Requête exécutée avec succès', 'success');
                } else {
                    showNotification('Erreur SQL: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            }
        }

        // Fonction pour afficher les résultats de requête
        function displayQueryResults(results, type) {
            const section = document.getElementById('query-results-section');
            const container = document.getElementById('query-results');

            section.style.display = 'block';

            if (type === 'SELECT' && results.length > 0) {
                const headers = Object.keys(results[0]);
                container.innerHTML = `
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    ${headers.map(header => `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${header}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${results.map(row => `
                                    <tr>
                                        ${headers.map(header => `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row[header] || ''}</td>`).join('')}
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-sm text-gray-600">
                        ${results.length} ligne(s) retournée(s)
                    </div>
                `;
            } else if (type === 'SELECT') {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-search text-gray-400 text-2xl mb-3"></i>
                        <p class="text-gray-500">Aucun résultat trouvé</p>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-green-800">Requête exécutée avec succès</p>
                                <p class="text-sm text-green-600">${results.message || 'Opération terminée'}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        // Fonction pour voir les données d'une table
        function viewTableData(tableName) {
            document.getElementById('sql-query').value = `SELECT * FROM ${tableName} LIMIT 50;`;
            openSqlModal();
        }

        // Fonction pour exporter une table
        async function exportTable(tableName) {
            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=export_table&table=${tableName}&csrf_token=${csrf_token}`
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${tableName}_export_${new Date().toISOString().split('T')[0]}.csv`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                    showNotification(`Table ${tableName} exportée avec succès`, 'success');
                } else {
                    showNotification('Erreur lors de l\'exportation', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            }
        }
        // Fonction pour charger les logs
        async function loadLogs() {
            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_logs&csrf_token=${csrf_token}`
                });

                const data = await response.json();

                if (data.success) {
                    allLogs = data.logs;
                    updateLogsStats(data.stats);
                    displayLogs();
                } else {
                    document.getElementById('logs-container').innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-3"></i>
                            <p class="text-red-400">Erreur lors du chargement des logs</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('logs-container').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-3"></i>
                        <p class="text-red-400">Erreur de connexion</p>
                    </div>
                `;
            }
        }

        // Fonction pour afficher les logs
        function displayLogs() {
            const filteredLogs = filterLogs();
            const startIndex = (currentLogsPage - 1) * logsPerPage;
            const endIndex = startIndex + logsPerPage;
            const logsToShow = filteredLogs.slice(startIndex, endIndex);

            const container = document.getElementById('logs-container');

            if (logsToShow.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-search text-gray-400 text-2xl mb-3"></i>
                        <p class="text-gray-400">Aucun log trouvé</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = logsToShow.map(log => {
                const logClass = getLogClass(log.content);
                return `
                    <div class="${logClass} p-2 rounded mb-1 font-mono text-sm">
                        <span class="text-gray-400">${log.timestamp}</span>
                        <span class="text-blue-300">${log.ip}</span>
                        <span class="text-green-300">${log.page}</span>
                        <span class="text-yellow-300">${log.userAgent}</span>
                    </div>
                `;
            }).join('');

            // Mettre à jour la pagination
            updateLogsPagination(filteredLogs.length);
        }

        // Fonction pour filtrer les logs
        function filterLogs() {
            const searchTerm = document.getElementById('log-search').value.toLowerCase();
            const startDate = document.getElementById('log-date-start').value;
            const endDate = document.getElementById('log-date-end').value;
            const logType = document.getElementById('log-type-filter').value;

            return allLogs.filter(log => {
                // Filtre par recherche
                if (searchTerm && !log.content.toLowerCase().includes(searchTerm)) {
                    return false;
                }

                // Filtre par date
                if (startDate && log.date < startDate) {
                    return false;
                }
                if (endDate && log.date > endDate) {
                    return false;
                }

                // Filtre par type
                if (logType !== 'all') {
                    if (logType === 'backup' && !log.content.includes('BACKUP')) {
                        return false;
                    }
                    if (logType === 'error' && !log.content.includes('ERROR')) {
                        return false;
                    }
                    if (logType === 'access' && (log.content.includes('BACKUP') || log.content.includes('ERROR'))) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Fonction pour obtenir la classe CSS du log
        function getLogClass(content) {
            if (content.includes('ERROR')) {
                return 'bg-red-900 border-l-4 border-red-500';
            }
            if (content.includes('BACKUP')) {
                return 'bg-blue-900 border-l-4 border-blue-500';
            }
            return 'bg-gray-800 border-l-4 border-gray-600';
        }

        // Fonction pour mettre à jour les statistiques
        function updateLogsStats(stats) {
            document.getElementById('total-logs').textContent = stats.total || 0;
            document.getElementById('today-logs').textContent = stats.today || 0;
            document.getElementById('hour-logs').textContent = stats.lastHour || 0;
            document.getElementById('log-size').textContent = stats.fileSize || '0 KB';
        }

        // Fonction pour mettre à jour la pagination
        function updateLogsPagination(totalLogs) {
            const totalPages = Math.ceil(totalLogs / logsPerPage);
            const startIndex = (currentLogsPage - 1) * logsPerPage + 1;
            const endIndex = Math.min(currentLogsPage * logsPerPage, totalLogs);

            document.getElementById('logs-start').textContent = totalLogs > 0 ? startIndex : 0;
            document.getElementById('logs-end').textContent = endIndex;
            document.getElementById('logs-total').textContent = totalLogs;

            document.getElementById('prev-logs-btn').disabled = currentLogsPage <= 1;
            document.getElementById('next-logs-btn').disabled = currentLogsPage >= totalPages;
        }

        // Fonctions de navigation
        function previousLogsPage() {
            if (currentLogsPage > 1) {
                currentLogsPage--;
                displayLogs();
            }
        }

        function nextLogsPage() {
            const filteredLogs = filterLogs();
            const totalPages = Math.ceil(filteredLogs.length / logsPerPage);
            if (currentLogsPage < totalPages) {
                currentLogsPage++;
                displayLogs();
            }
        }

        // Fonction pour actualiser les logs
        function refreshLogs() {
            currentLogsPage = 1;
            loadLogs();
        }

        // Fonction pour vider les logs
        async function clearLogs() {
            if (!confirm('Êtes-vous sûr de vouloir vider tous les logs ? Cette action est irréversible.')) {
                return;
            }

            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=clear_logs&csrf_token=${csrf_token}`
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Logs vidés avec succès', 'success');
                    loadLogs();
                } else {
                    showNotification('Erreur lors de la suppression des logs', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            }
        }

        // Fonction pour télécharger les logs
        function downloadLogs() {
            const link = document.createElement('a');
            link.href = 'admin_ajax.php?action=download_logs';
            link.download = `logs_${new Date().toISOString().split('T')[0]}.txt`;
            link.click();
        }

        // Fonction pour charger les utilisateurs
        function loadUsers() {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_users&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('users-table-body');
                    tbody.innerHTML = '';

                    if (data.success && data.users && data.users.length > 0) {
                        data.users.forEach(user => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-gray-50 transition-colors';

                            const roleColors = {
                                'admin': 'bg-red-100 text-red-800',
                                'moderator': 'bg-yellow-100 text-yellow-800',
                                'user': 'bg-green-100 text-green-800',
                                'opj': 'bg-blue-100 text-blue-800',
                                'avocat': 'bg-purple-100 text-purple-800',
                                'journaliste': 'bg-indigo-100 text-indigo-800',
                                'magistrat': 'bg-pink-100 text-pink-800',
                                'psychologue': 'bg-teal-100 text-teal-800',
                                'association': 'bg-orange-100 text-orange-800',
                                'rgpd': 'bg-gray-100 text-gray-800'
                            };

                            const statusColor = user.is_active == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            const statusText = user.is_active == 1 ? 'Actif' : 'Non Actif';

                            row.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                ${user.username.charAt(0).toUpperCase()}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${user.username}</div>
                                <div class="text-sm text-gray-500 sm:hidden">${user.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <div class="text-sm text-gray-900">${user.email}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${roleColors[user.role] || 'bg-gray-100 text-gray-800'}">
                            ${user.role}
                        </span>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <div class="text-sm text-gray-900">${user.created_at}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${statusColor}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${user.is_verified == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${user.is_verified == 1 ? 'Vérifié' : 'Non Vérifié'}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-800 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-800 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                            tbody.appendChild(row);
                        });
                    } else {
                        // Afficher un message si aucun utilisateur
                        tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium">Aucun utilisateur trouvé</p>
                            <p class="text-sm">Les utilisateurs apparaîtront ici une fois créés.</p>
                        </div>
                    </td>
                </tr>
            `;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des utilisateurs:', error);
                    const tbody = document.getElementById('users-table-body');
                    tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-red-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                        <p class="text-lg font-medium">Erreur de chargement</p>
                        <p class="text-sm">Impossible de charger la liste des utilisateurs.</p>
                    </div>
                </td>
            </tr>
        `;
                });
        }

        // Fonction pour charger les signalements
        function loadSignalements() {
            const filter = document.getElementById('signalement-filter')?.value || '';

            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_signalements&filter=${filter}&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('signalements-table-body');
                    tbody.innerHTML = '';

                    if (data.success && data.signalements) {
                        data.signalements.forEach(signalement => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-gray-50 transition-colors';

                            const statusColors = {
                                'nouveau': 'bg-blue-100 text-blue-800',
                                'en_cours': 'bg-yellow-100 text-yellow-800',
                                'resolu': 'bg-green-100 text-green-800',
                                'rejete': 'bg-red-100 text-red-800'
                            };

                            const priorityColors = {
                                'haute': 'bg-red-100 text-red-800',
                                'normale': 'bg-yellow-100 text-yellow-800',
                                'basse': 'bg-green-100 text-green-800'
                            };

                            const personName = signalement.nom && signalement.prenom
                                ? `${signalement.prenom} ${signalement.nom}`
                                : (signalement.titre || 'Non spécifié');

                            row.innerHTML = `
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-br from-red-500 to-red-600 h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        ${personName.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${personName}</div>
                                        <div class="text-sm text-gray-500">${signalement.type_incident}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden sm:table-cell">
                                <div class="text-sm text-gray-900">${signalement.type_incident}</div>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${signalement.incident_context === 'irl' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">
                                    ${signalement.incident_context === 'irl' ? 'IRL' : 'Virtuel'}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${priorityColors[signalement.priorite] || 'bg-gray-100 text-gray-800'}">
                                    ${signalement.priorite}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${statusColors[signalement.statut] || 'bg-gray-100 text-gray-800'}">
                                    ${signalement.statut}
                                </span>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <div class="text-sm text-gray-900">${new Date(signalement.date_signalement).toLocaleDateString('fr-FR')}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewSignalement(${signalement.id})" class="text-blue-600 hover:text-blue-800 transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editSignalement(${signalement.id})" class="text-green-600 hover:text-green-800 transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteSignalement(${signalement.id})" class="text-red-600 hover:text-red-800 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun signalement trouvé</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        }

        // Fonction pour charger les messages de contact
        function loadContacts() {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_contacts&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('contacts-table-body');
                    tbody.innerHTML = '';

                    if (data.status === 'success' && data.contacts) {
                        data.contacts.forEach(contact => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-gray-50 transition-colors';

                            const statusColor = contact.statut === 'nouveau' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800';
                            row.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="bg-gradient-to-br from-purple-500 to-purple-600 h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                ${contact.nom_affiche ? contact.nom_affiche.charAt(0).toUpperCase() : 'A'}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${contact.nom_affiche || 'Anonyme'}</div>
                                <div class="text-sm text-gray-500">${contact.email_affiche || 'Non communiqué'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <div class="text-sm text-gray-900">${contact.sujet}</div>
                        <div class="text-xs text-gray-500 mt-1">${contact.type_demande}</div>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <div class="text-sm text-gray-900">${contact.date_creation}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${statusColor}">
                            ${contact.statut}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button onclick="viewContact(${contact.id})" class="text-blue-600 hover:text-blue-800 transition-colors" title="Voir le détail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="markContactAsRead(${contact.id})" class="text-green-600 hover:text-green-800 transition-colors" title="Marquer comme lu">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="deleteContact(${contact.id})" class="text-red-600 hover:text-red-800 transition-colors" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun message de contact trouvé</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    const tbody = document.getElementById('contacts-table-body');
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Erreur lors du chargement des contacts</td></tr>';
                });
        }

        // Fonction pour afficher les détails d'un signalement
        function viewSignalement(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_signalement_details&id=${id}&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const signalement = data.signalement;
                        const personName = signalement.nom && signalement.prenom
                            ? `${signalement.prenom} ${signalement.nom}`
                            : signalement.titre || 'Signalement sans titre';

                        // Générer la section des preuves
                        let preuvesSection = '';
                        if (signalement.preuves && signalement.preuves.length > 0) {
                            preuvesSection = `
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Preuves et documents</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            ${signalement.preuves.map(preuve => {
                                const isImage = /\.(jpg|jpeg|png|gif)$/i.test(preuve.nom_fichier);
                                return `
                                    <div class="border rounded-lg p-2 hover:bg-gray-50 transition-colors">
                                        ${isImage ?
                                        `<img src="${preuve.chemin_fichier}" alt="${preuve.nom_fichier}" class="w-full h-20 object-cover rounded cursor-pointer" onclick="openImageModal('${preuve.chemin_fichier}', '${preuve.nom_fichier}')">` :
                                        `<div class="w-full h-20 bg-gray-100 rounded flex items-center justify-center">
                                                <i class="fas fa-file text-2xl text-gray-400"></i>
                                            </div>`
                                    }
                                        <p class="text-xs text-gray-600 mt-1 truncate" title="${preuve.nom_fichier}">${preuve.nom_fichier}</p>
                                        <a href="${preuve.chemin_fichier}" download class="text-xs text-blue-600 hover:text-blue-800">Télécharger</a>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
                        }

                        const modalContent = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeModal()">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 rounded-t-2xl">
                            <div class="flex justify-between items-center">
                                <h3 class="text-2xl font-bold">Détails du Signalement #${signalement.id}</h3>
                                <div class="flex space-x-2">
                                    <button onclick="editSignalement(${signalement.id})" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-edit mr-2"></i>Modifier
                                    </button>
                                    <button onclick="closeModal()" class="text-white hover:text-gray-200 text-2xl">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Personne signalée</h4>
                                    <p class="text-gray-700">${personName}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Type d'incident</h4>
                                    <p class="text-gray-700">${signalement.type_incident}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Contexte</h4>
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${signalement.incident_context === 'irl' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'
                            }">
                                        ${signalement.incident_context === 'irl' ? 'IRL' : 'Virtuel'}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Priorité</h4>
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${signalement.priorite === 'haute' ? 'bg-red-100 text-red-800' :
                                signalement.priorite === 'moyenne' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-green-100 text-green-800'
                            }">
                                        ${signalement.priorite}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Statut</h4>
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${signalement.statut === 'nouveau' ? 'bg-blue-100 text-blue-800' :
                                signalement.statut === 'en_cours' ? 'bg-yellow-100 text-yellow-800' :
                                    signalement.statut === 'resolu' ? 'bg-green-100 text-green-800' :
                                        'bg-red-100 text-red-800'
                            }">
                                        ${signalement.statut}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Date de signalement</h4>
                                    <p class="text-gray-700">${new Date(signalement.date_signalement).toLocaleDateString('fr-FR')}</p>
                                </div>
                                ${signalement.plateforme ? `
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Plateforme</h4>
                                        <p class="text-gray-700">${signalement.plateforme}</p>
                                    </div>
                                ` : ''}
                                ${signalement.lieu ? `
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Lieu</h4>
                                        <p class="text-gray-700">${signalement.lieu}</p>
                                    </div>
                                ` : ''}
                                ${signalement.signale_par ? `
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Signalé par</h4>
                                        <p class="text-gray-700">Utilisateur #${signalement.signale_par}</p>
                                    </div>
                                ` : ''}
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Description</h4>
                                <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">${signalement.description}</p>
                            </div>
                            ${preuvesSection}
                            <div class="flex justify-between items-center pt-4 border-t">
                                <div class="text-sm text-gray-500">
                                    ${signalement.date_traitement ?
                                `Traité le ${new Date(signalement.date_traitement).toLocaleDateString('fr-FR')}` :
                                `Signalé le ${new Date(signalement.date_signalement).toLocaleDateString('fr-FR')}`
                            }
                                </div>
                                <div class="flex space-x-3">
                                    <button onclick="updateSignalementStatus(${signalement.id}, 'en_cours')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors">
                                        Marquer en cours
                                    </button>
                                    <button onclick="updateSignalementStatus(${signalement.id}, 'resolu')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                                        Marquer résolu
                                    </button>
                                    <button onclick="updateSignalementStatus(${signalement.id}, 'rejete')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                                        Rejeter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                        document.body.insertAdjacentHTML('beforeend', modalContent);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement du signalement');
                });
        }

        // Fonction pour prévisualiser les fichiers sélectionnés
        function previewFiles(input, previewContainer) {
            const files = input.files;
            previewContainer.innerHTML = '';

            Array.from(files).forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'relative border rounded-lg p-2 bg-gray-50';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className = 'w-full h-20 object-cover rounded';
                    fileDiv.appendChild(img);
                } else {
                    const iconDiv = document.createElement('div');
                    iconDiv.className = 'w-full h-20 bg-gray-200 rounded flex items-center justify-center';
                    iconDiv.innerHTML = '<i class="fas fa-file text-2xl text-gray-400"></i>';
                    fileDiv.appendChild(iconDiv);
                }

                const fileName = document.createElement('p');
                fileName.className = 'text-xs text-gray-600 mt-1 truncate';
                fileName.textContent = file.name;
                fileDiv.appendChild(fileName);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = () => removeFile(input, index, previewContainer);
                fileDiv.appendChild(removeBtn);

                previewContainer.appendChild(fileDiv);
            });
        }

        // Fonction pour supprimer un fichier de la sélection
        function removeFile(input, index, previewContainer) {
            const dt = new DataTransfer();
            const files = input.files;

            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }

            input.files = dt.files;
            previewFiles(input, previewContainer);
        }

        // Fonction pour ouvrir une image en grand
        function openImageModal(imageSrc, imageName) {
            const modalContent = `
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" onclick="closeModal()">
            <div class="max-w-4xl max-h-[90vh] m-4" onclick="event.stopPropagation()">
                <div class="bg-white rounded-lg overflow-hidden">
                    <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
                        <h3 class="font-semibold">${imageName}</h3>
                        <button onclick="closeModal()" class="text-white hover:text-gray-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <img src="${imageSrc}" alt="${imageName}" class="max-w-full max-h-[70vh] object-contain mx-auto">
                    </div>
                </div>
            </div>
        </div>
    `;

            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        // Gestionnaire d'événements pour l'upload de fichiers
        document.addEventListener('DOMContentLoaded', function () {
            const editPreuvesInput = document.getElementById('edit_preuves');
            const editPreviewContainer = document.getElementById('edit_files_preview');

            if (editPreuvesInput && editPreviewContainer) {
                editPreuvesInput.addEventListener('change', function () {
                    previewFiles(this, editPreviewContainer);
                });

                // Drag and drop
                const dropZone = editPreuvesInput.closest('.border-dashed');
                if (dropZone) {
                    dropZone.addEventListener('dragover', function (e) {
                        e.preventDefault();
                        this.classList.add('border-blue-500', 'bg-blue-50');
                    });

                    dropZone.addEventListener('dragleave', function (e) {
                        e.preventDefault();
                        this.classList.remove('border-blue-500', 'bg-blue-50');
                    });

                    dropZone.addEventListener('drop', function (e) {
                        e.preventDefault();
                        this.classList.remove('border-blue-500', 'bg-blue-50');

                        const files = e.dataTransfer.files;
                        editPreuvesInput.files = files;
                        previewFiles(editPreuvesInput, editPreviewContainer);
                    });
                }
            }
        });
        // Fonction pour afficher les détails d'un contact
        function viewContact(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_contact&id=${id}&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const contact = data.contact;

                        const modalContent = `
                        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeModal()">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                                <div class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white p-6 rounded-t-2xl">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-2xl font-bold">Message de Contact #${contact.id}</h3>
                                        <button onclick="closeModal()" class="text-white hover:text-gray-200 text-2xl">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="font-semibold text-gray-900 mb-2">Nom</h4>
                                            <p class="text-gray-700">${contact.nom || 'Non spécifié'}</p>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 mb-2">Prénom</h4>
                                            <p class="text-gray-700">${contact.prenom || 'Non spécifié'}</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <h4 class="font-semibold text-gray-900 mb-2">Email</h4>
                                            <p class="text-gray-700">${contact.email}</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <h4 class="font-semibold text-gray-900 mb-2">Sujet</h4>
                                            <p class="text-gray-700">${contact.sujet}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">Message</h4>
                                        <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">${contact.message}</p>
                                    </div>
                                    <div class="flex justify-between items-center pt-4 border-t">
                                        <div class="text-sm text-gray-500">
                                            Reçu le ${new Date(contact.created_at).toLocaleDateString('fr-FR')}
                                        </div>
                                        <div class="flex space-x-3">
                                            <button onclick="markContactAsRead(${contact.id})" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                                                Marquer comme lu
                                            </button>
                                            <a href="mailto:${contact.email}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors inline-block">
                                                Répondre
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                        document.body.insertAdjacentHTML('beforeend', modalContent);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement du contact');
                });
        }

        // Fonction pour mettre à jour le statut d'un signalement
        function updateSignalementStatus(id, status) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_signalement_status&id=${id}&status=${status}&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadSignalements();
                        showNotification('Statut mis à jour avec succès', 'success');
                    } else {
                        alert('Erreur lors de la mise à jour du statut');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la mise à jour du statut');
                });
        }

        // Fonction pour marquer un contact comme lu
        function markContactAsRead(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_contact_read&id=${id}&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadContacts();
                        showNotification('Contact marqué comme lu', 'success');
                    } else {
                        alert('Erreur lors de la mise à jour du contact');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la mise à jour du contact');
                });
        }

        // Fonction pour supprimer un signalement
        function deleteSignalement(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_signalement&id=${id}&csrf_token=${csrf_token}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadSignalements();
                            showNotification('Signalement supprimé avec succès', 'success');
                        } else {
                            alert('Erreur lors de la suppression du signalement');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la suppression du signalement');
                    });
            }
        }

        // Fonction pour supprimer un contact
        function deleteContact(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce message de contact ?')) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_contact&id=${id}&csrf_token=${csrf_token}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadContacts();
                            showNotification('Contact supprimé avec succès', 'success');
                        } else {
                            alert('Erreur lors de la suppression du contact');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la suppression du contact');
                    });
            }
        }

        // Fonction pour fermer les modales
        function closeModal() {
            const modals = document.querySelectorAll('.fixed.inset-0');
            modals.forEach(modal => modal.remove());
        }

        // Fonction pour afficher les notifications
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };

            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Animation d'entrée
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Suppression automatique
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Fonction pour ouvrir le modal de création d'utilisateur
        function openCreateUserModal() {
            const modalContent = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeModal()">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4" onclick="event.stopPropagation()">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 rounded-t-2xl">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-bold">Créer un nouvel utilisateur</h3>
                                <button onclick="closeModal()" class="text-white hover:text-gray-200 text-xl">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <form onsubmit="createUser(event)" class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                                <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                                <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                                <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="user">Utilisateur</option>
                                    <option value="moderator">Modérateur</option>
                                    <option value="admin">Administrateur</option>
                                </select>
                            </div>
                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    Annuler
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Créer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        // Fonction pour créer un utilisateur
        function createUser(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('action', 'create_user');
            formData.append('csrf_token', csrf_token);

            fetch('admin_ajax.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadUsers();
                        showNotification('Utilisateur créé avec succès', 'success');
                    } else {
                        alert(data.message || 'Erreur lors de la création de l\'utilisateur');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la création de l\'utilisateur');
                });
        }




        // Fonction pour éditer un utilisateur
        function editUser(userId) {
            // Récupérer les données de l'utilisateur
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_user&id=${userId}&csrf_token=${csrf_token}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user) {
                        const user = data.user;

                        const modalContent = `
                        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeModal()">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4" onclick="event.stopPropagation()">
                                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-t-2xl">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-xl font-bold">Modifier l'utilisateur</h3>
                                        <button onclick="closeModal()" class="text-white hover:text-gray-200">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <form onsubmit="updateUser(event, ${userId})" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                                            <input type="text" name="username" value="${user.username}" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                            <input type="email" name="email" value="${user.email}" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                                            <select name="role" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="user" ${user.role === 'user' ? 'selected' : ''}>Utilisateur</option>
                                                <option value="moderator" ${user.role === 'moderator' ? 'selected' : ''}>Modérateur</option>
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
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_verified" ${user.is_verified == 1 ? 'checked' : ''} class="mr-2">
                                            <label class="text-sm font-medium text-gray-700">Compte vérifié</label>
                                        </div>
                                        <div class="flex space-x-3 pt-4">
                                            <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-xl font-medium transition-colors">
                                                Annuler
                                            </button>
                                            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-medium transition-all">
                                                Modifier
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;

                        document.body.insertAdjacentHTML('beforeend', modalContent);
                    } else {
                        alert('Erreur lors du chargement des données utilisateur');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des données utilisateur');
                });
        }

        // Fonction pour mettre à jour un utilisateur
        function updateUser(event, userId) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('action', 'update_user');
            formData.append('user_id', userId);
            formData.append('csrf_token', csrf_token);

            // Gérer la checkbox is_verified
            if (!formData.has('is_verified')) {
                formData.append('is_verified', '0');
            } else {
                formData.set('is_verified', '1');
            }

            fetch('admin_ajax.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadUsers();
                        showNotification('Utilisateur modifié avec succès', 'success');
                    } else {
                        alert(data.message || 'Erreur lors de la modification de l\'utilisateur');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la modification de l\'utilisateur');
                });
        }

        // Fonction pour supprimer un utilisateur
        function deleteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_user&user_id=${userId}&csrf_token=${csrf_token}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success || data.status === 'success') {
                            loadUsers();
                            showNotification('Utilisateur supprimé avec succès', 'success');
                        } else {
                            alert(data.message || 'Erreur lors de la suppression de l\'utilisateur');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la suppression de l\'utilisateur');
                    });
            }
        }



        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function () {
            showSection('users');
            loadUsers();
            document.getElementById('log-search').addEventListener('input', () => {
                currentLogsPage = 1;
                displayLogs();
            });

            document.getElementById('log-date-start').addEventListener('change', () => {
                currentLogsPage = 1;
                displayLogs();
            });

            document.getElementById('log-date-end').addEventListener('change', () => {
                currentLogsPage = 1;
                displayLogs();
            });

            document.getElementById('log-type-filter').addEventListener('change', () => {
                currentLogsPage = 1;
                displayLogs();
            });
            const adhesionSearch = document.getElementById('adhesion-search');
            const statusFilter = document.getElementById('status-filter');
            const roleFilterAdhesion = document.getElementById('role-filter-adhesion');

            if (adhesionSearch) {
                adhesionSearch.addEventListener('input', displayAdhesions);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', displayAdhesions);
            }
            if (roleFilterAdhesion) {
                roleFilterAdhesion.addEventListener('change', displayAdhesions);
            }
        });
    </script>
    <?php include_once('../Inc/Components/footer.php'); ?>
    <?php include_once('../Inc/Components/footers.php'); ?>
    <?php include('../Inc/Traitement/create_log.php'); ?>