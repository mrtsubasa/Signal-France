<?php
/**
 * Navigation Global Component
 * Handle user session, path consistency, and responsive menu
 */

// 1. Robust Path Handling
$scriptName = $_SERVER['SCRIPT_NAME'];
$dirCount = substr_count(dirname($scriptName), '/') - (strpos($scriptName, '/') === 0 ? 0 : -1);
// If we are in a subdirectory of the web root, we need to adjust
// This assumes the project root is where index.php is.
// A simpler way for this project structure:
$currentDir = dirname($_SERVER['SCRIPT_NAME']);
$depth = ($currentDir == '/' || $currentDir == '\\') ? 0 : count(explode('/', trim($currentDir, '/')));
$basePath = str_repeat('../', $depth);

// 2. Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootPath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
include_once($rootPath . "Inc" . DIRECTORY_SEPARATOR . "Constants" . DIRECTORY_SEPARATOR . "db.php");
include_once($rootPath . "Inc" . DIRECTORY_SEPARATOR . "Constants" . DIRECTORY_SEPARATOR . "CookieManager.php");

$user = null;
$id = null;
$username = null;
$email = null;
$role = null;
$avatar = null;

try {
    $conn = connect_db();

    // Check Session first
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $dataUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dataUser) {
            $user = $dataUser;
        }
    }
    // Then Cookie if no session
    elseif (isset($_COOKIE['user_token'])) {
        $cookieToken = $_COOKIE['user_token'];
        $hashedToken = hash('sha256', $cookieToken);

        $stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND (token_expiry IS NULL OR token_expiry > datetime('now'))");
        $stmt->execute([$hashedToken]);
        $dataUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dataUser) {
            $_SESSION['user_id'] = $dataUser['id'];
            $_SESSION['user_role'] = $dataUser['role'];
            $_SESSION['user_username'] = $dataUser['username'];
            $user = $dataUser;
        }
    }

    if ($user) {
        $id = $user['id'];
        $username = $user['username'];
        $email = $user['email'];
        $role = $user['role'];
        $avatar = $user['avatar'];
    }

} catch (Exception $e) {
    error_log("Nav error: " . $e->getMessage());
}

// Function to check active page
function is_active($pageName)
{
    $currentScript = basename($_SERVER['SCRIPT_NAME']);
    return $currentScript === $pageName;
}

$activeClass = "text-blue-600 bg-blue-50 font-bold";
$inactiveClass = "text-gray-700 hover:text-blue-600 hover:bg-blue-50";
?>

