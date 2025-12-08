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

<style>
/* Enhanced CSS with modern design patterns */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap');

* {
    font-family: 'Inter', sans-serif;
}

/* Background Effects */
.bg-gradient-mesh {
    background:
        radial-gradient(at 40% 20%, hsla(228, 100%, 74%, 0.3) 0px, transparent 25%),
        radial-gradient(at 80% 0%, hsla(189, 100%, 56%, 0.3) 0px, transparent 25%),
        radial-gradient(at 0% 50%, hsla(355, 100%, 93%, 0.3) 0px, transparent 25%),
        radial-gradient(at 80% 50%, hsla(340, 100%, 76%, 0.3) 0px, transparent 25%),
        radial-gradient(at 0% 100%, hsla(22, 100%, 77%, 0.3) 0px, transparent 25%),
        radial-gradient(at 80% 100%, hsla(242, 100%, 70%, 0.3) 0px, transparent 25%),
        radial-gradient(at 0% 0%, hsla(343, 100%, 76%, 0.3) 0px, transparent 25%);
    animation: mesh-animation 20s ease-in-out infinite;
}

@keyframes mesh-animation {
    0%, 100% { transform: scale(1) rotate(0deg); }
    50% { transform: scale(1.1) rotate(2deg); }
}

/* Floating Orbs System */
.floating-orb-system {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.floating-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.7;
    animation: float-complex 25s infinite ease-in-out;
}

.orb-1 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #3b82f6, #1d4ed8);
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 300px;
    height: 300px;
    background: linear-gradient(45deg, #8b5cf6, #5b21b6);
    top: 60%;
    right: 15%;
    animation-delay: 5s;
}

.orb-3 {
    width: 500px;
    height: 500px;
    background: linear-gradient(45deg, #ec4899, #be185d);
    bottom: 20%;
    left: 30%;
    animation-delay: 10s;
}

.orb-4 {
    width: 250px;
    height: 250px;
    background: linear-gradient(45deg, #06b6d4, #0891b2);
    top: 30%;
    left: 60%;
    animation-delay: 15s;
}

.orb-5 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #f59e0b, #d97706);
    bottom: 40%;
    right: 30%;
    animation-delay: 7s;
}

.orb-6 {
    width: 200px;
    height: 200px;
    background: linear-gradient(45deg, #10b981, #059669);
    top: 80%;
    left: 80%;
    animation-delay: 12s;
}

@keyframes float-complex {
    0%, 100% {
        transform: translate(0, 0) rotate(0deg) scale(1);
    }
    25% {
        transform: translate(50px, -60px) rotate(90deg) scale(1.1);
    }
    50% {
        transform: translate(-30px, 40px) rotate(180deg) scale(0.9);
    }
    75% {
        transform: translate(70px, 80px) rotate(270deg) scale(1.05);
    }
}

/* Particle System */
.particle-system .particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: white;
    border-radius: 50%;
    opacity: 0.6;
    animation: particle-float var(--duration) infinite ease-in-out;
    animation-delay: var(--delay);
}

@keyframes particle-float {
    0%, 100% {
        transform: translateY(0) translateX(0);
        opacity: 0;
    }
    10%, 90% {
        opacity: 0.6;
    }
    50% {
        transform: translateY(-100vh) translateX(50px);
        opacity: 0.8;
    }
}

/* Grid Pattern */
.bg-grid-pattern {
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 100px 100px;
    animation: grid-drift 30s linear infinite;
}

@keyframes grid-drift {
    0% { transform: translate(0, 0); }
    100% { transform: translate(100px, 100px); }
}

/* Glassmorphism Premium */
.glassmorphism-premium {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow:
        0 25px 50px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glassmorphism-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Profile Card Premium */
.profile-card-premium {
    animation: profile-entrance 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
    perspective: 1000px;
}

@keyframes profile-entrance {
    0% {
        opacity: 0;
        transform: translateY(100px) rotateX(20deg) scale(0.9);
    }
    100% {
        opacity: 1;
        transform: translateY(0) rotateX(0deg) scale(1);
    }
}

.profile-card-premium:hover {
    transform: translateY(-15px) rotateX(2deg) rotateY(2deg);
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Avatar Container */
.profile-avatar-container {
    animation: avatar-float 6s ease-in-out infinite;
}

@keyframes avatar-float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(2deg); }
}

/* Section Titles */
.section-title {
    display: flex;
    align-items: center;
    space-x: 12px;
    margin-bottom: 24px;
    font-size: 18px;
    font-weight: 600;
    color: white;
}

.section-title-large {
    display: flex;
    align-items: center;
    space-x: 16px;
    margin-bottom: 32px;
    font-size: 24px;
    font-weight: 700;
    color: white;
}

.section-icon, .section-icon-large {
    width: 32px;
    height: 32px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.section-icon-large {
    width: 40px;
    height: 40px;
    border-radius: 16px;
    margin-right: 16px;
}

/* Info Cards */
.info-card {
    display: flex;
    align-items: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.info-card:hover::before {
    opacity: 1;
}

.info-card:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.info-card-link:hover {
    transform: translateY(-8px) scale(1.02);
    background: rgba(255, 255, 255, 0.12);
}

.info-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 20px;
    position: relative;
}

.status-active .info-card-icon {
    background: rgba(16, 185, 129, 0.2);
}

.pulse-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    border: 2px solid rgba(16, 185, 129, 0.4);
    border-radius: 50%;
    animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
    0% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0;
    }
}

