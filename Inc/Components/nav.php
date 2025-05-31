<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Déterminer le chemin vers la racine depuis ce fichier
$rootPath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
include_once($rootPath . "Inc" . DIRECTORY_SEPARATOR . "Constants" . DIRECTORY_SEPARATOR . "db.php");
include_once($rootPath . "Inc" . DIRECTORY_SEPARATOR . "Constants" . DIRECTORY_SEPARATOR . "CookieManager.php");

// Initialisation des variables
$user = null;
$id = null;
$username = null;
$email = null;
$role = null;
$avatar = null;
$token = null;

try {
    // 1. Vérifier d'abord les sessions (priorité)
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
            $avatar = $dataUser['avatar'];
        }
    }
    // 2. Si pas de session, vérifier les cookies (connexion automatique)
    elseif (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
        $cookieToken = $_COOKIE['user_token'];
        
        // Validation basique du format du token
        if (strlen($cookieToken) >= 32) {
            $conn = connect_db();
            $hashedToken = hash('sha256', $cookieToken);
            
            // Requête corrigée avec token haché
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
                
                $user = $dataUser;
                $id = $dataUser['id'];
                $username = $dataUser['username'];
                $email = $dataUser['email'];
                $role = $dataUser['role'];
                $avatar = $dataUser['avatar'];
                $token = $cookieToken;
            } else {
                // Token invalide, nettoyer les cookies
                setcookie('user_token', '', time() - 3600, '/');
                setcookie('user_pseudo', '', time() - 3600, '/');
            }
        }
    }
    
    // Initialiser les variables d'avatar si utilisateur connecté
    if ($user) {
        $avatarDir = $rootPath . "Assets/Images/avatars/";
        $latestAvatar = null;
        
        // Chercher l'avatar le plus récent pour cet utilisateur
        if (is_dir($avatarDir)) {
            $files = glob($avatarDir . "avatar_" . $id . "_*");
            if (!empty($files)) {
                // Trier par date de modification (le plus récent en premier)
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $latestAvatar = basename($files[0]);
            }
        }
        
        $avatarFile = $latestAvatar ? $avatarDir . $latestAvatar : null;
        $defaultAvatar = $rootPath . "Assets/Images/default-avatar.png";
        $avatarname = $username . "'s Avatar";
    }
    
} catch (PDOException $e) {
    error_log("Database error in nav.php: " . $e->getMessage());
} catch (Exception $e) {
    error_log("General error in nav.php: " . $e->getMessage());
}

// Déterminer le chemin relatif pour les liens selon l'emplacement de la page courante
$currentPath = $_SERVER['REQUEST_URI'];
$isInSubfolder = (strpos($currentPath, '/Src/') !== false || strpos($currentPath, '/Pages/') !== false);
$basePath = $isInSubfolder ? '../' : './';
?>

<!-- Bande Marianne -->
<div class="w-full h-1 bg-gradient-to-r from-france-blue via-white to-france-red"></div>

