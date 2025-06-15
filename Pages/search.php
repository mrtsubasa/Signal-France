<?php
session_start();
include("../Inc/Constants/db.php");
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header de recherche -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-center mb-8">
                    <i class="fas fa-search mr-2"></i>
                    Recherche de Signalements
                </h1>

                <!-- Barre de recherche principale -->
                <div class="relative">
                    <div class="flex bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="flex items-center px-4 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text"
                               id="main-search"
                               class="flex-1 px-4 py-4 text-gray-900 placeholder-gray-500 focus:outline-none text-lg"
                               placeholder="Rechercher par nom, prénom, titre, type d'incident..."
                               autocomplete="off">
                        <button class="px-4 text-gray-400 hover:text-gray-600" type="button" id="clear-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Suggestions d'autocomplétion -->
                    <div id="autocomplete-suggestions"
                         class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-lg shadow-lg max-h-80 overflow-y-auto z-50 hidden">
                    </div>
                </div>

                <!-- Boutons de recherche rapide -->
                <div class="flex flex-wrap justify-center gap-4 mt-6">
                    <button onclick="loadStats()"
                            class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-chart-bar mr-2"></i>Statistiques
                    </button>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button onclick="showAdvancedSearch()"
                                class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-filter mr-2"></i>Recherche avancée
                        </button>
                        <button onclick="loadRecentReports()"
                                class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-clock mr-2"></i>Récents
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Message pour utilisateurs non connectés -->
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                    <div>
                        <p class="text-blue-800 font-medium">Recherche limitée</p>
                        <p class="text-blue-600 text-sm">En tant qu'utilisateur non connecté, vous ne verrez que si un signalement existe ou non. <a href="../login.php" class="underline">Connectez-vous</a> pour voir les détails complets.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtres avancés (uniquement pour utilisateurs connectés) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div id="advanced-filters" class="bg-white rounded-lg shadow-lg p-6 mb-8 hidden">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>
                    Filtres avancés
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Recherche par personne -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                        <input type="text" id="filter-nom"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Nom de famille">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                        <input type="text" id="filter-prenom"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Prénom">
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select id="filter-statut"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolu">Résolu</option>
                            <option value="ferme">Fermé</option>
                        </select>
                    </div>

                    <!-- Priorité -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                        <select id="filter-priorite"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Toutes les priorités</option>
                            <option value="basse">Basse</option>
                            <option value="normale">Normale</option>
                            <option value="haute">Haute</option>
                            <option value="critique">Critique</option>
                        </select>
                    </div>

                    <!-- Type d'incident -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type d'incident</label>
                        <input type="text" id="filter-type"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Type d'incident">
                    </div>

                    <!-- Contexte -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contexte</label>
                        <select id="filter-context"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tous les contextes</option>
                            <option value="irl">IRL</option>
                            <option value="online">En ligne</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 mt-6">
                    <button onclick="applyAdvancedFilters()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>Rechercher
                    </button>
                    <button onclick="clearAdvancedFilters()"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-eraser mr-2"></i>Effacer
                    </button>
                    <button onclick="hideAdvancedSearch()"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Fermer
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Zone de résultats -->
        <div id="search-results-container">
            <!-- Message de bienvenue -->
            <div id="welcome-message" class="text-center py-12">
                <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
                    <div class="text-6xl text-blue-600 mb-4">
                        <i class="fas fa-search"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Recherche de Signalements</h2>
                    <p class="text-gray-600 mb-6">
                        Utilisez la barre de recherche ci-dessus pour trouver des signalements par nom, prénom,
                        titre, type d'incident ou toute autre information.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <i class="fas fa-user text-blue-600 mb-2 text-lg"></i>
                            <p><strong>Recherche par personne</strong><br>Tapez un nom ou prénom</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <i class="fas fa-tags text-green-600 mb-2 text-lg"></i>
                            <p><strong>Par type d'incident</strong><br>Recherchez par catégorie</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <i class="fas fa-filter text-purple-600 mb-2 text-lg"></i>
                            <p><strong>Filtres avancés</strong><br><?php echo isset($_SESSION['user_id']) ? 'Affinez votre recherche' : 'Connectez-vous pour plus d\'options'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spinner de chargement -->
            <div id="loading-spinner" class="hidden text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600">Recherche en cours...</p>
            </div>

            <!-- Résultats de recherche -->
            <div id="search-results" class="hidden">
                <div id="results-header" class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="flex flex-wrap items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Résultats de recherche</h3>
                            <p id="results-count" class="text-gray-600"></p>
                        </div>
                        <div class="flex gap-2 mt-2 sm:mt-0">
                            <button onclick="exportResults()" class="text-sm bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                                <i class="fas fa-download mr-1"></i>Exporter
                            </button>
                            <button onclick="clearResults()" class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded">
                                <i class="fas fa-times mr-1"></i>Effacer
                            </button>
                        </div>
                    </div>
                </div>

                <div id="results-list" class="space-y-4">
                    <!-- Les résultats seront injectés ici par JavaScript -->
                </div>

                <div id="load-more-container" class="text-center mt-8 hidden">
                    <button onclick="loadMoreResults()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Charger plus de résultats
                    </button>
                </div>
            </div>

            <!-- Statistiques -->
            <div id="stats-container" class="hidden">
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h3 class="text-xl font-bold mb-6">
                        <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                        Statistiques des Signalements
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-blue-600" id="total-count">-</div>
                            <div class="text-sm text-gray-600">Total</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-green-600" id="recent-count">-</div>
                            <div class="text-sm text-gray-600">Récents (24h)</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-purple-600" id="with-names-count">-</div>
                            <div class="text-sm text-gray-600">Avec nom/prénom</div>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-orange-600" id="with-title-count">-</div>
                            <div class="text-sm text-gray-600">Avec titre seulement</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Statistiques par statut -->
                        <div>
                            <h4 class="font-semibold mb-4">Par Statut</h4>
                            <div id="stats-by-status" class="space-y-2">
                                <!-- Sera rempli par JavaScript -->
                            </div>
                        </div>

                        <!-- Statistiques par priorité -->
                        <div>
                            <h4 class="font-semibold mb-4">Par Priorité</h4>
                            <div id="stats-by-priority" class="space-y-2">
                                <!-- Sera rempli par JavaScript -->
                            </div>
                        </div>

                        <!-- Types d'incidents les plus fréquents -->
                        <div>
                            <h4 class="font-semibold mb-4">Types d'incidents les plus fréquents</h4>
                            <div id="stats-by-type" class="space-y-2">
                                <!-- Sera rempli par JavaScript -->
                            </div>
                        </div>

                        <!-- Personnes les plus signalées -->
                        <div>
                            <h4 class="font-semibold mb-4">Personnes les plus signalées</h4>
                            <div id="stats-most-reported" class="space-y-2">
                                <!-- Sera rempli par JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message aucun résultat -->
            <div id="no-results" class="hidden text-center py-12">
                <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-auto">
                    <div class="text-4xl text-gray-400 mb-4">
                        <i class="fas fa-search-minus"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Aucun résultat trouvé</h3>
                    <p class="text-gray-600 mb-4">Essayez avec d'autres termes de recherche ou utilisez les filtres avancés.</p>
                    <button onclick="clearSearch()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-redo mr-2"></i>Nouvelle recherche
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails (pour utilisateurs connectés) -->
<?php if (isset($_SESSION['user_id'])): ?>
    <div id="signalement-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeModal()"></div>

            <div class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Détails du Signalement</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="modal-content">
                    <!-- Le contenu sera injecté par JavaScript -->
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Variables globales
    let currentQuery = '';
    let currentResults = [];
    let isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    let debounceTimer;

    // Configuration AJAX
    const AJAX_URL = 'search_ajax.php';

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        initializeSearch();
    });

    function initializeSearch() {
        const searchInput = document.getElementById('main-search');
        const clearButton = document.getElementById('clear-search');

        // Événements de recherche
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                handleSearch(this.value);
            }, 300);
        });

        // Gestion de l'autocomplétion
        searchInput.addEventListener('keydown', function(e) {
            handleAutocompleteNavigation(e);
        });

        // Bouton effacer
        clearButton.addEventListener('click', function() {
            clearSearch();
        });

        // Cacher les suggestions quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#main-search') && !e.target.closest('#autocomplete-suggestions')) {
                hideAutocompleteSuggestions();
            }
        });
    }

    function handleSearch(query) {
        query = query.trim();
        currentQuery = query;

        if (query.length < 2) {
            hideAutocompleteSuggestions();
            showWelcomeMessage();
            return;
        }

        showLoading();

        // Recherche avec autocomplétion
        fetch(AJAX_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=quick_search&query=${encodeURIComponent(query)}&limit=20`
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    currentResults = data.suggestions;
                    displayResults(data.suggestions, data.query);

                    // Afficher autocomplétion si peu de résultats
                    if (data.suggestions.length < 5) {
                        showAutocompleteSuggestions(query);
                    }
                } else {
                    showError(data.error || 'Erreur lors de la recherche');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Erreur de connexion');
                console.error('Erreur:', error);
            });
    }

    function showAutocompleteSuggestions(query) {
        fetch(AJAX_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=autocomplete_nom_prenom&query=${encodeURIComponent(query)}&limit=8`
        })
            .then(response => response.json())
            .then(data => {
                if (data.suggestions && data.suggestions.length > 0) {
                    displayAutocompleteSuggestions(data.suggestions);
                }
            })
            .catch(error => console.error('Erreur autocomplétion:', error));
    }

    function displayAutocompleteSuggestions(suggestions) {
        const container = document.getElementById('autocomplete-suggestions');

        if (suggestions.length === 0) {
            hideAutocompleteSuggestions();
            return;
        }

        let html = '';
        suggestions.forEach(suggestion => {
            const statusClass = getStatusClass(suggestion.statut);
            html += `
            <div class="autocomplete-item flex items-center justify-between p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                 onclick="selectSuggestion('${suggestion.nom_complet}')">
                <div class="flex-1">
                    <div class="font-medium text-gray-900">${suggestion.nom_complet}</div>
                    <div class="text-sm text-gray-600">${suggestion.type_incident}</div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2 py-1 text-xs rounded-full ${statusClass}">
                        ${getStatusLabel(suggestion.statut)}
                    </span>
                    <div class="text-xs text-gray-500 mt-1">
                        ${formatDate(suggestion.date_signalement)}
                    </div>
                </div>
            </div>
        `;
        });

        container.innerHTML = html;
        container.classList.remove('hidden');
    }

    function hideAutocompleteSuggestions() {
        document.getElementById('autocomplete-suggestions').classList.add('hidden');
    }

    function selectSuggestion(text) {
        document.getElementById('main-search').value = text;
        hideAutocompleteSuggestions();
        handleSearch(text);
    }

    function displayResults(results, query) {
        hideAllSections();

        if (results.length === 0) {
            showNoResults();
            return;
        }

        const container = document.getElementById('search-results');
        const countElement = document.getElementById('results-count');
        const listElement = document.getElementById('results-list');

        countElement.textContent = `${results.length} résultat(s) trouvé(s) pour "${query}"`;

        let html = '';
        results.forEach(result => {
            if (isLoggedIn) {
                html += createDetailedResultCard(result);
            } else {
                html += createSimpleResultCard(result);
            }
        });

        listElement.innerHTML = html;
        container.classList.remove('hidden');
    }

    function createDetailedResultCard(result) {
        const statusClass = getStatusClass(result.statut);
        const priorityClass = getPriorityClass(result.priorite);

        return `
        <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6 border-l-4 ${priorityClass}">
            <div class="flex flex-wrap items-start justify-between mb-4">
                <div class="flex-1 min-w-0">
                    <h4 class="text-lg font-semibold text-gray-900 mb-1">
                        ${result.nom_complet}
                    </h4>
                    <div class="flex flex-wrap gap-2 text-sm text-gray-600">
                        <span class="inline-flex items-center">
                            <i class="fas fa-tag mr-1"></i>
                            ${result.type_incident}
                        </span>
                        <span class="inline-flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            ${result.date_formatted}
                        </span>
                        ${result.localisation ? `
                            <span class="inline-flex items-center">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                ${result.localisation}
                            </span>
                        ` : ''}
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2 ml-4">
                    <span class="inline-block px-3 py-1 text-sm rounded-full ${statusClass}">
                        ${getStatusLabel(result.statut)}
                    </span>
                    <span class="inline-block px-2 py-1 text-xs rounded-full ${getPriorityBadgeClass(result.priorite)}">
                        ${getPriorityLabel(result.priorite)}
                    </span>
                </div>
            </div>

            <p class="text-gray-600 mb-4 line-clamp-2">
                ${result.description_courte}
            </p>

            <div class="flex flex-wrap gap-2">
                <button onclick="viewSignalement(${result.id})"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    <i class="fas fa-eye mr-1"></i>
                    Voir détails
                </button>

                ${result.lieu ? `
                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded text-sm">
                        <i class="fas fa-map-pin mr-1"></i>
                        ${result.lieu}
                    </span>
                ` : ''}
            </div>
        </div>
    `;
    }

    function createSimpleResultCard(result) {
        return `
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">
                        Signalement trouvé
                    </h4>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                        <span class="inline-flex items-center">
                            <i class="fas fa-tag mr-1"></i>
                            ${result.type_incident}
                        </span>
                        <span class="inline-flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            ${result.date_formatted}
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-green-600 text-2xl mb-2">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p class="text-sm text-gray-600">
                        <a href="../login.php" class="text-blue-600 hover:underline">
                            Connectez-vous
                        </a><br>
                        pour voir les détails
                    </p>
                </div>
            </div>
        </div>
    `;
    }

    // Fonctions utilitaires pour les classes CSS
    function getStatusClass(status) {
        const classes = {
            'en_attente': 'bg-yellow-100 text-yellow-800',
            'en_cours': 'bg-blue-100 text-blue-800',
            'resolu': 'bg-green-100 text-green-800',
            'ferme': 'bg-gray-100 text-gray-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getStatusLabel(status) {
        const labels = {
            'en_attente': 'En attente',
            'en_cours': 'En cours',
            'resolu': 'Résolu',
            'ferme': 'Fermé'
        };
        return labels[status] || status;
    }

    function getPriorityClass(priority) {
        const classes = {
            'critique': 'border-red-500',
            'haute': 'border-orange-500',
            'normale': 'border-blue-500',
            'basse': 'border-green-500'
        };
        return classes[priority] || 'border-blue-500';
    }

    function getPriorityBadgeClass(priority) {
        const classes = {
            'critique': 'bg-red-100 text-red-800',
            'haute': 'bg-orange-100 text-orange-800',
            'normale': 'bg-blue-100 text-blue-800',
            'basse': 'bg-green-100 text-green-800'
        };
        return classes[priority] || 'bg-blue-100 text-blue-800';
    }

    function getPriorityLabel(priority) {
        const labels = {
            'critique': 'Critique',
            'haute': 'Haute',
            'normale': 'Normale',
            'basse': 'Basse'
        };
        return labels[priority] || priority;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Gestion des statistiques
    function loadStats() {
        hideAllSections();
        showLoading();

        fetch(AJAX_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_stats'
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    displayStats(data.stats);
                } else {
                    showError(data.error || 'Erreur lors du chargement des statistiques');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Erreur de connexion');
                console.error('Erreur:', error);
            });
    }

    function displayStats(stats) {
        hideAllSections();

        // Mettre à jour les compteurs principaux
        document.getElementById('total-count').textContent = stats.total || 0;
        document.getElementById('recent-count').textContent = stats.recent || 0;
        document.getElementById('with-names-count').textContent = stats.with_names || 0;
        document.getElementById('with-title-count').textContent = stats.with_title_only || 0;

        // Statistiques par statut
        displayStatSection('stats-by-status', stats.by_status, 'statut');

        // Statistiques par priorité
        displayStatSection('stats-by-priority', stats.by_priority, 'priorite');

        // Types d'incidents
        displayStatSection('stats-by-type', stats.by_type, 'type_incident');

        // Personnes les plus signalées
        displayMostReported('stats-most-reported', stats.most_reported);

        document.getElementById('stats-container').classList.remove('hidden');
    }

    function displayStatSection(containerId, data, field) {
        const container = document.getElementById(containerId);
        let html = '';

        if (data && data.length > 0) {
            data.forEach(item => {
                const percentage = ((item.count / getTotalFromStats()) * 100).toFixed(1);
                html += `
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700">${item[field] || 'Non défini'}</span>
                    <div class="flex items-center">
                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 w-8">${item.count}</span>
                    </div>
                </div>
            `;
            });
        } else {
            html = '<p class="text-gray-500 text-sm">Aucune donnée disponible</p>';
        }

        container.innerHTML = html;
    }

    function displayMostReported(containerId, data) {
        const container = document.getElementById(containerId);
        let html = '';

        if (data && data.length > 0) {
            data.forEach((item, index) => {
                html += `
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center">
                        <span class="w-6 h-6 bg-blue-600 text-white text-xs rounded-full flex items-center justify-center mr-3">
                            ${index + 1}
                        </span>
                        <span class="text-gray-700">${item.nom_complet}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">${item.count} signalement(s)</span>
                </div>
            `;
            });
        } else {
            html = '<p class="text-gray-500 text-sm">Aucune donnée disponible</p>';
        }

        container.innerHTML = html;
    }

    function getTotalFromStats() {
        return parseInt(document.getElementById('total-count').textContent) || 1;
    }

    // Filtres avancés (uniquement pour utilisateurs connectés)
    <?php if (isset($_SESSION['user_id'])): ?>
    function showAdvancedSearch() {
        document.getElementById('advanced-filters').classList.remove('hidden');
        document.getElementById('advanced-filters').scrollIntoView({ behavior: 'smooth' });
    }

    function hideAdvancedSearch() {
        document.getElementById('advanced-filters').classList.add('hidden');
    }

    function applyAdvancedFilters() {
        const nom = document.getElementById('filter-nom').value.trim();
        const prenom = document.getElementById('filter-prenom').value.trim();

        if (nom || prenom) {
            showLoading();

            const params = new URLSearchParams();
            params.append('action', 'search_by_person');
            if (nom) params.append('nom', nom);
            if (prenom) params.append('prenom', prenom);
            params.append('limit', '50');

            fetch(AJAX_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        currentResults = data.results;
                        displayResults(data.results, `${prenom} ${nom}`.trim());
                        hideAdvancedSearch();
                    } else {
                        showError(data.error || 'Erreur lors de la recherche avancée');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showError('Erreur de connexion');
                    console.error('Erreur:', error);
                });
        } else {
            alert('Veuillez renseigner au moins un nom ou un prénom');
        }
    }

    function clearAdvancedFilters() {
        document.getElementById('filter-nom').value = '';
        document.getElementById('filter-prenom').value = '';
        document.getElementById('filter-statut').value = '';
        document.getElementById('filter-priorite').value = '';
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-context').value = '';
    }

    // Modal de détails
    function viewSignalement(id) {
        showLoading();

        fetch(AJAX_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get_signalement&signalement_id=${id}`
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    displaySignalementModal(data.signalement);
                } else {
                    showError(data.error || 'Erreur lors du chargement du signalement');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Erreur de connexion');
                console.error('Erreur:', error);
            });
    }

    function displaySignalementModal(signalement) {
        const modalContent = document.getElementById('modal-content');

        let html = `
        <div class="space-y-6">
            <!-- En-tête -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex flex-wrap items-start justify-between">
                    <div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2">
                            ${signalement.titre_complet}
                        </h4>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded">
                                ID: #${signalement.id}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 ${getStatusClass(signalement.statut)} rounded">
                                ${getStatusLabel(signalement.statut)}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 ${getPriorityBadgeClass(signalement.priorite)} rounded">
                                ${getPriorityLabel(signalement.priorite)}
                            </span>
                        </div>
                    </div>
                    <div class="text-right text-sm text-gray-600">
                        <div>Signalé le ${signalement.date_signalement_formatted}</div>
                        ${signalement.date_traitement_formatted ? `<div>Traité le ${signalement.date_traitement_formatted}</div>` : ''}
                    </div>
                </div>
            </div>

            <!-- Informations principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h5 class="font-semibold text-gray-900 mb-3">Informations générales</h5>
                    <div class="space-y-2 text-sm">
                        <div><strong>Type d'incident:</strong> ${signalement.type_incident}</div>
                        <div><strong>Contexte:</strong> ${signalement.incident_context}</div>
                        ${signalement.plateforme ? `<div><strong>Plateforme:</strong> ${signalement.plateforme}</div>` : ''}
                        ${signalement.localisation ? `<div><strong>Localisation:</strong> ${signalement.localisation}</div>` : ''}
                        ${signalement.lieu ? `<div><strong>Lieu:</strong> ${signalement.lieu}</div>` : ''}
                    </div>
                </div>

                <div>
                    <h5 class="font-semibold text-gray-900 mb-3">Auteur</h5>
                    <div class="space-y-2 text-sm">
                        <div><strong>Signalé par:</strong> ${signalement.auteur_complet}</div>
                        ${signalement.auteur_organization ? `<div><strong>Organisation:</strong> ${signalement.auteur_organization}</div>` : ''}
                        ${signalement.email_contact ? `<div><strong>Contact:</strong> ${signalement.email_contact}</div>` : ''}
                        <div><strong>Anonyme:</strong> ${signalement.anonyme ? 'Oui' : 'Non'}</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h5 class="font-semibold text-gray-900 mb-3">Description</h5>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700 whitespace-pre-wrap">${signalement.description}</p>
                </div>
            </div>

            <!-- Images et preuves -->
            ${signalement.images_array.length > 0 || signalement.preuves_array.length > 0 ? `
                <div>
                    <h5 class="font-semibold text-gray-900 mb-3">Fichiers joints</h5>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        ${signalement.images_array.map(image => `
                            <div class="border rounded-lg p-2">
                                <img src="${image}" alt="Image" class="w-full h-20 object-cover rounded mb-2">
                                <p class="text-xs text-gray-600">Image</p>
                            </div>
                        `).join('')}
                        ${signalement.preuves_array.map(preuve => `
                            <div class="border rounded-lg p-2">
                                <div class="w-full h-20 bg-gray-100 rounded mb-2 flex items-center justify-center">
                                    <i class="fas fa-file text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-xs text-gray-600">Document</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}

            <!-- Traitement -->
            ${signalement.traite_par_username || signalement.commentaire_traitement ? `
                <div>
                    <h5 class="font-semibold text-gray-900 mb-3">Traitement</h5>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        ${signalement.traite_par_username ? `<div class="mb-2"><strong>Traité par:</strong> ${signalement.traite_par_username}</div>` : ''}
                        ${signalement.commentaire_traitement ? `
                            <div>
                                <strong>Commentaire:</strong>
                                <p class="mt-1 text-gray-700">${signalement.commentaire_traitement}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
        </div>
    `;

        modalContent.innerHTML = html;
        document.getElementById('signalement-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('signalement-modal').classList.add('hidden');
    }
    <?php endif; ?>

    // Signalements récents
    function loadRecentReports() {
        // Simuler une recherche pour les signalements récents
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 7);

        showLoading();

        // Pour cette demo, on fait une recherche générale et on filtre côté client
        fetch(AJAX_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=quick_search&query=&limit=50'
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    // Filtrer les résultats récents (dernière semaine)
                    const recentResults = data.suggestions.filter(result => {
                        const resultDate = new Date(result.date_signalement);
                        return resultDate >= yesterday;
                    });

                    currentResults = recentResults;
                    displayResults(recentResults, 'Signalements récents (7 derniers jours)');
                } else {
                    showError(data.error || 'Erreur lors du chargement des signalements récents');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Erreur de connexion');
                console.error('Erreur:', error);
            });
    }

    // Fonctions de gestion de l'affichage
    function hideAllSections() {
        document.getElementById('welcome-message').classList.add('hidden');
        document.getElementById('search-results').classList.add('hidden');
        document.getElementById('stats-container').classList.add('hidden');
        document.getElementById('no-results').classList.add('hidden');
        document.getElementById('loading-spinner').classList.add('hidden');
    }

    function showWelcomeMessage() {
        hideAllSections();
        document.getElementById('welcome-message').classList.remove('hidden');
    }

    function showLoading() {
        hideAllSections();
        document.getElementById('loading-spinner').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loading-spinner').classList.add('hidden');
    }

    function showNoResults() {
        hideAllSections();
        document.getElementById('no-results').classList.remove('hidden');
    }

    function showError(message) {
        alert('Erreur: ' + message);
    }

    // Fonctions utilitaires
    function clearSearch() {
        document.getElementById('main-search').value = '';
        currentQuery = '';
        currentResults = [];
        hideAutocompleteSuggestions();
        showWelcomeMessage();
    }

    function clearResults() {
        clearSearch();
    }

    function exportResults() {
        if (currentResults.length === 0) {
            alert('Aucun résultat à exporter');
            return;
        }

        // Créer un CSV simple
        let csv = 'ID,Nom,Type incident,Statut,Priorité,Date,Localisation\n';
        currentResults.forEach(result => {
            csv += `${result.id},"${result.nom_complet}","${result.type_incident}","${getStatusLabel(result.statut)}","${getPriorityLabel(result.priorite)}","${result.date_formatted}","${result.localisation || ''}"\n`;
        });

        // Télécharger le fichier
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `signalements_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // Gestion navigation clavier autocomplétion
    function handleAutocompleteNavigation(e) {
        const suggestions = document.getElementById('autocomplete-suggestions');
        if (suggestions.classList.contains('hidden')) return;

        const items = suggestions.querySelectorAll('.autocomplete-item');
        let activeIndex = -1;

        // Trouver l'élément actif
        items.forEach((item, index) => {
            if (item.classList.contains('active')) {
                activeIndex = index;
            }
        });

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                break;
            case 'ArrowUp':
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, -1);
                break;
            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0) {
                    items[activeIndex].click();
                }
                return;
            case 'Escape':
                hideAutocompleteSuggestions();
                return;
        }

        // Mettre à jour les classes actives
        items.forEach((item, index) => {
            if (index === activeIndex) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
</script>
<?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include('../Inc/Traitement/create_log.php'); ?>