.info-card-content {
    flex: 1;
}

.info-card-label {
    font-size: 14px;
    color: rgb(148, 163, 184);
    margin-bottom: 4px;
    font-weight: 500;
}

.info-card-value {
    font-size: 16px;
    font-weight: 600;
    color: white;
}

/* Contact Items */
.contact-item {
    display: flex;
    align-items: center;
    padding: 16px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.contact-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(8px);
}

.contact-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 16px;
}

.contact-text {
    color: rgb(203, 213, 225);
    font-weight: 500;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 32px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 16px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, rgba(59, 130, 246, 0.5), rgba(16, 185, 129, 0.5));
}

.timeline-item {
    position: relative;
    margin-bottom: 24px;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid rgba(15, 23, 42, 1);
    font-size: 12px;
}

.timeline-content {
    background: rgba(255, 255, 255, 0.05);
    padding: 16px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.timeline-title {
    color: white;
    font-weight: 600;
    margin-bottom: 4px;
}

.timeline-time {
    color: rgb(148, 163, 184);
    font-size: 14px;
}

/* Stats */
.stat-card-modern {
    display: flex;
    align-items: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.stat-card-modern:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.stat-content {
    flex: 1;
}

.stat-label {
    color: rgb(148, 163, 184);
    font-size: 14px;
    margin-bottom: 4px;
    font-weight: 500;
}

.stat-value {
    color: white;
    font-size: 24px;
    font-weight: 700;
    font-family: 'Inter', monospace;
}

.stat-badge {
    background: rgba(147, 51, 234, 0.2);
    color: rgb(196, 181, 253);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Action Buttons */
.action-btn {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.6s ease;
}

.action-btn:hover::before {
    left: 100%;
}

.action-btn:hover {
    transform: translateY(-6px) scale(1.02);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.action-btn-orange:hover {
    background: rgba(249, 115, 22, 0.1);
    border-color: rgba(249, 115, 22, 0.3);
}

.action-btn-blue:hover {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
}

.action-btn-red:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
}

.action-btn-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 18px;
    color: white;
    transition: all 0.3s ease;
}

.action-btn:hover .action-btn-icon {
    transform: scale(1.1) rotate(12deg);
}

.action-btn-content {
    flex: 1;
}

.action-btn-title {
    color: white;
    font-weight: 600;
    margin-bottom: 4px;
}

.action-btn-subtitle {
    color: rgb(148, 163, 184);
    font-size: 14px;
}

.action-btn-arrow {
    color: rgb(148, 163, 184);
    transition: all 0.3s ease;
}

.action-btn:hover .action-btn-arrow {
    transform: translateX(8px);
    color: white;
}

/* Buttons */
.btn-primary-sm {
    background: linear-gradient(135deg, rgb(59, 130, 246), rgb(37, 99, 235));
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
}

.btn-primary-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(59, 130, 246, 0.4);
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 32px 16px;
}

.empty-state-icon {
    width: 64px;
    height: 64px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
}

/* Pattern Background */
.bg-pattern-dots {
    background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.1) 1px, transparent 0);
    background-size: 20px 20px;
    animation: pattern-drift 20s linear infinite;
}

