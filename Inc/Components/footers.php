<?php
// Déterminer le chemin relatif pour les liens selon l'emplacement de la page courante
$currentPath = $_SERVER['REQUEST_URI'];
$isInSubfolder = (strpos($currentPath, '/Src/') !== false || strpos($currentPath, '/Pages/') !== false);
$basePath = $isInSubfolder ? '../' : './';
?>

<footer class="bg-slate-900 text-slate-300 py-12 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="bg-blue-600 p-2 rounded-lg">
                        <i class="fas fa-shield-alt text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold text-white">E Conscience</span>
                </div>
                <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                    Plateforme professionnelle de signalement et de gestion d'incidents. Sécurité, fiabilité et
                    confidentialité.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-slate-400 hover:text-white transition-colors"><i
                            class="fab fa-twitter text-lg"></i></a>
                    <a href="#" class="text-slate-400 hover:text-white transition-colors"><i
                            class="fab fa-linkedin text-lg"></i></a>
                    <a href="#" class="text-slate-400 hover:text-white transition-colors"><i
                            class="fab fa-github text-lg"></i></a>
                </div>
            </div>

            <!-- Links -->
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Navigation</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="<?php echo $basePath; ?>index.php"
                            class="hover:text-blue-400 transition-colors">Accueil</a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/search.php"
                            class="hover:text-blue-400 transition-colors">Recherche</a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/signal.php"
                            class="hover:text-blue-400 transition-colors">Signaler</a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/membres.php"
                            class="hover:text-blue-400 transition-colors">Membres</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Ressources</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="<?php echo $basePath; ?>Pages/guides.php"
                            class="hover:text-blue-400 transition-colors">Conseils & Guides</a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/faq.php"
                            class="hover:text-blue-400 transition-colors">FAQ</a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/cgu.php"
                            class="hover:text-blue-400 transition-colors">CGU</a></li>
                    <li><a href="<?php echo $basePath; ?>Pages/stats.php"
                            class="hover:text-blue-400 transition-colors">Statistiques</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Contact</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start">
                        <i class="fas fa-envelope mt-1 mr-3 text-blue-500"></i>
                        <a href="mailto:support@e-conscience.com"
                            class="hover:text-white transition-colors">support@econsciencefr.org</a>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-3 text-blue-500"></i>
                        <span>Paris, France</span>
                    </li>
                </ul>
            </div>
        </div>

        <div
            class="border-t border-slate-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-slate-500">
            <p>&copy; <?php echo date('Y'); ?> E Conscience. Tous droits réservés.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition-colors">Confidentialité</a>
                <a href="#" class="hover:text-white transition-colors">Mentions légales</a>
            </div>
        </div>
    </div>
</footer>