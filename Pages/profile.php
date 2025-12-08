<?php
session_start();
require_once '../Inc/Constants/db.php';
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';

try {
    $conn = connect_db();
    if (!$conn) {
        throw new Exception('Impossible de se connecter à la base de données');
    }

    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $conn = connect_db();
        $req = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $req->execute([$_SESSION['user_id']]);
        $dataUser = $req->fetch(PDO::FETCH_ASSOC);
        
        if ($dataUser) {
            $user = $dataUser;
            $id = $dataUser['id'];
            $username = $dataUser['username'];
            $email = $dataUser['email'];
            $role = $dataUser['role'];
            $banner = $dataUser['banner'];
            $avatar = $dataUser['avatar'];
            $organization = $dataUser['organization'] ?? '';
            $accreditation = $dataUser['accreditation'] ?? '';
            $phone = $dataUser['phone'] ?? '';
            $address = $dataUser['address'] ?? '';
            $city = $dataUser['city'] ?? '';
            $bio = $dataUser['bio'] ?? '';
            $created_at = $dataUser['created_at'];
            $last_activity = $dataUser['last_activity'];
            $github = $dataUser['github']?? '';
            $linkedin = $dataUser['linkedin']?? '';
            $website = $dataUser['website']?? '';
            $active = $dataUser['is_active'] ?? 1;
            $verified = $dataUser['is_verified']??0;
            $is_public = $dataUser['is_public'] ?? 0;
            $blacklisted = $dataUser['is_blacklisted']??0;
        }
    } else if (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
        $cookieToken = $_COOKIE['user_token'];
        if (strlen($cookieToken) >= 32) {
            $conn = connect_db();
            $hashedToken = hash('sha256', $cookieToken);
            $req = $conn->prepare("SELECT * FROM users WHERE token = ? AND (token_expiry IS NULL OR token_expiry > datetime('now'))");
            $req->execute([$hashedToken]);
            $dataUser = $req->fetch(PDO::FETCH_ASSOC);
            
            if ($dataUser) {
                // Token valide, restaurer la session
                session_start();
                $_SESSION['user_id'] = $dataUser['id'];
                $_SESSION['user_email'] = $dataUser['email'];
                $_SESSION['user_username'] = $dataUser['username'];
                $_SESSION['user_role'] = $dataUser['role'];
                $_SESSION['user_avatar'] = $dataUser['avatar'];
                $_SESSION['user_active'] = $dataUser['is_active'];
                $user = $dataUser;
                $id = $dataUser['id'];
                $username = $dataUser['username'];
                $email = $dataUser['email'];
                $role = $dataUser['role'];
                $avatar = $dataUser['avatar'];
                $banner = $dataUser['banner'];
                $organization = $dataUser['organization'] ?? '';
                $accreditation = $dataUser['accreditation'] ?? '';
                $phone = $dataUser['phone'] ?? '';
                $address = $dataUser['address'] ?? '';
                $city = $dataUser['city'] ?? '';
                $bio = $dataUser['bio'] ?? '';
                $created_at = $dataUser['created_at'];
                $last_activity = $dataUser['last_activity'];
                $github = $dataUser['github']?? '';
                $linkedin = $dataUser['linkedin']?? '';
                $website = $dataUser['website']?? '';
                $active = $dataUser['is_active']?? 1;
                $verified = $dataUser['is_verified']??0;
                $blacklisted = $dataUser['is_blacklisted']??0;
                $is_public = $dataUser['is_public'] ?? 0;
                $token = $cookieToken;
            } else {
                setcookie('user_token', '', time() - 3600, '/');
                setcookie('user_pseudo', '', time() - 3600, '/');
                header('Location: login.php');
                exit;
            }
        }
    } else {
        header('Location: login.php');
        exit;
    }
 
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../index.php');
    exit;
}

// Fonction pour obtenir le badge de rôle
function getRoleBadge($role) {
    $badges = [
        'admin' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-crown mr-1"></i>Administrateur</span>',
        'moderator' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-shield-alt mr-1"></i>Modérateur</span>',
        'developer' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-code mr-1"></i>Développeur</span>',
        'opj' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"><i class="fas fa-badge mr-1"></i>OPJ</span>',
        'avocat' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-balance-scale mr-1"></i>Avocat</span>',
        'journaliste' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-newspaper mr-1"></i>Journaliste</span>',
        'magistrat' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-gavel mr-1"></i>Magistrat</span>',
        'psychologue' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800"><i class="fas fa-brain mr-1"></i>Psychologue</span>',
        'association' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800"><i class="fas fa-hands-helping mr-1"></i>Association</span>',
        'rgpd' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"><i class="fas fa-user-shield mr-1"></i>RGPD</span>',
        'user' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-user mr-1"></i>Utilisateur</span>'
    ];
    return $badges[$role] ?? $badges['user'];
}
?>


