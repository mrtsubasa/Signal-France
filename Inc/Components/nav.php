<?php
// [Code PHP identique pour récupérer les données utilisateur]
$rootPath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
include_once($rootPath . "Inc" . DIRECTORY_SEPARATOR . "Constants" . DIRECTORY_SEPARATOR . "db.php");
include_once($rootPath . "Inc" . DIRECTORY_SEPARATOR . "Constants" . DIRECTORY_SEPARATOR . "CookieManager.php");

$user = null;
$id = null;
$username = null;
$email = null;
$role = null;
$avatar = null;
$token = null;

try {
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
    elseif (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
        $cookieToken = $_COOKIE['user_token'];
        if (strlen($cookieToken) >= 32) {
            $conn = connect_db();
            $hashedToken = hash('sha256', $cookieToken);
            $req = $conn->prepare("SELECT * FROM users WHERE token = ? AND (token_expiry IS NULL OR token_expiry > datetime('now'))");
            $req->execute([$hashedToken]);
            $dataUser = $req->fetch(PDO::FETCH_ASSOC);

            if ($dataUser) {
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
                setcookie('user_token', '', time() - 3600, '/');
                setcookie('user_pseudo', '', time() - 3600, '/');
            }
        }
    }

    if ($user) {
        $avatarDir = $rootPath . "Assets/Images/avatars/";
        $latestAvatar = null;
        if (is_dir($avatarDir)) {
            $files = glob($avatarDir . "avatar_" . $id . "_*");
            if (!empty($files)) {
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
} catch (Exception $e) {
    error_log("Error in nav.php: " . $e->getMessage());
}

$currentPath = $_SERVER['REQUEST_URI'];
$isInSubfolder = (strpos($currentPath, '/Src/') !== false || strpos($currentPath, '/Pages/') !== false);
$basePath = $isInSubfolder ? '../' : './';

function isActiveLink($linkPath, $currentPath) {
    $linkName = basename($linkPath, '.php');
    return strpos($currentPath, $linkName) !== false;
}

// Menu items
$menuItems = [
    ['path' => 'index.php', 'icon' => '🏠', 'label' => 'Accueil'],
    ['path' => 'Pages/search.php', 'icon' => '🔍', 'label' => 'Recherche'],
    ['path' => 'Pages/signal.php', 'icon' => '⚠️', 'label' => 'Signaler'],
    ['path' => 'Pages/guides.php', 'icon' => '📖', 'label' => 'Conseils'],
    ['path' => 'Pages/contact.php', 'icon' => '📧', 'label' => 'Contact']
];

if ($user == null) {
    $menuItems[] = ['path' => 'Pages/membres.php', 'icon' => '👥', 'label' => 'Membres'];
}
?>

    <!-- Bande Marianne -->
    <div class="flag-stripe">
        <div class="flag-blue"></div>
        <div class="flag-white"></div>
        <div class="flag-red"></div>
    </div>

    <!-- Navbar Ultra Simple -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">

            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo">
                    <img src="<?php echo $basePath; ?>Assets/Images/SignaleFrance.png" alt="Signale France">
                </div>
                <div class="brand">
                    <h1>Signale France</h1>
                    <span class="subtitle">Système d'alerte national</span>
                </div>
            </div>

            <!-- Desktop Menu (PC seulement) -->
            <div class="desktop-nav">
                <?php foreach ($menuItems as $item):
                    $isActive = isActiveLink($item['path'], $currentPath);
                    ?>
                    <a href="<?php echo $basePath . $item['path']; ?>"
                       class="nav-link <?php echo $isActive ? 'active' : ''; ?>">
                        <span class="icon"><?php echo $item['icon']; ?></span>
                        <span class="text"><?php echo $item['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- User Section -->
            <div class="user-section">
                <?php if ($user !== null) { ?>
                    <!-- User Menu Button -->
                    <button class="user-btn" id="userMenuBtn" aria-label="Menu utilisateur">
                        <img src="<?php echo ($latestAvatar && file_exists($avatarFile)) ? $basePath . 'Assets/Images/avatars/' . $latestAvatar : $basePath . 'Assets/Images/SignaleFrance.png'; ?>"
                             alt="Avatar" class="avatar">
                        <div class="user-info">
                            <span class="name"><?php echo htmlspecialchars($username); ?></span>
                            <span class="role"><?php echo ucfirst($role); ?></span>
                        </div>
                        <span class="chevron">▼</span>
                    </button>
                <?php } else { ?>
                    <!-- Menu Non-connecté -->
                    <button class="menu-btn" id="guestMenuBtn" aria-label="Menu principal">
                        <div class="menu-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span class="menu-text">Menu</span>
                    </button>
                <?php } ?>
            </div>
        </div>

        <!-- Menu Universel (Navigation + Utilisateur) -->
        <div class="universal-menu" id="universalMenu">
            <div class="menu-content">

                <?php if ($user !== null) { ?>
                    <!-- Header Utilisateur -->
                    <div class="menu-header">
                        <img src="<?php echo ($latestAvatar && file_exists($avatarFile)) ? $basePath . 'Assets/Images/avatars/' . $latestAvatar : $basePath . 'Assets/Images/SignaleFrance.png'; ?>"
                             alt="Avatar" class="header-avatar">
                        <div class="header-info">
                            <h3><?php echo htmlspecialchars($username); ?></h3>
                            <p><?php echo htmlspecialchars($email); ?></p>
                            <span class="badge"><?php echo ucfirst($role); ?></span>
                        </div>
                    </div>



                    <!-- Actions Utilisateur -->
                    <div class="menu-section">
                        <h4>Mon Compte</h4>

                        <a href="<?php echo $basePath; ?>Pages/profile.php" class="menu-item">
                            <span class="item-icon">👤</span>
                            <div class="item-content">
                                <span class="item-title">Mon Profil</span>
                                <span class="item-desc">Gérer mes informations</span>
                            </div>
                            <span class="item-arrow">→</span>
                        </a>

                        <a href="<?php echo $basePath; ?>Pages/chat.php" class="menu-item">
                            <span class="item-icon">💬</span>
                            <div class="item-content">
                                <span class="item-title">Chat</span>
                                <span class="item-desc">Messages instantanés</span>
                            </div>
                            <span class="new-badge">Nouveau</span>
                        </a>

                        <a href="<?php echo $basePath; ?>Pages/membres.php" class="menu-item">
                            <span class="item-icon">👥</span>
                            <div class="item-content">
                                <span class="item-title">Membres</span>
                                <span class="item-desc">Communauté Signal France</span>
                            </div>
                            <span class="item-arrow">→</span>
                        </a>

                        <?php if (in_array($role, ['admin'])) { ?>
                            <a href="<?php echo $basePath; ?>Pages/admin.php" class="menu-item admin">
                                <span class="item-icon">🛡️</span>
                                <div class="item-content">
                                    <span class="item-title">Administration</span>
                                    <span class="item-desc">Panneau de contrôle</span>
                                </div>
                                <span class="item-arrow">→</span>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>

                <!-- Navigation Principale -->
                <div class="menu-section">
                    <h4><?php echo $user ? 'Navigation' : 'Menu Principal'; ?></h4>

                    <?php foreach ($menuItems as $item):
                        $isActive = isActiveLink($item['path'], $currentPath);
                        ?>
                        <a href="<?php echo $basePath . $item['path']; ?>"
                           class="menu-item nav-item <?php echo $isActive ? 'active' : ''; ?>">
                            <span class="item-icon"><?php echo $item['icon']; ?></span>
                            <div class="item-content">
                                <span class="item-title"><?php echo $item['label']; ?></span>
                                <?php if ($isActive): ?>
                                    <span class="item-desc">Page actuelle</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($isActive): ?>
                                <span class="active-indicator">●</span>
                            <?php else: ?>
                                <span class="item-arrow">→</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($user !== null) { ?>
                    <!-- Déconnexion -->
                    <div class="menu-footer">
                        <a href="<?php echo $basePath; ?>Inc/Traitement/traitement_logout.php" class="logout-item">
                            <span class="item-icon">🚪</span>
                            <span class="logout-text">Déconnexion</span>
                            <span class="item-arrow">→</span>
                        </a>
                    </div>
                <?php } else { ?>
                    <!-- Boutons Connexion -->
                    <div class="menu-footer">
                        <a href="<?php echo $basePath; ?>Pages/register.php" class="auth-btn secondary">
                            <span class="btn-icon">✨</span>
                            <span>S'inscrire</span>
                        </a>
                        <a href="<?php echo $basePath; ?>Pages/login.php" class="auth-btn primary">
                            <span class="btn-icon">🔑</span>
                            <span>Se connecter</span>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </nav>

    <style>
        /* === RESET === */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* === FLAG STRIPE === */
        .flag-stripe {
            height: 3px;
            display: flex;
        }

        .flag-blue { flex: 1; background: #0055A4; }
        .flag-white { flex: 1; background: #FFFFFF; }
        .flag-red { flex: 1; background: #EF4135; }

        /* === NAVBAR === */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            height: 4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        /* === LOGO SECTION === */
        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
            cursor: pointer;
        }

        .logo {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0055A4, #4f46e5);
            padding: 0.25rem;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand h1 {
            font-size: 1.125rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1.2;
        }

        .brand .subtitle {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            display: none;
        }

        /* === DESKTOP NAV === */
        .desktop-nav {
            display: none;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            justify-content: center;
            max-width: 600px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .nav-link:hover {
            background: #f3f4f6;
            color: #0055A4;
        }

        .nav-link.active {
            background: #0055A4;
            color: white;
        }

        .nav-link .icon {
            font-size: 1rem;
        }

        /* === USER SECTION === */
        .user-section {
            flex-shrink: 0;
        }

        .user-btn,
        .menu-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border: none;
            border-radius: 0.75rem;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .user-btn:hover,
        .menu-btn:hover {
            background: #f3f4f6;
            transform: translateY(-1px);
        }

        .avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .user-info {
            display: none;
            flex-direction: column;
            align-items: flex-start;
            max-width: 8rem;
        }

        .user-info .name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .user-info .role {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1;
        }

        .chevron {
            font-size: 0.75rem;
            color: #9ca3af;
            transition: transform 0.2s ease;
        }

        .user-btn[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }

        /* Menu Button */
        .menu-icon {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            width: 1.5rem;
        }

        .menu-icon span {
            width: 100%;
            height: 2px;
            background: #374151;
            border-radius: 1px;
            transition: all 0.3s ease;
        }

        .menu-btn.active .menu-icon span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .menu-btn.active .menu-icon span:nth-child(2) {
            opacity: 0;
        }

        .menu-btn.active .menu-icon span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        .menu-text {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            display: none;
        }

        /* === UNIVERSAL MENU === */
        .universal-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            max-height: calc(100vh - 4rem);
            overflow-y: auto;
        }

        .universal-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .menu-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* === MENU HEADER === */
        .menu-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
        }

        .header-avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 1rem;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .header-info p {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
            word-break: break-all;
        }

        .header-info .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #0055A4;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
        }

        /* === NOTIFICATION SECTION === */
        .notification-section {
            margin-bottom: 1.5rem;
        }

        .notif-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notif-item:hover {
            background: #fde68a;
        }

        .notif-icon {
            font-size: 1.5rem;
        }

        .notif-content {
            flex: 1;
        }

        .notif-title {
            font-weight: 600;
            color: #92400e;
            display: block;
        }

        .notif-desc {
            font-size: 0.875rem;
            color: #b45309;
            display: block;
        }

        .notif-badge {
            background: #dc2626;
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            min-width: 1.5rem;
            text-align: center;
        }

        /* === MENU SECTIONS === */
        .menu-section {
            margin-bottom: 2rem;
        }

        .menu-section h4 {
            font-size: 0.875rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            padding-left: 0.5rem;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: #374151;
            transition: all 0.2s ease;
            margin-bottom: 0.5rem;
            border: 1px solid transparent;
        }

        .menu-item:hover {
            background: #f9fafb;
            border-color: #e5e7eb;
            transform: translateX(4px);
        }

        .menu-item.active {
            background: #eff6ff;
            border-color: #0055A4;
            color: #0055A4;
        }

        .menu-item.admin {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        .menu-item.admin:hover {
            background: #fee2e2;
        }

        .item-icon {
            font-size: 1.5rem;
            width: 2rem;
            text-align: center;
            flex-shrink: 0;
        }

        .item-content {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            font-size: 1rem;
            color: inherit;
            display: block;
            line-height: 1.2;
        }

        .item-desc {
            font-size: 0.875rem;
            color: #6b7280;
            display: block;
            line-height: 1.2;
            margin-top: 0.125rem;
        }

        .item-arrow {
            font-size: 1.125rem;
            color: #9ca3af;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .menu-item:hover .item-arrow {
            color: #0055A4;
            transform: translateX(2px);
        }

        .active-indicator {
            color: #0055A4;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .new-badge {
            background: #8b5cf6;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            flex-shrink: 0;
        }

        /* === MENU FOOTER === */
        .menu-footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .logout-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: #dc2626;
            transition: all 0.2s ease;
            border: 1px solid #fca5a5;
            background: #fef2f2;
        }

        .logout-item:hover {
            background: #fee2e2;
            transform: translateX(4px);
        }

        .logout-text {
            flex: 1;
            font-weight: 600;
        }

        .auth-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .auth-btn.secondary {
            background: #f9fafb;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .auth-btn.secondary:hover {
            background: #f3f4f6;
        }

        .auth-btn.primary {
            background: #0055A4;
            color: white;
            border: 1px solid #0055A4;
        }

        .auth-btn.primary:hover {
            background: #004494;
        }

        .btn-icon {
            font-size: 1.125rem;
        }

        /* === RESPONSIVE === */

        /* Mobile Small (425px+) */
        @media (min-width: 425px) {
            .brand .subtitle {
                display: block;
            }

            .menu-text {
                display: block;
            }

            .user-info {
                display: flex;
            }
        }

        /* Tablet (768px+) */
        @media (min-width: 768px) {
            .nav-container {
                height: 5rem;
                padding: 0 2rem;
            }

            .logo {
                width: 3rem;
                height: 3rem;
            }

            .brand h1 {
                font-size: 1.25rem;
            }

            .brand .subtitle {
                font-size: 0.875rem;
            }

            .desktop-nav {
                display: flex;
            }

            .avatar {
                width: 3rem;
                height: 3rem;
            }

            .user-info {
                display: flex;
            }

            .menu-content {
                padding: 2rem;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }

            .menu-header {
                grid-column: 1 / -1;
            }

            .notification-section {
                grid-column: 1 / -1;
            }

            .menu-footer {
                grid-column: 1 / -1;
            }
        }

        /* Desktop (1024px+) */
        @media (min-width: 1024px) {
            .nav-container {
                padding: 0 3rem;
            }

            .menu-content {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        /* Large Desktop (1280px+) */
        @media (min-width: 1280px) {
            .nav-container {
                max-width: 1400px;
            }
        }

        /* === MOBILE SPECIFIC === */
        @media (max-width: 767px) {
            .universal-menu {
                left: 0.5rem;
                right: 0.5rem;
                border-radius: 1rem;
                margin-top: 0.5rem;
            }

            .menu-content {
                padding: 1rem;
            }

            /* Touch targets */
            .menu-item {
                min-height: 3.5rem;
                padding: 1.25rem 1rem;
            }

            .notif-item {
                min-height: 3.5rem;
                padding: 1.25rem 1rem;
            }

            .auth-btn {
                min-height: 3.5rem;
                padding: 1.25rem;
            }
        }

        /* === VERY SMALL SCREENS === */
        @media (max-width: 320px) {
            .nav-container {
                padding: 0 0.75rem;
                gap: 0.5rem;
            }

            .logo {
                width: 2rem;
                height: 2rem;
            }

            .brand h1 {
                font-size: 1rem;
            }

            .brand .subtitle {
                display: none;
            }

            .universal-menu {
                left: 0.25rem;
                right: 0.25rem;
            }
        }

        /* === SCROLLBAR === */
        .universal-menu {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
        }

        .universal-menu::-webkit-scrollbar {
            width: 6px;
        }

        .universal-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .universal-menu::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.4);
            border-radius: 3px;
        }

        .universal-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.6);
        }

        /* === ANIMATIONS === */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .universal-menu.show .menu-content > * {
            animation: slideIn 0.3s ease forwards;
        }

        .universal-menu.show .menu-content > *:nth-child(1) { animation-delay: 0.05s; }
        .universal-menu.show .menu-content > *:nth-child(2) { animation-delay: 0.1s; }
        .universal-menu.show .menu-content > *:nth-child(3) { animation-delay: 0.15s; }
        .universal-menu.show .menu-content > *:nth-child(4) { animation-delay: 0.2s; }
        .universal-menu.show .menu-content > *:nth-child(5) { animation-delay: 0.25s; }
    </style>

    <script>
        class UniversalNavbar {
            constructor() {
                this.userMenuBtn = document.getElementById('userMenuBtn');
                this.guestMenuBtn = document.getElementById('guestMenuBtn');
                this.universalMenu = document.getElementById('universalMenu');
                this.navbar = document.getElementById('navbar');
                this.isMenuOpen = false;

                this.init();
            }

            init() {
                // User menu button
                if (this.userMenuBtn) {
                    this.userMenuBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleMenu();
                    });
                }

                // Guest menu button
                if (this.guestMenuBtn) {
                    this.guestMenuBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleMenu();
                    });
                }

                // Close menu when clicking outside
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.universal-menu') &&
                        !e.target.closest('.user-btn') &&
                        !e.target.closest('.menu-btn')) {
                        this.closeMenu();
                    }
                });

                // Close menu when clicking nav links
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.menu-item.nav-item')) {
                        this.closeMenu();
                    }
                });

                // Handle window resize
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768) {
                        // Keep menu available on tablet/desktop for user menu
                    }
                });

                // Keyboard support
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeMenu();
                    }
                });

                // Scroll behavior
                this.setupScrollBehavior();

                // Prevent body scroll when menu is open
                this.preventBodyScroll();
            }

            toggleMenu() {
                if (this.isMenuOpen) {
                    this.closeMenu();
                } else {
                    this.openMenu();
                }
            }

            openMenu() {
                this.universalMenu.classList.add('show');
                this.isMenuOpen = true;

                // Update button states
                if (this.userMenuBtn) {
                    this.userMenuBtn.setAttribute('aria-expanded', 'true');
                }
                if (this.guestMenuBtn) {
                    this.guestMenuBtn.classList.add('active');
                    this.guestMenuBtn.setAttribute('aria-expanded', 'true');
                }

                // Prevent body scroll on mobile
                if (window.innerWidth < 768) {
                    document.body.style.overflow = 'hidden';
                }

                // Focus first menu item
                const firstMenuItem = this.universalMenu.querySelector('.menu-item');
                if (firstMenuItem) {
                    setTimeout(() => firstMenuItem.focus(), 100);
                }
            }

            closeMenu() {
                this.universalMenu.classList.remove('show');
                this.isMenuOpen = false;

                // Update button states
                if (this.userMenuBtn) {
                    this.userMenuBtn.setAttribute('aria-expanded', 'false');
                }
                if (this.guestMenuBtn) {
                    this.guestMenuBtn.classList.remove('active');
                    this.guestMenuBtn.setAttribute('aria-expanded', 'false');
                }

                // Restore body scroll
                document.body.style.overflow = '';
            }

            setupScrollBehavior() {
                let lastScrollTop = 0;
                let ticking = false;

                const updateNavbar = () => {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                    // Don't hide navbar when menu is open
                    if (!this.isMenuOpen) {
                        if (scrollTop > lastScrollTop && scrollTop > 100) {
                            this.navbar.style.transform = 'translateY(-100%)';
                        } else {
                            this.navbar.style.transform = 'translateY(0)';
                        }
                    }

                    lastScrollTop = scrollTop;
                    ticking = false;
                };

                const requestTick = () => {
                    if (!ticking) {
                        requestAnimationFrame(updateNavbar);
                        ticking = true;
                    }
                };

                window.addEventListener('scroll', requestTick, { passive: true });
            }

            preventBodyScroll() {
                // Observe menu state changes
                const observer = new MutationObserver(() => {
                    if (this.isMenuOpen && window.innerWidth < 768) {
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = '';
                    }
                });

                observer.observe(this.universalMenu, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            new UniversalNavbar();
        });

        // Add haptic feedback for mobile
        if ('ontouchstart' in window && navigator.vibrate) {
            document.addEventListener('click', (e) => {
                if (e.target.closest('.user-btn, .menu-btn, .menu-item')) {
                    navigator.vibrate(50);
                }
            });
        }
    </script>

<?php if (!in_array($role, ['admin', 'moderator'])) :?>
    <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script>
<?php endif;?>