<!-- Navigation principale -->
<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 lg:h-20">
            <!-- Logo et titre -->
            <div class="flex items-center space-x-4">
                <img src="<?php echo $basePath; ?>Assets/Images/alerte_france.png" 
                     alt="Alerte France Logo" 
                     class="h-10 w-10 lg:h-12 lg:w-12">
                <div class="hidden sm:block">
                    <h1 class="text-lg lg:text-xl font-bold text-france-blue">Signale France</h1>
                    <p class="text-xs lg:text-sm text-gray-600">Système national d'alerte</p>
                </div>
            </div>

            <!-- Menu desktop -->
            <div class="hidden lg:flex items-center space-x-6">
                <a href="<?php echo $basePath; ?>index.php" 
                   class="text-gray-700 hover:text-france-blue px-3 py-2 text-sm font-medium transition-colors">
                   Accueil
                </a>
            
                    <!-- Liens pour utilisateurs connectés -->
                    <a href="<?php echo $basePath; ?>Pages/search.php" 
                       class="text-gray-700 hover:text-france-blue px-3 py-2 text-sm font-medium transition-colors">
                       Recherche
                    </a>
                    <a href="<?php echo $basePath; ?>Pages/signal.php" 
                       class="text-gray-700 hover:text-france-blue px-3 py-2 text-sm font-medium transition-colors">
                       Signaler
                    </a>
                    <?php if (in_array($role, ['admin', 'moderator'])) { ?>
                        <a href="<?php echo $basePath; ?>Pages/admin.php" 
                           class="text-gray-700 hover:text-france-blue px-3 py-2 text-sm font-medium transition-colors">
                           <i class="fas fa-tachometer-alt mr-1"></i>Admin
                        </a>
                    <?php } ?>
                <a href="<?php echo $basePath; ?>Pages/guides.php" 
                   class="text-gray-700 hover:text-france-blue px-3 py-2 text-sm font-medium transition-colors">
                   Conseils
                </a>
                <a href="<?php echo $basePath; ?>Pages/contact.php" 
                   class="text-gray-700 hover:text-france-blue px-3 py-2 text-sm font-medium transition-colors">
                   Contact
                </a>
                
                <?php if ($user !== null) { ?>
                    <div class="flex items-center space-x-3">
                        <!-- Affichage utilisateur connecté -->
                        <span class="text-sm text-gray-600">Bonjour, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                        <a href="<?php echo $basePath; ?>Pages/profile.php" class="flex items-center">
                        <img src="<?php echo ($latestAvatar && file_exists($avatarFile)) ? $basePath . 'Assets/Images/avatars/' . $latestAvatar : $basePath . 'Assets/Images/default-avatar.png'; ?>" 
                        alt="Avatar" 
                         class="h-8 w-8 rounded-full hover:scale-110 transition-transform">
                        </a>
                        <a href="<?php echo $basePath; ?>Inc/Traitement/traitement_logout.php" 
                           class="bg-france-red text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 transition-colors">
                           Déconnexion
                        </a>
                    </div>
                <?php } else { ?>
                    <a href="<?php echo $basePath; ?>Pages/login.php" 
                       class="bg-france-blue text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-800 transition-colors">
                       Se connecter
                    </a>
                <?php } ?>
            </div>

            <!-- Bouton menu mobile -->
            <button class="lg:hidden p-2" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl text-gray-700"></i>
            </button>
        </div>
    </div>

    <!-- Menu mobile -->
    <div id="mobile-menu" class="lg:hidden hidden bg-white border-t">
        <div class="px-4 py-2 space-y-1">
            <a href="<?php echo $basePath; ?>index.php" 
               class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
               Accueil
            </a>
            <?php if ($user !== null) { ?>
                <a href="<?php echo $basePath; ?>Pages/search.php" 
                   class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                   Recherche
                </a>
                <a href="<?php echo $basePath; ?>Pages/report.php" 
                   class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                   Signaler
                </a>
                <a href="<?php echo $basePath; ?>Pages/profile.php" 
                   class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                   Profil
                </a>
                <?php if (in_array($role, ['admin', 'moderator'])) { ?>
                    <a href="<?php echo $basePath; ?>Pages/admin.php" 
                       class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                       <i class="fas fa-tachometer-alt mr-1"></i>Admin
                    </a>
                <?php } ?>
            <?php } ?>
            <a href="<?php echo $basePath; ?>Pages/guides.php" 
               class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
               Conseils
            </a>
            <a href="<?php echo $basePath; ?>Pages/contact.php" 
               class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
               Contact
            </a>
            <?php if ($user !== null) { ?>
                <div class="px-3 py-2 text-sm text-gray-600 border-t">
                    Connecté en tant que <strong><?php echo htmlspecialchars($username); ?></strong>
                </div>
                <a href="<?php echo $basePath; ?>Inc/Traitement/logout.php" 
                   class="block px-3 py-2 text-france-red hover:bg-red-50 rounded-md">
                   Déconnexion
                </a>
            <?php } else { ?>
                <a href="<?php echo $basePath; ?>Pages/login.php" 
                   class="block px-3 py-2 bg-france-blue text-white rounded-md text-center">
                   Se connecter
                </a>
            <?php } ?>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}
</script>