<style>
/* Animations personnalisées */
@keyframes profileFloat {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(1deg); }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
    50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6); }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.profile-card {
    animation: fadeInScale 0.6s ease-out;
}

.stat-card {
    animation: slideInUp 0.8s ease-out;
}

.action-button {
    position: relative;
    overflow: hidden;
}

.action-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.action-button:hover::before {
    left: 100%;
}

.glassmorphism-enhanced {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
}

.profile-avatar {
    animation: profileFloat 6s ease-in-out infinite;
}

.shimmer-effect {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}



.interactive-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.interactive-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
}

.status-indicator {
    position: relative;
}

.status-indicator::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    background: #10B981;
    border-radius: 50%;
    border: 2px solid white;
    animation: pulse-glow 2s infinite;
}

.profile-section {
    animation: slideInUp 0.6s ease-out;
    animation-fill-mode: both;
}

.profile-section:nth-child(1) { animation-delay: 0.1s; }
.profile-section:nth-child(2) { animation-delay: 0.2s; }
.profile-section:nth-child(3) { animation-delay: 0.3s; }
</style>

<!-- Background amélioré avec particules animées -->
<div class="min-h-screen relative overflow-hidden">
    <!-- Fond avec gradient France et particules -->
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 opacity-10"></div>
    <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-32 h-32 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"></div>
        <div class="absolute top-40 right-20 w-24 h-24 bg-indigo-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse animation-delay-2000"></div>
        <div class="absolute bottom-20 left-20 w-28 h-28 bg-white rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse animation-delay-4000"></div>
        <div class="absolute bottom-40 right-10 w-20 h-20 bg-blue-600 rounded-full mix-blend-multiply filter blur-xl opacity-25 animate-pulse animation-delay-1000"></div>
    </div>
    
    <div class="relative z-10 container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profil principal avec design premium -->
            <div class="lg:col-span-2 space-y-8">
                <!-- En-tête du profil avec glassmorphism avancé -->
                <div class="profile-card glassmorphism-enhanced rounded-3xl overflow-hidden interactive-card">
                    <!-- Bannière avec overlay gradient -->
                    <div class="relative h-48 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600">
                        <?php if ($banner): ?>
                            <img src="../Assets/Images/banners/<?= htmlspecialchars($banner)?>" alt="Bannière" class="w-full h-full object-cover">
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                        
                        <!-- Bouton d'édition flottant -->
                        <button onclick="openEditProfileModal()" 
                                class="absolute top-4 right-4 bg-white/20 backdrop-blur-md text-white p-3 rounded-full hover:bg-white/30 transition-all duration-300 group">
                            <i class="fas fa-edit group-hover:rotate-12 transition-transform duration-300"></i>
                        </button>
                    </div>
                    
                    <!-- Contenu du profil -->
                    <div class="relative p-8">
                        <!-- Avatar avec effet de flottement -->
                        <div class="absolute -top-16 left-8">
                            <div class="profile-avatar status-indicator">
                                <?php if ($avatar): ?>
                                    <img src="../Assets/Images/avatars/<?= htmlspecialchars($avatar)?>" alt="Avatar" 
                                         class="w-32 h-32 rounded-full border-4 border-white shadow-2xl object-cover">
                                <?php else: ?>
                                    <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full border-4 border-white shadow-2xl flex items-center justify-center">
                                        <i class="fas fa-user text-white text-4xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Informations utilisateur -->
                        <div class="pt-20">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                                <div>
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($username) ?></h1>
                                        <?php if ($verified): ?>
                                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-3 py-1 rounded-full text-sm font-semibold flex items-center space-x-1 shadow-lg">
                                                <i class="fas fa-check-circle"></i>
                                                <span>CERTIFIÉ</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4 text-gray-600 mb-4">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-envelope text-blue-500"></i>
                                            <span><?= htmlspecialchars($email) ?></span>
                                        </div>
                                        <?php if ($organization): ?>
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-building text-purple-500"></i>
                                                <span><?= htmlspecialchars($organization) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Badge de rôle avec design amélioré -->
                                    <div class="mb-4">
                                        <?= getRoleBadge($user['access_level'] ?? 'basic') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section À propos avec design moderne -->
                            <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl p-6 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                    <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-2">
                                        <i class="fas fa-user text-white text-xs"></i>
                                    </div>
                                    À propos
                                </h3>
                                <?php if ($bio): ?>
                                    <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($bio)) ?></p>
                                <?php else: ?>
                                    <div class="text-center py-6">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-plus text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="text-gray-500 italic mb-3">Aucune biographie ajoutée.</p>
                                        <button onclick="openEditProfileModal()" 
                                                class="text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                            Ajouter une biographie
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Activité récente avec timeline -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-6 h-6 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mr-2">
                                        <i class="fas fa-clock text-white text-xs"></i>
                                    </div>
                                    Activité récente
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-3 p-3 bg-white/60 rounded-lg">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-sign-in-alt text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 text-sm">Dernière connexion</span>
                                            <div class="text-gray-800 font-medium"><?= $last_activity ? date('d/m/Y à H:i', strtotime($last_activity)) : 'Jamais' ?></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 p-3 bg-white/60 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-calendar-plus text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 text-sm">Membre depuis</span>
                                            <div class="text-gray-800 font-medium"><?= date('d/m/Y', strtotime($created_at)) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informations détaillées avec cartes interactives -->
                <div class="profile-section glassmorphism-enhanced rounded-3xl p-8 interactive-card">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-white text-sm"></i>
                        </div>
                        Informations détaillées
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Statut avec indicateur animé -->
                        <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl hover:from-emerald-100 hover:to-green-100 transition-all duration-300">
                            <div class="relative">
                                <i class="fas <?= $active ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-red-600' ?> text-2xl"></i>
                                <?php if ($active): ?>
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-400 rounded-full animate-ping"></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="text-gray-500 text-sm font-medium">Statut du compte</span>
                                <div class="<?= $is_active ? 'text-emerald-700' : 'text-red-700' ?> font-bold text-lg">
                                    <?= $active ? 'Actif' : 'Inactif' ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Visibilité -->
                        <?php if ($is_public): ?>
                            <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl hover:from-purple-100 hover:to-pink-100 transition-all duration-300">
                                <i class="fas fa-globe text-purple-600 text-2xl"></i>
                                <div>
                                    <span class="text-gray-500 text-sm font-medium">Visibilité</span>
                                    <div class="text-purple-700 font-bold text-lg">
                                        <?= $is_public == 1 ? 'Public' : 'Privé' ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Liens sociaux avec hover effects -->
                        <?php if ($github): ?>
                            <a href="<?= htmlspecialchars($github) ?>" target="_blank" 
                               class="flex items-center space-x-3 p-4 bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl hover:from-gray-100 hover:to-slate-100 transition-all duration-300 group">
                                <i class="fab fa-github text-gray-700 text-2xl group-hover:scale-110 transition-transform"></i>
                                <div>
                                    <span class="text-gray-500 text-sm font-medium">GitHub</span>
                                    <div class="text-gray-700 font-bold truncate"><?= htmlspecialchars($github) ?></div>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($linkedin): ?>
                            <a href="<?= htmlspecialchars($linkedin) ?>" target="_blank" 
                               class="flex items-center space-x-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl hover:from-blue-100 hover:to-indigo-100 transition-all duration-300 group">
                                <i class="fab fa-linkedin text-blue-600 text-2xl group-hover:scale-110 transition-transform"></i>
                                <div>
                                    <span class="text-gray-500 text-sm font-medium">LinkedIn</span>
                                    <div class="text-blue-700 font-bold truncate"><?= htmlspecialchars($linkedin) ?></div>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($website): ?>
                            <a href="<?= htmlspecialchars($website) ?>" target="_blank" 
                               class="flex items-center space-x-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl hover:from-green-100 hover:to-emerald-100 transition-all duration-300 group">
                                <i class="fas fa-globe text-green-600 text-2xl group-hover:scale-110 transition-transform"></i>
                                <div>
                                    <span class="text-gray-500 text-sm font-medium">Site web</span>
                                    <div class="text-green-700 font-bold truncate"><?= htmlspecialchars($website) ?></div>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar avec design premium -->
            <div class="space-y-8">
                <!-- Informations de contact avec animations -->
                <div class="profile-section glassmorphism-enhanced rounded-3xl p-6 interactive-card">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                            <i class="fas fa-address-card text-white text-sm"></i>
                        </div>
                        Contact
                    </h3>
                    <div class="space-y-4">
                        <?php if ($phone): ?>
                            <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-orange-50 to-red-50 rounded-xl hover:from-orange-100 hover:to-red-100 transition-all duration-300 group">
                                <i class="fas fa-phone text-orange-500 text-lg group-hover:rotate-12 transition-transform"></i>
                                <span class="text-gray-700 font-medium"><?= htmlspecialchars($phone) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($address): ?>
                            <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl hover:from-blue-100 hover:to-indigo-100 transition-all duration-300 group">
                                <i class="fas fa-map-marker-alt text-blue-500 text-lg group-hover:bounce transition-transform"></i>
                                <span class="text-gray-700 font-medium">
                                    <?= htmlspecialchars($address) ?>
                                    <?= $city ? ', ' . htmlspecialchars($city) : '' ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($accreditation): ?>
                            <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl hover:from-purple-100 hover:to-pink-100 transition-all duration-300 group">
                                <i class="fas fa-certificate text-purple-500 text-lg group-hover:rotate-12 transition-transform"></i>
                                <span class="text-gray-700 font-medium"><?= htmlspecialchars($accreditation) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$phone && !$address && !$accreditation): ?>
                            <div class="text-center py-6">
                                <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <i class="fas fa-plus text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-gray-500 text-sm italic mb-4">Aucune information de contact.</p>
                                <button onclick="openEditProfileModal()" 
                                        class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-300 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    Ajouter des informations
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Statistiques avec graphiques visuels améliorés -->
                <div class="profile-section glassmorphism-enhanced rounded-3xl p-6 interactive-card">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                            <i class="fas fa-chart-bar text-white text-sm"></i>
                        </div>
                        Statistiques
                    </h3>
                    <div class="space-y-4">
                        <div class="stat-card p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl hover:from-blue-100 hover:to-indigo-100 transition-all duration-300 group">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                                        <i class="fas fa-plus text-white text-sm"></i>
                                    </div>
                                    <span class="text-gray-700 font-medium">Signalements créés</span>
                                </div>
                                <span class="text-3xl font-bold text-blue-600 group-hover:scale-110 transition-transform">0</span>
                            </div>
                        </div>
                        
                        <div class="stat-card p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl hover:from-emerald-100 hover:to-green-100 transition-all duration-300 group">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg">
                                        <i class="fas fa-check text-white text-sm"></i>
                                    </div>
                                    <span class="text-gray-700 font-medium">Signalements traités</span>
                                </div>
                                <span class="text-3xl font-bold text-emerald-600 group-hover:scale-110 transition-transform">0</span>
                            </div>
                        </div>
                        
                        <div class="stat-card p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl hover:from-purple-100 hover:to-pink-100 transition-all duration-300 group">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shadow-lg">
                                        <i class="fas fa-shield-alt text-white text-sm"></i>
                                    </div>
                                    <span class="text-gray-700 font-medium">Niveau d'accès</span>
                                </div>
                                <span class="text-sm font-bold text-purple-600 bg-purple-100 px-3 py-1 rounded-full group-hover:scale-105 transition-transform">
                                    <?= ucfirst($user['access_level'] ?? 'basic') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides avec design premium et effets -->
                <div class="profile-section glassmorphism-enhanced rounded-3xl p-6 interactive-card">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                        Actions rapides
                    </h3>
                    <div class="space-y-4">
                        <button onclick="openChangePasswordModal()" 
                                class="action-button group w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-4 px-6 rounded-xl hover:from-orange-600 hover:to-red-600 transition-all duration-300 font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-2">
                            <div class="flex items-center justify-center space-x-3">
                                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span>Changer le mot de passe</span>
                            </div>
                        </button>
                        
                        <button onClick="fetchUserAccount()" 
                                class="action-button group w-full bg-gradient-to-r from-blue-500 to-indigo-500 text-white py-4 px-6 rounded-xl hover:from-blue-600 hover:to-indigo-600 transition-all duration-300 font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-2">
                            <div class="flex items-center justify-center space-x-3">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Exporter mes données</span>
                            </div>
                        </button>
                        
                        <button onClick="deleteAccount()" 
                                class="action-button group w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-4 px-6 rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-2">
                            <div class="flex items-center justify-center space-x-3">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Supprimer mon compte</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal unifié d'édition de profil et avatar -->
