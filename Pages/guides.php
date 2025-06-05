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
        /* Animations et transitions avancées */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .guide-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .guide-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .step-number {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            transition: all 0.3s ease;
        }
        
        .step-number:hover {
            animation: pulse 1s infinite;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #000091 0%, #6a6af4 50%, #f5f5fe 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .floating-element {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .step-badge {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            transition: all 0.3s ease;
        }
        
        .step-badge:hover {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            transform: scale(1.1);
        }
        
        .category-icon {
            transition: all 0.3s ease;
        }
        
        .category-icon:hover {
            transform: rotate(10deg) scale(1.1);
        }
        
        .help-section {
            background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
            position: relative;
        }
        
        .help-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(135deg, transparent 0%, rgba(224, 242, 254, 0.8) 50%, transparent 100%);
            transform: skewY(-2deg);
        }
        
        .progress-bar {
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: width 0.3s ease;
        }
        
        .guide-card:nth-child(1) { animation-delay: 0.1s; }
        .guide-card:nth-child(2) { animation-delay: 0.2s; }
        .guide-card:nth-child(3) { animation-delay: 0.3s; }
        .guide-card:nth-child(4) { animation-delay: 0.4s; }
        .guide-card:nth-child(5) { animation-delay: 0.5s; }
        .guide-card:nth-child(6) { animation-delay: 0.6s; }
        
        .tooltip {
            position: relative;
        }
        
        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            animation: fadeInUp 0.3s ease;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    
    <!-- Barre de progression -->   
    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
    
    <?php include '../Inc/Components/header.php'; ?>
    <?php include '../Inc/Components/nav.php'; ?>


    

    <!-- Section Hero améliorée -->
    <div class="hero-section py-16 relative">
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <div class="floating-element mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-4">
                        <i class="fas fa-book-open text-4xl text-white"></i>
                    </div>
                </div>
                <h1 class="text-5xl font-bold text-white mb-4 leading-tight">
                    Guides d'utilisation
                    <span class="block text-2xl font-normal text-blue-100 mt-2">
                        Maîtrisez Signale France en quelques étapes
                    </span>
                </h1>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Découvrez comment utiliser efficacement notre plateforme pour signaler et suivre les incidents
                </p>
                <div class="flex justify-center space-x-4">
                    <button onclick="scrollToGuides()" class="glass-effect text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-white hover:bg-opacity-30 transition-all duration-300">
                        <i class="fas fa-arrow-down mr-2"></i>
                        Commencer
                    </button>
                    <a href="faq.php" class="bg-white text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-blue-50 transition-all duration-300">
                        <i class="fas fa-question-circle mr-2"></i>
                        FAQ
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Éléments décoratifs flottants -->
        <div class="absolute top-20 left-10 w-16 h-16 bg-white bg-opacity-10 rounded-full floating-element" style="animation-delay: 1s;"></div>
        <div class="absolute top-40 right-20 w-12 h-12 bg-white bg-opacity-10 rounded-full floating-element" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-8 h-8 bg-white bg-opacity-10 rounded-full floating-element" style="animation-delay: 3s;"></div>
    </div>

    <div class="container mx-auto px-4 py-12" id="guides-section">
        <div class="max-w-7xl mx-auto">
            <!-- Statistiques rapides -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-plus text-3xl mb-3"></i>
                    <h3 class="text-lg font-semibold">Créer</h3>
                    <p class="text-blue-100 text-sm">Nouveau signalement</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-search text-3xl mb-3"></i>
                    <h3 class="text-lg font-semibold">Rechercher</h3>
                    <p class="text-green-100 text-sm">Trouver des incidents</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-cogs text-3xl mb-3"></i>
                    <h3 class="text-lg font-semibold">Gérer</h3>
                    <p class="text-purple-100 text-sm">Vos signalements</p>
                </div>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-shield-alt text-3xl mb-3"></i>
                    <h3 class="text-lg font-semibold">Sécuriser</h3>
                    <p class="text-orange-100 text-sm">Votre profil</p>
                </div>
            </div>

            <!-- Grille des guides améliorée -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Guide 1: Créer un signalement -->
                <div class="guide-card rounded-2xl shadow-xl p-8 hover:shadow-2xl group">
                    <div class="flex items-center mb-6">
                        <div class="step-number w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors">Créer un signalement</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3 group/step hover:bg-blue-50 p-3 rounded-lg transition-colors">
                            <span class="step-badge text-white px-3 py-1 rounded-full text-sm font-bold">1</span>
                            <span class="text-gray-700 group-hover/step:text-blue-700">Cliquez sur "Créer un signalement"</span>
                        </div>
                        <div class="flex items-start space-x-3 group/step hover:bg-blue-50 p-3 rounded-lg transition-colors">
                            <span class="step-badge text-white px-3 py-1 rounded-full text-sm font-bold">2</span>
                            <span class="text-gray-700 group-hover/step:text-blue-700">Remplissez le titre, type et priorité</span>
                        </div>
                        <div class="flex items-start space-x-3 group/step hover:bg-blue-50 p-3 rounded-lg transition-colors">
                            <span class="step-badge text-white px-3 py-1 rounded-full text-sm font-bold">3</span>
                            <span class="text-gray-700 group-hover/step:text-blue-700">Choisissez le type d'incident</span>
                        </div>
                        <div class="flex items-start space-x-3 group/step hover:bg-blue-50 p-3 rounded-lg transition-colors">
                            <span class="step-badge text-white px-3 py-1 rounded-full text-sm font-bold">4</span>
                            <span class="text-gray-700 group-hover/step:text-blue-700">Ajoutez une description détaillée</span>
                        </div>
                        <div class="flex items-start space-x-3 group/step hover:bg-blue-50 p-3 rounded-lg transition-colors">
                            <span class="step-badge text-white px-3 py-1 rounded-full text-sm font-bold">5</span>
                            <span class="text-gray-700 group-hover/step:text-blue-700">Joignez des preuves</span>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border-l-4 border-yellow-400">
                        <p class="text-sm text-yellow-800 flex items-center">
                            <i class="fas fa-lightbulb mr-2 text-yellow-600"></i>
                            Plus votre signalement est détaillé, plus il sera traité rapidement.
                        </p>
                    </div>
                </div>

                <!-- Guide 2: Rechercher -->
                <div class="guide-card rounded-2xl shadow-xl p-8 hover:shadow-2xl group">
                    <div class="flex items-center mb-6">
                        <div class="step-number w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-search text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-green-600 transition-colors">Rechercher</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="text-gray-700 font-semibold mb-4">
                            Filtres disponibles :
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl border border-blue-200 hover:shadow-md transition-all group/filter">
                                <i class="fas fa-tag text-blue-600 mr-2 category-icon"></i>
                                <span class="text-blue-800 font-medium">Type</span>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl border border-green-200 hover:shadow-md transition-all group/filter">
                                <i class="fas fa-exclamation-triangle text-green-600 mr-2 category-icon"></i>
                                <span class="text-green-800 font-medium">Incident</span>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-xl border border-yellow-200 hover:shadow-md transition-all group/filter">
                                <i class="fas fa-traffic-light text-yellow-600 mr-2 category-icon"></i>
                                <span class="text-yellow-800 font-medium">Statut</span>
                            </div>
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl border border-purple-200 hover:shadow-md transition-all group/filter">
                                <i class="fas fa-map-marker-alt text-purple-600 mr-2 category-icon"></i>
                                <span class="text-purple-800 font-medium">Lieu</span>
                            </div>
                        </div>
                        <div class="text-gray-600 bg-gray-50 p-3 rounded-lg">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Utilisez la recherche globale ou ciblée selon vos besoins.
                        </div>
                    </div>
                </div>

                <!-- Guide 3: Gérer ses signalements -->
                <div class="guide-card rounded-2xl shadow-xl p-8 hover:shadow-2xl group">
                    <div class="flex items-center mb-6">
                        <div class="step-number w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-purple-600 transition-colors">Gérer ses signalements</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-4 rounded-xl border border-purple-200">
                            <div class="text-gray-700 font-semibold mb-2 flex items-center">
                                <i class="fas fa-route text-purple-600 mr-2"></i>
                                Accès :
                            </div>
                            <p class="text-purple-800">Menu → Mon Profil → Mes signalements</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center p-3 bg-yellow-50 rounded-lg border border-yellow-200 hover:shadow-sm transition-all">
                                <div class="w-4 h-4 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full mr-3 shadow-sm"></div>
                                <span class="text-gray-700 font-medium">En attente - Nouveau signalement</span>
                            </div>
                            <div class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-200 hover:shadow-sm transition-all">
                                <div class="w-4 h-4 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full mr-3 shadow-sm"></div>
                                <span class="text-gray-700 font-medium">En cours - Traitement en cours</span>
                            </div>
                            <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200 hover:shadow-sm transition-all">
                                <div class="w-4 h-4 bg-gradient-to-r from-green-400 to-green-500 rounded-full mr-3 shadow-sm"></div>
                                <span class="text-gray-700 font-medium">Résolu - Traité avec succès</span>
                            </div>
                            <div class="flex items-center p-3 bg-red-50 rounded-lg border border-red-200 hover:shadow-sm transition-all">
                                <div class="w-4 h-4 bg-gradient-to-r from-red-400 to-red-500 rounded-full mr-3 shadow-sm"></div>
                                <span class="text-gray-700 font-medium">Rejeté - Non conforme</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guide 4: Profil et sécurité -->
                <div class="guide-card rounded-2xl shadow-xl p-8 hover:shadow-2xl group">
                    <div class="flex items-center mb-6">
                        <div class="step-number w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-shield text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">Profil & Sécurité</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors group/item">
                            <i class="fas fa-user text-blue-600 text-lg group-hover/item:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/item:text-blue-700">Modifier nom et email</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group/item">
                            <i class="fas fa-lock text-green-600 text-lg group-hover/item:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/item:text-green-700">Changer mot de passe</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors group/item">
                            <i class="fas fa-eye-slash text-purple-600 text-lg group-hover/item:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/item:text-purple-700">Signalement anonyme disponible</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors group/item">
                            <i class="fas fa-envelope text-orange-600 text-lg group-hover/item:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/item:text-orange-700">Email de contact optionnel</span>
                        </div>
                    </div>
                </div>

                <!-- Guide 5: Types de fichiers -->
                <div class="guide-card rounded-2xl shadow-xl p-8 hover:shadow-2xl group">
                    <div class="flex items-center mb-6">
                        <div class="step-number w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-file-upload text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">Fichiers acceptés</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-4 rounded-xl border border-green-200 hover:shadow-md transition-all group/file">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-camera text-green-600 mr-3 text-xl group-hover/file:scale-110 transition-transform"></i>
                                <span class="font-bold text-green-800">Photos</span>
                            </div>
                            <p class="text-green-700 text-sm">JPG, PNG, GIF, WebP</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-violet-50 p-4 rounded-xl border border-purple-200 hover:shadow-md transition-all group/file">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-video text-purple-600 mr-3 text-xl group-hover/file:scale-110 transition-transform"></i>
                                <span class="font-bold text-purple-800">Vidéos</span>
                            </div>
                            <p class="text-purple-700 text-sm">MP4, AVI, MOV, WMV</p>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-pink-50 p-4 rounded-xl border border-red-200 hover:shadow-md transition-all group/file">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-file-pdf text-red-600 mr-3 text-xl group-hover/file:scale-110 transition-transform"></i>
                                <span class="font-bold text-red-800">Documents</span>
                            </div>
                            <p class="text-red-700 text-sm">PDF, DOC, DOCX</p>
                        </div>
                        <div class="bg-gradient-to-r from-gray-50 to-slate-50 p-3 rounded-lg border border-gray-200">
                            <p class="text-gray-600 text-sm flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                Taille max : 10MB par fichier
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Guide 6: Conseils rapides -->
                <div class="guide-card rounded-2xl shadow-xl p-8 hover:shadow-2xl group">
                    <div class="flex items-center mb-6">
                        <div class="step-number w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold mr-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-lightbulb text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-amber-600 transition-colors">Conseils utiles</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group/tip">
                            <i class="fas fa-check text-green-600 mt-1 group-hover/tip:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/tip:text-green-700">Soyez précis dans vos descriptions</span>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group/tip">
                            <i class="fas fa-check text-green-600 mt-1 group-hover/tip:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/tip:text-green-700">Ajoutez des preuves visuelles</span>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group/tip">
                            <i class="fas fa-check text-green-600 mt-1 group-hover/tip:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/tip:text-green-700">Utilisez la priorité appropriée</span>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group/tip">
                            <i class="fas fa-check text-green-600 mt-1 group-hover/tip:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/tip:text-green-700">Vérifiez avant de publier</span>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group/tip">
                            <i class="fas fa-check text-green-600 mt-1 group-hover/tip:scale-110 transition-transform"></i>
                            <span class="text-gray-700 group-hover/tip:text-green-700">Suivez l'évolution de vos signalements</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section d'aide rapide améliorée -->
            <div class="help-section mt-16 rounded-3xl p-12 relative overflow-hidden">
                <div class="relative z-10 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-6">
                        <i class="fas fa-question-circle text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-blue-800 mb-4">
                        Besoin d'aide supplémentaire ?
                    </h3>
                    <p class="text-blue-700 text-lg mb-8 max-w-2xl mx-auto">
                        Notre équipe support est là pour vous accompagner. Consultez notre FAQ ou contactez-nous directement.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                        <a href="faq.php" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-semibold hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-question mr-3"></i>
                            Consulter la FAQ
                        </a>
                        <a href="contact.php" class="bg-white text-blue-600 px-8 py-4 rounded-2xl font-semibold hover:bg-blue-50 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl border border-blue-200">
                            <i class="fas fa-envelope mr-3"></i>
                            Contacter le support
                        </a>
                    </div>
                </div>
                
                <!-- Éléments décoratifs -->
                <div class="absolute top-10 right-10 w-20 h-20 bg-white bg-opacity-10 rounded-full floating-element"></div>
                <div class="absolute bottom-10 left-10 w-16 h-16 bg-white bg-opacity-10 rounded-full floating-element" style="animation-delay: 1s;"></div>
            </div>
        </div>
    </div>

    <!-- Bouton de retour en haut -->
    <button id="backToTop" class="fixed bottom-8 right-8 bg-blue-600 text-white w-12 h-12 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-110 opacity-0 invisible" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Animation de la barre de progression
        window.addEventListener('scroll', () => {
            const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            document.getElementById('progressBar').style.width = scrolled + '%';
            
            // Bouton retour en haut
            const backToTop = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTop.classList.remove('opacity-0', 'invisible');
                backToTop.classList.add('opacity-100', 'visible');
            } else {
                backToTop.classList.add('opacity-0', 'invisible');
                backToTop.classList.remove('opacity-100', 'visible');
            }
        });
        
        // Fonction de défilement vers les guides
        function scrollToGuides() {
            document.getElementById('guides-section').scrollIntoView({
                behavior: 'smooth'
            });
        }
        
        // Fonction de retour en haut
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        // Animation d'apparition au scroll
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
        
        // Observer les cartes de guide
        document.addEventListener('DOMContentLoaded', () => {
            const guideCards = document.querySelectorAll('.guide-card');
            guideCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                observer.observe(card);
            });
        });
    </script>
   <?php include '../Inc/Components/footer.php'; ?>
    <?php include '../Inc/Components/footers.php'; ?>

<?php include('../Inc/Traitement/create_log.php'); ?>