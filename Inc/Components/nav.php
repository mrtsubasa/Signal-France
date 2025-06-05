<?php
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
        $defaultAvatar = $rootPath . "Assets/Images/SignaleFrance.png";
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
<div class="w-full h-1 bg-gradient-to-r from-blue-600 via-white to-red-600"></div>

<!-- Navigation principale -->
<nav class="bg-white shadow-lg sticky top-0 z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center h-16">
            <!-- Logo et titre -->
           <!-- Logo et titre -->
<div class="flex items-center space-x-3 px-4">
    <img src="<?php echo $basePath; ?>Assets/Images/SignaleFrance.png" 
         alt="Signale France Logo" 
         class="h-10 w-10 object-contain">
    <div class="block">
        <h1 class="text-lg sm:text-xl font-bold text-blue-600">Signale France</h1>
        <p class="text-xs text-gray-500 hidden sm:block">Système national d'alerte</p>
    </div>
</div>

            <!-- Menu desktop -->
            <div class="hidden lg:flex items-center space-x-1">
                <a href="<?php echo $basePath; ?>index.php" 
                   class="nav-link px-4 py-2 rounded-lg text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 font-medium">
                   <i class="fas fa-home mr-2"></i>Accueil
                </a>
                
                <?php if ($user !== null) { ?>
                 
                    <?php if (in_array($role, ['admin', 'moderator'])) { ?>
                        <a href="<?php echo $basePath; ?>Pages/admin.php" 
                           class="nav-link px-4 py-2 rounded-lg text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition-all duration-200 font-medium">
                           <i class="fas fa-tachometer-alt mr-2"></i>Admin
                        </a>
                    <?php } ?>

                <?php } ?>


                <a href="<?php echo $basePath; ?>Pages/search.php" 
                       class="nav-link px-4 py-2 rounded-lg text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 font-medium">
                       <i class="fas fa-search mr-2"></i>Recherche
                    </a>
                    <a href="<?php echo $basePath; ?>Pages/signal.php" 
                       class="nav-link px-4 py-2 rounded-lg text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 font-medium">
                       <i class="fas fa-exclamation-triangle mr-2"></i>Signaler
                    </a>
                
                <a href="<?php echo $basePath; ?>Pages/guides.php" 
                   class="nav-link px-4 py-2 rounded-lg text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 font-medium">
                   <i class="fas fa-book mr-2"></i>Conseils
                </a>
                <a href="<?php echo $basePath; ?>Pages/contact.php" 
                   class="nav-link px-4 py-2 rounded-lg text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 font-medium">
                   <i class="fas fa-envelope mr-2"></i>Contact
                </a>
                <?php if($user == null):?>
                <a href="<?php echo $basePath; ?>Pages/membres.php" 
                   class="nav-link px-4 py-2 rounded-lg text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 font-medium">
                   <i class="fas fa-envelope mr-2"></i>Membres
                </a>
                <?php endif;?>
              
            </div>

            <!-- Section utilisateur -->
            <div class="flex items-center space-x-3 px-4">
                
                <?php if ($user !== null) { ?>
                    <!-- Dropdown utilisateur -->
                    <div class="relative" id="user-dropdown">
                        <button onclick="toggleUserDropdown()" 
                                class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <img src="<?php echo ($latestAvatar && file_exists($avatarFile)) ? $basePath . 'Assets/Images/avatars/' . $latestAvatar : $basePath . 'Assets/Images/SignaleFrance.png'; ?>" 
                                 alt="Avatar" 
                                 class="h-8 w-8 rounded-full object-cover border-2 border-gray-200">
                            <span class="hidden md:block text-sm font-medium text-gray-700"><?php echo htmlspecialchars($username); ?></span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        
                        <!-- Menu dropdown -->
                        <div id="dropdown-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($username); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($email); ?></p>
                            </div>
                            <a href="<?php echo $basePath; ?>Pages/profile.php" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                               <i class="fas fa-user mr-2"></i>Mon Profil
                            </a>
                            <a href="<?php echo $basePath; ?>Pages/membres.php" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                               <i class="fas fa-user mr-2"></i>Membres
                            </a>

                            <a href="<?php echo $basePath; ?>Pages/chat.php" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                               <i class="fas fa-user mr-2"></i>Chat
                            </a>
                            
                            <?php if (in_array($role, ['admin', 'moderator'])) { ?>
                                <a href="<?php echo $basePath; ?>Pages/admin.php" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                   <i class="fas fa-cog mr-2"></i>Administration
                                </a>
                            <?php } ?>
                            <div class="border-t border-gray-100 mt-1">
                                <a href="<?php echo $basePath; ?>Inc/Traitement/traitement_logout.php" 
                                   class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                   <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <a href="<?php echo $basePath; ?>Pages/login.php" 
                       class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-sm">
                       <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                    </a>
                <?php } ?>
                
                <!-- Bouton menu mobile -->
                <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()">
                    <i id="mobile-menu-icon" class="fas fa-bars text-xl text-gray-700"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu mobile -->
    <div id="mobile-menu" class="lg:hidden hidden bg-white border-t border-gray-200">
        <div class="px-4 py-3 space-y-1">
            <a href="<?php echo $basePath; ?>index.php" 
               class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
               <i class="fas fa-home mr-3 w-5"></i>Accueil
            </a>
            
            <?php if ($user !== null) { ?>
                <a href="<?php echo $basePath; ?>Pages/profile.php" 
                   class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
                   <i class="fas fa-user mr-3 w-5"></i>Mon Profil
                </a>
                <a href="<?php echo $basePath;?>Pages/chat.php"
               class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
               <i class="fas fa-user mr-3 w-5"></i>Chat
                </a>
                <?php if (in_array($role, ['admin', 'moderator'])) { ?>
                    <a href="<?php echo $basePath; ?>Pages/admin.php" 
                       class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition-colors">
                       <i class="fas fa-tachometer-alt mr-3 w-5"></i>Administration
                    </a>
                <?php } ?>
            <?php } ?>
            <a href="<?php echo $basePath; ?>Pages/search.php" 
                   class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
                   <i class="fas fa-search mr-3 w-5"></i>Recherche
                </a>
                <a href="<?php echo $basePath; ?>Pages/signal.php" 
                   class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
                   <i class="fas fa-exclamation-triangle mr-3 w-5"></i>Signaler
                </a>

            <a href="<?php echo $basePath;?>Pages/membres.php"
               class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
               <i class="fas fa-user mr-3 w-5"></i>Membres
            </a>

           
            
            <a href="<?php echo $basePath; ?>Pages/guides.php" 
               class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
               <i class="fas fa-book mr-3 w-5"></i>Conseils
            </a>
            <a href="<?php echo $basePath; ?>Pages/contact.php" 
               class="mobile-nav-link block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors">
               <i class="fas fa-envelope mr-3 w-5"></i>Contact
            </a>
            
            <?php if ($user !== null) { ?>
                <div class="border-t border-gray-200 mt-3 pt-3">
                    <div class="px-4 py-2 text-sm text-gray-600">
                        Connecté en tant que <strong><?php echo htmlspecialchars($username); ?></strong>
                    </div>
                    <a href="<?php echo $basePath; ?>Inc/Traitement/traitement_logout.php" 
                       class="block px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                       <i class="fas fa-sign-out-alt mr-3 w-5"></i>Déconnexion
                    </a>
                </div>
            <?php } else { ?>
                <div class="border-t border-gray-200 mt-3 pt-3">
                    <a href="<?php echo $basePath; ?>Pages/login.php" 
                       class="block px-4 py-3 bg-blue-600 text-white rounded-lg text-center font-medium hover:bg-blue-700 transition-colors">
                       <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</nav>

<?php if (!in_array($role, ['admin', 'moderator'])) :?>
    <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script>
<?php endif;?>

<style>
.nav-link {
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.nav-link:hover::before {
    width: 80%;
}

.mobile-nav-link {
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}

.mobile-nav-link:hover {
    border-left-color: #2563eb;
    transform: translateX(4px);
}

@media (max-width: 1024px) {
    .nav-link::before {
        display: none;
    }
}
</style>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    const icon = document.getElementById('mobile-menu-icon');
    
    menu.classList.toggle('hidden');
    
    if (menu.classList.contains('hidden')) {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    } else {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    }
}

function toggleUserDropdown() {
    const dropdown = document.getElementById('dropdown-menu');
    dropdown.classList.toggle('hidden');
}

// Fermer le dropdown si on clique ailleurs
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('user-dropdown');
    const menu = document.getElementById('dropdown-menu');
    
    if (dropdown && !dropdown.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Fermer le menu mobile si on redimensionne la fenêtre
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        document.getElementById('mobile-menu').classList.add('hidden');
        document.getElementById('mobile-menu-icon').classList.remove('fa-times');
        document.getElementById('mobile-menu-icon').classList.add('fa-bars');
    }
});
</script>