<div id="editProfileModal" class="hidden fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50 backdrop-blur-sm">
    <div class="relative top-4 mx-auto p-0 w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl max-h-[95vh] overflow-hidden">
        <!-- Header du modal avec gradient -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-edit text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Modifier le profil</h3>
                        <p class="text-blue-100 text-sm">Personnalisez vos informations</p>
                    </div>
                </div>
                <button onclick="closeEditProfileModal()" class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition-all">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
        </div>

        <!-- Contenu du modal -->
        <div class="bg-white rounded-b-xl shadow-2xl overflow-y-auto max-h-[calc(95vh-120px)]">
            <div class="p-6">
                <!-- Section Avatar avec design amélioré -->
                <div class="mb-8 p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-camera text-white text-sm"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800">Photo de profil</h4>
                    </div>
                    <div class="flex flex-col md:flex-row items-center space-y-6 md:space-y-0 md:space-x-8">
                        <!-- Avatar actuel avec effet hover -->
                        <div class="text-center group">
                            <div class="mx-auto w-28 h-28 relative">
                                <div id="currentAvatarPreview" class="w-full h-full rounded-full overflow-hidden border-4 border-white shadow-lg group-hover:shadow-xl transition-shadow">
                                    <?php if ($avatar && file_exists('../Assets/Images/avatars/' . $avatar)): ?>
                                        <img src="../Assets/Images/avatars/<?= htmlspecialchars($avatar) ?>" 
                                             alt="Avatar actuel" 
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center">
                                            <i class="fas fa-user text-white text-2xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center border-2 border-white">
                                    <i class="fas fa-camera text-white text-xs"></i>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 font-medium">Avatar actuel</p>
                        </div>
                        
                        <!-- Zone de upload stylisée -->
                        <div class="flex-1">
                            <label for="avatarFile" class="group flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-blue-300 rounded-xl cursor-pointer bg-gradient-to-br from-blue-50 to-white hover:from-blue-100 hover:to-blue-50 transition-all duration-300">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-cloud-upload-alt text-white text-lg"></i>
                                    </div>
                                    <p class="text-sm text-gray-700 text-center font-medium">
                                        <span class="text-blue-600">Choisir un nouvel avatar</span><br>
                                        <span class="text-xs text-gray-500">PNG, JPG ou JPEG (MAX. 10MB)</span>
                                    </p>
                                </div>
                                <input id="avatarFile" type="file" class="hidden" accept="image/*" onchange="previewAvatar(this)">
                            </label>
                        </div>
                        
                        <!-- Aperçu du nouvel avatar -->
                        <div id="newAvatarPreview" class="hidden text-center">
                            <div class="mx-auto w-28 h-28 relative">
                                <div class="w-full h-full rounded-full overflow-hidden border-4 border-green-200 shadow-lg">
                                    <img id="newAvatarImg" src="" alt="Nouvel avatar" class="w-full h-full object-cover">
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <p class="text-sm text-green-600 mt-2 font-medium">Nouvel avatar</p>
                        </div>
                    </div>
                </div>

                <!-- Section Bannière avec design amélioré -->
                <div class="mb-8 p-6 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-image text-white text-sm"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800">Bannière</h4>
                    </div>
                    <div class="flex flex-col md:flex-row items-center space-y-6 md:space-y-0 md:space-x-8">
                        <!-- Bannière actuelle -->
                        <div class="text-center group">
                            <div class="mx-auto w-32 h-20 relative">
                                <div id="currentBannerPreview" class="w-full h-full rounded-lg overflow-hidden border-4 border-white shadow-lg group-hover:shadow-xl transition-shadow">
                                    <?php if ($banner && file_exists('../Assets/Images/banners/'. $banner)):?>
                                        <img src="../Assets/Images/banners/<?= htmlspecialchars($banner)?>" 
                                             alt="Bannière actuelle" 
                                             class="w-full h-full object-cover">
                                    <?php else:?>
                                        <div class="w-full h-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                                            <i class="fas fa-image text-white text-xl"></i>
                                        </div>
                                    <?php endif;?>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 font-medium">Bannière actuelle</p>
                        </div>

                        <!-- Zone de upload bannière -->
                        <div class="flex-1">
                            <label for="bannerFile" class="group flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-purple-300 rounded-xl cursor-pointer bg-gradient-to-br from-purple-50 to-white hover:from-purple-100 hover:to-purple-50 transition-all duration-300">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-cloud-upload-alt text-white text-lg"></i>
                                    </div>
                                    <p class="text-sm text-gray-700 text-center font-medium">
                                        <span class="text-purple-600">Choisir une nouvelle bannière</span><br>
                                        <span class="text-xs text-gray-500">PNG, JPG ou JPEG (MAX. 2MB)</span>
                                    </p>
                                </div>
                                <input id="bannerFile" type="file" class="hidden" accept="image/*" onchange="previewBanner(this)">
                            </label>
                        </div>

                        <!-- Aperçu nouvelle bannière -->
                        <div id="newBannerPreview" class="hidden text-center">
                            <div class="mx-auto w-32 h-20 relative">
                                <div class="w-full h-full rounded-lg overflow-hidden border-4 border-green-200 shadow-lg">
                                    <img id="newBannerImg" src="" alt="Nouvelle bannière" class="w-full h-full object-cover">
                                </div>
                            </div>
                            <p class="text-sm text-green-600 mt-2 font-medium">Nouvelle bannière</p>
                        </div>
                    </div>
                </div>

                <!-- Formulaire avec sections organisées -->
                <form id="editProfileForm" class="space-y-6">
                    <!-- Informations de base -->
                    <div class="bg-gray-50 p-6 rounded-xl">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-gray-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Informations de base</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom d'utilisateur</label>
                                <input type="text" id="editUsername" value="<?= htmlspecialchars($username) ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input type="email" id="editEmail" value="<?= htmlspecialchars($email) ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Biographie</label>
                            <textarea id="editBio" rows="4" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all resize-none"
                                      placeholder="Parlez-nous de vous..."><?= htmlspecialchars($bio) ?></textarea>
                        </div>
                    </div>

                    <!-- Informations de contact -->
                    <div class="bg-green-50 p-6 rounded-xl">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-phone text-white text-sm"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Informations de contact</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                                <input type="tel" id="editPhone" value="<?= htmlspecialchars($phone) ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Organisation</label>
                                <input type="text" id="editOrganization" value="<?= htmlspecialchars($organization) ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                                <input type="text" id="editAddress" value="<?= htmlspecialchars($address) ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Ville</label>
                                <input type="text" id="editCity" value="<?= htmlspecialchars($city) ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Informations professionnelles -->
                    <div class="bg-yellow-50 p-6 rounded-xl">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-briefcase text-white text-sm"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Informations professionnelles</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Accréditation</label>
                            <input type="text" id="editAccreditation" value="<?= htmlspecialchars($accreditation) ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all"
                                   placeholder="Numéro d'accréditation, certification...">
                        </div>
                    </div>

                      <!-- Paramètres de confidentialité -->
                      <div class="bg-red-50 p-6 rounded-xl">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-white text-sm"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Confidentialité</h4>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-red-200">
                                <div class="flex-1">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Profil public</label>
                                    <p class="text-sm text-gray-600">Permettre aux autres utilisateurs de voir votre profil complet</p>
                                </div>
                                <div class="flex items-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="editIsPublic" <?= $is_public ? 'checked' : '' ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                                    </label>
                                </div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                                    <div>
                                        <h5 class="text-sm font-semibold text-blue-800 mb-1">À propos de la visibilité</h5>
                                        <ul class="text-xs text-blue-700 space-y-1">
                                            <li>• <strong>Profil public :</strong> Votre profil est visible par tous les utilisateurs</li>
                                            <li>• <strong>Profil privé :</strong> Seuls les administrateurs peuvent voir votre profil complet</li>
                                            <li>• Votre nom d'utilisateur reste toujours visible dans les discussions</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Réseaux sociaux -->
                    <div class="bg-indigo-50 p-6 rounded-xl">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-share-alt text-white text-sm"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Réseaux sociaux</h4>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Site web</label>
                                <input type="url" id="editWebsite" value="<?= htmlspecialchars($website)?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                       placeholder="https://votre-site.com">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">GitHub</label>
                                <input type="url" id="editGithub" value="<?= htmlspecialchars($github)?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                       placeholder="https://github.com/username">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">LinkedIn</label>
                                <input type="url" id="editLinkedIn" value="<?= htmlspecialchars($linkedin)?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                       placeholder="https://linkedin.com/in/username">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer avec boutons -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-4">
                <button type="button" onclick="closeEditProfileModal()"
                        class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-medium">
                    <i class="fas fa-times mr-2"></i>Annuler
                </button>
                <button type="submit" form="editProfileForm"
                        class="px-6 py-3 bg-gradient-to-r from-france-blue to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all font-medium shadow-lg">
                    <i class="fas fa-save mr-2"></i>Sauvegarder tout
                </button>
            </div>
        </div>
