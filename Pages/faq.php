<?php
session_start();

// Gestion de l'authentification
$user = null;


include '../Inc/Components/header.php';
include '../Inc/Components/nav.php';
?>


<div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header Section -->
    <div class="text-center mb-12">
        <div
            class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-france-blue to-blue-600 rounded-full mb-6">
            <i class="fas fa-question-circle text-3xl text-white"></i>
        </div>
        <h1
            class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-france-blue to-blue-600 bg-clip-text text-transparent mb-4">
            Foire Aux Questions
        </h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Trouvez rapidement les réponses à vos questions concernant l'utilisation de la plateforme E Conscience.
        </p>
    </div>

    <!-- Search Section -->
    <div class="glass-effect rounded-2xl p-6 mb-8 shadow-lg">
        <div class="relative">
            <input type="text" id="searchFAQ" placeholder="Rechercher dans la FAQ..."
                class="w-full px-6 py-4 pl-14 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-france-blue transition-all duration-300 bg-white/80">
            <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
            <button id="clearSearch"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors hidden">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="searchResults" class="mt-4 hidden"></div>
    </div>

    <!-- Categories -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <button onclick="filterFAQ('all')"
            class="category-btn bg-gradient-to-r from-france-blue to-blue-600 text-white px-6 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
            <i class="fas fa-list mr-2"></i>Toutes les questions
        </button>
        <button onclick="filterFAQ('signalement')"
            class="category-btn glass-effect text-gray-700 px-6 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
            <i class="fas fa-exclamation-triangle mr-2 text-orange-500"></i>Signalements
        </button>
        <button onclick="filterFAQ('compte')"
            class="category-btn glass-effect text-gray-700 px-6 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
            <i class="fas fa-user mr-2 text-green-500"></i>Compte
        </button>
        <button onclick="filterFAQ('general')"
            class="category-btn glass-effect text-gray-700 px-6 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
            <i class="fas fa-info-circle mr-2 text-purple-500"></i>Général
        </button>
    </div>

    <!-- FAQ Container -->
    <div class="space-y-6" id="faqContainer">

        <!-- Signalement Questions -->
        <div class="faq-item glass-effect rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            data-category="signalement">
            <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-orange-400 to-red-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-plus text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        Comment créer un nouveau signalement ?
                    </h3>
                </div>
                <i class="fas fa-chevron-down faq-toggle text-2xl text-france-blue"></i>
            </div>
            <div class="faq-content px-6">
                <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-6">
                    <p class="mb-4 font-medium text-gray-800">Pour créer un nouveau signalement, suivez ces étapes :</p>
                    <ol class="space-y-3">
                        <li class="flex items-start">
                            <span
                                class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">1</span>
                            <span>Cliquez sur "Créer un signalement" dans le menu principal</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">2</span>
                            <span>Remplissez le titre et sélectionnez le type d'incident</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">3</span>
                            <span>Choisissez la priorité et la localisation</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">4</span>
                            <span>Rédigez une description détaillée</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-orange-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">5</span>
                            <span>Ajoutez des preuves si nécessaire</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="faq-item glass-effect rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            data-category="signalement">
            <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-file-upload text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        Quels types de fichiers puis-je joindre ?
                    </h3>
                </div>
                <i class="fas fa-chevron-down faq-toggle text-2xl text-france-blue"></i>
            </div>
            <div class="faq-content px-6">
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-image text-white text-2xl"></i>
                            </div>
                            <h4 class="font-semibold mb-2">Images</h4>
                            <p class="text-sm text-gray-600">JPEG, PNG, GIF, WebP</p>
                        </div>
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-video text-white text-2xl"></i>
                            </div>
                            <h4 class="font-semibold mb-2">Vidéos</h4>
                            <p class="text-sm text-gray-600">MP4, AVI, MOV, WMV</p>
                        </div>
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-file-alt text-white text-2xl"></i>
                            </div>
                            <h4 class="font-semibold mb-2">Documents</h4>
                            <p class="text-sm text-gray-600">PDF, DOC, DOCX, TXT</p>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-yellow-100 rounded-lg border-l-4 border-yellow-500">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Limites :</strong> 10 MB par fichier, maximum 5 fichiers par signalement
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item glass-effect rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            data-category="signalement">
            <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user-secret text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        Puis-je faire un signalement anonyme ?
                    </h3>
                </div>
                <i class="fas fa-chevron-down faq-toggle text-2xl text-france-blue"></i>
            </div>
            <div class="faq-content px-6">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <p class="font-medium text-lg">Oui, les signalements anonymes sont possibles !</p>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-purple-500 mr-3 mt-1"></i>
                            <span>Cochez l'option "Signalement anonyme" lors de la création</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-eye-slash text-purple-500 mr-3 mt-1"></i>
                            <span>Votre nom ne sera pas visible publiquement</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope text-purple-500 mr-3 mt-1"></i>
                            <span>Email de contact optionnel</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Compte Questions -->
        <div class="faq-item glass-effect rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            data-category="compte">
            <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user-edit text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        Comment modifier mes informations personnelles ?
                    </h3>
                </div>
                <i class="fas fa-chevron-down faq-toggle text-2xl text-france-blue"></i>
            </div>
            <div class="faq-content px-6">
                <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6">
                    <ol class="space-y-3">
                        <li class="flex items-start">
                            <span
                                class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">1</span>
                            <span>Cliquez sur votre nom dans le menu</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">2</span>
                            <span>Sélectionnez "Mon Profil"</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">3</span>
                            <span>Cliquez sur "Modifier le profil"</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">4</span>
                            <span>Sauvegardez vos modifications</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="faq-item glass-effect rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            data-category="compte">
            <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-red-400 to-pink-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-key text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        Comment changer mon mot de passe ?
                    </h3>
                </div>
                <i class="fas fa-chevron-down faq-toggle text-2xl text-france-blue"></i>
            </div>
            <div class="faq-content px-6">
                <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-6">
                    <ol class="space-y-3 mb-4">
                        <li class="flex items-start">
                            <span
                                class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">1</span>
                            <span>Allez dans "Mon Profil"</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">2</span>
                            <span>Cliquez sur "Modifier le profil"</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">3</span>
                            <span>Remplissez les champs mot de passe</span>
                        </li>
                    </ol>
                    <div class="p-4 bg-yellow-100 rounded-lg border-l-4 border-yellow-500">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Utilisez un mot de passe fort avec au moins 8 caractères
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- General Questions -->
        <div class="faq-item glass-effect rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            data-category="general">
            <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-eye text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        Qui peut voir mes signalements ?
                    </h3>
                </div>
                <i class="fas fa-chevron-down faq-toggle text-2xl text-france-blue"></i>
            </div>
            <div class="faq-content px-6">
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <h4 class="font-semibold text-green-600 mb-2">
                                <i class="fas fa-users mr-2"></i>Utilisateurs connectés
                            </h4>
                            <p class="text-sm">Peuvent voir tous les signalements publics</p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <h4 class="font-semibold text-blue-600 mb-2">
                                <i class="fas fa-user-secret mr-2"></i>Signalements anonymes
                            </h4>
                            <p class="text-sm">Votre nom n'apparaît pas</p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <h4 class="font-semibold text-red-600 mb-2">
                                <i class="fas fa-user-shield mr-2"></i>Administrateurs
                            </h4>
                            <p class="text-sm">Accès complet pour modération</p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <h4 class="font-semibold text-purple-600 mb-2">
                                <i class="fas fa-user-cog mr-2"></i>Modérateurs
                            </h4>
                            <p class="text-sm">Peuvent gérer les signalements</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item glass-effect rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300"
            data-category="general">
            <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-green-400 to-teal-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-headset text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        Comment contacter le support ?
                    </h3>
                </div>
                <i class="fas fa-chevron-down faq-toggle text-2xl text-france-blue"></i>
            </div>
            <div class="faq-content px-6">
                <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="text-center p-6 bg-white rounded-xl shadow-sm">
                            <div
                                class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-envelope text-white text-2xl"></i>
                            </div>
                            <h4 class="font-semibold mb-2">Email</h4>
                            <p class="text-sm text-gray-600 mb-3">support@signale-france.fr</p>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">24-48h</span>
                        </div>
                        <div class="text-center p-6 bg-white rounded-xl shadow-sm">
                            <div
                                class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-phone text-white text-2xl"></i>
                            </div>
                            <h4 class="font-semibold mb-2">Téléphone</h4>
                            <p class="text-sm text-gray-600 mb-3">01 23 45 67 89</p>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Immédiat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="mt-16 glass-effect rounded-2xl p-8 text-center shadow-xl">
        <div
            class="w-20 h-20 bg-gradient-to-r from-france-blue to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-life-ring text-3xl text-white"></i>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">
            Besoin d'aide supplémentaire ?
        </h2>
        <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
            Si vous ne trouvez pas la réponse à votre question, notre équipe de support est là pour vous aider.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="mailto:support@signale-france.fr"
                class="bg-gradient-to-r from-france-blue to-blue-600 text-white px-8 py-4 rounded-xl font-semibold hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-envelope mr-2"></i>
                Envoyer un email
            </a>
            <a href="tel:0123456789"
                class="glass-effect text-france-blue px-8 py-4 rounded-xl font-semibold hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-phone mr-2"></i>
                Nous appeler
            </a>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let currentOpenFAQ = null;

    // Fonction principale pour basculer les FAQ
    function toggleFAQ(element) {
        const content = element.nextElementSibling;
        const toggle = element.querySelector('.faq-toggle');
        const isCurrentlyOpen = content.classList.contains('active');

        // Fermer la FAQ actuellement ouverte si différente
        if (currentOpenFAQ && currentOpenFAQ !== content) {
            currentOpenFAQ.classList.remove('active');
            const currentToggle = currentOpenFAQ.previousElementSibling.querySelector('.faq-toggle');
            currentToggle.classList.remove('active');
        }

        // Basculer la FAQ cliquée
        if (isCurrentlyOpen) {
            content.classList.remove('active');
            toggle.classList.remove('active');
            currentOpenFAQ = null;
        } else {
            content.classList.add('active');
            toggle.classList.add('active');
            currentOpenFAQ = content;

            // Scroll smooth vers l'élément après ouverture
            setTimeout(() => {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'nearest'
                });
            }, 100);
        }
    }

    // Fonction de filtrage par catégorie
    function filterFAQ(category) {
        const items = document.querySelectorAll('.faq-item');
        const buttons = document.querySelectorAll('.category-btn');

        // Réinitialiser les boutons
        buttons.forEach(btn => {
            btn.classList.remove('bg-gradient-to-r', 'from-france-blue', 'to-blue-600', 'text-white');
            btn.classList.add('glass-effect', 'text-gray-700');
        });

        // Activer le bouton sélectionné
        event.target.classList.remove('glass-effect', 'text-gray-700');
        event.target.classList.add('bg-gradient-to-r', 'from-france-blue', 'to-blue-600', 'text-white');

        // Fermer toutes les FAQ ouvertes
        if (currentOpenFAQ) {
            currentOpenFAQ.classList.remove('active');
            const currentToggle = currentOpenFAQ.previousElementSibling.querySelector('.faq-toggle');
            currentToggle.classList.remove('active');
            currentOpenFAQ = null;
        }

        // Filtrer et animer les éléments
        items.forEach((item, index) => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = 'block';
                item.classList.add('fade-in');
                setTimeout(() => {
                    item.classList.remove('fade-in');
                }, 500);
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Fonction de recherche
    function setupSearch() {
        const searchInput = document.getElementById('searchFAQ');
        const clearButton = document.getElementById('clearSearch');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.faq-item');
            let visibleCount = 0;

            // Afficher/masquer le bouton clear
            if (searchTerm) {
                clearButton.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
                searchResults.classList.add('hidden');
            }

            // Supprimer les anciens surlignages
            items.forEach(item => {
                const highlights = item.querySelectorAll('.search-highlight');
                highlights.forEach(highlight => {
                    highlight.outerHTML = highlight.textContent;
                });
            });

            // Filtrer et surligner
            items.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                const content = item.querySelector('.faq-content').textContent.toLowerCase();

                if (searchTerm === '' || title.includes(searchTerm) || content.includes(searchTerm)) {
                    item.style.display = 'block';
                    visibleCount++;

                    // Surligner les termes trouvés
                    if (searchTerm) {
                        highlightSearchTerm(item, searchTerm);
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            // Afficher les résultats
            if (searchTerm) {
                showSearchResults(visibleCount, searchTerm);
            }
        });

        clearButton.addEventListener('click', function () {
            searchInput.value = '';
            clearButton.classList.add('hidden');
            searchResults.classList.add('hidden');

            const items = document.querySelectorAll('.faq-item');
            items.forEach(item => {
                item.style.display = 'block';
                const highlights = item.querySelectorAll('.search-highlight');
                highlights.forEach(highlight => {
                    highlight.outerHTML = highlight.textContent;
                });
            });
        });
    }

    // Fonction pour surligner les termes de recherche
    function highlightSearchTerm(element, searchTerm) {
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        const textNodes = [];
        let node;

        while (node = walker.nextNode()) {
            if (node.parentNode.classList && !node.parentNode.classList.contains('search-highlight')) {
                textNodes.push(node);
            }
        }

        textNodes.forEach(textNode => {
            const text = textNode.textContent;
            const regex = new RegExp(`(${searchTerm})`, 'gi');

            if (regex.test(text)) {
                const highlightedHTML = text.replace(regex, '<span class="search-highlight">$1</span>');
                const wrapper = document.createElement('span');
                wrapper.innerHTML = highlightedHTML;
                textNode.parentNode.replaceChild(wrapper, textNode);
            }
        });
    }

    // Fonction pour afficher les résultats de recherche
    function showSearchResults(count, term) {
        const searchResults = document.getElementById('searchResults');
        searchResults.classList.remove('hidden');

        if (count === 0) {
            searchResults.innerHTML = `
                    <div class="flex items-center justify-center p-4 bg-red-50 rounded-lg border border-red-200">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                        <span class="text-red-700 font-medium">Aucun résultat trouvé pour "<strong>${term}</strong>"</span>
                    </div>
                `;
        } else {
            searchResults.innerHTML = `
                    <div class="flex items-center justify-center p-4 bg-green-50 rounded-lg border border-green-200">
                        <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                        <span class="text-green-700 font-medium">${count} résultat(s) trouvé(s) pour "<strong>${term}</strong>"</span>
                    </div>
                `;
        }
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function () {
        setupSearch();

        // Fermer les FAQ en cliquant en dehors
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.faq-item') && currentOpenFAQ) {
                currentOpenFAQ.classList.remove('active');
                const currentToggle = currentOpenFAQ.previousElementSibling.querySelector('.faq-toggle');
                currentToggle.classList.remove('active');
                currentOpenFAQ = null;
            }
        });

        // Support clavier
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && currentOpenFAQ) {
                currentOpenFAQ.classList.remove('active');
                const currentToggle = currentOpenFAQ.previousElementSibling.querySelector('.faq-toggle');
                currentToggle.classList.remove('active');
                currentOpenFAQ = null;
            }
        });
    });
</script>
<?php include '../Inc/Components/footer.php'; ?>
<?php include '../Inc/Components/footers.php'; ?>
<?php include('../Inc/Traitement/create_log.php'); ?>