<?php
session_start();
require_once 'Inc/Components/header.php';
require_once 'Inc/Components/nav.php';
require_once 'Inc/Constants/db.php';
require_once 'Inc/Constants/CookieManager.php';

$user = null;
if (isset($_SESSION['username'])) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($_COOKIE['remember_token'])) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$_COOKIE['remember_token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['username'] = $user['username'];
    }
}
?>

<style>
    .hero-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 25%, #1e40af 50%, #7c3aed 75%, #1d4ed8 100%);
    }
    
    .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .text-shadow {
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
    }
    
    .stats-counter {
        background: linear-gradient(135deg, #60a5fa, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<main class="flex-grow">
    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center hero-gradient overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0">
            <!-- Animated Particles -->
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white/30 rounded-full animate-float" style="animation-delay: 0s;"></div>
            <div class="absolute top-1/3 right-1/3 w-3 h-3 bg-blue-300/40 rounded-full animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-1/4 left-1/3 w-4 h-4 bg-purple-300/30 rounded-full animate-float" style="animation-delay: 4s;"></div>
            <div class="absolute top-1/2 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-float" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-1/3 right-1/2 w-3 h-3 bg-indigo-300/30 rounded-full animate-float" style="animation-delay: 3s;"></div>
        </div>
        
        <!-- French Flag Stripe -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-white to-red-600"></div>
        
        <!-- Content -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
           
            
            <!-- Main Title -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black mb-8 animate-fade-in-up text-shadow">
                <span class="block mb-2">Signale</span>
                <span class="block bg-gradient-to-r from-blue-200 to-purple-200 bg-clip-text text-transparent">France</span>
            </h1>
            
            <!-- Subtitle -->
            <p class="text-lg sm:text-xl md:text-2xl lg:text-3xl max-w-4xl mx-auto mb-12 leading-relaxed text-blue-100 animate-fade-in-up" style="animation-delay: 0.2s;">
                Le système national d'alerte et d'information pour la sécurité des citoyens.
                <span class="block mt-4 text-base sm:text-lg text-white/80 font-light">
                    Restez informé • Restez en sécurité • Restez connecté
                </span>
            </p>

             <!-- Status Badge - Repositionné comme une carte -->
    <div class="flex justify-center mb-8 animate-fade-in-up" style="animation-delay: 0.3s;">
        <div class="glass-effect rounded-2xl p-4 sm:p-6 border border-white/20 hover:bg-white/20 transition-all duration-300 inline-flex items-center">
            <div class="w-3 h-3 bg-green-400 rounded-full mr-4 animate-pulse"></div>
            <div class="text-center">
                <div class="text-sm sm:text-base font-bold text-white mb-1">
                    <i class="fas fa-flag mr-2"></i>
                    Service Public Numérique Officiel
                </div>
                <div class="text-xs text-white/80 font-medium uppercase tracking-wider">Certifié République Française</div>
            </div>
        </div>
    </div>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-12 sm:mb-16 max-w-4xl mx-auto animate-fade-in-up" style="animation-delay: 0.4s;">
                <div class="glass-effect rounded-2xl p-4 sm:p-6 border border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="stats-counter text-2xl sm:text-3xl md:text-4xl font-black mb-2">24/7</div>
                    <div class="text-xs sm:text-sm text-white/80 font-medium uppercase tracking-wider">Surveillance Continue</div>
                </div>
                <div class="glass-effect rounded-2xl p-4 sm:p-6 border border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="stats-counter text-2xl sm:text-3xl md:text-4xl font-black mb-2">100%</div>
                    <div class="text-xs sm:text-sm text-white/80 font-medium uppercase tracking-wider">Territoire Couvert</div>
                </div>
                <div class="glass-effect rounded-2xl p-4 sm:p-6 border border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="stats-counter text-2xl sm:text-3xl md:text-4xl font-black mb-2">&lt; 2min</div>
                    <div class="text-xs sm:text-sm text-white/80 font-medium uppercase tracking-wider">Temps de Réponse</div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <!-- Action Buttons -->
<div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6 animate-fade-in-up mb-16 sm:mb-20 md:mb-24" style="animation-delay: 0.6s;">
    <?php if (isset($user)): ?>
        <button onclick="window.location.href='Pages/search.php'" 
                class="group btn-primary text-white px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded-2xl w-full sm:min-w-64 flex items-center justify-center gap-3 sm:gap-4 transform hover:scale-105 transition-all duration-300">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-search text-sm sm:text-lg"></i>
            </div>
            <span class="text-sm sm:text-base">Rechercher une personne</span>
            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform text-sm sm:text-base"></i>
        </button>
        
        <button onclick="window.location.href='Pages/signal.php'" 
                class="group btn-secondary text-white px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded-2xl w-full sm:min-w-64 flex items-center justify-center gap-3 sm:gap-4 transform hover:scale-105 transition-all duration-300">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-sm sm:text-lg"></i>
            </div>
            <span class="text-sm sm:text-base">Signaler un incident</span>
            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform text-sm sm:text-base"></i>
        </button>
        
        <?php if ($user['role'] === 'journaliste'): ?>
            <button onclick="window.location.href='Pages/create_post.php'" 
                    class="group bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded-2xl w-full sm:min-w-64 flex items-center justify-center gap-3 sm:gap-4 transform hover:scale-105 transition-all duration-300">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-edit text-sm sm:text-lg"></i>
                </div>
                <span class="text-sm sm:text-base">Publier un article</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform text-sm sm:text-base"></i>
            </button>
        <?php endif; ?>
    <?php else: ?>
        <button onclick="window.location.href='Pages/search.php'" 
                class="group btn-primary text-white px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded-2xl w-full sm:min-w-64 flex items-center justify-center gap-3 sm:gap-4 transform hover:scale-105 transition-all duration-300">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-search text-sm sm:text-lg"></i>
            </div>
            <span class="text-sm sm:text-base">Rechercher une personne</span>
            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform text-sm sm:text-base"></i>
        </button>
        
        <button onclick="window.location.href='Pages/signal.php'" 
                class="group btn-secondary text-white px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded-2xl w-full sm:min-w-64 flex items-center justify-center gap-3 sm:gap-4 transform hover:scale-105 transition-all duration-300">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-sm sm:text-lg"></i>
            </div>
            <span class="text-sm sm:text-base">Signaler un incident</span>
            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform text-sm sm:text-base"></i>
        </button>
        
        <button onclick="window.location.href='Pages/login.php'" 
                class="group bg-white text-blue-900 px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded-2xl w-full sm:min-w-64 flex items-center justify-center gap-3 sm:gap-4 transform hover:scale-105 transition-all duration-300 hover:bg-gray-50">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-900/10 rounded-lg flex items-center justify-center">
                <i class="fas fa-sign-in-alt text-sm sm:text-lg text-blue-900"></i>
            </div>
            <span class="text-sm sm:text-base">Se connecter</span>
            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform text-blue-900 text-sm sm:text-base"></i>
        </button>
    <?php endif; ?>
</div>
        </div>
        
        <!-- Wave Transition -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="rgb(249 250 251)"/>
            </svg>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-12 sm:py-16 md:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12 sm:mb-16 md:mb-20">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold mb-6">
                    <i class="fas fa-cogs mr-2"></i>
                    Nos Services
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 mb-6">
                    Des outils <span class="text-blue-600">modernes</span><br class="hidden sm:block">
                    pour votre <span class="text-red-600">sécurité</span>
                </h2>
                <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Découvrez notre gamme complète de services conçus pour protéger et informer les citoyens français
                </p>
            </div>
            
            <!-- Services Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <!-- Service 1 -->
                <div class="group bg-white rounded-3xl p-6 sm:p-8 shadow-lg hover:shadow-2xl border border-gray-100 transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-search text-white text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Recherche de Personnes</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed text-sm sm:text-base">
                        Retrouvez rapidement des personnes disparues grâce à notre système de recherche avancé et notre réseau national.
                    </p>
                    <a href="Pages/search.php" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-700 transition-colors text-sm sm:text-base">
                        En savoir plus
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <!-- Service 2 -->
                <div class="group bg-white rounded-3xl p-6 sm:p-8 shadow-lg hover:shadow-2xl border border-gray-100 transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-exclamation-triangle text-white text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Signalement d'Incidents</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed text-sm sm:text-base">
                        Signalez immédiatement tout incident ou situation d'urgence pour une intervention rapide des autorités.
                    </p>
                    <a href="Pages/signal.php" class="inline-flex items-center text-red-600 font-semibold hover:text-red-700 transition-colors text-sm sm:text-base">
                        Signaler maintenant
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <!-- Service 3 -->
                <div class="group bg-white rounded-3xl p-6 sm:p-8 shadow-lg hover:shadow-2xl border border-gray-100 transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-white text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Communauté</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed text-sm sm:text-base">
                        Rejoignez notre communauté de citoyens engagés et participez à la sécurité collective de notre territoire.
                    </p>
                    <a href="Pages/membres.php" class="inline-flex items-center text-green-600 font-semibold hover:text-green-700 transition-colors text-sm sm:text-base">
                        Rejoindre
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <!-- Service 4 -->
                <div class="group bg-white rounded-3xl p-6 sm:p-8 shadow-lg hover:shadow-2xl border border-gray-100 transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chart-line text-white text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Statistiques</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed text-sm sm:text-base">
                        Consultez les données en temps réel sur la sécurité publique et les interventions sur le territoire.
                    </p>
                    <a href="Pages/stats.php" class="inline-flex items-center text-purple-600 font-semibold hover:text-purple-700 transition-colors text-sm sm:text-base">
                        Voir les stats
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <!-- Service 5 -->
                <div class="group bg-white rounded-3xl p-6 sm:p-8 shadow-lg hover:shadow-2xl border border-gray-100 transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-book text-white text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Guides & FAQ</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed text-sm sm:text-base">
                        Accédez à nos guides pratiques et trouvez rapidement les réponses à vos questions les plus fréquentes.
                    </p>
                    <a href="Pages/guides.php" class="inline-flex items-center text-indigo-600 font-semibold hover:text-indigo-700 transition-colors text-sm sm:text-base">
                        Consulter
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <!-- Service 6 -->
                <div class="group bg-white rounded-3xl p-6 sm:p-8 shadow-lg hover:shadow-2xl border border-gray-100 transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-envelope text-white text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Contact</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed text-sm sm:text-base">
                        Contactez directement nos équipes pour toute question ou demande d'assistance personnalisée.
                    </p>
                    <a href="Pages/contact.php" class="inline-flex items-center text-orange-600 font-semibold hover:text-orange-700 transition-colors text-sm sm:text-base">
                        Nous contacter
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-12 sm:py-16 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                <!-- Content -->
                <div class="order-2 lg:order-1">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold mb-6">
                        <i class="fas fa-info-circle mr-2"></i>
                        À propos
                    </div>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 mb-6">
                        Une mission de <span class="text-blue-600">service public</span>
                    </h2>
                    <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed">
                        Signale France est le système national d'alerte et d'information développé pour assurer la sécurité et la protection de tous les citoyens français.
                    </p>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Sécurité Garantie</h3>
                                <p class="text-gray-600">Toutes vos données sont protégées selon les standards les plus élevés de sécurité.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-clock text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Disponibilité 24/7</h3>
                                <p class="text-gray-600">Notre service est disponible en permanence pour répondre aux urgences.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-network-wired text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Réseau National</h3>
                                <p class="text-gray-600">Connecté à tous les services d'urgence et autorités compétentes.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Visual -->
                <div class="order-1 lg:order-2">
                    <div class="relative">
                        <div class="w-full h-64 sm:h-80 md:h-96 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl flex items-center justify-center">
                            <div class="text-center text-white">
                                <i class="fas fa-flag text-4xl sm:text-5xl md:text-6xl mb-4 opacity-80"></i>
                                <h3 class="text-xl sm:text-2xl font-bold mb-2">République Française</h3>
                                <p class="text-blue-100 text-sm sm:text-base">Liberté • Égalité • Fraternité</p>
                            </div>
                        </div>
                        <!-- Floating Elements -->
                        <div class="absolute -top-2 -right-2 sm:-top-4 sm:-right-4 w-12 h-12 sm:w-16 sm:h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center animate-float">
                            <i class="fas fa-heart text-red-500 text-lg sm:text-xl"></i>
                        </div>
                        <div class="absolute -bottom-2 -left-2 sm:-bottom-4 sm:-left-4 w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-2xl shadow-lg flex items-center justify-center animate-float" style="animation-delay: 1s;">
                            <i class="fas fa-users text-blue-500 text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-12 sm:py-16 md:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 sm:mb-16">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold mb-6">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 mb-6">
                    Besoin d'aide ?
                </h2>
                <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto">
                    Notre équipe est là pour vous accompagner
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
                <!-- Emergency -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-lg text-center transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-phone text-red-600 text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Urgence</h3>
                    <p class="text-gray-600 mb-6 text-sm sm:text-base">Pour toute situation d'urgence</p>
                    <a href="tel:112" class="inline-flex items-center justify-center w-full bg-red-600 text-white py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors text-sm sm:text-base">
                        <i class="fas fa-phone mr-2"></i>
                        112
                    </a>
                </div>
                
                <!-- Support -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-lg text-center transform hover:-translate-y-2 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-headset text-blue-600 text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Support</h3>
                    <p class="text-gray-600 mb-6 text-sm sm:text-base">Assistance technique</p>
                    <a href="Pages/contact.php" class="inline-flex items-center justify-center w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors text-sm sm:text-base">
                        <i class="fas fa-envelope mr-2"></i>
                        Contacter
                    </a>
                </div>
                
                <!-- FAQ -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-lg text-center transform hover:-translate-y-2 hover:scale-105 transition-all duration-300 sm:col-span-2 md:col-span-1">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-question-circle text-green-600 text-lg sm:text-2xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">FAQ</h3>
                    <p class="text-gray-600 mb-6 text-sm sm:text-base">Questions fréquentes</p>
                    <a href="Pages/faq.php" class="inline-flex items-center justify-center w-full bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors text-sm sm:text-base">
                        <i class="fas fa-book mr-2"></i>
                        Consulter
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.group').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });

    
</script>
<?php require_once 'Inc/Components/footers.php'; ?>
<?php require_once 'Inc/Components/footer.php'; ?>
<?php require_once 'Inc/Traitement/create_log.php'; ?>