</div>
    </div>
<!-- Modal de changement de mot de passe -->
<div id="changePasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl transform transition-all duration-300 scale-95 hover:scale-100">
            <!-- Header moderne -->
            <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-t-3xl p-8">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 via-purple-600/20 to-indigo-700/20 rounded-t-3xl"></div>
                <div class="relative flex items-center justify-between text-white">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold">Sécurité</h3>
                            <p class="text-white/80 text-sm">Modifier votre mot de passe</p>
                        </div>
                    </div>
                    <button onclick="closeChangePasswordModal()" class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-all duration-200 backdrop-blur-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Contenu du modal -->
            <div class="p-8">
                <form id="changePasswordForm" class="space-y-6">
                    <!-- Mot de passe actuel -->
                    <div class="space-y-3">
                        <label for="currentPassword" class="block text-sm font-semibold text-gray-800">
                            <span class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Mot de passe actuel</span>
                            </span>
                        </label>
                        <div class="relative group">
                            <input type="password" id="currentPassword" name="current_password" required
                                   class="w-full px-4 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-800 placeholder-gray-400"
                                   placeholder="••••••••••••">
                            <button type="button" onclick="togglePasswordVisibility('currentPassword')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Nouveau mot de passe -->
                    <div class="space-y-3">
                        <label for="newPassword" class="block text-sm font-semibold text-gray-800">
                            <span class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span>Nouveau mot de passe</span>
                            </span>
                        </label>
                        <div class="relative group">
                            <input type="password" id="newPassword" name="new_password" required
                                   class="w-full px-4 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-800 placeholder-gray-400"
                                   placeholder="••••••••••••">
                            <button type="button" onclick="togglePasswordVisibility('newPassword')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <!-- Indicateur de force amélioré -->
                        <div id="passwordStrength" class="hidden">
                            <div class="flex space-x-1 mt-3">
                                <div class="h-2 flex-1 rounded-full bg-gray-200 overflow-hidden">
                                    <div id="strengthBar" class="h-full rounded-full transition-all duration-500 ease-out"></div>
                                </div>
                            </div>
                            <p id="strengthText" class="text-xs mt-2 font-medium"></p>
                        </div>
                    </div>

                    <!-- Confirmation du nouveau mot de passe -->
                    <div class="space-y-3">
                        <label for="confirmPassword" class="block text-sm font-semibold text-gray-800">
                            <span class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Confirmer le nouveau mot de passe</span>
                            </span>
                        </label>
                        <div class="relative group">
                            <input type="password" id="confirmPassword" name="confirm_password" required
                                   class="w-full px-4 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-800 placeholder-gray-400"
                                   placeholder="••••••••••••">
                            <button type="button" onclick="togglePasswordVisibility('confirmPassword')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="passwordMatch" class="text-xs font-medium hidden"></div>
                    </div>

                    <!-- Boutons d'action modernes -->
                    <div class="flex space-x-4 pt-6">
                        <button type="button" onclick="closeChangePasswordModal()"
                                class="flex-1 px-6 py-4 bg-gray-100 text-gray-700 rounded-2xl hover:bg-gray-200 transition-all duration-200 font-semibold border-2 border-transparent hover:border-gray-300">
                            <span class="flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Annuler</span>
                            </span>
                        </button>
                        <button type="submit"
                                class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white rounded-2xl hover:from-blue-700 hover:via-purple-700 hover:to-indigo-800 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <span class="flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Confirmer</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>



