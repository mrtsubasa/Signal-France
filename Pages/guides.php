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
    <title>Guides - Signale France</title>
    <link rel="stylesheet" href="../Assets/Css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .guide-card { transition: all 0.3s ease; }
        .guide-card:hover { transform: translateY(-5px); }
        .step-number { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../Inc/Components/header.php'; ?>
    <?php include '../Inc/Components/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- En-tête -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-3">
                    <i class="fas fa-book-open text-blue-600 mr-3"></i>
                    Guides d'utilisation
                </h1>
                <p class="text-gray-600">Guide rapide pour utiliser efficacement Signale France</p>
            </div>

            <!-- Grille des guides -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Guide 1: Créer un signalement -->
                <div class="guide-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="step-number w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Créer un signalement</h2>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start space-x-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">1</span>
                            <span class="text-gray-700">Cliquez sur "Créer un signalement"</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">2</span>
                            <span class="text-gray-700">Remplissez le titre, type et priorité</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">3</span>
                            <span class="text-gray-700">Choisissez le type d'incident (Physique/En ligne)</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">4</span>
                            <span class="text-gray-700">Ajoutez une description détaillée</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">5</span>
                            <span class="text-gray-700">Joignez des preuves (photos, vidéos, documents)</span>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-yellow-50 rounded">
                        <p class="text-xs text-yellow-800"><i class="fas fa-lightbulb mr-1"></i> Plus votre signalement est détaillé, plus il sera traité rapidement.</p>
                    </div>
                </div>

                <!-- Guide 2: Rechercher -->
                <div class="guide-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="step-number w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Rechercher</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="text-sm text-gray-700">
                            <strong>Filtres disponibles :</strong>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-blue-50 p-2 rounded">
                                <i class="fas fa-tag text-blue-600 mr-1"></i> Type
                            </div>
                            <div class="bg-green-50 p-2 rounded">
                                <i class="fas fa-exclamation-triangle text-green-600 mr-1"></i> Incident
                            </div>
                            <div class="bg-yellow-50 p-2 rounded">
                                <i class="fas fa-traffic-light text-yellow-600 mr-1"></i> Statut
                            </div>
                            <div class="bg-purple-50 p-2 rounded">
                                <i class="fas fa-map-marker-alt text-purple-600 mr-1"></i> Lieu
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            Utilisez la recherche globale ou ciblée selon vos besoins.
                        </div>
                    </div>
                </div>

                <!-- Guide 3: Gérer ses signalements -->
                <div class="guide-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="step-number w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Gérer ses signalements</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="text-sm text-gray-700 mb-3">
                            <strong>Accès :</strong> Menu → Mon Profil → Mes signalements
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center text-xs">
                                <div class="w-3 h-3 bg-yellow-400 rounded-full mr-2"></div>
                                <span class="text-gray-700">En attente - Nouveau signalement</span>
                            </div>
                            <div class="flex items-center text-xs">
                                <div class="w-3 h-3 bg-blue-400 rounded-full mr-2"></div>
                                <span class="text-gray-700">En cours - Traitement en cours</span>
                            </div>
                            <div class="flex items-center text-xs">
                                <div class="w-3 h-3 bg-green-400 rounded-full mr-2"></div>
                                <span class="text-gray-700">Résolu - Traité avec succès</span>
                            </div>
                            <div class="flex items-center text-xs">
                                <div class="w-3 h-3 bg-red-400 rounded-full mr-2"></div>
                                <span class="text-gray-700">Rejeté - Non conforme</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guide 4: Profil et sécurité -->
                <div class="guide-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="step-number w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Profil & Sécurité</h2>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-user text-blue-600"></i>
                            <span class="text-gray-700">Modifier nom et email</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-lock text-green-600"></i>
                            <span class="text-gray-700">Changer mot de passe</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-eye-slash text-purple-600"></i>
                            <span class="text-gray-700">Signalement anonyme disponible</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-orange-600"></i>
                            <span class="text-gray-700">Email de contact optionnel</span>
                        </div>
                    </div>
                </div>

                <!-- Guide 5: Types de fichiers -->
                <div class="guide-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="step-number w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Fichiers acceptés</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div class="bg-green-50 p-2 rounded flex items-center">
                                <i class="fas fa-camera text-green-600 mr-2"></i>
                                <div>
                                    <strong>Photos</strong><br>
                                    JPG, PNG, GIF, WebP
                                </div>
                            </div>
                            <div class="bg-purple-50 p-2 rounded flex items-center">
                                <i class="fas fa-video text-purple-600 mr-2"></i>
                                <div>
                                    <strong>Vidéos</strong><br>
                                    MP4, AVI, MOV, WMV
                                </div>
                            </div>
                            <div class="bg-red-50 p-2 rounded flex items-center">
                                <i class="fas fa-file-pdf text-red-600 mr-2"></i>
                                <div>
                                    <strong>Documents</strong><br>
                                    PDF, DOC, DOCX
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-600 mt-2">
                            Taille max : 10MB par fichier
                        </div>
                    </div>
                </div>

                <!-- Guide 6: Conseils rapides -->
                <div class="guide-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="step-number w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Conseils utiles</h2>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-600 mt-1"></i>
                            <span class="text-gray-700">Soyez précis dans vos descriptions</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-600 mt-1"></i>
                            <span class="text-gray-700">Ajoutez des preuves visuelles</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-600 mt-1"></i>
                            <span class="text-gray-700">Utilisez la priorité appropriée</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-600 mt-1"></i>
                            <span class="text-gray-700">Vérifiez avant de publier</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-600 mt-1"></i>
                            <span class="text-gray-700">Suivez l'évolution de vos signalements</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section d'aide rapide -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">
                        <i class="fas fa-question-circle mr-2"></i>
                        Besoin d'aide supplémentaire ?
                    </h3>
                    <p class="text-blue-700 text-sm mb-4">
                        Consultez notre FAQ ou contactez le support pour plus d'informations.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="faq.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                            <i class="fas fa-question mr-2"></i>
                            Voir la FAQ
                        </a>
                        <a href="mailto:support@signale-france.fr" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition-colors">
                            <i class="fas fa-envelope mr-2"></i>
                            Contacter le support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../Inc/Components/footer.php'; ?>
</body>
</html>