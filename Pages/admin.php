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
    $totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $adminUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch()['count'];
    $activeUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_activity > datetime('now', '-30 days')")->fetch()['count'];
    
    // Vérifier si la colonne email_verified existe
    try {
        $verifiedUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 1")->fetch()['count'];
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
    <title>Panel Admin - Signale France</title>
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
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
<main class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header moderne avec effet glassmorphism -->
        <div class="mb-8 relative overflow-hidden">
            <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl">
                <!-- Éléments décoratifs avec animation -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -mr-32 -mt-32 animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-10 rounded-full -ml-24 -mb-24 animate-pulse delay-75"></div>
                <div class="absolute top-1/2 left-1/2 w-32 h-32 bg-white opacity-5 rounded-full transform -translate-x-1/2 -translate-y-1/2 animate-ping"></div>

                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 class="text-4xl lg:text-5xl font-bold mb-2 flex items-center">
                                <div class="bg-white bg-opacity-20 backdrop-blur-sm p-4 rounded-2xl mr-4 shadow-lg">
                                    <i class="fas fa-shield-alt text-3xl"></i>
                                </div>
                                Panel d'Administration
                            </h1>
                            <p class="text-blue-100 text-lg lg:text-xl font-medium">
                                Bienvenue <?php echo htmlspecialchars($username); ?>, gérez votre plateforme Signale France
                            </p>
                            <div class="flex items-center mt-4 space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-400 bg-opacity-20 text-green-100">
                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                    Système opérationnel
                                </span>
                                <span class="text-blue-200 text-sm">
                                    <i class="fas fa-users mr-1"></i>
                                    <?php echo $totalUsers; ?> utilisateurs connectés
                                </span>
                            </div>
                        </div>
                        <div class="mt-6 lg:mt-0">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white border-opacity-20">
                                <div class="text-center">
                                    <div class="text-3xl font-bold"><?php echo date('H:i'); ?></div>
                                    <div class="text-sm text-blue-200 font-medium"><?php echo strftime('%A %d %B %Y'); ?></div>
                                    <div class="text-xs text-blue-300 mt-1 flex items-center justify-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        Dernière connexion
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques avec animations et effets améliorés -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <!-- Total Utilisateurs -->
            <div class="group hover:scale-105 transition-all duration-500 hover:rotate-1">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-white/50 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-400/10 to-blue-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full -mr-10 -mt-10 opacity-10 group-hover:opacity-20 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-users text-xl text-white"></i>
                            </div>
                            <span class="text-2xl font-bold text-gray-900 group-hover:text-blue-700 transition-colors"><?php echo $totalUsers; ?></span>
                        </div>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors">Total Utilisateurs</p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-arrow-up text-green-500 text-xs mr-1"></i>
                            <span class="text-xs text-green-500 font-medium">+12% ce mois</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilisateurs Vérifiés -->
            <div class="group hover:scale-105 transition-all duration-500 hover:-rotate-1">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-white/50 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/10 to-emerald-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full -mr-10 -mt-10 opacity-10 group-hover:opacity-20 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-3 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-user-check text-xl text-white"></i>
                            </div>
                            <span class="text-2xl font-bold text-gray-900 group-hover:text-emerald-700 transition-colors"><?php echo $verifiedUsers; ?></span>
                        </div>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-emerald-600 transition-colors">Utilisateurs Vérifiés</p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-shield-check text-emerald-500 text-xs mr-1"></i>
                            <span class="text-xs text-emerald-500 font-medium">Vérifiés</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signalements Actifs -->
            <div class="group hover:scale-105 transition-all duration-500 hover:rotate-1">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-white/50 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-400/10 to-red-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-400 to-red-600 rounded-full -mr-10 -mt-10 opacity-10 group-hover:opacity-20 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-flag text-xl text-white"></i>
                            </div>
                            <span class="text-2xl font-bold text-gray-900 group-hover:text-red-700 transition-colors"><?php echo $pendingSignalements; ?></span>
                        </div>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-red-600 transition-colors">En Attente</p>
                        <div class="flex items-center mt-2">
                            <?php if($pendingSignalements > 0): ?>
                                <i class="fas fa-exclamation-triangle text-red-500 text-xs mr-1 animate-pulse"></i>
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
            <div class="group hover:scale-105 transition-all duration-500 hover:-rotate-1">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-white/50 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-400/10 to-green-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-full -mr-10 -mt-10 opacity-10 group-hover:opacity-20 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-check-circle text-xl text-white"></i>
                            </div>
                            <span class="text-2xl font-bold text-gray-900 group-hover:text-green-700 transition-colors"><?php echo $resolvedSignalements; ?></span>
                        </div>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-green-600 transition-colors">Résolus</p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-thumbs-up text-green-500 text-xs mr-1"></i>
                            <span class="text-xs text-green-500 font-medium">Traités</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signalements par Personne -->
            <div class="group hover:scale-105 transition-all duration-500 hover:rotate-1">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-white/50 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400/10 to-purple-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full -mr-10 -mt-10 opacity-10 group-hover:opacity-20 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-3 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-user-tag text-xl text-white"></i>
                            </div>
                            <span class="text-2xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors"><?php echo $signalementsByPerson; ?></span>
                        </div>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-purple-600 transition-colors">Avec Nom/Prénom</p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-id-card text-purple-500 text-xs mr-1"></i>
                            <span class="text-xs text-purple-500 font-medium">Identifiés</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Contact -->
            <div class="group hover:scale-105 transition-all duration-500 hover:-rotate-1">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl p-6 border border-white/50 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-400/10 to-amber-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full -mr-10 -mt-10 opacity-10 group-hover:opacity-20 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-gradient-to-br from-amber-500 to-amber-600 p-3 rounded-xl shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-envelope text-xl text-white"></i>
                            </div>
                            <span class="text-2xl font-bold text-gray-900 group-hover:text-amber-700 transition-colors"><?php echo $newContacts; ?></span>
                        </div>
                        <p class="text-sm font-medium text-gray-600 group-hover:text-amber-600 transition-colors">Nouveaux Messages</p>
                        <div class="flex items-center mt-2">
                            <?php if($newContacts > 0): ?>
                                <i class="fas fa-bell text-amber-500 text-xs mr-1 animate-bounce"></i>
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

        <!-- Layout principal responsive avec amélioration du design -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
            <!-- Navigation latérale améliorée -->
            <div class="xl:col-span-1">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden sticky top-8">
                    <div class="bg-gradient-to-r from-gray-50 via-blue-50 to-indigo-50 p-6 border-b border-gray-100">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-2 rounded-lg mr-3 shadow-lg">
                                <i class="fas fa-cogs text-white"></i>
                            </div>
                            Gestion
                        </h3>
                        <p class="text-gray-600 text-sm mt-1">Centre de contrôle administrateur</p>
                    </div>
                    <nav class="p-4 space-y-2">
                        <!-- Menu Utilisateurs -->
                        <a href="#" onclick="showSection('users')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-700 transition-all duration-300 border border-transparent hover:border-blue-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-blue-100 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-users group-hover:text-blue-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Utilisateurs</span>
                            <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm group-hover:bg-blue-700 transition-colors"><?php echo $totalUsers; ?></span>
                        </a>

                        <!-- Menu Signalements -->
                        <a href="#" onclick="showSection('signalements')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 hover:text-red-700 transition-all duration-300 border border-transparent hover:border-red-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-red-100 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-flag group-hover:text-red-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Signalements</span>
                            <?php if($pendingSignalements > 0): ?>
                                <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm animate-pulse group-hover:bg-red-600 transition-colors"><?php echo $pendingSignalements; ?></span>
                            <?php else: ?>
                                <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm group-hover:bg-green-600 transition-colors">0</span>
                            <?php endif; ?>
                        </a>

                        <!-- Menu Messages Contact -->
                        <a href="#" onclick="showSection('contacts')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-indigo-50 hover:text-purple-700 transition-all duration-300 border border-transparent hover:border-purple-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-purple-100 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-envelope group-hover:text-purple-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Messages Contact</span>
                            <?php if($newContacts > 0): ?>
                                <span class="bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm animate-pulse group-hover:bg-purple-600 transition-colors"><?php echo $newContacts; ?></span>
                            <?php else: ?>
                                <span class="bg-gray-400 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm group-hover:bg-gray-500 transition-colors">0</span>
                            <?php endif; ?>
                        </a>

                        <!-- Menu Adhésions -->
                        <a href="#" onclick="showSection('adhesions')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-orange-50 hover:to-amber-50 hover:text-orange-700 transition-all duration-300 border border-transparent hover:border-orange-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-orange-100 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-user-plus group-hover:text-orange-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Demandes d'Adhésion</span>
                            <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm group-hover:bg-orange-600 transition-colors" id="adhesions-count">0</span>
                        </a>

                        <!-- Menu Analytiques -->
                        <a href="#" onclick="showSection('analytics')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-700 transition-all duration-300 border border-transparent hover:border-emerald-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-emerald-100 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-chart-bar group-hover:text-emerald-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Analytiques</span>
                        </a>

                        <!-- Menu Paramètres -->
                        <a href="#" onclick="showSection('settings')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-amber-50 hover:to-yellow-50 hover:text-amber-700 transition-all duration-300 border border-transparent hover:border-amber-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-amber-100 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-cog group-hover:text-amber-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Paramètres</span>
                        </a>

                        <!-- Menu Logs -->
                        <a href="#" onclick="showSection('logs')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:text-gray-700 transition-all duration-300 border border-transparent hover:border-gray-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-gray-200 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-file-alt group-hover:text-gray-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Logs Système</span>
                        </a>

                        <!-- Menu Database -->
                        <a href="#" onclick="showSection('database')" class="nav-link group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-xl hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-700 transition-all duration-300 border border-transparent hover:border-indigo-200 hover:shadow-md">
                            <div class="bg-gray-100 group-hover:bg-indigo-100 p-2 rounded-lg mr-3 transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-database group-hover:text-indigo-600 transition-colors"></i>
                            </div>
                            <span class="flex-1">Base de Données</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Contenu principal avec sections améliorées -->
            <div class="xl:col-span-3">
                <!-- Section Utilisateurs -->
                <div id="users-section" class="admin-section active">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-users text-white"></i>
                                        </div>
                                        Gestion des Utilisateurs
                                    </h3>
                                    <p class="text-gray-600 mt-1">Gérez les comptes utilisateurs et leurs permissions</p>
                                </div>
                                <button onclick="openCreateUserModal()" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>Nouvel Utilisateur
                                </button>
                            </div>
                        </div>

                        <!-- Tableau responsive amélioré -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Utilisateur</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rôle</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Créé le</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Certification</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
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
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-red-50 via-pink-50 to-red-50 p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                        <div class="bg-gradient-to-br from-red-500 to-pink-600 p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-flag text-white"></i>
                                        </div>
                                        Gestion des Signalements
                                    </h3>
                                    <p class="text-gray-600 mt-1">Traitez les signalements avec nom et prénom des personnes signalées</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des signalements -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-gray-50 to-red-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Personne Signalée</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Contexte</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Priorité</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
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
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 via-indigo-50 to-purple-50 p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-envelope text-white"></i>
                                        </div>
                                        Messages de Contact
                                    </h3>
                                    <p class="text-gray-600 mt-1">Gérez les messages reçus via le formulaire de contact</p>
                                </div>
                                <button onclick="markAllAsRead()" class="bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="fas fa-check-double mr-2"></i>Tout marquer lu
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-gray-50 to-purple-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Expéditeur</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Sujet</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100" id="contacts-table-body">
                                <!-- Les messages seront chargés ici via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section Demandes d'Adhésion - CORRIGÉE -->
                <div id="adhesions-section" class="admin-section">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                        <div class="bg-gradient-to-br from-orange-500 to-amber-600 p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-user-plus text-white"></i>
                                        </div>
                                        Demandes d'Adhésion
                                    </h3>
                                    <p class="text-gray-600 mt-1">Gérez les demandes d'inscription en attente</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="refreshAdhesions()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all duration-200">
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
                                    <input type="text" id="adhesion-search" placeholder="Rechercher par nom, email..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="flex gap-2">
                                    <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="approved">Approuvées</option>
                                        <option value="rejected">Rejetées</option>
                                    </select>
                                    <select id="role-filter-adhesion" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
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
                                <thead class="bg-gradient-to-r from-gray-50 to-orange-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Demandeur</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Organisation</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rôle Demandé</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100" id="adhesions-table-body">
                                <!-- Les demandes seront chargées ici via AJAX -->
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
                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                                    Évolution des Signalements
                                </h4>
                                <div class="h-64 flex items-center justify-center bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl">
                                    <div class="text-center">
                                        <i class="fas fa-chart-line text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">Graphique à implémenter</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                                    Répartition par Type
                                </h4>
                                <div class="h-64 flex items-center justify-center bg-gradient-to-br from-gray-50 to-green-50 rounded-xl">
                                    <div class="text-center">
                                        <i class="fas fa-chart-pie text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">Graphique à implémenter</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Paramètres -->
                <div id="settings-section" class="admin-section">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 p-6 border-b border-gray-100">
                            <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                <div class="bg-gradient-to-br from-amber-500 to-yellow-600 p-3 rounded-xl mr-3 shadow-lg">
                                    <i class="fas fa-cog text-white"></i>
                                </div>
                                Paramètres du Système
                            </h3>
                            <p class="text-gray-600 mt-1">Configurez les paramètres de votre plateforme</p>
                        </div>

                        <div class="p-6">
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                            <i class="fas fa-globe mr-2 text-blue-600"></i>
                                            Paramètres Généraux
                                        </h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du site</label>
                                                <input type="text" value="Signale France" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Email administrateur</label>
                                                <input type="email" value="admin@signalefrance.fr" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gradient-to-br from-gray-50 to-green-50 rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                            <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                                            Sécurité
                                        </h4>
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">Vérification email obligatoire</span>
                                                <button class="bg-green-500 relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                    <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                                </button>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">Modération automatique</span>
                                                <button class="bg-gray-200 relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                    <span class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button class="bg-gradient-to-r from-amber-600 to-yellow-700 hover:from-amber-700 hover:to-yellow-800 text-white px-8 py-3 rounded-xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                        <i class="fas fa-save mr-2"></i>Sauvegarder
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Logs -->
                <div id="logs-section" class="admin-section">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 via-slate-50 to-gray-50 p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                        <div class="bg-gradient-to-br from-gray-500 to-slate-600 p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                        Logs Système
                                    </h3>
                                    <p class="text-gray-600 mt-2">Surveillance et analyse des activités du système</p>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button onclick="refreshLogs()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                        <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                    </button>
                                    <button onclick="clearLogs()" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                        <i class="fas fa-trash mr-2"></i>Vider les logs
                                    </button>
                                    <button onclick="downloadLogs()" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                        <i class="fas fa-download mr-2"></i>Télécharger
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Le reste du contenu des logs reste identique -->
                        <!-- ... -->
                    </div>
                </div>

                <!-- Section Database -->
                <div id="database-section" class="admin-section">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-indigo-50 p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <div class="mb-4 sm:mb-0">
                                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-3 rounded-xl mr-3 shadow-lg">
                                            <i class="fas fa-database text-white"></i>
                                        </div>
                                        Gestion Base de Données
                                    </h3>
                                    <p class="text-gray-600 mt-2">Administration et maintenance de la base de données SQLite</p>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button onclick="refreshDatabase()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                        <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                    </button>
                                    <button onclick="initializeTables()" class="bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                        <i class="fas fa-cogs mr-2"></i>Initialiser Tables
                                    </button>
                                    <button onclick="backupDatabase()" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                        <i class="fas fa-download mr-2"></i>Sauvegarde
                                    </button>
                                    <button onclick="openSqlModal()" class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center">
                                        <i class="fas fa-code mr-2"></i>Requête SQL
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Le reste du contenu de la base de données reste identique -->
                        <!-- ... -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modales restent identiques mais avec le design amélioré -->
<!-- Modal Création Utilisateur -->
<div id="createUserModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto border border-white/20">
        <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-2 rounded-lg mr-3">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                Créer un Utilisateur
            </h3>
        </div>
        <form id="createUserForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                <input type="text" name="username" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                <select name="role" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="user">Utilisateur</option>
                    <option value="moderator">Modérateur</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeCreateUserModal()" class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Les autres modales restent identiques avec les améliorations de design -->

<!-- Modal Requête SQL -->
<div id="sqlModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-white/20">
        <div class="bg-gradient-to-r from-purple-50 via-indigo-50 to-purple-50 p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-2 rounded-lg mr-3">
                            <i class="fas fa-code text-white"></i>
                        </div>
                        Exécuter une Requête SQL
                    </h3>
                    <p class="text-gray-600 mt-1">⚠️ Attention : Les requêtes de modification sont irréversibles</p>
                </div>
                <button onclick="closeSqlModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Requête SQL</label>
                <div class="relative">
                    <textarea id="sql-query" rows="8" placeholder="SELECT * FROM users LIMIT 10;" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm bg-gray-50"></textarea>
                    <div class="absolute top-2 right-2">
                        <button onclick="clearSqlQuery()" class="text-gray-400 hover:text-gray-600 text-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Templates de requêtes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Templates rapides</label>
                <div class="flex flex-wrap gap-2">
                    <button onclick="insertSqlTemplate('SELECT * FROM users LIMIT 10;')" class="px-3 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm hover:bg-blue-200 transition-colors flex items-center">
                        <i class="fas fa-users mr-1"></i>
                        SELECT users
                    </button>
                    <button onclick="insertSqlTemplate('SELECT * FROM signalements ORDER BY created_at DESC LIMIT 10;')" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition-colors flex items-center">
                        <i class="fas fa-flag mr-1"></i>
                        SELECT signalements
                    </button>
                    <button onclick="insertSqlTemplate('SELECT * FROM contacts ORDER BY created_at DESC LIMIT 10;')" class="px-3 py-2 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200 transition-colors flex items-center">
                        <i class="fas fa-envelope mr-1"></i>
                        SELECT contacts
                    </button>
                    <button onclick="insertSqlTemplate('SELECT name FROM sqlite_master WHERE type=\'table\';')" class="px-3 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm hover:bg-purple-200 transition-colors flex items-center">
                        <i class="fas fa-list mr-1"></i>
                        Liste tables
                    </button>
                    <button onclick="insertSqlTemplate('PRAGMA table_info(users);')" class="px-3 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-sm hover:bg-yellow-200 transition-colors flex items-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Structure table
                    </button>
                    <button onclick="insertSqlTemplate('SELECT COUNT(*) as total FROM users;')" class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-lg text-sm hover:bg-indigo-200 transition-colors flex items-center">
                        <i class="fas fa-calculator mr-1"></i>
                        COUNT users
                    </button>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeSqlModal()" class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button onclick="executeSqlQuery()" class="bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center">
                    <i class="fas fa-play mr-2"></i>
                    <span id="sql-execute-text">Exécuter</span>
                    <span id="sql-execute-loading" class="hidden">Exécution...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'édition des signalements -->
<div id="editSignalementModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center z-50">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto border border-white/20">
        <div class="bg-gradient-to-r from-red-50 via-pink-50 to-red-50 p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                        <div class="bg-gradient-to-br from-red-500 to-pink-600 p-3 rounded-xl mr-3">
                            <i class="fas fa-edit text-white"></i>
                        </div>
                        Modifier le Signalement
                    </h3>
                    <p class="text-gray-600 mt-1">Modifiez les détails du signalement sélectionné</p>
                </div>
                <button onclick="closeEditSignalementModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form id="editSignalementForm" class="p-6 space-y-6">
            <input type="hidden" id="edit_signalement_id" name="id">

            <!-- Informations principales -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    Informations principales
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                        <input type="text" id="edit_titre" name="titre" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type d'incident</label>
                        <select id="edit_type_incident" name="type_incident" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                            <option value="harcelement">🚫 Harcèlement</option>
                            <option value="discrimination">⚖️ Discrimination</option>
                            <option value="violence">⚠️ Violence</option>
                            <option value="fraude">💳 Fraude</option>
                            <option value="cyber-harcelement">💻 Cyber-harcèlement</option>
                            <option value="autre">❓ Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-align-left text-green-600 mr-2"></i>
                    Description détaillée
                </h4>
                <textarea id="edit_description" name="description" rows="6" placeholder="Décrivez en détail les faits..." class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent" required></textarea>
            </div>

            <!-- Gestion des fichiers -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-paperclip text-purple-600 mr-2"></i>
                    Preuves et documents
                </h4>

                <!-- Zone de drop pour nouveaux fichiers -->
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 mb-4 hover:border-purple-400 transition-colors" id="edit-drop-zone">
                    <div class="text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                        <p class="text-lg text-gray-600 mb-2">Glissez-déposez vos fichiers ici</p>
                        <p class="text-sm text-gray-500 mb-4">ou cliquez pour sélectionner</p>
                        <input type="file" id="edit_preuves" name="preuves[]" multiple accept="image/*,.pdf,.doc,.docx" class="hidden">
                        <button type="button" onclick="document.getElementById('edit_preuves').click()" class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white px-6 py-3 rounded-xl transition-all duration-200 shadow-lg">
                            <i class="fas fa-plus mr-2"></i>Sélectionner des fichiers
                        </button>
                    </div>
                    <div id="edit_files_preview" class="mt-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
                </div>

                <!-- Fichiers existants -->
                <div id="edit_existing_files" class="space-y-2"></div>

                <p class="text-xs text-gray-500 flex items-center mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Formats acceptés : JPG, PNG, GIF, PDF, DOC, DOCX (max 5MB par fichier)
                </p>
            </div>

            <!-- Statut et priorité -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-tasks text-amber-600 mr-2"></i>
                    Statut et priorité
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select id="edit_status" name="status" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                            <option value="en_attente">⏳ En attente</option>
                            <option value="en_cours">🔄 En cours de traitement</option>
                            <option value="resolu">✅ Résolu</option>
                            <option value="rejete">❌ Rejeté</option>
                            <option value="archive">📁 Archivé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                        <select id="edit_priorite" name="priorite" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                            <option value="basse">🟢 Basse</option>
                            <option value="moyenne">🟡 Moyenne</option>
                            <option value="haute">🟠 Haute</option>
                            <option value="critique">🔴 Critique</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notes administratives -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-indigo-600 mr-2"></i>
                    Notes administratives
                </h4>
                <textarea id="edit_admin_notes" name="admin_notes" rows="4" placeholder="Notes internes pour le suivi du dossier..." class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeEditSignalementModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>Annuler
                </button>
                <button type="button" onclick="duplicateSignalement()" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                    <i class="fas fa-copy mr-2"></i>Dupliquer
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-red-600 to-pink-700 text-white rounded-xl hover:from-red-700 hover:to-pink-800 transition-all duration-200 shadow-lg">
                    <span id="edit-save-text">
                        <i class="fas fa-save mr-2"></i>Sauvegarder
                    </span>
                    <span id="edit-save-loading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Sauvegarde...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-md w-full border border-white/20">
        <div class="bg-gradient-to-r from-red-50 to-pink-50 p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                <div class="bg-gradient-to-br from-red-500 to-pink-600 p-2 rounded-lg mr-3">
                    <i class="fas fa-exclamation-triangle text-white"></i>
                </div>
                Confirmer la suppression
            </h3>
        </div>
        <div class="p-6">
            <div class="flex items-center space-x-4 mb-6">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-trash text-red-600 text-xl"></i>
                    </div>
                </div>
                <div>
                    <p class="text-gray-900 font-medium" id="delete-message">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                    <p class="text-gray-500 text-sm mt-1">Cette action est irréversible.</p>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                    <div class="text-sm">
                        <p class="text-red-800 font-medium">Attention !</p>
                        <p class="text-red-700 mt-1">Cette suppression ne peut pas être annulée. Toutes les données associées seront perdues définitivement.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>Annuler
                </button>
                <button onclick="confirmDelete()" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg">
                    <span id="delete-confirm-text">
                        <i class="fas fa-trash mr-2"></i>Supprimer
                    </span>
                    <span id="delete-confirm-loading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Suppression...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de visualisation des détails -->
<div id="detailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-white/20">
        <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-3 rounded-xl mr-3">
                            <i class="fas fa-eye text-white"></i>
                        </div>
                        <span id="details-title">Détails</span>
                    </h3>
                    <p class="text-gray-600 mt-1" id="details-subtitle">Informations complètes</p>
                </div>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="p-6">
            <div id="details-content" class="space-y-6">
                <!-- Le contenu sera injecté dynamiquement -->
            </div>
        </div>

        <div class="bg-gray-50 p-6 border-t border-gray-200 flex justify-end space-x-3">
            <button onclick="closeDetailsModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                <i class="fas fa-times mr-2"></i>Fermer
            </button>
            <button onclick="exportDetails()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl hover:from-blue-700 hover:to-indigo-800 transition-all duration-200 shadow-lg">
                <i class="fas fa-download mr-2"></i>Exporter
            </button>
        </div>
    </div>
</div>

<!-- Modal d'édition d'utilisateur -->
<div id="editUserModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-white/20">
        <div class="bg-gradient-to-r from-green-50 via-emerald-50 to-green-50 p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <div class="bg-gradient-to-br from-green-500 to-emerald-600 p-2 rounded-lg mr-3">
                            <i class="fas fa-user-edit text-white"></i>
                        </div>
                        Modifier l'Utilisateur
                    </h3>
                    <p class="text-gray-600 mt-1">Modifiez les informations de l'utilisateur</p>
                </div>
                <button onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form id="editUserForm" class="p-6 space-y-6">
            <input type="hidden" id="edit_user_id" name="user_id">

            <!-- Informations personnelles -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    Informations personnelles
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                        <input type="text" id="edit_username" name="username" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse email</label>
                        <input type="email" id="edit_email" name="email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Rôle et permissions -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-shield-alt text-purple-600 mr-2"></i>
                    Rôle et permissions
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                        <select id="edit_role" name="role" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="user">👤 Utilisateur</option>
                            <option value="moderator">🛡️ Modérateur</option>
                            <option value="admin">👑 Administrateur</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut du compte</label>
                        <select id="edit_status_user" name="status" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="active">✅ Actif</option>
                            <option value="suspended">⏸️ Suspendu</option>
                            <option value="banned">🚫 Banni</option>
                            <option value="pending">⏳ En attente</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Options de vérification -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    Vérifications
                </h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Email vérifié</span>
                            <p class="text-xs text-gray-500">L'utilisateur a vérifié son adresse email</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="edit_email_verified" name="email_verified" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Profil certifié</span>
                            <p class="text-xs text-gray-500">Marquer ce profil comme certifié/vérifié</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="edit_certified" name="certified" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notes administratives -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-amber-600 mr-2"></i>
                    Notes administratives
                </h4>
                <textarea id="edit_admin_notes_user" name="admin_notes" rows="4" placeholder="Notes internes sur cet utilisateur..." class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeEditUserModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>Annuler
                </button>
                <button type="button" onclick="resetUserPassword()" class="px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-colors">
                    <i class="fas fa-key mr-2"></i>Reset MDP
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-700 text-white rounded-xl hover:from-green-700 hover:to-emerald-800 transition-all duration-200 shadow-lg">
                    <span id="edit-user-save-text">
                        <i class="fas fa-save mr-2"></i>Sauvegarder
                    </span>
                    <span id="edit-user-save-loading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Sauvegarde...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de traitement des demandes d'adhésion -->
<div id="adhesionModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto border border-white/20">
        <div class="bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 p-6 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <div class="bg-gradient-to-br from-orange-500 to-amber-600 p-2 rounded-lg mr-3">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                        <span id="adhesion-modal-title">Demande d'Adhésion</span>
                    </h3>
                    <p class="text-gray-600 mt-1">Examinez et traitez la demande d'adhésion</p>
                </div>
                <button onclick="closeAdhesionModal()" class="text-gray-400 hover:text-gray-600 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="p-6">
            <div id="adhesion-content" class="space-y-6">
                <!-- Le contenu sera injecté dynamiquement -->

                <!-- Template de contenu par défaut -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        Informations du demandeur
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <p id="adhesion-name" class="text-gray-900 font-medium">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <p id="adhesion-email" class="text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organisation</label>
                            <p id="adhesion-organization" class="text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rôle demandé</label>
                            <p id="adhesion-role" class="text-gray-900">-</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-comment text-green-600 mr-2"></i>
                        Motivation
                    </h4>
                    <div class="bg-white p-4 rounded-lg border">
                        <p id="adhesion-motivation" class="text-gray-700 whitespace-pre-wrap">-</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-paperclip text-purple-600 mr-2"></i>
                        Documents joints
                    </h4>
                    <div id="adhesion-documents" class="space-y-2">
                        <!-- Documents seront listés ici -->
                    </div>
                </div>

                <!-- Section de décision -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-gavel text-indigo-600 mr-2"></i>
                        Décision administrative
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire de décision</label>
                            <textarea id="adhesion-decision-comment" rows="4" placeholder="Ajoutez un commentaire expliquant votre décision..." class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle attribué (si approuvé)</label>
                            <select id="adhesion-assigned-role" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="user">👤 Utilisateur</option>
                                <option value="moderator">🛡️ Modérateur</option>
                                <option value="admin">👑 Administrateur</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-6 border-t border-gray-200 flex justify-between">
            <button onclick="closeAdhesionModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                <i class="fas fa-times mr-2"></i>Fermer
            </button>
            <div class="flex space-x-3">
                <button onclick="rejectAdhesion()" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg">
                    <i class="fas fa-times mr-2"></i>Rejeter
                </button>
                <button onclick="approveAdhesion()" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-700 text-white rounded-xl hover:from-green-700 hover:to-emerald-800 transition-all duration-200 shadow-lg">
                    <i class="fas fa-check mr-2"></i>Approuver
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de notification/succès -->
<div id="notificationModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-md w-full border border-white/20">
        <div class="p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div id="notification-icon" class="flex-shrink-0">
                    <!-- Icône sera injectée dynamiquement -->
                </div>
                <div>
                    <h3 id="notification-title" class="text-lg font-semibold text-gray-900">Notification</h3>
                    <p id="notification-message" class="text-gray-600 mt-1">Message de notification</p>
                </div>
            </div>

            <div class="flex justify-end">
                <button onclick="closeNotificationModal()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl hover:from-blue-700 hover:to-indigo-800 transition-all duration-200">
                    Compris
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Animations et styles personnalisés */
    .admin-section {
        display: none;
        animation: fadeInUp 0.5s ease-out;
    }

    .admin-section.active {
        display: block;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Effet de parallax pour le fond */
    body {
        background-attachment: fixed;
        background-size: cover;
    }
    </style>
    

    <script>
        // Variables globales
        let currentSection = '';
        let allLogs = [];
        let currentLogsPage = 1;
        const logsPerPage = 20;
        let databaseTables = [];
        let selectedTable = null;
        let allAdhesions = [];

        // Fonction pour afficher une section
        function showSection(sectionId) {
            if (currentSection === sectionId) return;

            // Masquer toutes les sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.add('hidden');
            });

            // Afficher la section demandée
            const section = document.getElementById(sectionId + '-section');
            if (section) {
                section.classList.remove('hidden');
                currentSection = sectionId;

                // Charger les données spécifiques à la section
                switch(sectionId) {
                    case 'users': loadUsers(); break;
                    case 'signalements': loadSignalements(); break;
                    case 'contacts': loadContacts(); break;
                    case 'logs': loadLogs(); break;
                    case 'database': loadDatabase(); break;
                }
            }
        }

        // Fonction pour charger les utilisateurs avec gestion d'erreurs
        function loadUsers() {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_users'
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('users-table-body');
                    tbody.innerHTML = '';

                    if (data.success && data.users && data.users.length > 0) {
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

                        tbody.innerHTML = data.users.map(user => {
                            const statusColor = user.is_active == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            const statusText = user.is_active == 1 ? 'Actif' : 'Non Actif';

                            return `
                    <tr class="hover:bg-gray-50 transition-colors">
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
                                ${user.is_verified == 1? 'Vérifié' : 'Non Vérifié'}
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
                    </tr>
                `;
                        }).join('');
                    } else {
                        // Message si aucun utilisateur
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
                    document.getElementById('users-table-body').innerHTML = `
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
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_signalements&filter=${filter}`
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('signalements-table-body');

                    if (data.success && data.signalements && data.signalements.length > 0) {
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

                        tbody.innerHTML = data.signalements.map(signalement => {
                            const personName = signalement.nom && signalement.prenom
                                ? `${signalement.prenom} ${signalement.nom}`
                                : (signalement.titre || 'Non spécifié');

                            return `
                    <tr class="hover:bg-gray-50 transition-colors">
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
                    </tr>
                `;
                        }).join('');
                    } else {
                        tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun signalement trouvé</td>
                </tr>
            `;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('signalements-table-body').innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-red-500">Erreur lors du chargement des signalements</td>
            </tr>
        `;
                });
        }

        // Fonction pour charger les messages de contact
        function loadContacts() {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_contacts'
            })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('contacts-table-body');

                    if (data.status === 'success' && data.contacts && data.contacts.length > 0) {
                        tbody.innerHTML = data.contacts.map(contact => {
                            const statusColor = contact.statut === 'nouveau' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800';
                            return `
                    <tr class="hover:bg-gray-50 transition-colors">
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
                    </tr>
                `;
                        }).join('');
                    } else {
                        tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun message de contact trouvé</td>
                </tr>
            `;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('contacts-table-body').innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-red-500">Erreur lors du chargement des contacts</td>
            </tr>
        `;
                });
        }

        // Fonction pour afficher les détails d'un signalement
        function viewSignalement(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_signalement_details&id=${id}`
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

                        document.body.insertAdjacentHTML('beforeend', createSignalementModalHTML(signalement, personName, preuvesSection));
                    } else {
                        showNotification('Erreur lors du chargement des détails', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur de connexion', 'error');
                });
        }

        // Fonction pour générer le HTML de la modal de signalement
        function createSignalementModalHTML(signalement, personName, preuvesSection) {
            return `
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
                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${
                signalement.incident_context === 'irl' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'
            }">
                                ${signalement.incident_context === 'irl' ? 'IRL' : 'Virtuel'}
                            </span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Priorité</h4>
                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${
                signalement.priorite === 'haute' ? 'bg-red-100 text-red-800' :
                    signalement.priorite === 'moyenne' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-green-100 text-green-800'
            }">
                                ${signalement.priorite}
                            </span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Statut</h4>
                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${
                signalement.statut === 'nouveau' ? 'bg-blue-100 text-blue-800' :
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
        }

        // Fonction pour éditer un signalement
        function editSignalement(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_signalement_details&id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remplir le formulaire avec les données
                        const signalement = data.signalement;
                        document.getElementById('edit_signalement_id').value = signalement.id;
                        document.getElementById('edit_titre').value = signalement.titre || '';
                        document.getElementById('edit_type_incident').value = signalement.type_incident;
                        document.getElementById('edit_incident_context').value = signalement.incident_context;
                        document.getElementById('edit_description').value = signalement.description;
                        document.getElementById('edit_priorite').value = signalement.priorite;
                        document.getElementById('edit_statut').value = signalement.statut;

                        if (signalement.nom) document.getElementById('edit_nom').value = signalement.nom;
                        if (signalement.prenom) document.getElementById('edit_prenom').value = signalement.prenom;
                        if (signalement.lieu) document.getElementById('edit_lieu').value = signalement.lieu;
                        if (signalement.plateforme) document.getElementById('edit_plateforme').value = signalement.plateforme;

                        // Afficher les fichiers existants
                        const existingFilesContainer = document.getElementById('existing_files_container');
                        if (existingFilesContainer) {
                            if (signalement.preuves && signalement.preuves.length > 0) {
                                const preuves = signalement.preuves;
                                existingFilesContainer.innerHTML = `
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            ${preuves.map(preuve => {
                                    const isImage = /\.(jpg|jpeg|png|gif)$/i.test(preuve.nom_fichier);
                                    return `
                                    <div class="relative border rounded-lg p-2 bg-white">
                                        ${isImage ?
                                        `<img src="${preuve.chemin_fichier}" alt="${preuve.nom_fichier}" class="w-full h-16 object-cover rounded cursor-pointer" onclick="openImageModal('${preuve.chemin_fichier}', '${preuve.nom_fichier}')">` :
                                        `<div class="w-full h-16 bg-gray-100 rounded flex items-center justify-center">
                                                <i class="fas fa-file text-lg text-gray-400"></i>
                                            </div>`
                                    }
                                        <p class="text-xs text-gray-600 mt-1 truncate" title="${preuve.nom_fichier}">${preuve.nom_fichier}</p>
                                        <button type="button" onclick="deleteExistingFile('${preuve.nom_fichier}', this)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
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
                        if (newFilePreview) newFilePreview.innerHTML = '';

                        // Fermer la modal de détails si elle est ouverte
                        closeModal();

                        // Ouvrir la modal d'édition
                        document.getElementById('editSignalementModal').classList.remove('hidden');

                    } else {
                        console.error('Erreur lors du chargement des détails:', data.message);
                        showNotification('Erreur lors du chargement des détails du signalement', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des détails du signalement:', error);
                    showNotification('Erreur lors du chargement des détails du signalement', 'error');
                });
        }

        // Fonction pour supprimer un fichier existant
        function deleteExistingFile(fileName, buttonElement) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette preuve ?')) {
                const signalementId = document.getElementById('edit_signalement_id').value;

                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete_signalement_file&signalement_id=${signalementId}&file_name=${fileName}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            buttonElement.closest('.relative').remove();
                            showNotification('Fichier supprimé avec succès', 'success');
                        } else {
                            showNotification('Erreur lors de la suppression du fichier: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        showNotification('Erreur lors de la suppression du fichier', 'error');
                    });
            }
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
                if (i !== index) dt.items.add(files[i]);
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

        // Fonction pour afficher les détails d'un contact
        function viewContact(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_contact&id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const contact = data.contact;

                        document.body.insertAdjacentHTML('beforeend', `
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
            `);
                    } else {
                        showNotification('Erreur lors du chargement du contact', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur lors du chargement du contact', 'error');
                });
        }

        // Fonction pour mettre à jour le statut d'un signalement
        function updateSignalementStatus(id, status) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=update_signalement_status&id=${id}&status=${status}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadSignalements();
                        showNotification('Statut mis à jour avec succès', 'success');
                    } else {
                        showNotification('Erreur lors de la mise à jour du statut', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur lors de la mise à jour du statut', 'error');
                });
        }

        // Fonction pour marquer un contact comme lu
        function markContactAsRead(id) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=mark_contact_read&id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        loadContacts();
                        showNotification('Contact marqué comme lu', 'success');
                    } else {
                        showNotification('Erreur lors de la mise à jour du contact', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur lors de la mise à jour du contact', 'error');
                });
        }

        // Fonction pour supprimer un signalement
        function deleteSignalement(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete_signalement&id=${id}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadSignalements();
                            showNotification('Signalement supprimé avec succès', 'success');
                        } else {
                            showNotification('Erreur lors de la suppression du signalement', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        showNotification('Erreur lors de la suppression du signalement', 'error');
                    });
            }
        }

        // Fonction pour supprimer un contact
        function deleteContact(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce message de contact ?')) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete_contact&id=${id}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadContacts();
                            showNotification('Contact supprimé avec succès', 'success');
                        } else {
                            showNotification('Erreur lors de la suppression du contact', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        showNotification('Erreur lors de la suppression du contact', 'error');
                    });
            }
        }

        // Fonction pour fermer les modales
        function closeModal() {
            document.querySelectorAll('.fixed.inset-0').forEach(modal => modal.remove());
        }

        // Fonction pour fermer la modal d'édition de signalement
        function closeEditSignalementModal() {
            document.getElementById('editSignalementModal').classList.add('hidden');
            document.getElementById('editSignalementForm').reset();
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
            setTimeout(() => notification.classList.remove('translate-x-full'), 10);

            // Suppression automatique
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Fonction pour ouvrir le modal de création d'utilisateur
        function openCreateUserModal() {
            document.body.insertAdjacentHTML('beforeend', `
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
    `);
        }

        // Fonction pour créer un utilisateur
        function createUser(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('action', 'create_user');

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
                        showNotification(data.message || 'Erreur lors de la création de l\'utilisateur', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur lors de la création de l\'utilisateur', 'error');
                });
        }

        // Fonction pour éditer un utilisateur
        function editUser(userId) {
            fetch('admin_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_user&id=${userId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user) {
                        const user = data.user;

                        document.body.insertAdjacentHTML('beforeend', `
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
            `);
                    } else {
                        showNotification('Erreur lors du chargement des données utilisateur', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur lors du chargement des données utilisateur', 'error');
                });
        }

        // Fonction pour mettre à jour un utilisateur
        function updateUser(event, userId) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('action', 'update_user');
            formData.append('user_id', userId);

            // Gérer la checkbox is_verified
            formData.set('is_verified', formData.has('is_verified') ? '1' : '0');

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
                        showNotification(data.message || 'Erreur lors de la modification de l\'utilisateur', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur lors de la modification de l\'utilisateur', 'error');
                });
        }

        // Fonction pour supprimer un utilisateur
        function deleteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
                fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete_user&user_id=${userId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success || data.status === 'success') {
                            loadUsers();
                            showNotification('Utilisateur supprimé avec succès', 'success');
                        } else {
                            showNotification(data.message || 'Erreur lors de la suppression de l\'utilisateur', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        showNotification('Erreur lors de la suppression de l\'utilisateur', 'error');
                    });
            }
        }

        // Fonctions pour les logs
        async function loadLogs() {
            try {
                const response = await fetch('admin_ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=get_logs'
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

            updateLogsPagination(filteredLogs.length);
        }

        function filterLogs() {
            const searchTerm = document.getElementById('log-search').value.toLowerCase();
            const startDate = document.getElementById('log-date-start').value;
            const endDate = document.getElementById('log-date-end').value;
            const logType = document.getElementById('log-type-filter').value;

            return allLogs.filter(log => {
                if (searchTerm && !log.content.toLowerCase().includes(searchTerm)) return false;
                if (startDate && log.date < startDate) return false;
                if (endDate && log.date > endDate) return false;

                if (logType !== 'all') {
                    if (logType === 'backup' && !log.content.includes('BACKUP')) return false;
                    if (logType === 'error' && !log.content.includes('ERROR')) return false;
                    if (logType === 'access' && (log.content.includes('BACKUP') || log.content.includes('ERROR'))) return false;
                }

                return true;
            });
        }

        function getLogClass(content) {
            if (content.includes('ERROR')) {
                return 'bg-red-900 border-l-4 border-red-500';
            }
            if (content.includes('BACKUP')) {
                return 'bg-blue-900 border-l-4 border-blue-500';
            }
            return 'bg-gray-800 border-l-4 border-gray-600';
        }

        function updateLogsStats(stats) {
            document.getElementById('total-logs').textContent = stats.total || 0;
            document.getElementById('today-logs').textContent = stats.today || 0;
            document.getElementById('hour-logs').textContent = stats.lastHour || 0;
            document.getElementById('log-size').textContent = stats.fileSize || '0 KB';
        }

        function updateLogsPagination(totalLogs) {
            const totalPages = Math.ceil(totalLogs / logsPerPage);
            const startIndex = (currentLogsPage - 1) * logsPerPage + 1;
            const endIndex = Math.min(currentLogsPage * logsPerPage, totalLogs);

            document.getElementById('logs-start').textContent = totalLogs > 0 ? startIndex : 0;
            document.getElementById('logs-end').textContent = endIndex;
            document.getElementById('logs-total').textContent = totalLogs;

            document.getElementById('prev-logs-btn').disable
    </script>
<?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include('../Inc/Traitement/create_log.php'); ?>