<script>
// Fonctions pour le modal unifié
function openEditProfileModal() {
    document.getElementById('editProfileModal').classList.remove('hidden');
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').classList.add('hidden');
    // Reset avatar preview
    document.getElementById('newAvatarPreview').classList.add('hidden');
    document.getElementById('avatarFile').value = '';
}




// Fonction pour prévisualiser l'avatar
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('newAvatarImg').src = e.target.result;
            document.getElementById('newAvatarPreview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function deleteAccount() {
    if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
        // Créer un formulaire pour la soumission
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'profile_ajax.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_account';
        
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// fonction pour recuper les donnees du compte 
function fetchUserAccount() {
    if (confirm("Souhaitez-vous récupérer vos données de compte ?")) {
        const formData = new FormData();
        formData.append('action', 'fetch_user_account');

        fetch('profile_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
           if (data.success) {
               // Formatage amélioré des données
               const userData = data.user;
               const formattedData = `=== DONNÉES DE COMPTE SIGNALE FRANCE ===\n\n` +
                   `📧 Email: ${userData.email || 'Non renseigné'}\n` +
                   `👤 Nom d'utilisateur: ${userData.username || 'Non renseigné'}\n` +
                   `📅 Date de création: ${userData.created_at ? new Date(userData.created_at).toLocaleDateString('fr-FR') : 'Non renseignée'}\n` +
                   `🔐 Dernière connexion: ${userData.last_login ? new Date(userData.last_login).toLocaleDateString('fr-FR') : 'Non renseignée'}\n` +
                   `📊 Statut: ${userData.status || 'Actif'}\n` +
                   `🎭 Rôle: ${userData.role || 'Utilisateur'}\n\n` +
                   `=== INFORMATIONS TECHNIQUES ===\n` +
                   `🆔 ID utilisateur: ${userData.id || 'Non disponible'}\n` +
                   `📱 Avatar: ${userData.avatar ? 'Configuré' : 'Non configuré'}\n\n` +
                   `=== DONNÉES BRUTES (JSON) ===\n` +
                   JSON.stringify(userData, null, 2) +
                   `\n\n=== FIN DU FICHIER ===\n` +
                   `Fichier généré le: ${new Date().toLocaleString('fr-FR')}`;

               const blob = new Blob([formattedData], {type: 'text/plain; charset=utf-8'});
               const url = URL.createObjectURL(blob);
               const link = document.createElement('a');
               link.href = url;
               link.download = `donnees_compte_${userData.username || 'utilisateur'}_${new Date().toISOString().split('T')[0]}.txt`;
               link.click();
               URL.revokeObjectURL(url);
               
               // Message de confirmation
               alert('✅ Vos données de compte ont été téléchargées avec succès !');
           } else {
               alert('❌ Erreur: ' + data.message);
           }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('❌ Erreur de communication avec le serveur');
        });
    }
}

