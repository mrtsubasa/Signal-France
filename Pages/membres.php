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
    function getRoleBadge($role)
    {
        switch ($role) {
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
    function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);
        if ($time < 60)
            return 'maintenant';
        if ($time < 3600)
            return floor($time / 60) . 'm';
        if ($time < 86400)
            return floor($time / 3600) . 'h';
        if ($time < 2592000)
            return floor($time / 86400) . 'j';
        if ($time < 31536000)
            return floor($time / 2592000) . ' mois';
        return floor($time / 31536000) . ' ans';
    }

} catch (Exception $e) {
    error_log("Erreur dans membres.php: " . $e->getMessage());
    $publicUsers = [];
}
?>
<main>
    <!-- Section Membres avec Tailwind -->
    <section class="py-20 px-5 max-w-7xl mx-auto">
        <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-slate-900 via-blue-900 to-slate-800">
            <!-- Particules flottantes avec couleurs France -->
            <div class="absolute inset-0">
                <div class="particle w-32 h-32 bg-blue-500 top-10 left-10 opacity-20 blur-xl"
                    style="animation-delay: 0s;"></div>
                <div class="particle w-24 h-24 bg-red-500 top-40 right-20 opacity-15 blur-xl"
                    style="animation-delay: 2s;"></div>
                <div class="particle w-28 h-28 bg-white bottom-20 left-20 opacity-25 blur-xl"
                    style="animation-delay: 4s;"></div>
                <div class="particle w-20 h-20 bg-blue-600 bottom-40 right-10 opacity-20 blur-xl"
                    style="animation-delay: 1s;"></div>
                <div class="particle w-16 h-16 bg-red-400 top-1/2 left-1/3 opacity-15 blur-xl"
                    style="animation-delay: 3s;"></div>
            </div>

            <!-- Mesh gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/20 via-transparent to-red-900/20"></div>

            <div class="relative z-10">
                <!-- En-tête de section avec design premium -->
                <section class="py-20 px-5 max-w-7xl mx-auto">
                    <div class="text-center mb-16">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-red-500 rounded-full mb-6 shadow-2xl morphing-bg">
                            <i class="fas fa-users text-white text-3xl"></i>
                        </div>
                        <h1
                            class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-white via-blue-200 to-white bg-clip-text text-transparent mb-4">
                            Nos Membres
                        </h1>
                        <p class="text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
                            Découvrez les profils publics de notre communauté engagée pour la sécurité en France
                        </p>
                        <div class="mt-8 flex justify-center">
                            <div class="france-gradient h-1 w-32 rounded-full"></div>
                        </div>
                    </div>

                    <?php if (empty($publicUsers)): ?>
                        <!-- État vide amélioré -->
                        <div class="text-center py-20 member-card">
                            <div
                                class="glassmorphism-premium w-40 h-40 rounded-full flex items-center justify-center mx-auto mb-8 morphing-bg">
                                <i class="fas fa-users text-6xl text-blue-400"></i>
                            </div>
                            <h3 class="text-4xl font-bold text-white mb-6">Aucun profil public</h3>
                            <p class="text-slate-400 max-w-md mx-auto text-lg leading-relaxed mb-8">
                                Personne n'a encore rendu son profil public.
                                Soyez le premier à rejoindre la communauté visible !
                            </p>
                            <a href="profile.php"
                                class="inline-flex items-center gap-3 bg-gradient-to-r from-blue-500 to-red-500 text-white px-8 py-4 rounded-full font-semibold hover:from-blue-600 hover:to-red-600 transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:-translate-y-2">
                                <i class="fas fa-user-plus"></i>
                                Rendre mon profil public
                            </a>
                        </div>
                    <?php else: ?>

                        <!-- Grille des membres avec design premium -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                            <?php foreach ($publicUsers as $index => $member): ?>
                                <div class="member-card bg-[#2f3136] rounded-lg overflow-hidden hover:bg-[#36393f] transition-all duration-200 relative"
                                    data-role="<?= htmlspecialchars($member['role']) ?>"
                                    style="animation-delay: <?= $index * 0.1 ?>s">

                                    <!-- Bannière style Discord -->
                                    <div class="h-24 relative overflow-hidden bg-[#5865f2]">
                                        <?php if ($member['banner'] && file_exists('../Assets/Images/banners/' . $member['banner'])): ?>
                                            <img src="../Assets/Images/banners/<?= htmlspecialchars($member['banner']) ?>"
                                                alt="Bannière de <?= htmlspecialchars($member['username']) ?>"
                                                class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gradient-to-r from-[#5865f2] to-[#7289da]"></div>
                                        <?php endif; ?>

                                        <!-- Badge rôle style Discord -->
                                        <?php
                                        $badgeColors = [
                                            'admin' => 'bg-[#f23f42]',
                                            'moderator' => 'bg-[#5865f2]',
                                            'verified' => 'bg-[#57f287]',
                                            'user' => 'bg-[#99aab5]'
                                        ];
                                        $badgeClass = $badgeColors[$member['role']] ?? $badgeColors['user'];
                                        ?>
                                        <div
                                            class="absolute top-3 right-3 px-2 py-1 <?= $badgeClass ?> text-white rounded text-xs font-semibold uppercase">
                                            <?= htmlspecialchars($member['role']) ?>
                                        </div>
                                    </div>

                                    <!-- Avatar style Discord - chevauche entre bannière et contenu -->
                                    <div class="absolute top-16 left-4 z-10">
                                        <div class="relative">
                                            <div
                                                class="w-20 h-20 rounded-full border-6 border-[#2f3136] overflow-hidden bg-[#36393f] hover:border-[#5865f2] transition-all duration-200">
                                                <?php if ($member['avatar'] && file_exists('../Assets/Images/avatars/' . $member['avatar'])): ?>
                                                    <img src="../Assets/Images/avatars/<?= htmlspecialchars($member['avatar']) ?>"
                                                        alt="Avatar de <?= htmlspecialchars($member['username']) ?>"
                                                        class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div
                                                        class="w-full h-full bg-[#5865f2] flex items-center justify-center text-white text-xl font-bold">
                                                        <?= strtoupper(substr($member['username'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Indicateur de statut style Discord -->
                                            <div
                                                class="absolute -bottom-1 -right-1 w-6 h-6 <?= $member['is_active'] ? 'bg-[#23a55a]' : 'bg-[#80848e]' ?> border-4 border-[#2f3136] rounded-full">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contenu principal style Discord -->
                                    <div class="pt-12 pb-4 px-4">
                                        <!-- Nom et discriminator style Discord -->
                                        <div class="mb-3">
                                            <h3 class="text-white text-lg font-semibold hover:underline cursor-pointer">
                                                <?= htmlspecialchars($member['username']) ?>
                                            </h3>
                                            <p class="text-[#b9bbbe] text-sm">
                                                <?= htmlspecialchars($member['email']) ?>
                                            </p>
                                        </div>

                                        <!-- Informations style Discord -->
                                        <div class="space-y-2 text-sm">
                                            <?php if (!empty($member['organization'])): ?>
                                                <div class="flex items-center gap-2 text-[#b9bbbe]">
                                                    <i class="fas fa-building text-[#b9bbbe] w-4"></i>
                                                    <span><?= htmlspecialchars($member['organization']) ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($member['accreditation'])): ?>
                                                <div class="flex items-center gap-2 text-[#b9bbbe]">
                                                    <i class="fas fa-certificate text-[#faa61a] w-4"></i>
                                                    <span><?= htmlspecialchars($member['accreditation']) ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex items-center gap-2 text-[#b9bbbe]">
                                                <i class="fas fa-calendar text-[#b9bbbe] w-4"></i>
                                                <span>Membre depuis <?= date('M Y', strtotime($member['created_at'])) ?></span>
                                            </div>
                                        </div>

                                        <!-- Bio style Discord -->
                                        <?php if (!empty($member['bio'])): ?>
                                            <div class="mt-3 p-3 bg-[#202225] rounded border-l-4 border-[#5865f2]">
                                                <p class="text-[#dcddde] text-sm leading-relaxed">
                                                    <?= nl2br(htmlspecialchars($member['bio'])) ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Bouton d'action style Discord -->
                                        <div class="mt-4">
                                            <button onclick="showProfile(<?= $member['id'] ?>)"
                                                class="w-full bg-[#5865f2] hover:bg-[#4752c4] text-white py-3 px-4 rounded-md text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-lg hover:scale-[1.02]">
                                                <i class="fas fa-eye"></i>
                                                Voir le profil
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                </div>




                <!-- Section Stats avec design premium -->
                <div class="mt-20 glassmorphism-premium rounded-3xl p-10 interactive-hover">
                    <div class="text-center mb-12">
                        <h2
                            class="text-4xl font-bold bg-gradient-to-r from-white via-blue-200 to-white bg-clip-text text-transparent mb-4">
                            Statistiques de la communauté
                        </h2>
                        <div class="france-gradient h-1 w-24 rounded-full mx-auto"></div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <?php
                        $adminCount = count(array_filter($publicUsers, function ($u) {
                            return $u['role'] === 'admin'; }));
                        $modCount = count(array_filter($publicUsers, function ($u) {
                            return $u['role'] === 'moderator'; }));
                        $userCount = count(array_filter($publicUsers, function ($u) {
                            return $u['role'] === 'user'; }));
                        $verifiedCount = count(array_filter($publicUsers, function ($u) {
                            return $u['role'] === 'verified'; }));

                        $stats = [
                            ['count' => $adminCount, 'label' => 'Admins', 'icon' => 'crown', 'color' => 'red'],
                            ['count' => $modCount, 'label' => 'Modérateurs', 'icon' => 'shield-alt', 'color' => 'blue'],
                            ['count' => $verifiedCount, 'label' => 'Vérifiés', 'icon' => 'check-circle', 'color' => 'green'],
                            ['count' => $userCount, 'label' => 'Utilisateurs', 'icon' => 'users', 'color' => 'purple']
                        ];
                        ?>

                        <?php foreach ($stats as $stat): ?>
                            <div class="text-center stats-counter group">
                                <div
                                    class="w-20 h-20 mx-auto mb-4 glassmorphism-premium rounded-2xl flex items-center justify-center border border-<?= $stat['color'] ?>-500/30 group-hover:border-<?= $stat['color'] ?>-400/50 transition-all duration-300 morphing-bg">
                                    <i
                                        class="fas fa-<?= $stat['icon'] ?> text-3xl text-<?= $stat['color'] ?>-400 group-hover:scale-110 transition-transform"></i>
                                </div>
                                <div
                                    class="text-4xl font-bold text-white mb-2 font-mono group-hover:text-<?= $stat['color'] ?>-300 transition-colors">
                                    <?= $stat['count'] ?></div>
                                <div class="text-slate-400 font-medium group-hover:text-slate-300 transition-colors">
                                    <?= $stat['label'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
    </section>
    <!-- CTA Section avec design France premium -->
    <section class="py-20 px-5 max-w-4xl mx-auto">
        <div
            class="glassmorphism-premium rounded-3xl p-12 text-center text-white relative overflow-hidden interactive-hover">
            <!-- Pattern de fond France -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0 france-gradient"></div>
                <div class="absolute inset-0"
                    style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;">
                </div>
            </div>

            <!-- Particules flottantes -->
            <div class="absolute top-10 left-10 w-4 h-4 bg-blue-400 rounded-full opacity-60 animate-ping"></div>
            <div class="absolute bottom-10 right-10 w-6 h-6 bg-red-400 rounded-full opacity-40 animate-pulse"></div>
            <div class="absolute top-1/2 right-20 w-3 h-3 bg-white rounded-full opacity-50 animate-bounce"></div>

            <div class="relative z-10">
                <div
                    class="w-24 h-24 mx-auto mb-6 glassmorphism-premium rounded-full flex items-center justify-center morphing-bg">
                    <i class="fas fa-user-plus text-3xl text-blue-400"></i>
                </div>
                <h2
                    class="text-5xl font-bold mb-6 bg-gradient-to-r from-blue-200 via-white to-red-200 bg-clip-text text-transparent">
                    Rejoins la communauté
                </h2>
                <p class="text-xl mb-8 text-slate-300 leading-relaxed max-w-2xl mx-auto">
                    Rends ton profil public et montre-toi à la communauté E Conscience !
                    Connecte-toi avec d'autres membres engagés pour la sécurité.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="profile.php"
                        class="inline-flex items-center gap-3 bg-gradient-to-r from-blue-500 to-red-500 text-white px-8 py-4 rounded-full font-semibold hover:from-blue-600 hover:to-red-600 transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:-translate-y-2">
                        <i class="fas fa-user-cog"></i>
                        Modifier mon profil
                    </a>
                    <a href="signal.php"
                        class="inline-flex items-center gap-3 glassmorphism-premium text-white px-8 py-4 rounded-full font-semibold hover:bg-white/20 transition-all duration-300 border border-white/20">
                        <i class="fas fa-plus"></i>
                        Créer un signalement
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    /* Animations personnalisées pour Tailwind */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        33% {
            transform: translateY(-10px) rotate(1deg);
        }

        66% {
            transform: translateY(5px) rotate(-1deg);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    @keyframes pulse-glow {

        0%,
        100% {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }

        50% {
            box-shadow: 0 0 40px rgba(59, 130, 246, 0.6);
        }
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(50px) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
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

    @keyframes morphing {

        0%,
        100% {
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
        }

        50% {
            border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
        }
    }

    .member-card {
        animation: fadeInScale 0.8s ease-out;
        perspective: 1000px;
    }

    .member-card:hover {
        transform: translateY(-15px) rotateX(5deg) rotateY(5deg);
    }

    .floating-avatar {
        animation: float 6s ease-in-out infinite;
    }

    .glassmorphism-premium {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
    }

    .france-gradient {
        background: linear-gradient(135deg, #002395 0%, #FFFFFF 50%, #ED2939 100%);
    }

    .interactive-hover {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .interactive-hover:hover {
        transform: translateY(-12px) scale(1.03);
        box-shadow: 0 40px 80px rgba(0, 0, 0, 0.25);
    }

    .morphing-bg {
        animation: morphing 8s ease-in-out infinite;
    }

    .particle {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        opacity: 0.6;
        animation: float 8s ease-in-out infinite;
    }

    .stats-counter {
        transition: all 0.3s ease;
    }

    .stats-counter:hover {
        transform: scale(1.1) rotateY(10deg);
    }

    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.6s ease-out;
    }

    /* Effet shimmer pour les dégradés */
    .animate-shimmer {
        background-size: 200% 200%;
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }
</style>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include_once('../Inc/Components/footer.php'); ?>

<script>


    function showProfile(memberId) {
        // Récupérer les données du membre via AJAX
        fetch(`profile_ajax.php?action=get_member&id=${memberId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    createProfileModal(data.member);
                } else {
                    console.error('Erreur lors du chargement du profil:', data.message);
                }
            })
            .catch(error => {
                console.error('Erreur réseau:', error);
            });
    }

    function createProfileModal(member) {
        // Créer le modal
        const modal = document.createElement('div');
        modal.id = 'profileModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black bg-opacity-50 backdrop-blur-sm';
        modal.style.animation = 'fadeIn 0.3s ease-out';
        const modalContent = `
    <div class="relative w-full max-w-md bg-gray-800 rounded-lg shadow-2xl transform transition-all duration-300 max-h-[95vh] overflow-y-auto" style="animation: slideUp 0.4s ease-out;">
        <!-- Header avec bannière style Discord -->
        <div class="relative h-20 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-lg overflow-hidden">
            ${member.banner ?
                `<img src="../Assets/Images/banners/${member.banner}" alt="Bannière" class="w-full h-full object-cover opacity-80">` :
                `<div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600"></div>`
            }
            <div class="absolute top-3 right-3">
                <button onclick="closeProfileModal()" class="w-8 h-8 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center text-gray-300 hover:text-white transition-all duration-200">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        
        <!-- Avatar style Discord -->
        <div class="absolute top-12 left-6">
            <div class="relative">
                <div class="w-20 h-20 rounded-full border-6 border-gray-800 overflow-hidden bg-gray-700">
                    ${member.avatar ?
                `<img src="../Assets/Images/avatars/${member.avatar}" alt="Avatar" class="w-full h-full object-cover">` :
                `<div class="w-full h-full bg-gray-600 flex items-center justify-center">
                            <i class="fas fa-user text-2xl text-gray-400"></i>
                        </div>`
            }
                </div>
                ${member.is_active ?
                '<div class="absolute bottom-1 right-1 w-6 h-6 bg-green-500 rounded-full border-4 border-gray-800 flex items-center justify-center"><div class="w-2 h-2 bg-white rounded-full"></div></div>' :
                '<div class="absolute bottom-1 right-1 w-6 h-6 bg-gray-500 rounded-full border-4 border-gray-800"></div>'
            }
            </div>
        </div>
        
        <!-- Badge de rôle style Discord -->
        <div class="absolute top-16 right-6">
            ${getRoleBadgeDiscordStyle(member.role)}
        </div>
        
        <!-- Contenu principal -->
        <div class="pt-16 p-6 bg-gray-800">
            <!-- Nom et statut -->
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <h2 class="text-xl font-bold text-white">${member.username}</h2>
                    ${member.is_verified ?
                '<div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>' :
                ''
            }
                </div>
                <p class="text-gray-400 text-sm">${member.is_active ? '🟢 En ligne' : '⚫ Hors ligne'}</p>
                ${member.organization ?
                `<p class="text-gray-400 text-sm mt-1">${member.organization}</p>` : ''
            }
            </div>
            
            <!-- Section À propos style Discord -->
            <div class="mb-6">
                <h3 class="text-white font-semibold mb-3 text-sm uppercase tracking-wide">À PROPOS DE MOI</h3>
                <div class="bg-gray-700 rounded-lg p-4">
                    ${member.bio ?
                `<p class="text-gray-300 text-sm mb-3">${member.bio}</p>` :
                `<p class="text-gray-400 text-sm italic mb-3">Aucune description disponible</p>`
            }
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-envelope text-gray-400 w-4"></i>
                            <span class="text-gray-300">${member.email}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar text-gray-400 w-4"></i>
                            <span class="text-gray-300">Membre depuis ${formatDate(member.created_at)}</span>
                        </div>
                        ${member.last_login ?
                `<div class="flex items-center gap-2">
                                <i class="fas fa-clock text-gray-400 w-4"></i>
                                <span class="text-gray-300">Dernière activité ${formatDate(member.last_login)}</span>
                            </div>` : ''
            }
                    </div>
                </div>
            </div>
            
            <!-- Statistiques style Discord -->
            <div class="mb-6">
                <h3 class="text-white font-semibold mb-3 text-sm uppercase tracking-wide">ACTIVITÉ</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-white">${member.signals_created || 0}</div>
                        <div class="text-gray-400 text-xs">Signalements créés</div>
                    </div>
                    <div class="bg-gray-700 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-white">${member.signals_processed || 0}</div>
                        <div class="text-gray-400 text-xs">Signalements traités</div>
                    </div>
                </div>
            </div>
            
            <!-- Liens sociaux style Discord -->
            ${(member.website || member.github || member.linkedin) ?
                `<div class="mb-6">
                    <h3 class="text-white font-semibold mb-3 text-sm uppercase tracking-wide">LIENS</h3>
                    <div class="space-y-2">
                        ${member.website ?
                    `<a href="${member.website}" target="_blank" class="flex items-center gap-3 p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors duration-200">
                                <i class="fas fa-globe text-blue-400"></i>
                                <span class="text-white text-sm">Site Web</span>
                                <i class="fas fa-external-link-alt text-gray-400 text-xs ml-auto"></i>
                            </a>` : ''
                }
                        ${member.github ?
                    `<a href="${member.github}" target="_blank" class="flex items-center gap-3 p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors duration-200">
                                <i class="fab fa-github text-gray-300"></i>
                                <span class="text-white text-sm">GitHub</span>
                                <i class="fas fa-external-link-alt text-gray-400 text-xs ml-auto"></i>
                            </a>` : ''
                }
                        ${member.linkedin ?
                    `<a href="${member.linkedin}" target="_blank" class="flex items-center gap-3 p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors duration-200">
                                <i class="fab fa-linkedin text-blue-400"></i>
                                <span class="text-white text-sm">LinkedIn</span>
                                <i class="fas fa-external-link-alt text-gray-400 text-xs ml-auto"></i>
                            </a>` : ''
                }
                    </div>
                </div>` : ''
            }
            
            <!-- Contact style Discord -->
            ${(member.phone || member.address) ?
                `<div>
                    <h3 class="text-white font-semibold mb-3 text-sm uppercase tracking-wide">CONTACT</h3>
                    <div class="bg-gray-700 rounded-lg p-4 space-y-2">
                        ${member.phone ?
                    `<div class="flex items-center gap-3">
                                <i class="fas fa-phone text-green-400"></i>
                                <span class="text-gray-300 text-sm">${member.phone}</span>
                            </div>` : ''
                }
                        ${member.address ?
                    `<div class="flex items-center gap-3">
                                <i class="fas fa-map-marker-alt text-red-400"></i>
                                <span class="text-gray-300 text-sm">${member.address}</span>
                            </div>` : ''
                }
                    </div>
                </div>` : ''
            }
        </div>
    </div>
`;


        modal.innerHTML = modalContent;
        document.body.appendChild(modal);

        // Fermer le modal en cliquant à l'extérieur
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeProfileModal();
            }
        });

        // Fermer avec Escape
        document.addEventListener('keydown', handleEscapeKey);
    }


    function getRoleBadgeDiscordStyle(role) {
        const badges = {
            'admin': '<div class="bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">ADMIN</div>',
            'moderator': '<div class="bg-blue-500 text-white px-2 py-1 rounded text-xs font-bold">MOD</div>',
            'verified': '<div class="bg-green-500 text-white px-2 py-1 rounded text-xs font-bold">VÉRIFIÉ</div>',
            'member': '<div class="bg-gray-500 text-white px-2 py-1 rounded text-xs font-bold">MEMBRE</div>'
        };
        return badges[role] || badges['member'];
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
        if (e.key === 'Escape') {
            closeProfileModal();
        }
    }

    function getRoleBadgeHTML(role) {
        const badges = {
            'admin': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-300 border border-red-400/30"><i class="fas fa-crown mr-1"></i>Administrateur</span>',
            'moderator': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-500/20 text-orange-300 border border-orange-400/30"><i class="fas fa-shield-alt mr-1"></i>Modérateur</span>',
            'verified': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-400/30"><i class="fas fa-check-circle mr-1"></i>Vérifié</span>',
            'user': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-500/20 text-gray-300 border border-gray-400/30"><i class="fas fa-user mr-1"></i>Utilisateur</span>'
        };
        return badges[role] || badges['user'];
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Scripts améliorés avec animations premium
    document.addEventListener('DOMContentLoaded', () => {
        // Intersection Observer pour les animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slideInUp');
                    entry.target.style.opacity = '1';
                }
            });
        }, observerOptions);

        // Observer toutes les cartes de membres
        const memberCards = document.querySelectorAll('.member-card');
        memberCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.animationDelay = `${index * 0.1}s`;
            observer.observe(card);
        });

        // Animation des compteurs avec effet premium
        const animateCounters = () => {
            const counters = document.querySelectorAll('.stats-counter');
            counters.forEach((counter, index) => {
                const target = parseInt(counter.querySelector('div:nth-child(2)').textContent);
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.querySelector('div:nth-child(2)').textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.querySelector('div:nth-child(2)').textContent = Math.floor(current);
                    }
                }, 30);
            });
        };

        // Observer pour les statistiques
        const statsSection = document.querySelector('.stats-counter');
        if (statsSection) {
            const statsObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(animateCounters, 500);
                        statsObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            statsObserver.observe(statsSection);
        }

        // Parallax effect pour les particules
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const particles = document.querySelectorAll('.particle');
            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.1;
                particle.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * 0.05}deg)`;
            });
        });

        // Effet de hover 3D pour les cartes
        memberCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;

                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(20px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
            });
        });

        // Animation des badges au scroll
        const badges = document.querySelectorAll('[class*="badge"]');
        badges.forEach(badge => {
            badge.addEventListener('mouseenter', () => {
                badge.style.transform = 'scale(1.1) rotate(5deg)';
            });
            badge.addEventListener('mouseleave', () => {
                badge.style.transform = 'scale(1) rotate(0deg)';
            });
        });
    });
</script>
</body>

</html>