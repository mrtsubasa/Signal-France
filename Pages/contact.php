<?php
session_start();
require_once '../Inc/Constants/db.php';

// Traitement du formulaire de contact
$message_sent = false;
$error_message = '';

$pdo = connect_db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type_demande = $_POST['type_demande'] ?? '';
    $anonyme = isset($_POST['anonyme']) ? 1 : 0;
    
    // Validation
    if (empty($nom) || empty($sujet) || empty($message) || empty($type_demande)) {
        $error_message = 'Le nom, le sujet, le message et le type de demande sont obligatoires.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Adresse email invalide.';
    } elseif (strlen($message) < 10) {
        $error_message = 'Le message doit contenir au moins 10 caractères.';
    } else {
        try {
            // Si anonyme, on masque certaines informations
            $nom_save = $anonyme ? 'Utilisateur anonyme' : $nom;
            $email_save = $anonyme ? null : $email;
            
            $sql = "INSERT INTO messages_contact (nom, email, type_demande, sujet, message, anonyme, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nom_save,
                $email_save,
                $type_demande,
                $sujet,
                $message,
                $anonyme,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            $message_sent = true;
            
            // Réinitialiser les variables pour vider le formulaire
            $_POST = [];
            
        } catch (PDOException $e) {
            $error_message = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Signale France</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Animations et styles personnalisés */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 30px rgba(59, 130, 246, 0.6); }
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .hero-gradient {
            /* Option 1: Gradient blanc pur avec nuances subtiles */
            background: linear-gradient(-45deg, #ffffff, #f8fafc, #f1f5f9, #e2e8f0);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        
        /* Alternative 1: Gradient blanc avec touches de gris très subtiles */
        .hero-gradient-alt1 {
            background: linear-gradient(-45deg, #ffffff, #fefefe, #fdfdfd, #f9f9f9);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        
        /* Alternative 2: Gradient blanc avec nuances bleutées très légères */
        .hero-gradient-alt2 {
            background: linear-gradient(-45deg, #ffffff, #fbfcff, #f6f8ff, #f0f4ff);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        
        /* Alternative 3: Gradient blanc avec effet nacré */
        .hero-gradient-alt3 {
            background: linear-gradient(-45deg, #ffffff, #fefefe, #fcfcfc, #f7f7f7);
            background-size: 400% 400%;
            animation: gradient-shift 20s ease infinite;
            position: relative;
        }
        
        .hero-gradient-alt3::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, 
                rgba(255,255,255,0.8) 0%, 
                rgba(248,250,252,0.6) 25%, 
                rgba(241,245,249,0.4) 50%, 
                rgba(226,232,240,0.6) 75%, 
                rgba(255,255,255,0.8) 100%);
            background-size: 200% 200%;
            animation: shimmer 8s ease-in-out infinite;
            pointer-events: none;
        }
        
        @keyframes shimmer {
            0%, 100% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
        }
        
        /* Alternative 4: Gradient blanc minimaliste premium */
        .hero-gradient-alt4 {
            background: linear-gradient(135deg, 
                #ffffff 0%, 
                #fafbfc 25%, 
                #f5f7fa 50%, 
                #f0f3f7 75%, 
                #ffffff 100%);
            background-size: 300% 300%;
            animation: gradient-shift 25s ease infinite;
        }
        
        .contact-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
        }
        
        .contact-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        .form-input {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .notification {
            animation: slideInRight 0.5s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .typing-indicator {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .typing-indicator.show {
            opacity: 1;
        }
        
        .social-icon {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .social-icon:hover {
            transform: translateY(-3px) rotate(5deg);
        }
        
        .faq-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .faq-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .progress-bar {
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            transition: width 0.3s ease;
        }
        
        .character-counter {
            font-size: 0.75rem;
            transition: color 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../Inc/Components/header.php'; ?>
    <?php include '../Inc/Components/nav.php'; ?>

    <!-- Hero Section avec gradient animé -->
    <div class="hero-gradient min-h-screen relative overflow-hidden">
        <!-- Particules flottantes -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl floating-icon"></div>
            <div class="absolute top-3/4 right-1/4 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl floating-icon" style="animation-delay: -1s;"></div>
            <div class="absolute top-1/2 left-3/4 w-32 h-32 bg-white opacity-10 rounded-full blur-3xl floating-icon" style="animation-delay: -2s;"></div>
        </div>
        
        <div class="relative z-10 container mx-auto px-4 py-20">
            <!-- En-tête amélioré -->
            <div class="text-center mb-16">
                <div class="glass-effect rounded-2xl p-10 mx-auto max-w-4xl">
                    <div class="inline-block mb-6">
                        <div class="w-20 h-20 bg-gray-800 bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4 floating-icon">
                            <i class="fas fa-envelope text-gray-800 text-3xl"></i>
                        </div>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-bold mb-6 tracking-tight text-gray-800">
                        Contactez-nous
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-700 max-w-3xl mx-auto leading-relaxed">
                        Nous sommes là pour vous accompagner. Votre voix compte, votre sécurité nous importe.
                    </p>
                    <div class="mt-8">
                        <div class="inline-flex items-center bg-gray-800 bg-opacity-10 rounded-full px-6 py-3 text-gray-800">
                            <i class="fas fa-shield-alt mr-2"></i>
                            <span class="text-sm font-medium">Communication sécurisée et confidentielle</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cartes d'information améliorées -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Carte Support avec effet glass -->
                        <div class="contact-card glass-effect rounded-2xl p-8 group">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-headset text-white text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">Support Technique</h3>
                            </div>
                            <p class="text-gray-600 mb-6 leading-relaxed">Notre équipe d'experts est disponible pour résoudre tous vos problèmes techniques.</p>
                            <div class="space-y-3">
                                <div class="flex items-center text-gray-700 group-hover:text-blue-600 transition-colors">
                                    <i class="fas fa-envelope mr-3 text-blue-500"></i>
                                    <span class="font-medium">support@signale-france.fr</span>
                                </div>
                                <div class="flex items-center text-gray-700 group-hover:text-blue-600 transition-colors">
                                    <i class="fas fa-clock mr-3 text-blue-500"></i>
                                    <span>Lun-Ven: 9h-18h</span>
                                </div>
                                <div class="flex items-center text-gray-700 group-hover:text-blue-600 transition-colors">
                                    <i class="fas fa-bolt mr-3 text-blue-500"></i>
                                    <span>Réponse sous 2h en moyenne</span>
                                </div>
                            </div>
                        </div>

                        <!-- Carte Urgence améliorée -->
                        <div class="contact-card bg-gradient-to-br from-red-500 via-red-600 to-red-700 rounded-2xl p-8 text-white group relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-10 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-all duration-1000"></div>
                            <div class="relative z-10">
                                <div class="flex items-center mb-6">
                                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center mr-4">
                                        <i class="fas fa-exclamation-triangle text-white text-2xl pulse-animation"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold">Urgence</h3>
                                </div>
                                <p class="mb-6 opacity-95 leading-relaxed">Pour les situations critiques nécessitant une intervention immédiate des services d'urgence.</p>
                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-phone mr-3"></i>
                                        <span class="font-medium">📞 15 (SAMU) - 17 (Police) - 18 (Pompiers)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-mobile-alt mr-3"></i>
                                        <span class="font-medium">📱 112 (Numéro d'urgence européen)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Carte Anonymat améliorée -->
                        <div class="contact-card bg-gradient-to-br from-purple-500 via-purple-600 to-indigo-600 rounded-2xl p-8 text-white group">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center mr-4 group-hover:rotate-12 transition-transform duration-300">
                                    <i class="fas fa-user-secret text-white text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold">Contact Anonyme</h3>
                            </div>
                            <p class="mb-6 opacity-95 leading-relaxed">Votre confidentialité est notre priorité. Contactez-nous en toute sécurité.</p>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <i class="fas fa-check mr-3 text-green-300"></i>
                                    <span>Aucune donnée personnelle conservée</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-check mr-3 text-green-300"></i>
                                    <span>Chiffrement de bout en bout</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-check mr-3 text-green-300"></i>
                                    <span>Confidentialité garantie</span>
                                </div>
                            </div>
                        </div>

                        <!-- Réseaux sociaux améliorés -->
                        <div class="contact-card glass-effect rounded-2xl p-8">
                            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-share-alt mr-3 text-purple-600"></i>
                                Suivez-nous
                            </h3>
                            <div class="flex space-x-4">
                                <a href="#" class="social-icon w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center text-white hover:shadow-lg">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-icon w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center text-white hover:shadow-lg">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="social-icon w-12 h-12 bg-gradient-to-br from-blue-800 to-blue-900 rounded-xl flex items-center justify-center text-white hover:shadow-lg">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="#" class="social-icon w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center text-white hover:shadow-lg">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de contact amélioré -->
                    <div class="lg:col-span-2">
                        <div class="glass-effect rounded-2xl p-10">
                            <div class="mb-10">
                                <h2 class="text-3xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-paper-plane text-blue-600 mr-4"></i>
                                    Envoyez-nous un message
                                </h2>
                                <p class="text-gray-600 text-lg leading-relaxed">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais. Votre message est important pour nous.</p>
                            </div>

                            <!-- Messages de notification améliorés -->
                            <?php if ($message_sent): ?>
                                <div class="notification bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 mb-8">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-green-800 font-bold text-lg">Message envoyé avec succès !</h4>
                                            <p class="text-green-700">Nous avons bien reçu votre message et vous répondrons dans les plus brefs délais.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div class="notification bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-6 mb-8">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-red-800 font-bold text-lg">Erreur</h4>
                                            <p class="text-red-700"><?php echo htmlspecialchars($error_message); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-8" id="contactForm">
                                <!-- Option anonyme améliorée -->
                                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-xl p-6">
                                    <div class="flex items-center">
                                        <div class="relative">
                                            <input type="checkbox" name="anonyme" id="anonyme" class="sr-only" <?php echo (isset($_POST['anonyme']) && $_POST['anonyme']) ? 'checked' : ''; ?>>
                                            <div class="toggle-bg w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-colors duration-300 cursor-pointer"></div>
                                            <div class="toggle-dot absolute w-5 h-5 bg-white rounded-full shadow top-0.5 left-0.5 transition-transform duration-300"></div>
                                        </div>
                                        <label for="anonyme" class="ml-4 text-purple-800 font-semibold cursor-pointer flex items-center">
                                            <i class="fas fa-user-secret mr-2"></i>
                                            Envoyer ce message de manière anonyme
                                        </label>
                                    </div>
                                    <div class="anonyme-notice mt-4 text-sm text-purple-700 bg-white bg-opacity-50 rounded-lg p-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        En mode anonyme, votre identité sera protégée et vos données personnelles ne seront pas conservées.
                                    </div>
                                </div>

                                <!-- Type de demande -->
                                <div class="space-y-2">
                                    <label for="type_demande" class="block text-sm font-bold text-gray-700 mb-3">
                                        <i class="fas fa-tag mr-2 text-blue-600"></i>
                                        Type de demande *
                                    </label>
                                    <select name="type_demande" id="type_demande" required class="form-input w-full px-6 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500 focus:border-blue-500 text-lg">
                                        <option value="">Sélectionnez le type de demande</option>
                                        <option value="support_technique" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'support_technique') ? 'selected' : ''; ?>>🔧 Support technique</option>
                                        <option value="question_generale" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'question_generale') ? 'selected' : ''; ?>>❓ Question générale</option>
                                        <option value="suggestion" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'suggestion') ? 'selected' : ''; ?>>💡 Suggestion d'amélioration</option>
                                        <option value="signalement_probleme" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'signalement_probleme') ? 'selected' : ''; ?>>⚠️ Signaler un problème</option>
                                        <option value="partenariat" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'partenariat') ? 'selected' : ''; ?>>🤝 Partenariat</option>
                                        <option value="autre" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'autre') ? 'selected' : ''; ?>>📝 Autre</option>
                                    </select>
                                </div>

                                <!-- Nom et Email -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label for="nom" class="block text-sm font-bold text-gray-700 mb-3">
                                            <i class="fas fa-user mr-2 text-blue-600"></i>
                                            Nom complet *
                                        </label>
                                        <input type="text" name="nom" id="nom" required 
                                               value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>"
                                               class="form-input w-full px-6 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                               placeholder="Votre nom complet">
                                        <p class="text-sm text-gray-500 mt-2 nom-notice">Ce nom sera masqué si vous choisissez l'option anonyme</p>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="email" class="block text-sm font-bold text-gray-700 mb-3">
                                            <i class="fas fa-envelope mr-2 text-blue-600"></i>
                                            Adresse email <span class="text-gray-500 font-normal">(facultatif)</span>
                                        </label>
                                        <input type="email" name="email" id="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                               class="form-input w-full px-6 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                               placeholder="votre@email.com">
                                        <p class="text-sm text-gray-500 mt-2 email-notice">Nécessaire uniquement si vous souhaitez une réponse</p>
                                    </div>
                                </div>

                                <!-- Sujet -->
                                <div class="space-y-2">
                                    <label for="sujet" class="block text-sm font-bold text-gray-700 mb-3">
                                        <i class="fas fa-heading mr-2 text-blue-600"></i>
                                        Sujet *
                                    </label>
                                    <input type="text" name="sujet" id="sujet" required 
                                           value="<?php echo htmlspecialchars($_POST['sujet'] ?? ''); ?>"
                                           class="form-input w-full px-6 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500 focus:border-blue-500 text-lg" 
                                           placeholder="Résumé de votre demande">
                                </div>

                                <!-- Message -->
                                <div class="space-y-2">
                                    <label for="message" class="block text-sm font-bold text-gray-700 mb-3">
                                        <i class="fas fa-comment-alt mr-2 text-blue-600"></i>
                                        Message *
                                    </label>
                                    <div class="relative">
                                        <textarea name="message" id="message" rows="8" required 
                                                  class="form-input w-full px-6 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500 focus:border-blue-500 resize-none text-lg" 
                                                  placeholder="Décrivez votre demande en détail..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                        <div class="absolute bottom-4 right-4">
                                            <div class="typing-indicator bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-medium">
                                                <i class="fas fa-keyboard mr-1"></i>
                                                <span id="typingText">En cours de frappe...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center mt-3">
                                        <p class="text-sm text-gray-500 character-counter" id="charCounter">Minimum 10 caractères</p>
                                        <div class="w-32 bg-gray-200 rounded-full h-2">
                                            <div class="progress-bar h-2 rounded-full" id="progressBar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bouton d'envoi amélioré -->
                                <div class="flex items-center justify-between pt-6">
                                    <p class="text-sm text-gray-600 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                        Les champs marqués d'un * sont obligatoires
                                    </p>
                                    <button type="submit" class="btn-primary text-white px-10 py-4 rounded-xl font-bold text-lg transition-all duration-300 transform hover:scale-105 focus:ring-4 focus:ring-blue-300">
                                        <i class="fas fa-paper-plane mr-3"></i>
                                        Envoyer le message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Section FAQ améliorée -->
                <div class="mt-20">
                    <div class="glass-effect rounded-2xl p-10">
                        <div class="text-center mb-12">
                            <h2 class="text-4xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-question-circle text-blue-600 mr-4"></i>
                                Questions fréquentes
                            </h2>
                            <p class="text-xl text-gray-600 leading-relaxed">Trouvez rapidement des réponses aux questions les plus courantes</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div class="faq-card bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-8 border border-blue-200">
                                <div class="w-16 h-16 bg-blue-500 rounded-xl flex items-center justify-center mb-6 mx-auto">
                                    <i class="fas fa-plus-circle text-white text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-blue-800 mb-4 text-xl text-center">
                                    Comment créer un signalement ?
                                </h3>
                                <p class="text-blue-700 mb-6 leading-relaxed text-center">Cliquez sur "Créer un signalement" et remplissez le formulaire avec tous les détails nécessaires.</p>
                                <div class="text-center">
                                    <a href="guides.php" class="inline-flex items-center text-blue-600 font-bold hover:text-blue-800 transition-colors">
                                        Voir le guide complet
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="faq-card bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-8 border border-green-200">
                                <div class="w-16 h-16 bg-green-500 rounded-xl flex items-center justify-center mb-6 mx-auto">
                                    <i class="fas fa-clock text-white text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-green-800 mb-4 text-xl text-center">
                                    Délai de traitement ?
                                </h3>
                                <p class="text-green-700 mb-6 leading-relaxed text-center">Les signalements sont généralement traités sous 24-48h selon leur priorité et leur complexité.</p>
                                <div class="text-center">
                                    <a href="faq.php" class="inline-flex items-center text-green-600 font-bold hover:text-green-800 transition-colors">
                                        En savoir plus
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="faq-card bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-8 border border-purple-200">
                                <div class="w-16 h-16 bg-purple-500 rounded-xl flex items-center justify-center mb-6 mx-auto">
                                    <i class="fas fa-eye-slash text-white text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-purple-800 mb-4 text-xl text-center">
                                    Contact anonyme ?
                                </h3>
                                <p class="text-purple-700 mb-6 leading-relaxed text-center">Oui, vous pouvez nous contacter de manière totalement anonyme en cochant l'option correspondante.</p>
                                <div class="text-center">
                                    <a href="faq.php" class="inline-flex items-center text-purple-600 font-bold hover:text-purple-800 transition-colors">
                                        Plus d'infos
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-12">
                            <a href="faq.php" class="inline-flex items-center bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 px-8 py-4 rounded-xl font-bold text-lg hover:from-gray-200 hover:to-gray-300 transition-all duration-300 transform hover:scale-105">
                                <i class="fas fa-list mr-3"></i>
                                Voir toutes les FAQ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../Inc/Components/footer.php'; ?>

    <script>
        // Gestion du toggle anonyme amélioré
        const anonymeCheckbox = document.getElementById('anonyme');
        const toggleBg = document.querySelector('.toggle-bg');
        const toggleDot = document.querySelector('.toggle-dot');
        const anonymeNotice = document.querySelector('.anonyme-notice');
        const nomNotice = document.querySelector('.nom-notice');
        const emailNotice = document.querySelector('.email-notice');
        const emailInput = document.getElementById('email');
        
        function updateToggleVisual() {
            if (anonymeCheckbox.checked) {
                toggleBg.classList.add('bg-purple-500');
                toggleBg.classList.remove('bg-gray-300');
                toggleDot.style.transform = 'translateX(24px)';
            } else {
                toggleBg.classList.remove('bg-purple-500');
                toggleBg.classList.add('bg-gray-300');
                toggleDot.style.transform = 'translateX(0)';
            }
        }
        
        function toggleAnonymeMode() {
            updateToggleVisual();
            
            if (anonymeCheckbox.checked) {
                anonymeNotice.classList.add('show');
                nomNotice.textContent = 'Ce nom sera remplacé par "Utilisateur anonyme"';
                nomNotice.className = 'text-sm text-purple-600 mt-2 nom-notice font-medium';
                emailNotice.textContent = 'Cet email ne sera pas conservé en mode anonyme';
                emailNotice.className = 'text-sm text-purple-600 mt-2 email-notice font-medium';
                emailInput.placeholder = 'Ne sera pas conservé (mode anonyme)';
            } else {
                anonymeNotice.classList.remove('show');
                nomNotice.textContent = 'Ce nom sera visible par les administrateurs';
                nomNotice.className = 'text-sm text-gray-500 mt-2 nom-notice';
                emailNotice.textContent = 'Nécessaire uniquement si vous souhaitez une réponse';
                emailNotice.className = 'text-sm text-gray-500 mt-2 email-notice';
                emailInput.placeholder = 'votre@email.com';
            }
        }
        
        // Event listeners pour le toggle
        toggleBg.addEventListener('click', () => {
            anonymeCheckbox.checked = !anonymeCheckbox.checked;
            toggleAnonymeMode();
        });
        
        anonymeCheckbox.addEventListener('change', toggleAnonymeMode);
        
        // Initialiser l'état au chargement
        updateToggleVisual();
        toggleAnonymeMode();

        // Animation au scroll améliorée
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0) scale(1)';
                    }, index * 100);
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec animation
        document.querySelectorAll('.contact-card, .faq-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px) scale(0.95)';
            card.style.transition = `opacity 0.8s ease ${index * 0.1}s, transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) ${index * 0.1}s`;
            observer.observe(card);
        });

        // Validation en temps réel améliorée
        const messageTextarea = document.getElementById('message');
        const charCounter = document.getElementById('charCounter');
        const progressBar = document.getElementById('progressBar');
        const typingIndicator = document.querySelector('.typing-indicator');
        const typingText = document.getElementById('typingText');
        
        let typingTimer;
        
        messageTextarea.addEventListener('input', function() {
            const minLength = 10;
            const maxLength = 1000;
            const currentLength = this.value.length;
            
            // Afficher l'indicateur de frappe
            typingIndicator.classList.add('show');
            clearTimeout(typingTimer);
            
            // Masquer l'indicateur après 1 seconde d'inactivité
            typingTimer = setTimeout(() => {
                typingIndicator.classList.remove('show');
            }, 1000);
            
            // Mettre à jour le compteur
            if (currentLength < minLength) {
                charCounter.textContent = `Minimum 10 caractères (${currentLength}/${minLength})`;
                charCounter.className = 'text-sm text-red-500 character-counter font-medium';
                progressBar.style.width = `${(currentLength / minLength) * 100}%`;
                progressBar.style.background = 'linear-gradient(90deg, #ef4444, #f87171)';
            } else if (currentLength <= maxLength) {
                charCounter.textContent = `${currentLength} caractères`;
                charCounter.className = 'text-sm text-green-500 character-counter font-medium';
                progressBar.style.width = '100%';
                progressBar.style.background = 'linear-gradient(90deg, #10b981, #34d399)';
            } else {
                charCounter.textContent = `Trop long (${currentLength}/${maxLength})`;
                charCounter.className = 'text-sm text-orange-500 character-counter font-medium';
                progressBar.style.width = '100%';
                progressBar.style.background = 'linear-gradient(90deg, #f59e0b, #fbbf24)';
            }
        });
        
        // Messages de frappe aléatoires
        const typingMessages = [
            'En cours de frappe...',
            'Réflexion en cours...',
            'Formulation du message...',
            'Rédaction en cours...'
        ];
        
        messageTextarea.addEventListener('keydown', function() {
            const randomMessage = typingMessages[Math.floor(Math.random() * typingMessages.length)];
            typingText.textContent = randomMessage;
        });
        
        // Animation des cartes FAQ au clic
        document.querySelectorAll('.faq-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-5px) scale(1)';
                }, 150);
            });
        });
        
        // Validation du formulaire avant soumission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Animation du bouton de soumission
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-3"></i>Envoi en cours...';
            submitBtn.disabled = true;
            
            // Simuler un délai (sera remplacé par la vraie soumission)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
        
        // Effet de parallaxe léger sur les particules
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelectorAll('.floating-icon');
            
            parallax.forEach((element, index) => {
                const speed = 0.5 + (index * 0.1);
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

// Fonction pour fermer les messages
function closeMessage(messageId) {
    const message = document.getElementById(messageId);
    if (message) {
        message.classList.add('closing');
        setTimeout(() => {
            message.style.display = 'none';
        }, 500);
    }
}

// Fonction de confirmation avant envoi
function confirmSendMessage(event) {
    event.preventDefault(); // Empêcher l'envoi immédiat
    
    // Récupérer les données du formulaire
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    const nom = formData.get('nom');
    const sujet = formData.get('sujet');
    const typedemande = formData.get('type_demande');
    const anonyme = formData.get('anonyme') ? 'en mode anonyme' : '';
    
    // Créer le modal de confirmation
    const confirmModal = document.createElement('div');
    confirmModal.id = 'confirmModal';
    confirmModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm';
    confirmModal.style.animation = 'fadeIn 0.3s ease-out';
    
    confirmModal.innerHTML = `
        <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl transform" style="animation: slideInScale 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-paper-plane text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Confirmer l'envoi</h3>
                <p class="text-gray-600">Êtes-vous sûr de vouloir envoyer ce message ?</p>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    <span class="font-semibold">Nom:</span>
                    <span class="ml-2 text-gray-700">${nom} ${anonyme}</span>
                </div>
                <div class="flex items-center mb-2">
                    <i class="fas fa-tag text-blue-600 mr-2"></i>
                    <span class="font-semibold">Type:</span>
                    <span class="ml-2 text-gray-700">${getTypeLabel(typedemande)}</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-envelope text-blue-600 mr-2"></i>
                    <span class="font-semibold">Sujet:</span>
                    <span class="ml-2 text-gray-700">${sujet}</span>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-600 mr-2"></i>
                    <span class="text-sm text-yellow-800">
                        <strong>Rappel:</strong> Vous ne pourrez envoyer un nouveau message que dans 30 minutes après celui-ci.
                    </span>
                </div>
            </div>
            
            <div class="flex space-x-4">
                <button onclick="cancelSend()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </button>
                <button onclick="proceedSend()" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Envoyer
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(confirmModal);
    
    // Empêcher le scroll du body
    document.body.style.overflow = 'hidden';
}

// Fonction pour obtenir le label du type de demande
function getTypeLabel(type) {
    const types = {
        'support_technique': '🔧 Support technique',
        'question_generale': '❓ Question générale',
        'suggestion': '💡 Suggestion d\'amélioration',
        'signalement_probleme': '⚠️ Signaler un problème',
        'partenariat': '🤝 Partenariat',
        'autre': '📝 Autre'
    };
    return types[type] || type;
}

// Fonction pour annuler l'envoi
function cancelSend() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

// Fonction pour procéder à l'envoi
function proceedSend() {
    const modal = document.getElementById('confirmModal');
    const form = document.getElementById('contactForm');
    
    // Afficher un loader dans le modal
    modal.querySelector('.bg-white').innerHTML = `
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Envoi en cours...</h3>
            <p class="text-gray-600">Veuillez patienter</p>
        </div>
    `;
    
    // Envoyer le formulaire après un court délai
    setTimeout(() => {
        form.submit();
    }, 1000);
}

// Auto-fermeture avec délais plus longs
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter l'événement de confirmation au formulaire
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', confirmSendMessage);
    }
    
    // Message de succès : 15 secondes
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        setTimeout(() => {
            closeMessage('successMessage');
        }, 15000);
    }
    
    // Message d'erreur : 12 secondes
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        setTimeout(() => {
            closeMessage('errorMessage');
        }, 12000);
    }
    
    // Fermeture avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMessage('successMessage');
            closeMessage('errorMessage');
            cancelSend(); // Fermer aussi le modal de confirmation
        }
    });
});
    </script>
    
    <?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include('../Inc/Traitement/create_log.php'); ?>