// Fonction pour prévisualiser la nouvelle bannière
function previewBanner(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Vérifier la taille du fichier (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('La taille du fichier ne doit pas dépasser 10MB.');
            input.value = '';
            return;
        }
        // Vérifier le type de fichier
        if (!file.type.match('image.*')) {
            alert('Veuillez sélectionner un fichier image valide.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            // Afficher l'aperçu de la nouvelle bannière
            document.getElementById('newBannerImg').src = e.target.result;
            document.getElementById('newBannerPreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

// Fonctions pour le modal de changement de mot de passe
function openChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    // Réinitialiser le formulaire
    document.getElementById('changePasswordForm').reset();
    document.getElementById('passwordStrength').classList.add('hidden');
    document.getElementById('passwordMatch').classList.add('hidden');
}

// Fonction pour basculer la visibilité du mot de passe
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        button.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
            </svg>
        `;
    } else {
        input.type = 'password';
        button.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
        `;
    }
}

// Vérification de la force du mot de passe
function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (password.length === 0) {
        strengthIndicator.classList.add('hidden');
        return;
    }
    
    strengthIndicator.classList.remove('hidden');
    
    let score = 0;
    let feedback = [];
    
    // Critères de force
    if (password.length >= 8) score++;
    else feedback.push('Au moins 8 caractères');
    
    if (/[a-z]/.test(password)) score++;
    else feedback.push('Une minuscule');
    
    if (/[A-Z]/.test(password)) score++;
    else feedback.push('Une majuscule');
    
    if (/\d/.test(password)) score++;
    else feedback.push('Un chiffre');
    
    if (/[^\w\s]/.test(password)) score++;
    else feedback.push('Un caractère spécial');
    
    // Mise à jour visuelle
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
    const texts = ['Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'];
    const textColors = ['text-red-600', 'text-orange-600', 'text-yellow-600', 'text-blue-600', 'text-green-600'];
    
    strengthBar.className = `h-full rounded-full transition-all duration-300 ${colors[score - 1] || 'bg-gray-300'}`;
    strengthBar.style.width = `${(score / 5) * 100}%`;
    
    strengthText.className = `text-xs mt-1 ${textColors[score - 1] || 'text-gray-500'}`;
    strengthText.textContent = score > 0 ? texts[score - 1] : 'Entrez un mot de passe';
    
    if (feedback.length > 0 && score < 5) {
        strengthText.textContent += ` - Manque: ${feedback.join(', ')}`;
    }
}

