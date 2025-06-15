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

    // Récupérer tous les utilisateurs avec profil public
    $stmt = $conn->prepare("
        SELECT id, username, email, role, avatar, banner, bio, organization, 
               accreditation, website, github, linkedin, created_at, last_activity, is_active
        FROM users 
        WHERE is_public = 1 AND is_deleted = 0 AND is_banned = 0
        ORDER BY last_activity DESC
    ");
    $stmt->execute();
    $publicUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fonction pour obtenir le badge de rôle
    function getRoleBadge($role) {
        switch($role) {
            case 'admin':
                return '<div class="role-badge admin"><i class="fas fa-crown"></i><span>Admin</span></div>';
            case 'moderator':
                return '<div class="role-badge moderator"><i class="fas fa-shield-alt"></i><span>Modo</span></div>';
            case 'user':
                return '<div class="role-badge user"><i class="fas fa-user"></i><span>User</span></div>';
            case 'verified':
                return '<div class="role-badge verified"><i class="fas fa-check-circle"></i><span>Vérifié</span></div>';
            default:
                return '<div class="role-badge member"><i class="fas fa-user"></i><span>Membre</span></div>';
        }
    }

    // Fonction pour formater la date
    function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        if ($time < 60) return 'maintenant';
        if ($time < 3600) return floor($time/60) . 'm';
        if ($time < 86400) return floor($time/3600) . 'h';
        if ($time < 2592000) return floor($time/86400) . 'j';
        if ($time < 31536000) return floor($time/2592000) . ' mois';
        return floor($time/31536000) . ' ans';
    }

} catch (Exception $e) {
    error_log("Erreur dans membres.php: " . $e->getMessage());
    $publicUsers = [];
}
?>

    <main class="relative min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0">
            <!-- Animated grid -->
            <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>

            <!-- Floating orbs -->
            <div class="floating-orb orb-1"></div>
            <div class="floating-orb orb-2"></div>
            <div class="floating-orb orb-3"></div>
            <div class="floating-orb orb-4"></div>
            <div class="floating-orb orb-5"></div>

            <!-- Gradient overlays -->
            <div class="absolute inset-0 bg-gradient-radial from-blue-500/10 via-transparent to-purple-500/10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/50 via-transparent to-transparent"></div>
        </div>

        <div class="relative z-10">
            <!-- Hero Section -->
            <section class="py-20 px-4 lg:px-8">
                <div class="max-w-7xl mx-auto">
                    <!-- Header avec animation sophistiquée -->
                    <div class="text-center mb-20">
                        <div class="relative inline-block mb-8">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full blur-3xl opacity-30 animate-pulse-slow"></div>
                            <div class="relative bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 p-6 rounded-3xl shadow-2xl transform hover:scale-110 transition-all duration-500">
                                <i class="fas fa-users text-white text-4xl"></i>
                            </div>
                        </div>

                        <h1 class="text-6xl lg:text-8xl font-black mb-6">
                        <span class="bg-gradient-to-r from-white via-blue-200 to-purple-200 bg-clip-text text-transparent drop-shadow-2xl">
                            Notre
                        </span>
                            <br>
                            <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                            Communauté
                        </span>
                        </h1>

                        <p class="text-xl lg:text-2xl text-slate-300 max-w-4xl mx-auto leading-relaxed mb-8 font-light">
                            Découvrez les membres engagés de Signale France, une communauté unie pour la sécurité et la protection de tous
                        </p>

                        <!-- Statistics bar -->
                        <div class="flex justify-center items-center space-x-8 mb-8">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white mb-1"><?= count($publicUsers) ?></div>
                                <div class="text-sm text-slate-400">Membres publics</div>
                            </div>
                            <div class="w-px h-12 bg-slate-600"></div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white mb-1"><?= count(array_filter($publicUsers, fn($u) => $u['is_active'])) ?></div>
                                <div class="text-sm text-slate-400">En ligne</div>
                            </div>
                            <div class="w-px h-12 bg-slate-600"></div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white mb-1"><?= count(array_filter($publicUsers, fn($u) => $u['role'] !== 'user')) ?></div>
                                <div class="text-sm text-slate-400">Staff</div>
                            </div>
                        </div>

                        <!-- France colors separator -->
                        <div class="flex justify-center">
                            <div class="h-1 w-64 bg-gradient-to-r from-blue-600 via-white to-red-600 rounded-full shadow-lg"></div>
                        </div>
                    </div>

                    <?php if (empty($publicUsers)): ?>
                        <!-- Empty state -->
                        <div class="text-center py-20">
                            <div class="relative inline-block mb-8">
                                <div class="absolute inset-0 bg-slate-800 rounded-full blur-2xl opacity-50"></div>
                                <div class="relative glassmorphism w-48 h-48 rounded-full flex items-center justify-center mx-auto border border-slate-700/50">
                                    <i class="fas fa-users text-6xl text-slate-400"></i>
                                </div>
                            </div>
                            <h3 class="text-4xl font-bold text-white mb-6">Aucun profil public</h3>
                            <p class="text-xl text-slate-400 max-w-2xl mx-auto mb-8 leading-relaxed">
                                Soyez le premier à rejoindre notre communauté visible et à partager votre engagement pour la sécurité
                            </p>
                            <a href="profile.php" class="group inline-flex items-center gap-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 shadow-2xl hover:shadow-blue-500/25 transform hover:-translate-y-2 hover:scale-105">
                                <i class="fas fa-user-plus group-hover:scale-110 transition-transform"></i>
                                Rendre mon profil public
                            </a>
                        </div>
                    <?php else: ?>

                        <!-- Filters and Search -->
                        <div class="flex flex-col lg:flex-row gap-6 mb-16">
                            <div class="flex-1">
                                <div class="relative">
                                    <input type="text" id="searchMembers" placeholder="Rechercher un membre..."
                                           class="w-full pl-12 pr-4 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300">
                                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <select id="roleFilter" class="px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                                    <option value="">Tous les rôles</option>
                                    <option value="admin">Administrateurs</option>
                                    <option value="moderator">Modérateurs</option>
                                    <option value="verified">Vérifiés</option>
                                    <option value="user">Utilisateurs</option>
                                </select>
                                <select id="sortFilter" class="px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                                    <option value="activity">Dernière activité</option>
                                    <option value="name">Nom (A-Z)</option>
                                    <option value="role">Rôle</option>
                                    <option value="date">Date d'inscription</option>
                                </select>
                            </div>
                        </div>

                        <!-- Members Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-20" id="membersGrid">
                            <?php foreach ($publicUsers as $index => $member): ?>
                                <div class="member-card group relative" data-member='<?= json_encode($member) ?>' style="animation-delay: <?= $index * 0.1 ?>s">
                                    <!-- Glow effect -->
                                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-500/20 to-purple-500/20 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>

                                    <!-- Card content -->
                                    <div class="relative glassmorphism rounded-3xl overflow-hidden border border-white/10 group-hover:border-white/20 transition-all duration-500 hover:transform hover:scale-[1.02] hover:-translate-y-2">

                                        <!-- Banner -->
                                        <div class="relative h-32 overflow-hidden">
                                            <?php if ($member['banner'] && file_exists('../Assets/Images/banners/' . $member['banner'])): ?>
                                                <img src="../Assets/Images/banners/<?= htmlspecialchars($member['banner']) ?>"
                                                     alt="Bannière" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 group-hover:scale-110 transition-transform duration-700"></div>
                                            <?php endif; ?>

                                            <!-- Role badge -->
                                            <div class="absolute top-4 right-4">
                                                <?php
                                                $badgeConfig = [
                                                    'admin' => ['bg' => 'bg-red-500', 'icon' => 'crown', 'text' => 'Admin'],
                                                    'moderator' => ['bg' => 'bg-blue-500', 'icon' => 'shield-alt', 'text' => 'Modo'],
                                                    'verified' => ['bg' => 'bg-green-500', 'icon' => 'check-circle', 'text' => 'Vérifié'],
                                                    'user' => ['bg' => 'bg-slate-500', 'icon' => 'user', 'text' => 'Membre']
                                                ];
                                                $badge = $badgeConfig[$member['role']] ?? $badgeConfig['user'];
                                                ?>
                                                <div class="<?= $badge['bg'] ?> text-white px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1 shadow-lg backdrop-blur-sm">
                                                    <i class="fas fa-<?= $badge['icon'] ?> text-xs"></i>
                                                    <span class="hidden sm:inline"><?= $badge['text'] ?></span>
                                                </div>
                                            </div>

                                            <!-- Online status -->
                                            <div class="absolute top-4 left-4">
                                                <div class="flex items-center gap-2 bg-black/30 backdrop-blur-sm rounded-full px-3 py-1">
                                                    <div class="w-3 h-3 <?= $member['is_active'] ? 'bg-green-500 animate-pulse' : 'bg-slate-400' ?> rounded-full"></div>
                                                    <span class="text-white text-xs font-medium"><?= $member['is_active'] ? 'En ligne' : 'Hors ligne' ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Avatar -->
                                        <div class="absolute top-20 left-1/2 transform -translate-x-1/2">
                                            <div class="relative">
                                                <div class="w-24 h-24 rounded-full border-4 border-white/20 overflow-hidden shadow-2xl group-hover:border-white/40 transition-all duration-300">
                                                    <?php if ($member['avatar'] && file_exists('../Assets/Images/avatars/' . $member['avatar'])): ?>
                                                        <img src="../Assets/Images/avatars/<?= htmlspecialchars($member['avatar']) ?>"
                                                             alt="Avatar" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                                    <?php else: ?>
                                                        <div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-2xl font-bold">
                                                            <?= strtoupper(substr($member['username'], 0, 1)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <!-- Verification badge -->
                                                <?php if ($member['role'] === 'verified' || $member['role'] === 'admin'): ?>
                                                    <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-blue-500 rounded-full border-4 border-white/20 flex items-center justify-center">
                                                        <i class="fas fa-check text-white text-xs"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="pt-16 pb-6 px-6 text-center">
                                            <!-- Name and title -->
                                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-300 transition-colors">
                                                <?= htmlspecialchars($member['username']) ?>
                                            </h3>

                                            <?php if (!empty($member['organization'])): ?>
                                                <p class="text-slate-400 text-sm mb-3 flex items-center justify-center gap-2">
                                                    <i class="fas fa-building text-xs"></i>
                                                    <?= htmlspecialchars($member['organization']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- Bio preview -->
                                            <?php if (!empty($member['bio'])): ?>
                                                <p class="text-slate-300 text-sm mb-4 line-clamp-2 leading-relaxed">
                                                    <?= htmlspecialchars(substr($member['bio'], 0, 100)) ?><?= strlen($member['bio']) > 100 ? '...' : '' ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- Stats -->
                                            <div class="flex justify-center items-center gap-4 mb-6 text-xs text-slate-400">
                                                <div class="flex items-center gap-1">
                                                    <i class="fas fa-calendar"></i>
                                                    <span><?= date('M Y', strtotime($member['created_at'])) ?></span>
                                                </div>
                                                <?php if ($member['last_activity']): ?>
                                                    <div class="w-px h-4 bg-slate-600"></div>
                                                    <div class="flex items-center gap-1">
                                                        <i class="fas fa-clock"></i>
                                                        <span><?= timeAgo($member['last_activity']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Social links -->
                                            <div class="flex justify-center gap-3 mb-6">
                                                <?php if (!empty($member['website'])): ?>
                                                    <a href="<?= htmlspecialchars($member['website']) ?>" target="_blank"
                                                       class="w-8 h-8 bg-white/10 hover:bg-blue-500 rounded-full flex items-center justify-center text-slate-400 hover:text-white transition-all duration-300 hover:scale-110">
                                                        <i class="fas fa-globe text-xs"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['github'])): ?>
                                                    <a href="<?= htmlspecialchars($member['github']) ?>" target="_blank"
                                                       class="w-8 h-8 bg-white/10 hover:bg-gray-800 rounded-full flex items-center justify-center text-slate-400 hover:text-white transition-all duration-300 hover:scale-110">
                                                        <i class="fab fa-github text-xs"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['linkedin'])): ?>
                                                    <a href="<?= htmlspecialchars($member['linkedin']) ?>" target="_blank"
                                                       class="w-8 h-8 bg-white/10 hover:bg-blue-600 rounded-full flex items-center justify-center text-slate-400 hover:text-white transition-all duration-300 hover:scale-110">
                                                        <i class="fab fa-linkedin text-xs"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>

                                            <!-- View profile button -->
                                            <button onclick="showProfile(<?= $member['id'] ?>)"
                                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 px-6 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 group-hover:shadow-blue-500/25">
                                                <i class="fas fa-eye mr-2"></i>Voir le profil
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Community Stats -->
                        <div class="glassmorphism rounded-3xl p-10 border border-white/10 mb-20">
                            <div class="text-center mb-12">
                                <h2 class="text-4xl font-bold bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent mb-4">
                                    Statistiques de la Communauté
                                </h2>
                                <div class="h-1 w-32 bg-gradient-to-r from-blue-600 via-white to-red-600 rounded-full mx-auto"></div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                                <?php
                                $stats = [
                                    ['count' => count(array_filter($publicUsers, fn($u) => $u['role'] === 'admin')), 'label' => 'Administrateurs', 'icon' => 'crown', 'color' => 'red', 'gradient' => 'from-red-500 to-pink-500'],
                                    ['count' => count(array_filter($publicUsers, fn($u) => $u['role'] === 'moderator')), 'label' => 'Modérateurs', 'icon' => 'shield-alt', 'color' => 'blue', 'gradient' => 'from-blue-500 to-cyan-500'],
                                    ['count' => count(array_filter($publicUsers, fn($u) => $u['role'] === 'verified')), 'label' => 'Vérifiés', 'icon' => 'check-circle', 'color' => 'green', 'gradient' => 'from-green-500 to-emerald-500'],
                                    ['count' => count(array_filter($publicUsers, fn($u) => $u['role'] === 'user')), 'label' => 'Membres', 'icon' => 'users', 'color' => 'purple', 'gradient' => 'from-purple-500 to-indigo-500']
                                ];
                                ?>

                                <?php foreach ($stats as $stat): ?>
                                    <div class="stat-card group text-center">
                                        <div class="relative mb-6">
                                            <div class="absolute inset-0 bg-gradient-to-r <?= $stat['gradient'] ?> rounded-2xl blur-xl opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                                            <div class="relative w-20 h-20 mx-auto bg-gradient-to-r <?= $stat['gradient'] ?> rounded-2xl flex items-center justify-center shadow-2xl group-hover:scale-110 transition-transform duration-300">
                                                <i class="fas fa-<?= $stat['icon'] ?> text-white text-2xl"></i>
                                            </div>
                                        </div>
                                        <div class="text-4xl font-bold text-white mb-2 font-mono counter" data-target="<?= $stat['count'] ?>">0</div>
                                        <div class="text-slate-400 font-medium group-hover:text-slate-300 transition-colors"><?= $stat['label'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Call to Action -->
                    <div class="glassmorphism rounded-3xl p-12 text-center border border-white/10 relative overflow-hidden">
                        <!-- Background patterns -->
                        <div class="absolute inset-0 opacity-5">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600"></div>
                            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
                        </div>

                        <div class="relative z-10">
                            <div class="w-32 h-32 mx-auto mb-8 bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-full flex items-center justify-center shadow-2xl animate-pulse-slow">
                                <i class="fas fa-user-plus text-white text-4xl"></i>
                            </div>

                            <h2 class="text-5xl font-bold mb-6 bg-gradient-to-r from-blue-300 via-white to-purple-300 bg-clip-text text-transparent">
                                Rejoignez-nous
                            </h2>

                            <p class="text-xl text-slate-300 max-w-3xl mx-auto mb-10 leading-relaxed">
                                Faites partie de notre communauté engagée ! Rendez votre profil public et connectez-vous avec d'autres membres passionnés par la sécurité et la protection de tous.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                                <a href="profile.php" class="group inline-flex items-center gap-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 shadow-2xl hover:shadow-blue-500/25 transform hover:-translate-y-2 hover:scale-105">
                                    <i class="fas fa-user-cog group-hover:scale-110 transition-transform"></i>
                                    Gérer mon profil
                                </a>
                                <a href="signal.php" class="group inline-flex items-center gap-3 bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 border border-white/20 hover:border-white/40 backdrop-blur-sm transform hover:-translate-y-2 hover:scale-105">
                                    <i class="fas fa-plus group-hover:scale-110 transition-transform"></i>
                                    Créer un signalement
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <style>
        /* Enhanced CSS with modern animations and effects */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        /* Glassmorphism effect */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Floating orbs background */
        .floating-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.7;
            animation: float-around 20s infinite ease-in-out;
        }

        .orb-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(45deg, #8b5cf6, #5b21b6);
            top: 50%;
            right: 10%;
            animation-delay: 5s;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: linear-gradient(45deg, #ec4899, #be185d);
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
        }

        .orb-4 {
            width: 350px;
            height: 350px;
            background: linear-gradient(45deg, #06b6d4, #0891b2);
            top: 30%;
            left: 50%;
            animation-delay: 15s;
        }

        .orb-5 {
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, #f59e0b, #d97706);
            bottom: 10%;
            right: 30%;
            animation-delay: 7s;
        }

        /* Grid background pattern */
        .bg-grid-pattern {
            background-image:
                    linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: grid-move 20s linear infinite;
        }

        /* Advanced animations */
        @keyframes float-around {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(30px, -40px) rotate(90deg) scale(1.1);
            }
            50% {
                transform: translate(-20px, 20px) rotate(180deg) scale(0.9);
            }
            75% {
                transform: translate(40px, 30px) rotate(270deg) scale(1.05);
            }
        }

        @keyframes grid-move {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        @keyframes pulse-slow {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(100px) scale(0.9) rotateX(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1) rotateX(0deg);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8) rotateY(20deg);
            }
            to {
                opacity: 1;
                transform: scale(1) rotateY(0deg);
            }
        }

        @keyframes counter-animation {
            from { transform: scale(0.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Member card animations */
        .member-card {
            animation: fadeInScale 0.8s ease-out;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .member-card:hover {
            transform: translateY(-10px) rotateX(5deg);
        }

        /* Stat card hover effects */
        .stat-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.05);
        }

        /* Utility classes */
        .animate-pulse-slow {
            animation: pulse-slow 4s ease-in-out infinite;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Gradient backgrounds */
        .bg-gradient-radial {
            background: radial-gradient(circle, var(--tw-gradient-stops));
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .floating-orb {
                width: 150px !important;
                height: 150px !important;
                filter: blur(40px);
            }

            .member-card:hover {
                transform: translateY(-5px);
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #2563eb, #7c3aed);
        }

        /* Enhanced select styles */
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        select option {
            background-color: #1e293b;
            color: white;
        }
    </style>

    <script>
        // Enhanced JavaScript with advanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            initializeAnimations();
            initializeSearch();
            initializeCounters();
            initializeParallax();
            initializeCardHovers();
        });

        function initializeAnimations() {
            // Intersection Observer for scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -100px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = `${index * 0.1}s`;
                        entry.target.classList.add('animate-slideInUp');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all member cards
            document.querySelectorAll('.member-card').forEach(card => {
                observer.observe(card);
            });
        }

        function initializeSearch() {
            const searchInput = document.getElementById('searchMembers');
            const roleFilter = document.getElementById('roleFilter');
            const sortFilter = document.getElementById('sortFilter');
            const membersGrid = document.getElementById('membersGrid');

            function filterMembers() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedRole = roleFilter.value;
                const sortBy = sortFilter.value;

                const cards = Array.from(membersGrid.children);

                // Filter cards
                const filteredCards = cards.filter(card => {
                    const memberData = JSON.parse(card.dataset.member);
                    const matchesSearch = memberData.username.toLowerCase().includes(searchTerm) ||
                        memberData.email.toLowerCase().includes(searchTerm) ||
                        (memberData.organization || '').toLowerCase().includes(searchTerm);
                    const matchesRole = !selectedRole || memberData.role === selectedRole;

                    return matchesSearch && matchesRole;
                });

                // Sort cards
                filteredCards.sort((a, b) => {
                    const memberA = JSON.parse(a.dataset.member);
                    const memberB = JSON.parse(b.dataset.member);

                    switch(sortBy) {
                        case 'name':
                            return memberA.username.localeCompare(memberB.username);
                        case 'role':
                            const roleOrder = { admin: 0, moderator: 1, verified: 2, user: 3 };
                            return (roleOrder[memberA.role] || 3) - (roleOrder[memberB.role] || 3);
                        case 'date':
                            return new Date(memberB.created_at) - new Date(memberA.created_at);
                        default: // activity
                            return new Date(memberB.last_activity || 0) - new Date(memberA.last_activity || 0);
                    }
                });

                // Hide all cards first
                cards.forEach(card => {
                    card.style.display = 'none';
                    card.style.animation = 'none';
                });

                // Show filtered cards with stagger animation
                filteredCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.display = 'block';
                        card.style.animation = `fadeInScale 0.6s ease-out ${index * 0.1}s both`;
                    }, 50);
                });
            }

            // Add event listeners
            searchInput.addEventListener('input', debounce(filterMembers, 300));
            roleFilter.addEventListener('change', filterMembers);
            sortFilter.addEventListener('change', filterMembers);
        }

        function initializeCounters() {
            const counters = document.querySelectorAll('.counter');

            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = parseInt(counter.dataset.target);
                        animateCounter(counter, target);
                        counterObserver.unobserve(counter);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => {
                counterObserver.observe(counter);
            });
        }

        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 60; // 60 frames for smooth animation

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 16); // ~60fps
        }

        function initializeParallax() {
            let ticking = false;

            function updateParallax() {
                const scrolled = window.pageYOffset;
                const orbs = document.querySelectorAll('.floating-orb');

                orbs.forEach((orb, index) => {
                    const speed = (index + 1) * 0.05;
                    const yPos = scrolled * speed;
                    const rotation = scrolled * 0.02;

                    orb.style.transform = `translate3d(0, ${yPos}px, 0) rotate(${rotation}deg)`;
                });

                ticking = false;
            }

            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }

            window.addEventListener('scroll', requestTick, { passive: true });
        }

        function initializeCardHovers() {
            const cards = document.querySelectorAll('.member-card');

            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    // Add subtle glow effect to neighboring cards
                    const siblings = Array.from(this.parentElement.children);
                    siblings.forEach((sibling, index) => {
                        if (sibling !== this) {
                            const distance = Math.abs(siblings.indexOf(this) - index);
                            if (distance <= 2) {
                                sibling.style.opacity = '0.7';
                                sibling.style.transform = 'scale(0.95)';
                            }
                        }
                    });
                });

                card.addEventListener('mouseleave', function() {
                    // Reset all cards
                    const siblings = Array.from(this.parentElement.children);
                    siblings.forEach(sibling => {
                        sibling.style.opacity = '1';
                        sibling.style.transform = 'scale(1)';
                    });
                });

                // Advanced 3D hover effect
                card.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;

                    const rotateX = (y - centerY) / 20;
                    const rotateY = (centerX - x) / 20;

                    this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(20px)`;
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
                });
            });
        }

        // Profile modal function (enhanced)
        function showProfile(memberId) {
            // Find member data
            const memberCard = document.querySelector(`[data-member*='"id":${memberId}']`);
            if (!memberCard) return;

            const member = JSON.parse(memberCard.dataset.member);
            createAdvancedProfileModal(member);
        }

        function createAdvancedProfileModal(member) {
            // Create modal with enhanced design
            const modal = document.createElement('div');
            modal.id = 'profileModal';
            modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md';
            modal.style.animation = 'fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1)';

            const modalContent = `
        <div class="relative w-full max-w-2xl glassmorphism rounded-3xl overflow-hidden border border-white/20 shadow-2xl transform transition-all duration-500 max-h-[90vh] overflow-y-auto">
            <!-- Enhanced header with parallax effect -->
            <div class="relative h-48 overflow-hidden">
                ${member.banner ?
                `<img src="../Assets/Images/banners/${member.banner}" alt="Bannière" class="w-full h-full object-cover">` :
                `<div class="w-full h-full bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600"></div>`
            }
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>

                <!-- Close button -->
                <button onclick="closeProfileModal()" class="absolute top-6 right-6 w-10 h-10 glassmorphism rounded-full flex items-center justify-center text-white hover:bg-white/20 transition-all duration-300 group">
                    <i class="fas fa-times group-hover:scale-110 transition-transform"></i>
                </button>

                <!-- Status indicators -->
                <div class="absolute top-6 left-6 flex items-center gap-3">
                    <div class="glassmorphism px-3 py-1 rounded-full flex items-center gap-2">
                        <div class="w-3 h-3 ${member.is_active ? 'bg-green-500 animate-pulse' : 'bg-gray-400'} rounded-full"></div>
                        <span class="text-white text-sm font-medium">${member.is_active ? 'En ligne' : 'Hors ligne'}</span>
                    </div>
                    ${getRoleIndicator(member.role)}
                </div>
            </div>

            <!-- Profile content -->
            <div class="relative -mt-20 z-10 px-8 pb-8">
                <!-- Avatar section -->
                <div class="text-center mb-8">
                    <div class="relative inline-block">
                        <div class="w-32 h-32 rounded-full border-4 border-white/30 overflow-hidden shadow-2xl mx-auto bg-gradient-to-br from-blue-500 to-purple-500">
                            ${member.avatar ?
                `<img src="../Assets/Images/avatars/${member.avatar}" alt="Avatar" class="w-full h-full object-cover">` :
                `<div class="w-full h-full flex items-center justify-center text-white text-4xl font-bold">
                                    ${member.username.charAt(0).toUpperCase()}
                                </div>`
            }
                        </div>
                        ${member.role === 'verified' || member.role === 'admin' ?
                '<div class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-500 rounded-full border-4 border-white/30 flex items-center justify-center"><i class="fas fa-check text-white"></i></div>' : ''
            }
                    </div>

                    <h2 class="text-3xl font-bold text-white mt-6 mb-2">${member.username}</h2>
                    ${member.organization ? `<p class="text-slate-300 mb-4">${member.organization}</p>` : ''}
                </div>

                <!-- Enhanced content sections -->
                <div class="space-y-6">
                    ${member.bio ? `
                        <div class="glassmorphism rounded-2xl p-6 border border-white/10">
                            <h3 class="text-white font-semibold mb-3 flex items-center gap-2">
                                <i class="fas fa-quote-left text-blue-400"></i>
                                À propos
                            </h3>
                            <p class="text-slate-300 leading-relaxed">${member.bio}</p>
                        </div>
                    ` : ''}

                    <!-- Info grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="glassmorphism rounded-2xl p-6 border border-white/10">
                            <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                                <i class="fas fa-info-circle text-blue-400"></i>
                                Informations
                            </h4>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-envelope text-slate-400 w-4"></i>
                                    <span class="text-slate-300">${member.email}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-calendar text-slate-400 w-4"></i>
                                    <span class="text-slate-300">Membre depuis ${formatDate(member.created_at)}</span>
                                </div>
                                ${member.last_activity ? `
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-clock text-slate-400 w-4"></i>
                                        <span class="text-slate-300">Actif ${formatTimeAgo(member.last_activity)}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>

                        ${(member.website || member.github || member.linkedin) ? `
                            <div class="glassmorphism rounded-2xl p-6 border border-white/10">
                                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                                    <i class="fas fa-link text-blue-400"></i>
                                    Liens
                                </h4>
                                <div class="space-y-3">
                                    ${member.website ? `
                                        <a href="${member.website}" target="_blank" class="flex items-center gap-3 p-3 glassmorphism rounded-xl hover:bg-white/10 transition-all duration-300 group">
                                            <i class="fas fa-globe text-blue-400 group-hover:scale-110 transition-transform"></i>
                                            <span class="text-slate-300 group-hover:text-white transition-colors">Site Web</span>
                                            <i class="fas fa-external-link-alt text-slate-500 ml-auto text-xs"></i>
                                        </a>
                                    ` : ''}
                                    ${member.github ? `
                                        <a href="${member.github}" target="_blank" class="flex items-center gap-3 p-3 glassmorphism rounded-xl hover:bg-white/10 transition-all duration-300 group">
                                            <i class="fab fa-github text-gray-300 group-hover:scale-110 transition-transform"></i>
                                            <span class="text-slate-300 group-hover:text-white transition-colors">GitHub</span>
                                            <i class="fas fa-external-link-alt text-slate-500 ml-auto text-xs"></i>
                                        </a>
                                    ` : ''}
                                    ${member.linkedin ? `
                                        <a href="${member.linkedin}" target="_blank" class="flex items-center gap-3 p-3 glassmorphism rounded-xl hover:bg-white/10 transition-all duration-300 group">
                                            <i class="fab fa-linkedin text-blue-400 group-hover:scale-110 transition-transform"></i>
                                            <span class="text-slate-300 group-hover:text-white transition-colors">LinkedIn</span>
                                            <i class="fas fa-external-link-alt text-slate-500 ml-auto text-xs"></i>
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;

            modal.innerHTML = modalContent;
            document.body.appendChild(modal);

            // Enhanced event listeners
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeProfileModal();
            });

            document.addEventListener('keydown', handleEscapeKey);
        }

        function getRoleIndicator(role) {
            const indicators = {
                'admin': '<div class="glassmorphism px-3 py-1 rounded-full bg-red-500/20 border border-red-400/30"><span class="text-red-300 text-sm font-bold flex items-center gap-1"><i class="fas fa-crown"></i>Admin</span></div>',
                'moderator': '<div class="glassmorphism px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30"><span class="text-blue-300 text-sm font-bold flex items-center gap-1"><i class="fas fa-shield-alt"></i>Modo</span></div>',
                'verified': '<div class="glassmorphism px-3 py-1 rounded-full bg-green-500/20 border border-green-400/30"><span class="text-green-300 text-sm font-bold flex items-center gap-1"><i class="fas fa-check-circle"></i>Vérifié</span></div>',
                'user': '<div class="glassmorphism px-3 py-1 rounded-full bg-gray-500/20 border border-gray-400/30"><span class="text-gray-300 text-sm font-bold flex items-center gap-1"><i class="fas fa-user"></i>Membre</span></div>'
            };
            return indicators[role] || indicators['user'];
        }

        function closeProfileModal() {
            const modal = document.getElementById('profileModal');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease-in';
                setTimeout(() => {
                    modal.remove();
                    document.removeEventListener('keydown', handleEscapeKey);
                }, 300);
            }
        }

        function handleEscapeKey(e) {
            if (e.key === 'Escape') closeProfileModal();
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function formatTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));

            if (diffInHours < 1) return 'il y a moins d\'1h';
            if (diffInHours < 24) return `il y a ${diffInHours}h`;
            if (diffInHours < 168) return `il y a ${Math.floor(diffInHours / 24)}j`;
            return `il y a ${Math.floor(diffInHours / 168)} semaine(s)`;
        }

        // Utility function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Add fadeIn/fadeOut animations
        const style = document.createElement('style');
        style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
        document.head.appendChild(style);
    </script>

<?php include_once('../Inc/Components/footers.php'); ?>
<?php include_once('../Inc/Components/footer.php'); ?>