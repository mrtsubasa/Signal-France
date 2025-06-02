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

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header du profil -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="relative">
                <!-- Bannière de couverture -->
                <?php if ($banner && file_exists('../Assets/Images/banners/'. $banner)):?>
                    <img src="../Assets/Images/banners/<?= htmlspecialchars($banner)?>"
                         alt="Bannière de couverture"
                         class="w-full h-48 object-cover rounded-t-lg">
                <?php else:?>
                    <div class="h-48 bg-gradient-to-r from-france-blue to-blue-600 rounded-t-lg"></div>
                <?php endif;?>

                
                <!-- Photo de profil et informations principales -->
                <div class="relative px-6 pb-6">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:space-x-6">
                        <!-- Avatar -->
                        <div class="-mt-16 relative">
                            <div class="w-32 h-32 bg-white rounded-full p-2 shadow-lg">
                                <?php if ($avatar && file_exists('../Assets/Images/avatars/' . $avatar)): ?>
                                    <img src="../Assets/Images/avatars/<?= htmlspecialchars($avatar) ?>" 
                                         alt="Avatar" 
                                         class="w-full h-full rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full rounded-full bg-france-blue flex items-center justify-center">
                                        <i class="fas fa-user text-white text-4xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                             <!-- Icon Verif-->
                             <?php if ($verified):?>
    <div class="absolute bottom-0 right-0 w-6 h-6 bg-gradient-to-br from-blue-800 to-blue-950 rounded-full shadow-lg border-2 border-white flex items-center justify-center">
        <i class="fas fa-check-circle text-white text-xs"></i>
    </div>
<?php endif;?>
                        </div>
                        
                        <!-- Informations utilisateur -->
                        <div class="mt-4 sm:mt-0 flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                                        <?= htmlspecialchars($username) ?>
                                        <span class="ml-3"><?= getRoleBadge($role) ?></span>
                                         <!-- Icon Active-->
                                         <?php if ($active):?>
                                            <span class="ml-3">
                                                <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition-colors">
                                                     <i class="fas fa-circle text-xs mr-1"></i>
                                                        <span class="ml-2 text-sm text-green-600 font-medium">Actif</span>
                                                 </div>
                                            </span>
                                        <?php endif;?>
                                         <!-- Blacklist -->
                                         <?php if ($blacklisted):?>
                                            <span class="ml-3 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 align-middle">
                                                <i class="fas fa-ban mr-1.5 text-red-600"></i>
                                                Blacklisté
                                            </span>
                                        <?php endif;?>
                                    </h1>
                                    <p class="text-lg text-gray-600 mt-1">
                                        <i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($email) ?>
                                    </p>
                                    <?php if ($organization): ?>
                                        <p class="text-gray-600 mt-1">
                                            <i class="fas fa-building mr-2"></i><?= htmlspecialchars($organization) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-4 sm:mt-0">
                                    <!-- Remplacer les deux boutons par un seul -->
                                    <button onclick="openEditProfileModal()" 
                                            class="bg-france-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2">
                                        <i class="fas fa-edit"></i>
                                        <span>Modifier le profil</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-8">
                <!-- À propos -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user-circle text-france-blue mr-2"></i>
                        À propos
                    </h2>
                    <?php if ($bio): ?>
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($bio)) ?></p>
                    <?php else: ?>
                        <p class="text-gray-500 italic">Aucune biographie renseignée.</p>
                        <button onclick="openEditProfileModal()" 
                                class="mt-2 text-france-blue hover:text-blue-700 text-sm">
                            Ajouter une biographie
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Activité récente -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-clock text-france-blue mr-2"></i>
                        Activité récente
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sign-in-alt text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Dernière connexion</p>
                                <p class="text-xs text-gray-500">
                                    <?= $last_activity ? date('d/m/Y à H:i', strtotime($last_activity)) : 'Jamais' ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-plus text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Membre depuis</p>
                                <p class="text-xs text-gray-500">
                                    <?= date('d/m/Y', strtotime($created_at)) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Informations complémentaires -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-france-blue mr-2"></i>
                        Informations complémentaires
                    </h2>
                    <div class="space-y-4">
                        <?php if ($github):?>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-certificate text-gray-400 w-4"></i>
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($github)?></span>
                            </div>
                        <?php endif;?>

                        <?php if ($linkedin):?>
                            <div class="flex items-center space-x-3">
                                <i class="fab fa-linkedin text-gray-400 w-4"></i>
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($linkedin)?></span>
                            </div>
                        <?php endif;?>

                        <?php if ($website):?>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-globe text-gray-400 w-4"></i>
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($website)?></span>
                            </div>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Informations de contact -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-address-card text-france-blue mr-2"></i>
                        Informations de contact
                    </h3>
                    <div class="space-y-3">
                        <?php if ($phone): ?>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-phone text-gray-400 w-4"></i>
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($phone) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($address): ?>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-map-marker-alt text-gray-400 w-4"></i>
                                <span class="text-sm text-gray-700">
                                    <?= htmlspecialchars($address) ?>
                                    <?= $city ? ', ' . htmlspecialchars($city) : '' ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($accreditation): ?>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-certificate text-gray-400 w-4"></i>
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($accreditation) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        
                        <?php if (!$phone && !$address && !$accreditation): ?>
                            <p class="text-gray-500 text-sm italic">Aucune information de contact.</p>
                            <button onclick="openEditProfileModal()" 
                                    class="text-france-blue hover:text-blue-700 text-sm">
                                Ajouter des informations
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-france-blue mr-2"></i>
                        Statistiques
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Signalements créés</span>
                            <span class="text-lg font-semibold text-gray-900">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Signalements traités</span>
                            <span class="text-lg font-semibold text-gray-900">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Niveau d'accès</span>
                            <span class="text-sm font-medium text-france-blue"><?= ucfirst($user['access_level'] ?? 'basic') ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-bolt text-france-blue mr-2"></i>
                        Actions rapides
                    </h3>
                    <div class="space-y-3">
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors flex items-center">
                            <i class="fas fa-key mr-3 text-gray-400"></i>
                            Changer le mot de passe
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors flex items-center">
                            <i class="fas fa-download mr-3 text-gray-400"></i>
                            Exporter mes données
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors flex items-center">
                            <i class="fas fa-trash mr-3 text-red-400"></i>
                            Supprimer mon compte
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
        <div class="bg-gradient-to-r from-france-blue to-blue-700 text-white p-6 rounded-t-xl">
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
                                        <div class="w-full h-full bg-gradient-to-br from-france-blue to-blue-700 flex items-center justify-center">
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
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input type="email" id="editEmail" value="<?= htmlspecialchars($email) ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all">
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Biographie</label>
                            <textarea id="editBio" rows="4" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-france-blue focus:border-transparent transition-all resize-none"
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