// Vérification de la correspondance des mots de passe
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchIndicator = document.getElementById('passwordMatch');
    
    if (confirmPassword.length === 0) {
        matchIndicator.classList.add('hidden');
        return;
    }
    
    matchIndicator.classList.remove('hidden');
    
    if (newPassword === confirmPassword) {
        matchIndicator.textContent = '✓ Les mots de passe correspondent';
        matchIndicator.className = 'text-xs text-green-600';
    } else {
        matchIndicator.textContent = '✗ Les mots de passe ne correspondent pas';
        matchIndicator.className = 'text-xs text-red-600';
    }
}

// Gestionnaire de soumission du formulaire de changement de mot de passe
async function changePassword() {
    const form = document.getElementById('changePasswordForm');
    const formData = new FormData(form);
    formData.append('action', 'update_password');
    
    try {
        const response = await fetch('profile_ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Mot de passe changé avec succès !');
            closeChangePasswordModal();
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur de communication avec le serveur');
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Vérification de la force du mot de passe en temps réel
    document.getElementById('newPassword').addEventListener('input', function() {
        checkPasswordStrength(this.value);
        checkPasswordMatch();
    });
    
    // Vérification de la correspondance en temps réel
    document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
    
    // Gestionnaire de soumission du formulaire
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword !== confirmPassword) {
            alert('Les mots de passe ne correspondent pas');
            return;
        }
        
        if (newPassword.length < 8) {
            alert('Le mot de passe doit contenir au moins 8 caractères');
            return;
        }
        
        changePassword();
    });
    
    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('changePasswordModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeChangePasswordModal();
        }
    });
    
    // Fermer avec la touche Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('changePasswordModal').classList.contains('hidden')) {
            closeChangePasswordModal();
        }
    });
});
// Gestionnaire de soumission unifié
document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    
    // Ajouter les données du profil
    formData.append('action', 'update_profile');
    formData.append('username', document.getElementById('editUsername').value);
    formData.append('email', document.getElementById('editEmail').value);
    formData.append('bio', document.getElementById('editBio').value);
    formData.append('phone', document.getElementById('editPhone').value);
    formData.append('organization', document.getElementById('editOrganization').value);
    formData.append('address', document.getElementById('editAddress').value);
    formData.append('city', document.getElementById('editCity').value);
    formData.append('accreditation', document.getElementById('editAccreditation').value);
    formData.append('website', document.getElementById('editWebsite').value);
    formData.append('github', document.getElementById('editGithub').value);
    formData.append('linkedin', document.getElementById('editLinkedIn').value);
    formData.append('is_public', document.getElementById('editIsPublic').checked ? '1' : '0');
    
    // Ajouter l'avatar s'il y en a un
    const avatarFile = document.getElementById('avatarFile').files[0];
    if (avatarFile) {
        formData.append('avatar', avatarFile);
        formData.append('update_avatar', 'true');
    }

    const bannerFile = document.getElementById('bannerFile').files[0];
    if (bannerFile) {
    formData.append('banner', bannerFile);
    formData.append('update_banner', 'true');
}
    
    try {
        const response = await fetch('profile_ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Profil mis à jour avec succès!');
            location.reload(); // Recharger pour voir les changements
        } else {
            alert('Erreur: ' + result.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de la mise à jour.');
    }
});

// Fermer le modal en cliquant à l'extérieur
document.getElementById('editAvatarModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditAvatarModal();
    }
});

// Fermer le modal en cliquant à l'extérieur
document.getElementById('editProfileModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditProfileModal();
    }
});
</script>

<?php require_once '../Inc/Components/footers.php'; ?>
<?php require_once '../Inc/Components/footer.php'; ?>
<?php include('../Inc/Traitement/create_log.php'); ?>