<nav class="bg-white/95 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Brand -->
            <a href="<?php echo $basePath; ?>index.php" class="flex items-center space-x-3 group">
                <div class="p-1.5 bg-blue-600 rounded-xl group-hover:rotate-6 transition-transform duration-300 shadow-md shadow-blue-200">
                    <img src="<?php echo $basePath; ?>Assets/Images/Econscience.png" alt="Logo" class="h-8 w-8 object-contain brightness-110">
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black text-gray-900 tracking-tight leading-none">E CONSCIENCE</span>
                    <span class="text-[10px] text-blue-600 font-bold tracking-widest uppercase opacity-70">Secours National</span>
                </div>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-1">
                <a href="<?php echo $basePath; ?>index.php" 
                   class="px-4 py-2 rounded-xl text-sm transition-all duration-200 <?php echo is_active('index.php') ? $activeClass : $inactiveClass; ?>">
                    <i class="fas fa-home mr-2 opacity-70"></i>Accueil
                </a>
                
                <a href="<?php echo $basePath; ?>Pages/signal.php" 
                   class="px-4 py-2 rounded-xl text-sm transition-all duration-200 <?php echo is_active('signal.php') ? $activeClass : $inactiveClass; ?>">
                    <i class="fas fa-bullhorn mr-2 opacity-70"></i>Signaler
                </a>

                <a href="<?php echo $basePath; ?>Pages/search.php" 
                   class="px-4 py-2 rounded-xl text-sm transition-all duration-200 <?php echo is_active('search.php') ? $activeClass : $inactiveClass; ?>">
                    <i class="fas fa-search mr-2 opacity-70"></i>Recherche
                </a>

                <a href="<?php echo $basePath; ?>Pages/membres.php" 
                   class="px-4 py-2 rounded-xl text-sm transition-all duration-200 <?php echo is_active('membres.php') ? $activeClass : $inactiveClass; ?>">
                    <i class="fas fa-users mr-2 opacity-70"></i>Membres
                </a>

                <div class="h-6 w-px bg-gray-200 mx-2"></div>

                <a href="<?php echo $basePath; ?>Pages/guides.php" 
                   class="px-4 py-2 rounded-xl text-sm transition-all duration-200 <?php echo is_active('guides.php') ? $activeClass : $inactiveClass; ?>">
                    <i class="fas fa-shield-alt mr-2 opacity-70"></i>Conseils
                </a>

                <a href="<?php echo $basePath; ?>Pages/contact.php" 
                   class="px-4 py-2 rounded-xl text-sm transition-all duration-200 <?php echo is_active('contact.php') ? $activeClass : $inactiveClass; ?>">
                    <i class="fas fa-headset mr-2 opacity-70"></i>Support
                </a>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center space-x-4">
                <?php if ($user): ?>
                        <div class="relative" id="user-dropdown-container">
                            <button onclick="toggleUserMenu()" class="flex items-center space-x-3 p-1 pr-3 rounded-full hover:bg-gray-100 transition-all border border-transparent hover:border-gray-200 group">
                                <div class="relative">
                                    <img src="<?php echo $avatar ? $basePath . 'Assets/Images/avatars/' . $avatar : $basePath . 'Assets/Images/Econscience.png'; ?>" 
                                         alt="Avatar" class="h-8 w-8 rounded-full object-cover ring-2 ring-white shadow-sm transition-transform group-hover:scale-105">
                                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></div>
                                </div>
                                <span class="hidden md:block text-sm font-semibold text-gray-800 tracking-tight"><?php echo htmlspecialchars($username); ?></span>
                                <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-blue-600 transition-colors"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="user-menu" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 overflow-hidden transform origin-top-right transition-all animate-in fade-in zoom-in duration-200">
                                <div class="px-5 py-3 bg-gray-50/50 mb-2">
                                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1">Connecté en tant que</p>
                                    <p class="text-sm font-bold text-gray-900 truncate"><?php echo htmlspecialchars($username); ?></p>
                                    <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($email); ?></p>
                                </div>
                            
                                <a href="<?php echo $basePath; ?>Pages/profile.php" class="flex items-center px-5 py-2.5 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-id-card text-blue-600"></i>
                                    </div>
                                    Mon Profil
                                </a>

                                <a href="<?php echo $basePath; ?>Pages/chat.php" class="flex items-center px-5 py-2.5 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-comments text-indigo-600"></i>
                                    </div>
                                    Messagerie
                                </a>

                                <?php if (in_array($role, ['admin', 'moderator'])): ?>
                                        <a href="<?php echo $basePath; ?>Pages/admin.php" class="flex items-center px-5 py-2.5 text-sm text-gray-600 hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-tachometer-alt text-orange-600"></i>
                                            </div>
                                            Panel Admin
                                        </a>
                                <?php endif; ?>

                                <div class="border-t border-gray-100 mt-2 pt-2 px-2">
                                    <a href="<?php echo $basePath; ?>Inc/Traitement/traitement_logout.php" 
                                       class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors font-semibold">
                                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </div>
                                        Déconnexion
                                    </a>
                                </div>
                            </div>
                        </div>
                <?php else: ?>
                        <a href="<?php echo $basePath; ?>Pages/login.php" 
                           class="inline-flex items-center px-5 py-2.5 rounded-2xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-lg shadow-blue-200">
                            <i class="fas fa-sign-in-alt mr-2 mb-0.5"></i>Connexion
                        </a>
                <?php endif; ?>

                <!-- Mobile Button -->
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:bg-gray-100 active:scale-90 transition-all border border-gray-200">
                    <i id="mobile-icon" class="fas fa-bars-staggered"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 bg-white/95 backdrop-blur-md animate-in slide-in-from-top duration-300">
        <div class="p-4 space-y-1.5">
            <a href="<?php echo $basePath; ?>index.php" class="flex items-center px-4 py-3 rounded-xl text-sm <?php echo is_active('index.php') ? $activeClass : $inactiveClass; ?>">
                <i class="fas fa-home w-8 opacity-60"></i>Accueil
            </a>
            <a href="<?php echo $basePath; ?>Pages/signal.php" class="flex items-center px-4 py-3 rounded-xl text-sm <?php echo is_active('signal.php') ? $activeClass : $inactiveClass; ?>">
                <i class="fas fa-bullhorn w-8 opacity-60"></i>Signaler un incident
            </a>
            <a href="<?php echo $basePath; ?>Pages/search.php" class="flex items-center px-4 py-3 rounded-xl text-sm <?php echo is_active('search.php') ? $activeClass : $inactiveClass; ?>">
                <i class="fas fa-search w-8 opacity-60"></i>Recherche
            </a>
            <a href="<?php echo $basePath; ?>Pages/membres.php" class="flex items-center px-4 py-3 rounded-xl text-sm <?php echo is_active('membres.php') ? $activeClass : $inactiveClass; ?>">
                <i class="fas fa-users w-8 opacity-60"></i>Membres
            </a>
            
            <div class="py-2"><div class="h-px bg-gray-100 mx-4"></div></div>

            <?php if ($user): ?>
                    <a href="<?php echo $basePath; ?>Pages/profile.php" class="flex items-center px-4 py-3 rounded-xl text-sm <?php echo is_active('profile.php') ? $activeClass : $inactiveClass; ?>">
                        <i class="fas fa-id-card w-8 opacity-60"></i>Mon Profil
                    </a>
                    <a href="<?php echo $basePath; ?>Pages/chat.php" class="flex items-center px-4 py-3 rounded-xl text-sm <?php echo is_active('chat.php') ? $activeClass : $inactiveClass; ?>">
                        <i class="fas fa-comments w-8 opacity-60"></i>Messagerie
                    </a>
                    <?php if (in_array($role, ['admin', 'moderator'])): ?>
                            <a href="<?php echo $basePath; ?>Pages/admin.php" class="flex items-center px-4 py-3 rounded-xl text-sm <?php echo is_active('admin.php') ? $activeClass : 'text-orange-600 hover:bg-orange-50'; ?>">
                                <i class="fas fa-tachometer-alt w-8 opacity-60"></i>Panel Admin
                            </a>
                    <?php endif; ?>
                
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between px-4">
                        <div class="flex items-center">
                            <img src="<?php echo $avatar ? $basePath . 'Assets/Images/avatars/' . $avatar : $basePath . 'Assets/Images/Econscience.png'; ?>" 
                                 class="h-8 w-8 rounded-full border border-gray-200">
                            <div class="ml-3">
                                <p class="text-xs font-bold text-gray-900 leading-none"><?php echo htmlspecialchars($username); ?></p>
                                <p class="text-[10px] text-gray-500 mt-0.5"><?php echo htmlspecialchars($email); ?></p>
                            </div>
                        </div>
                        <a href="<?php echo $basePath; ?>Inc/Traitement/traitement_logout.php" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
            <?php else: ?>
                    <div class="mt-4 px-4 pb-2">
                        <a href="<?php echo $basePath; ?>Pages/login.php" class="flex items-center justify-center p-3 rounded-xl bg-blue-600 text-white font-bold transition-all shadow-lg active:scale-95">
                            <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                        </a>
                    </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeIn 0.2s ease-out forwards; }
    
    .nav-link-underline {
        position: relative;
    }
    .nav-link-underline::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 50%;
        width: 0;
        height: 2px;
        background: currentColor;
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 2px;
    }
    .nav-link-underline:hover::after {
        width: 60%;
    }
</style>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('mobile-icon');
        const isOpen = !menu.classList.contains('hidden');
        
        menu.classList.toggle('hidden');
        icon.className = isOpen ? 'fas fa-bars-staggered' : 'fas fa-xmark';
    }

    function toggleUserMenu() {
        const menu = document.getElementById('user-menu');
        menu.classList.toggle('hidden');
    }

    // Close menus on outside click
    document.addEventListener('click', (e) => {
        const userContainer = document.getElementById('user-dropdown-container');
        const userMenu = document.getElementById('user-menu');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileBtn = document.querySelector('button[onclick="toggleMobileMenu()"]');

        if (userContainer && !userContainer.contains(e.target) && userMenu) {
            userMenu.classList.add('hidden');
        }
        
        if (mobileMenu && !mobileMenu.contains(e.target) && !mobileBtn.contains(e.target)) {
            if (!mobileMenu.classList.contains('hidden')) {
                toggleMobileMenu();
            }
        }
    });

    // Close on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            document.getElementById('mobile-menu')?.classList.add('hidden');
            const icon = document.getElementById('mobile-icon');
            if (icon) icon.className = 'fas fa-bars-staggered';
        }
    });
</script>