@keyframes pattern-drift {
    0% { background-position: 0 0; }
    100% { background-position: 20px 20px; }
}

/* Verified Badge */
.verified-badge {
    animation: verified-glow 3s ease-in-out infinite;
}

@keyframes verified-glow {
    0%, 100% { filter: drop-shadow(0 0 5px rgba(59, 130, 246, 0.5)); }
    50% { filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.8)); }
}


/* Responsive Adjustments */
@media (max-width: 1024px) {
    .floating-orb {
        width: 200px !important;
        height: 200px !important;
        filter: blur(40px);
    }

    .profile-card-premium:hover {
        transform: translateY(-8px);
    }

    .section-title, .section-title-large {
        font-size: 16px;
    }
}

@media (max-width: 768px) {
    .floating-orb {
        width: 150px !important;
        height: 150px !important;
        filter: blur(30px);
    }

    .action-btn {
        padding: 16px;
    }

    .info-card {
        padding: 16px;
    }

    .stat-card-modern {
        padding: 16px;
    }
}
</style>


<!-- Background amélioré avec particules animées -->
    <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950">
        <!-- Background Elements Enhanced -->
        <div class="absolute inset-0">
            <!-- Animated mesh gradient -->
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0 bg-gradient-mesh"></div>
            </div>

            <!-- Dynamic floating orbs -->
            <div class="floating-orb-system">
                <div class="floating-orb orb-1"></div>
                <div class="floating-orb orb-2"></div>
                <div class="floating-orb orb-3"></div>
                <div class="floating-orb orb-4"></div>
                <div class="floating-orb orb-5"></div>
                <div class="floating-orb orb-6"></div>
            </div>

            <!-- Particle system -->
            <div class="particle-system">
                <div class="particle" style="--delay: 0s; --duration: 15s;"></div>
                <div class="particle" style="--delay: 3s; --duration: 20s;"></div>
                <div class="particle" style="--delay: 6s; --duration: 18s;"></div>
                <div class="particle" style="--delay: 9s; --duration: 22s;"></div>
            </div>

            <!-- Grid overlay -->
            <div class="absolute inset-0 bg-grid-pattern opacity-20"></div>

            <!-- Gradient overlays -->
            <div class="absolute inset-0 bg-gradient-radial from-blue-500/10 via-transparent to-purple-500/10"></div>
        </div>

        <div class="relative z-10 container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Profile Section -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Enhanced Profile Header -->
                    <div class="profile-card-premium glassmorphism-premium rounded-3xl overflow-hidden group">
                        <!-- Banner Section with parallax effect -->
                        <div class="relative h-64 overflow-hidden">
                            <?php if ($banner): ?>
                                <img src="../Assets/Images/banners/<?= htmlspecialchars($banner)?>"
                                     alt="Bannière"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 group-hover:from-blue-700 group-hover:via-purple-700 group-hover:to-indigo-800 transition-all duration-700"></div>
                            <?php endif; ?>

                            <!-- Overlay with animated pattern -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute inset-0 opacity-20">
                                <div class="absolute inset-0 bg-pattern-dots"></div>
                            </div>

                            <!-- Floating edit button -->
                            <button onclick="openEditProfileModal()"
                                    class="absolute top-6 right-6 w-14 h-14 glassmorphism-premium rounded-2xl flex items-center justify-center text-white hover:scale-110 hover:rotate-12 transition-all duration-300 group/btn">
                                <i class="fas fa-edit text-lg group-hover/btn:scale-110 transition-transform"></i>
                                <div class="absolute inset-0 rounded-2xl bg-white/20 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                            </button>

                            <!-- Status indicators -->
                            <div class="absolute top-6 left-6 flex space-x-3">
                                <div class="glassmorphism-premium px-4 py-2 rounded-full flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-white text-sm font-medium">En ligne</span>
                                </div>
                                <?php if ($verified): ?>
                                    <div class="glassmorphism-premium px-4 py-2 rounded-full flex items-center space-x-2">
                                        <i class="fas fa-check-circle text-blue-400"></i>
                                        <span class="text-white text-sm font-medium">Vérifié</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Profile Content -->
                        <div class="relative p-8">
                            <!-- Floating Avatar -->
                            <div class="absolute -top-20 left-8">
                                <div class="profile-avatar-container group/avatar">
                                    <div class="relative">
                                        <div class="w-40 h-40 rounded-3xl overflow-hidden shadow-2xl border-4 border-white/30 backdrop-blur-sm transition-all duration-500 group-hover/avatar:border-white/50 group-hover/avatar:scale-105">
                                            <?php if ($avatar): ?>
                                                <img src="../Assets/Images/avatars/<?= htmlspecialchars($avatar)?>"
                                                     alt="Avatar"
                                                     class="w-full h-full object-cover transition-transform duration-700 group-hover/avatar:scale-110">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 flex items-center justify-center">
                                                    <i class="fas fa-user text-white text-5xl"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Avatar glow effect -->
                                        <div class="absolute inset-0 rounded-3xl bg-gradient-to-r from-blue-500/30 to-purple-500/30 blur-xl opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-500 -z-10"></div>

                                        <!-- Online indicator -->
                                        <div class="absolute -bottom-2 -right-2 w-12 h-12 glassmorphism-premium rounded-2xl flex items-center justify-center border-4 border-white/30">
                                            <i class="fas fa-crown text-yellow-400 text-lg animate-pulse"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User Information -->
                            <div class="pt-24">
                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-8">
                                    <div class="space-y-4">
                                        <!-- Name and title -->
                                        <div class="space-y-3">
                                            <div class="flex items-center space-x-4">
                                                <h1 class="text-4xl lg:text-5xl font-black text-white leading-tight">
                                                    <?= htmlspecialchars($username) ?>
                                                </h1>
                                                <?php if ($verified): ?>
                                                    <div class="verified-badge">
                                                        <i class="fas fa-check-circle text-blue-400 text-2xl"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- User meta -->
                                            <div class="flex flex-wrap items-center gap-4 text-slate-300">
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-envelope text-blue-400"></i>
                                                    <span class="font-medium"><?= htmlspecialchars($email) ?></span>
                                                </div>
                                                <?php if ($organization): ?>
                                                    <div class="w-px h-6 bg-slate-600"></div>
                                                    <div class="flex items-center space-x-2">
                                                        <i class="fas fa-building text-purple-400"></i>
                                                        <span class="font-medium"><?= htmlspecialchars($organization) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Role badge enhanced -->
                                            <div class="inline-flex">
                                                <?= getRolebadge($user['access_level'] ?? 'basic') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quick stats -->
                                    <div class="mt-6 lg:mt-0 flex space-x-6">
                                        <div class="text-center">
                                            <div class="text-3xl font-bold text-white mb-1">0</div>
                                            <div class="text-sm text-slate-400">Signalements</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-3xl font-bold text-white mb-1">0</div>
                                            <div class="text-sm text-slate-400">Traités</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bio Section Enhanced -->
                                <div class="bio-section glassmorphism-card rounded-2xl p-6 mb-8">
                                    <h3 class="section-title">
                                        <div class="section-icon bg-gradient-to-br from-blue-500 to-indigo-600">
                                            <i class="fas fa-user-circle text-white"></i>
                                        </div>
                                        <span>À propos</span>
                                    </h3>

                                    <?php if ($bio): ?>
                                        <div class="prose prose-invert max-w-none">
                                            <p class="text-slate-300 leading-relaxed text-lg"><?= nl2br(htmlspecialchars($bio)) ?></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-plus text-slate-400"></i>
                                            </div>
                                            <p class="text-slate-400 mb-4">Aucune biographie ajoutée.</p>
                                            <button onclick="openEditProfileModal()" class="btn-primary-sm">
                                                <i class="fas fa-edit mr-2"></i>
                                                Ajouter une biographie
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Activity Timeline -->
                                <div class="activity-section glassmorphism-card rounded-2xl p-6">
                                    <h3 class="section-title">
                                        <div class="section-icon bg-gradient-to-br from-green-500 to-emerald-600">
                                            <i class="fas fa-clock text-white"></i>
                                        </div>
                                        <span>Activité récente</span>
                                    </h3>

                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-green-500">
                                                <i class="fas fa-sign-in-alt text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Dernière connexion</div>
                                                <div class="timeline-time"><?= $last_activity ? date('d/m/Y à H:i', strtotime($last_activity)) : 'Jamais' ?></div>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-blue-500">
                                                <i class="fas fa-calendar-plus text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Membre depuis</div>
                                                <div class="timeline-time"><?= date('d/m/Y', strtotime($created_at)) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Information Section -->
                    <div class="details-section glassmorphism-premium rounded-3xl p-8">
                        <h3 class="section-title-large">
                            <div class="section-icon-large bg-gradient-to-br from-purple-500 to-pink-600">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                            <span>Informations détaillées</span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Enhanced Info Cards -->
                            <div class="info-card status-active">
                                <div class="info-card-icon">
                                    <i class="fas fa-check-circle text-emerald-400"></i>
                                    <div class="pulse-ring"></div>
                                </div>
                                <div class="info-card-content">
                                    <div class="info-card-label">Statut du compte</div>
                                    <div class="info-card-value text-emerald-400"><?= $active ? 'Actif' : 'Inactif' ?></div>
                                </div>
                            </div>

                            <?php if ($is_public): ?>
                                <div class="info-card">
                                    <div class="info-card-icon">
                                        <i class="fas fa-globe text-purple-400"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">Visibilité</div>
                                        <div class="info-card-value text-purple-400"><?= $is_public == 1 ? 'Public' : 'Privé' ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Social Links Enhanced -->
                            <?php if ($github): ?>
                                <a href="<?= htmlspecialchars($github) ?>" target="_blank" class="info-card info-card-link group">
                                    <div class="info-card-icon">
                                        <i class="fab fa-github text-gray-300 group-hover:text-white transition-colors"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">GitHub</div>
                                        <div class="info-card-value text-gray-300 truncate group-hover:text-white transition-colors"><?= htmlspecialchars($github) ?></div>
                                    </div>
                                    <i class="fas fa-external-link-alt text-slate-500 group-hover:text-white transition-colors"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($linkedin): ?>
                                <a href="<?= htmlspecialchars($linkedin) ?>" target="_blank" class="info-card info-card-link group">
                                    <div class="info-card-icon">
                                        <i class="fab fa-linkedin text-blue-400 group-hover:text-blue-300 transition-colors"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">LinkedIn</div>
                                        <div class="info-card-value text-blue-400 truncate group-hover:text-blue-300 transition-colors"><?= htmlspecialchars($linkedin) ?></div>
                                    </div>
                                    <i class="fas fa-external-link-alt text-slate-500 group-hover:text-blue-300 transition-colors"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($website): ?>
                                <a href="<?= htmlspecialchars($website) ?>" target="_blank" class="info-card info-card-link group">
                                    <div class="info-card-icon">
                                        <i class="fas fa-globe text-green-400 group-hover:text-green-300 transition-colors"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">Site web</div>
                                        <div class="info-card-value text-green-400 truncate group-hover:text-green-300 transition-colors"><?= htmlspecialchars($website) ?></div>
                                    </div>
                                    <i class="fas fa-external-link-alt text-slate-500 group-hover:text-green-300 transition-colors"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Sidebar -->
                <div class="space-y-8">
                    <!-- Contact Information -->
                    <div class="contact-section glassmorphism-premium rounded-3xl p-6">
                        <h3 class="section-title">
                            <div class="section-icon bg-gradient-to-br from-orange-500 to-red-600">
                                <i class="fas fa-address-card text-white"></i>
                            </div>
                            <span>Contact</span>
                        </h3>

                        <div class="space-y-4">
                            <?php if ($phone): ?>
                                <div class="contact-item">
                                    <div class="contact-icon">
                                        <i class="fas fa-phone text-orange-400"></i>
                                    </div>
                                    <span class="contact-text"><?= htmlspecialchars($phone) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($address): ?>
                                <div class="contact-item">
                                    <div class="contact-icon">
                                        <i class="fas fa-map-marker-alt text-blue-400"></i>
                                    </div>
                                    <span class="contact-text">
                                    <?= htmlspecialchars($address) ?>
                                    <?= $city ? ', ' . htmlspecialchars($city) : '' ?>
                                </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($accreditation): ?>
                                <div class="contact-item">
                                    <div class="contact-icon">
                                        <i class="fas fa-certificate text-purple-400"></i>
                                    </div>
                                    <span class="contact-text"><?= htmlspecialchars($accreditation) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!$phone && !$address && !$accreditation): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-plus text-slate-400"></i>
                                    </div>
                                    <p class="text-slate-400 mb-4">Aucune information de contact.</p>
                                    <button onclick="openEditProfileModal()" class="btn-primary-sm">
                                        <i class="fas fa-plus mr-2"></i>
                                        Ajouter des informations
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Enhanced Statistics -->
                    <div class="stats-section glassmorphism-premium rounded-3xl p-6">
                        <h3 class="section-title">
                            <div class="section-icon bg-gradient-to-br from-indigo-500 to-purple-600">
                                <i class="fas fa-chart-bar text-white"></i>
                            </div>
                            <span>Statistiques</span>
                        </h3>

                        <div class="space-y-4">
                            <div class="stat-card-modern">
                                <div class="stat-icon bg-gradient-to-br from-blue-500 to-indigo-600">
                                    <i class="fas fa-plus text-white"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">Signalements créés</div>
                                    <div class="stat-value">0</div>
                                </div>
                            </div>

                            <div class="stat-card-modern">
                                <div class="stat-icon bg-gradient-to-br from-emerald-500 to-green-600">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">Signalements traités</div>
                                    <div class="stat-value">0</div>
                                </div>
                            </div>

                            <div class="stat-card-modern">
                                <div class="stat-icon bg-gradient-to-br from-purple-500 to-pink-600">
                                    <i class="fas fa-shield-alt text-white"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">Niveau d'accès</div>
                                    <div class="stat-badge"><?= ucfirst($user['access_level'] ?? 'basic') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Quick Actions -->
                    <div class="actions-section glassmorphism-premium rounded-3xl p-6">
                        <h3 class="section-title">
                            <div class="section-icon bg-gradient-to-br from-yellow-500 to-orange-600">
                                <i class="fas fa-bolt text-white"></i>
                            </div>
                            <span>Actions rapides</span>
                        </h3>

                        <div class="space-y-4">
                            <button onclick="openChangePasswordModal()" class="action-btn action-btn-orange">
                                <div class="action-btn-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div class="action-btn-content">
                                    <div class="action-btn-title">Changer le mot de passe</div>
                                    <div class="action-btn-subtitle">Sécurisez votre compte</div>
                                </div>
                                <i class="fas fa-chevron-right action-btn-arrow"></i>
                            </button>

                            <button onClick="fetchUserAccount()" class="action-btn action-btn-blue">
                                <div class="action-btn-icon">
                                    <i class="fas fa-download"></i>
                                </div>
                                <div class="action-btn-content">
                                    <div class="action-btn-title">Exporter mes données</div>
                                    <div class="action-btn-subtitle">Télécharger vos informations</div>
                                </div>
                                <i class="fas fa-chevron-right action-btn-arrow"></i>
                            </button>

                            <button onClick="deleteAccount()" class="action-btn action-btn-red">
                                <div class="action-btn-icon">
                                    <i class="fas fa-trash"></i>
                                </div>
                                <div class="action-btn-content">
                                    <div class="action-btn-title">Supprimer mon compte</div>
                                    <div class="action-btn-subtitle">Action irréversible</div>
                                </div>
                                <i class="fas fa-chevron-right action-btn-arrow"></i>
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
                                        <span class="text-xs text-gray-500">PNG, JPG ou JPEG (MAX. 10MB)</span>
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
    // Enhanced JavaScript for modern interactions
    document.addEventListener('DOMContentLoaded', function() {
        initializeProfileAnimations();
        initializeIntersectionObserver();
        initializeParallaxEffects();
        initializeInteractiveElements();
    });

    function initializeProfileAnimations() {
        // Stagger animation for cards
        const cards = document.querySelectorAll('.glassmorphism-premium, .glassmorphism-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
            card.style.opacity = '0';
            card.style.transform = 'translateY(50px)';

            setTimeout(() => {
                card.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 200);
        });
    }

    function initializeIntersectionObserver() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');

                    // Special handling for stat cards
                    if (entry.target.classList.contains('stat-card-modern')) {
                        animateCounter(entry.target);
                    }

                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.stat-card-modern, .info-card, .action-btn').forEach(el => {
            observer.observe(el);
        });
    }

    function animateCounter(statCard) {
        const valueElement = statCard.querySelector('.stat-value');
        const targetValue = parseInt(valueElement.textContent) || 0;
        let currentValue = 0;
        const duration = 2000;
        const step = targetValue / (duration / 16);

        const counter = setInterval(() => {
            currentValue += step;
            if (currentValue >= targetValue) {
                valueElement.textContent = targetValue;
                clearInterval(counter);
            } else {
                valueElement.textContent = Math.floor(currentValue);
            }
        }, 16);
    }

    function initializeParallaxEffects() {
        let ticking = false;

        function updateParallax() {
            const scrolled = window.pageYOffset;

            // Parallax for floating orbs
            const orbs = document.querySelectorAll('.floating-orb');
            orbs.forEach((orb, index) => {
                const speed = 0.5 + (index * 0.1);
                const yPos = scrolled * speed;
                const rotation = scrolled * 0.02;
                orb.style.transform = `translate3d(0, ${yPos}px, 0) rotate(${rotation}deg)`;
            });

            // Parallax for background patterns
            const patterns = document.querySelectorAll('.bg-pattern-dots');
            patterns.forEach(pattern => {
                const yPos = scrolled * 0.3;
                pattern.style.transform = `translateY(${yPos}px)`;
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

    function initializeInteractiveElements() {
        // Enhanced hover effects for info cards
        const infoCards = document.querySelectorAll('.info-card');
        infoCards.forEach(card => {
            card.addEventListener('mouseenter', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                this.style.setProperty('--mouse-x', `${x}px`);
                this.style.setProperty('--mouse-y', `${y}px`);
            });

            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                this.style.background = `
                radial-gradient(circle at ${x}% ${y}%, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.05) 100%)
            `;
            });

            card.addEventListener('mouseleave', function() {
                this.style.background = 'rgba(255, 255, 255, 0.08)';
            });
        });

        // 3D tilt effect for action buttons
        const actionBtns = document.querySelectorAll('.action-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;

                this.style.transform = `
                perspective(1000px)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                translateY(-6px)
                scale(1.02)
            `;
            });

            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0) scale(1)';
            });
        });

        // Ripple effect for buttons
        document.querySelectorAll('.btn-primary-sm, .action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                pointer-events: none;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                z-index: 1;
            `;

                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    // Add ripple animation to CSS
    const style = document.createElement('style');
    style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }

    .animate-in {
        animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
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
`;
    document.head.appendChild(style);
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