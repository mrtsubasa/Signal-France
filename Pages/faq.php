<?php
session_start();
require_once '../Inc/Constants/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Signale France</title>
    <link rel="stylesheet" href="../Assets/Css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .faq-item {
            transition: all 0.3s ease;
        }
        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .faq-content.active {
            max-height: 500px;
        }
        .faq-toggle {
            transition: transform 0.3s ease;
        }
        .faq-toggle.active {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../Inc/Components/header.php'; ?>
    <?php include '../Inc/Components/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-question-circle text-blue-600 mr-3"></i>
                    Foire Aux Questions
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Trouvez rapidement les réponses à vos questions les plus fréquentes concernant 
                    l'utilisation de la plateforme Signale France.
                </p>
            </div>

            <!-- Barre de recherche -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="relative">
                    <input type="text" 
                           id="searchFAQ" 
                           placeholder="Rechercher dans la FAQ..."
                           class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Catégories -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <button onclick="filterFAQ('all')" class="filter-btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-list mr-2"></i>Toutes
                </button>
                <button onclick="filterFAQ('signalement')" class="filter-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition duration-200">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Signalements
                </button>
                <button onclick="filterFAQ('compte')" class="filter-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition duration-200">
                    <i class="fas fa-user mr-2"></i>Compte
                </button>
            </div>

            <!-- Questions FAQ -->
            <div class="space-y-4" id="faqContainer">
                
                <!-- Catégorie: Signalements -->
                <div class="faq-item bg-white rounded-lg shadow-md" data-category="signalement">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Comment créer un nouveau signalement ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Pour créer un nouveau signalement :</p>
                            <ol class="list-decimal list-inside space-y-2 ml-4">
                                <li>Cliquez sur "Créer un signalement" dans le menu principal</li>
                                <li>Remplissez le titre et sélectionnez le type d'incident</li>
                                <li>Choisissez le type d'incident (Physique ou En ligne)</li>
                                <li>Définissez la priorité et la localisation</li>
                                <li>Rédigez une description détaillée</li>
                                <li>Ajoutez des preuves si nécessaire (photos, vidéos, documents)</li>
                                <li>Cliquez sur "Créer le signalement"</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-lg shadow-md" data-category="signalement">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Quels types de fichiers puis-je joindre comme preuves ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Vous pouvez joindre les types de fichiers suivants :</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-semibold mb-2">Images :</h4>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        <li>JPEG, JPG, PNG, GIF, WebP</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Vidéos :</h4>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        <li>MP4, AVI, MOV, WMV</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Documents :</h4>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        <li>PDF, DOC, DOCX</li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-gray-600">Taille maximale : 10 MB par fichier, maximum 5 fichiers par signalement.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-lg shadow-md" data-category="signalement">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Puis-je faire un signalement anonyme ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Oui, vous pouvez créer des signalements anonymes :</p>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Cochez l'option "Signalement anonyme" lors de la création</li>
                                <li>Votre nom ne sera pas visible publiquement</li>
                                <li>Vous pouvez optionnellement fournir un email de contact</li>
                                <li>L'anonymat est respecté dans tous les affichages publics</li>
                            </ul>
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Note : Les administrateurs peuvent toujours voir l'auteur pour des raisons de modération.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-lg shadow-md" data-category="signalement">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Comment suivre l'état de mon signalement ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Plusieurs moyens de suivre vos signalements :</p>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Consultez votre profil pour voir tous vos signalements</li>
                                <li>Utilisez la fonction de recherche avec votre nom</li>
                                <li>Les statuts possibles sont : En attente, En cours, Résolu, Rejeté</li>
                                <li>Vous recevrez des notifications en cas de changement de statut</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Catégorie: Compte -->
                <div class="faq-item bg-white rounded-lg shadow-md" data-category="compte">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Comment modifier mes informations personnelles ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Pour modifier vos informations :</p>
                            <ol class="list-decimal list-inside space-y-2 ml-4">
                                <li>Cliquez sur votre nom dans le menu (coin supérieur droit)</li>
                                <li>Sélectionnez "Mon Profil"</li>
                                <li>Cliquez sur "Modifier le profil"</li>
                                <li>Modifiez les informations souhaitées</li>
                                <li>Cliquez sur "Sauvegarder les modifications"</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-lg shadow-md" data-category="compte">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Comment changer mon mot de passe ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Pour changer votre mot de passe :</p>
                            <ol class="list-decimal list-inside space-y-2 ml-4">
                                <li>Allez dans "Mon Profil"</li>
                                <li>Cliquez sur "Modifier le profil"</li>
                                <li>Remplissez les champs "Mot de passe actuel" et "Nouveau mot de passe"</li>
                                <li>Confirmez le nouveau mot de passe</li>
                                <li>Cliquez sur "Sauvegarder"</li>
                            </ol>
                            <div class="mt-4 p-3 bg-yellow-50 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Utilisez un mot de passe fort avec au moins 8 caractères, incluant majuscules, minuscules et chiffres.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-lg shadow-md" data-category="compte">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Que faire si j'ai oublié mon mot de passe ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Si vous avez oublié votre mot de passe :</p>
                            <ol class="list-decimal list-inside space-y-2 ml-4">
                                <li>Cliquez sur "Mot de passe oublié ?" sur la page de connexion</li>
                                <li>Saisissez votre adresse email</li>
                                <li>Vérifiez votre boîte mail (et les spams)</li>
                                <li>Cliquez sur le lien de réinitialisation</li>
                                <li>Créez un nouveau mot de passe</li>
                            </ol>
                            <p class="mt-3 text-sm text-gray-600">
                                Si vous ne recevez pas l'email, contactez l'administrateur.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Questions générales -->
                <div class="faq-item bg-white rounded-lg shadow-md" data-category="general">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Qui peut voir mes signalements ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">La visibilité de vos signalements dépend de plusieurs facteurs :</p>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li><strong>Utilisateurs connectés :</strong> Peuvent voir tous les signalements publics</li>
                                <li><strong>Signalements anonymes :</strong> Votre nom n'apparaît pas</li>
                                <li><strong>Administrateurs :</strong> Ont accès à tous les signalements pour modération</li>
                                <li><strong>Modérateurs :</strong> Peuvent gérer et modifier les signalements</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-lg shadow-md" data-category="general">
                    <div class="faq-header p-6 cursor-pointer flex justify-between items-center" onclick="toggleFAQ(this)">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Comment contacter le support ?
                        </h3>
                        <i class="fas fa-chevron-down faq-toggle text-blue-600"></i>
                    </div>
                    <div class="faq-content px-6 pb-6">
                        <div class="text-gray-700 leading-relaxed">
                            <p class="mb-3">Plusieurs moyens de nous contacter :</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-semibold text-blue-800 mb-2">
                                        <i class="fas fa-envelope mr-2"></i>Email
                                    </h4>
                                    <p class="text-sm text-blue-700">support@signale-france.fr</p>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <h4 class="font-semibold text-green-800 mb-2">
                                        <i class="fas fa-phone mr-2"></i>Téléphone
                                    </h4>
                                    <p class="text-sm text-green-700">01 23 45 67 89</p>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                Temps de réponse habituel : 24-48 heures ouvrées.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section d'aide supplémentaire -->
            <div class="mt-12 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-8 text-white text-center">
                <h2 class="text-2xl font-bold mb-4">
                    <i class="fas fa-life-ring mr-3"></i>
                    Besoin d'aide supplémentaire ?
                </h2>
                <p class="text-blue-100 mb-6">
                    Si vous ne trouvez pas la réponse à votre question, n'hésitez pas à nous contacter directement.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="mailto:support@signale-france.fr" 
                       class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-envelope mr-2"></i>
                        Envoyer un email
                    </a>
                    <a href="tel:0123456789" 
                       class="bg-blue-800 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-900 transition duration-200">
                        <i class="fas fa-phone mr-2"></i>
                        Nous appeler
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour basculer l'affichage des réponses FAQ
        function toggleFAQ(element) {
            const content = element.nextElementSibling;
            const toggle = element.querySelector('.faq-toggle');
            
            content.classList.toggle('active');
            toggle.classList.toggle('active');
        }

        // Fonction pour filtrer les FAQ par catégorie
        function filterFAQ(category) {
            const items = document.querySelectorAll('.faq-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Réinitialiser les boutons
            buttons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            // Activer le bouton sélectionné
            event.target.classList.remove('bg-gray-200', 'text-gray-700');
            event.target.classList.add('bg-blue-600', 'text-white');
            
            // Filtrer les éléments
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Fonction de recherche dans la FAQ
        document.getElementById('searchFAQ').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const items = document.querySelectorAll('.faq-item');
            
            items.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                const content = item.querySelector('.faq-content').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || content.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Fermer toutes les FAQ ouvertes quand on en ouvre une nouvelle
        document.querySelectorAll('.faq-header').forEach(header => {
            header.addEventListener('click', function() {
                const currentContent = this.nextElementSibling;
                const currentToggle = this.querySelector('.faq-toggle');
                
                // Si cette FAQ n'est pas active, fermer toutes les autres
                if (!currentContent.classList.contains('active')) {
                    document.querySelectorAll('.faq-content.active').forEach(content => {
                        content.classList.remove('active');
                    });
                    document.querySelectorAll('.faq-toggle.active').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                }
            });
        });
    </script>

    <?php include '../Inc/Components/footer.php'; ?>
</body>
</html>