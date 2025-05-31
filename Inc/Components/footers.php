<?php
// Déterminer le chemin relatif pour les liens selon l'emplacement de la page courante
$currentPath = $_SERVER['REQUEST_URI'];
$isInSubfolder = (strpos($currentPath, '/Src/') !== false || strpos($currentPath, '/Pages/') !== false);
$basePath = $isInSubfolder ? '../' : './';
?>
<footer class="bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800 py-12 mt-auto border-t border-gray-200 w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Grille principale -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
            <div>
                <h3 class="text-lg font-bold text-france-blue mb-4 flex items-center">
                    <i class="fas fa-database mr-2"></i>
                    Données ouvertes
                </h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-chart-bar mr-2 text-xs"></i>Statistiques nationales</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-map-marked-alt mr-2 text-xs"></i>Cartes interactives</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-download mr-2 text-xs"></i>Téléchargements</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-code mr-2 text-xs"></i>API publique</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold text-france-blue mb-4 flex items-center">
                    <i class="fas fa-cogs mr-2"></i>
                    Plateforme
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?php echo $basePath; ?>Pages/search.php" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-search mr-2 text-xs"></i>Recherche avancée</a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/signal.php" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-exclamation-triangle mr-2 text-xs"></i>Signaler un incident</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-bell mr-2 text-xs"></i>Alertes personnalisées</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-mobile-alt mr-2 text-xs"></i>Application mobile</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold text-france-blue mb-4 flex items-center">
                    <i class="fas fa-book mr-2"></i>
                    Ressources
                </h3>
                <ul class="space-y-3">
                    <li><a href="<?php echo $basePath; ?>Pages/guides.php" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-life-ring mr-2 text-xs"></i>Guide de sécurité</a></li>
                    <li><a href="<?php echo $basePath;?>Pages/faq.php" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-question-circle mr-2 text-xs"></i>FAQ</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-video mr-2 text-xs"></i>Tutoriels vidéo</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-file-pdf mr-2 text-xs"></i>Documentation PDF</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold text-france-blue mb-4 flex items-center">
                    <i class="fas fa-code-branch mr-2"></i>
                    Développement
                </h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fab fa-github mr-2 text-xs"></i>Code source</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-bug mr-2 text-xs"></i>Signaler un bug</a></li>
                    <li><a href="#" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-lightbulb mr-2 text-xs"></i>Proposer une amélioration</a></li>
                    <li><a href="<?php echo $basePath; ?>Src/Pages/contact.php" class="text-gray-700 hover:text-france-blue hover:underline text-sm transition-all duration-200 flex items-center"><i class="fas fa-envelope mr-2 text-xs"></i>Contact développeurs</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Section République Française -->
        <div class="border-t border-gray-300 pt-8">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="flex items-center mb-6 lg:mb-0">
                    <img src="<?php echo $basePath; ?>Assets/Images/alerte_france.png" 
                         alt="République Française" 
                         class="h-16 w-16 mr-4 rounded-lg shadow-sm">
                    <div>
                        <h4 class="text-lg font-bold text-france-blue">République Française</h4>
                        <p class="text-sm text-gray-600">Service public numérique</p>
                    </div>
                </div>
                
                <!-- Réseaux sociaux -->
                <div class="flex space-x-4 mb-6 lg:mb-0">
                    <a href="#" class="text-gray-500 hover:text-france-blue transition-colors" aria-label="Twitter">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-france-blue transition-colors" aria-label="Facebook">
                        <i class="fab fa-facebook text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-france-blue transition-colors" aria-label="LinkedIn">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-france-blue transition-colors" aria-label="YouTube">
                        <i class="fab fa-youtube text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Liens légaux et copyright -->
        <div class="border-t border-gray-200 pt-6 mt-8">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                <div class="flex flex-wrap justify-center md:justify-start gap-4 mb-4 md:mb-0">
                    <a href="#" class="text-gray-600 hover:text-france-blue hover:underline transition-colors">Accessibilité</a>
                    <span class="text-gray-400 hidden md:inline">|</span>
                    <a href="#" class="text-gray-600 hover:text-france-blue hover:underline transition-colors">Mentions légales</a>
                    <span class="text-gray-400 hidden md:inline">|</span>
                    <a href="#" class="text-gray-600 hover:text-france-blue hover:underline transition-colors">Politique de confidentialité</a>
                    <span class="text-gray-400 hidden md:inline">|</span>
                    <a href="#" class="text-gray-600 hover:text-france-blue hover:underline transition-colors">Plan du site</a>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-gray-600">&copy; 2025 SignaleFrance.fr - Tous droits réservés</p>
                    <p class="text-xs text-gray-500 mt-1">Version 1.0.0 - Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
</footer>