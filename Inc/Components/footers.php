<?php
// Déterminer le chemin relatif pour les liens selon l'emplacement de la page courante
$currentPath = $_SERVER['REQUEST_URI'];
$isInSubfolder = (strpos($currentPath, '/Src/') !== false || strpos($currentPath, '/Pages/') !== false);
$basePath = $isInSubfolder ? '../' : './';
?>

<style>
    /* Footer professionnel dans le thème du site */
    .footer-official {
        background: linear-gradient(135deg, #000091 0%, #1e3a8a 50%, #1e40af 100%);
        position: relative;
        overflow: hidden;
    }

    .footer-official::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #000091, #ffffff, #e1000f);
    }

    .footer-section {
        transition: all 0.3s ease;
    }

    .footer-link {
        position: relative;
        transition: all 0.3s ease;
        padding: 8px 0;
        border-radius: 6px;
    }

    .footer-link::before {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 0;
        height: 2px;
        background: #ffffff;
        transition: width 0.3s ease;
    }

    .footer-link:hover::before {
        width: 100%;
    }

    .footer-link:hover {
        color: #ffffff !important;
        transform: translateX(4px);
    }

    .service-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .service-card:hover {
        background: rgba(255, 255, 255, 0.12);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .icon-official {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .icon-official:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: scale(1.1);
    }

    .social-official {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        color: #ffffff;
    }

    .social-official:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .logo-official {
        transition: all 0.3s ease;
    }

    .logo-official:hover {
        transform: scale(1.05);
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .legal-section {
        background: rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(5px);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    @media (max-width: 768px) {
        .footer-official {
            padding: 2rem 0;
        }
    }
</style>

<footer class="footer-official text-white py-16 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Grille principale -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- Services publics -->
            <div class="footer-section">
                <h3 class="text-lg font-semibold mb-6 text-white flex items-center">
                    <div class="icon-official mr-3">
                        <i class="fas fa-landmark text-white"></i>
                    </div>
                    Services publics
                </h3>
                <ul class="space-y-3">
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-chart-line text-blue-300 mr-3 w-4"></i>
                            Données ouvertes
                        </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-map-marked-alt text-blue-300 mr-3 w-4"></i>
                            Cartographie nationale
                        </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-download text-blue-300 mr-3 w-4"></i>
                            Téléchargements
                        </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-code text-blue-300 mr-3 w-4"></i>
                            API développeurs
                        </a></li>
                </ul>
            </div>

            <!-- Plateforme -->
            <div class="footer-section">
                <h3 class="text-lg font-semibold mb-6 text-white flex items-center">
                    <div class="icon-official mr-3">
                        <i class="fas fa-desktop text-white"></i>
                    </div>
                    Plateforme
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?php echo $basePath; ?>Pages/search.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-search text-blue-300 mr-3 w-4"></i>
                            Recherche avancée
                        </a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/signal.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-300 mr-3 w-4"></i>
                            Signaler un incident
                        </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-bell text-yellow-300 mr-3 w-4"></i>
                            Alertes personnalisées
                        </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-mobile-alt text-blue-300 mr-3 w-4"></i>
                            Application mobile
                        </a></li>
                </ul>
            </div>

            <!-- Aide et support -->
            <div class="footer-section">
                <h3 class="text-lg font-semibold mb-6 text-white flex items-center">
                    <div class="icon-official mr-3">
                        <i class="fas fa-life-ring text-white"></i>
                    </div>
                    Aide et support
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?php echo $basePath; ?>Pages/guides.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-book text-green-300 mr-3 w-4"></i>
                            Guide d'utilisation
                        </a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/faq.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-question-circle text-blue-300 mr-3 w-4"></i>
                            Questions fréquentes
                        </a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/contact.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-envelope text-blue-300 mr-3 w-4"></i>
                            Nous contacter
                        </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-headset text-blue-300 mr-3 w-4"></i>
                            Support technique
                        </a></li>
                </ul>
            </div>

            <!-- À propos -->
            <div class="footer-section">
                <h3 class="text-lg font-semibold mb-6 text-white flex items-center">
                    <div class="icon-official mr-3">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                    À propos
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?php echo $basePath; ?>Pages/cgu.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-flag text-blue-300 mr-3 w-4"></i>
                            C.G.U
                        </a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/equipe.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-users text-blue-300 mr-3 w-4"></i>
                            Équipe
                        </a></li>
                    <li><a href="#" class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-newspaper text-blue-300 mr-3 w-4"></i>
                            Actualités
                        </a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/stats.php"
                            class="footer-link text-gray-300 hover:text-white text-sm flex items-center">
                            <i class="fas fa-chart-bar text-blue-300 mr-3 w-4"></i>
                            Statistiques
                        </a></li>
                </ul>
            </div>
        </div>

        <!-- Section République Française -->
        <div class="border-t border-white/20 pt-10 mb-10">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="flex items-center mb-8 lg:mb-0">
                    <div class="logo-official mr-6">
                        <img src="<?php echo $basePath; ?>Assets/Images/SignaleFrance.png" alt="République Française"
                            class="h-16 w-16 rounded-lg shadow-lg border border-white/20">
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-white mb-1">E Conscience</h4>
                        <p class="text-gray-300 text-sm mb-2">Service public numérique</p>
                        <div class="flex items-center">
                            <div class="status-dot mr-2"></div>
                            <span class="text-green-300 text-xs font-medium">Service opérationnel</span>
                        </div>
                    </div>
                </div>

                <!-- Réseaux sociaux officiels -->
                <div class="flex space-x-4">
                    <a href="#" class="social-official" aria-label="Twitter officiel">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-official" aria-label="Facebook officiel">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="social-official" aria-label="LinkedIn officiel">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="#" class="social-official" aria-label="YouTube officiel">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Section légale -->
        <div class="legal-section rounded-lg p-6">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                <div class="flex flex-wrap justify-center md:justify-start gap-6 mb-4 md:mb-0">
                    <a href="#" class="text-gray-300 hover:text-white transition-colors">Accessibilité</a>
                    <a href="#" class="text-gray-300 hover:text-white transition-colors">Mentions légales</a>
                    <a href="#" class="text-gray-300 hover:text-white transition-colors">Données personnelles</a>
                    <a href="#" class="text-gray-300 hover:text-white transition-colors">Plan du site</a>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-gray-300">&copy; 2025 E Conscience - Service public</p>
                    <p class="text-xs text-gray-400 mt-1">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
</footer>