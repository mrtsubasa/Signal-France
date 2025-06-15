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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --glassmorphism: rgba(255, 255, 255, 0.85);
            --glassmorphism-strong: rgba(255, 255, 255, 0.95);
            --glassmorphism-border: rgba(255, 255, 255, 0.3);
            --shadow-glass: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            --text-primary: #1a202c;
            --text-secondary: #2d3748;
            --text-muted: #4a5568;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            color: var(--text-primary);
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Enhanced Glassmorphism avec texte lisible */
        .glass-morphism {
            background: var(--glassmorphism);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--glassmorphism-border);
            box-shadow: var(--shadow-glass);
            color: var(--text-primary);
        }

        .glass-morphism-strong {
            background: var(--glassmorphism-strong);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.5);
            color: var(--text-primary);
        }

        /* Cartes colorées avec texte blanc seulement */
        .glass-colored {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-glass);
            color: white;
        }

        /* Floating Elements */
        .floating-orbs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            opacity: 0.7;
            filter: blur(60px);
            animation: floatOrb 20s infinite linear;
        }

        .orb:nth-child(1) {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.4) 100%);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .orb:nth-child(2) {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(240, 147, 251, 0.8) 0%, rgba(245, 87, 108, 0.4) 100%);
            top: 60%;
            right: 15%;
            animation-delay: -5s;
        }

        .orb:nth-child(3) {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(67, 233, 123, 0.8) 0%, rgba(56, 249, 215, 0.4) 100%);
            bottom: 20%;
            left: 20%;
            animation-delay: -10s;
        }

        .orb:nth-child(4) {
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(250, 112, 154, 0.8) 0%, rgba(254, 225, 64, 0.4) 100%);
            top: 30%;
            left: 50%;
            animation-delay: -15s;
        }

        @keyframes floatOrb {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(50px, -50px) rotate(90deg) scale(1.1);
            }
            50% {
                transform: translate(-30px, 30px) rotate(180deg) scale(0.9);
            }
            75% {
                transform: translate(40px, 60px) rotate(270deg) scale(1.05);
            }
        }

        /* Enhanced Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(100px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-100px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes pulse3d {
            0%, 100% {
                transform: scale(1) rotateY(0deg);
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
            }
            50% {
                transform: scale(1.05) rotateY(180deg);
                box-shadow: 0 0 0 20px rgba(102, 126, 234, 0);
            }
        }

        @keyframes morphing {
            0%, 100% {
                border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            }
            50% {
                border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
            }
        }

        /* Enhanced Cards */
        .contact-card {
            transition: all 0.6s cubic-bezier(0.23, 1, 0.320, 1);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .contact-card:hover::before {
            opacity: 1;
        }

        .contact-card:hover {
            transform: translateY(-20px) rotateX(5deg) rotateY(5deg) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        }

        /* Enhanced Form Elements avec texte noir */
        .form-group {
            position: relative;
            margin-bottom: 2rem;
        }

        .form-input {
            width: 100%;
            padding: 1.5rem 1.5rem 1.5rem 3.5rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-primary);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .form-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.8);
            background: rgba(255, 255, 255, 1);
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }

        .form-input::placeholder {
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .form-input:focus::placeholder {
            color: rgba(74, 85, 104, 0.5);
        }

        .form-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(102, 126, 234, 0.8);
            font-size: 1.25rem;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .form-group:focus-within .form-icon {
            color: #667eea;
            transform: translateY(-50%) scale(1.1);
        }

        /* Enhanced Labels avec texte noir */
        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1rem;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        }

        /* Enhanced Buttons */
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1.25rem 2.5rem;
            border-radius: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.6);
        }

        .btn-primary:active {
            transform: translateY(-2px) scale(1.02);
        }

        /* Enhanced Toggle */
        .toggle-container {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.4);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 34px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 3px;
            bottom: 3px;
            background: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .toggle-input:checked + .toggle-slider {
            background: var(--primary-gradient);
            border-color: rgba(102, 126, 234, 0.5);
        }

        .toggle-input:checked + .toggle-slider:before {
            transform: translateX(26px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Enhanced Notifications avec texte noir */
        .notification {
            animation: notificationSlide 0.8s cubic-bezier(0.23, 1, 0.320, 1);
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            color: var(--text-primary);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .notification.success {
            border-left: 4px solid #38a169;
        }

        .notification.error {
            border-left: 4px solid #e53e3e;
        }

        .notification::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes notificationSlide {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Enhanced Typography avec texte noir */
        .hero-title {
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 8px rgba(255, 255, 255, 0.5);
            animation: titleGlow 3s ease-in-out infinite alternate;
            filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.8));
        }

        @keyframes titleGlow {
            from {
                filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.8));
            }
            to {
                filter: drop-shadow(0 4px 8px rgba(255, 255, 255, 1));
            }
        }

        /* Texte noir pour les cartes glassmorphism */
        .glass-text-dark {
            color: var(--text-primary);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        }

        .glass-text-secondary {
            color: var(--text-secondary);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.6);
        }

        .glass-text-muted {
            color: var(--text-muted);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.4);
        }

        /* Particle Effect */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: particleFloat 15s infinite linear;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Character Counter avec texte noir */
        .character-counter {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-primary);
            border: 1px solid rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }

        /* Progress Bar */
        .progress-container {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 0 0 16px 16px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary-gradient);
            transition: width 0.3s ease;
            position: relative;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: progressShimmer 1.5s infinite;
        }

        @keyframes progressShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Enhanced FAQ Cards avec texte noir */
        .faq-card {
            transition: all 0.6s cubic-bezier(0.23, 1, 0.320, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            color: var(--text-primary);
        }

        .faq-card:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.95);
        }

        .faq-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
            opacity: 0;
        }

        .faq-card:hover::after {
            opacity: 1;
            animation: rotateSweep 0.8s ease-out;
        }

        @keyframes rotateSweep {
            0% { transform: rotate(45deg) translateX(-100%); }
            100% { transform: rotate(45deg) translateX(100%); }
        }

        /* Mobile Enhancements */
        @media (max-width: 768px) {
            .contact-card:hover {
                transform: translateY(-10px) scale(1.02);
            }

            .form-input {
                padding: 1.25rem 1.25rem 1.25rem 3rem;
                font-size: 1rem;
            }

            .btn-primary {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
        }

        /* Loading States */
        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Accessibility Improvements */
        .form-input:focus,
        .btn-primary:focus,
        .toggle-slider:focus {
            outline: 3px solid rgba(102, 126, 234, 0.5);
            outline-offset: 2px;
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>
<?php include '../Inc/Components/header.php'; ?>
<?php include '../Inc/Components/nav.php'; ?>

<!-- Floating Orbs Background -->
<div class="floating-orbs">
    <div class="orb"></div>
    <div class="orb"></div>
    <div class="orb"></div>
    <div class="orb"></div>
</div>

<!-- Particles Effect -->
<div class="particles" id="particles"></div>

<!-- Main Content -->
<div class="min-h-screen relative overflow-hidden py-20">
    <div class="container mx-auto px-4">
        <!-- Enhanced Hero Section avec texte noir -->
        <div class="text-center mb-20 animate-slideInUp">
            <div class="glass-morphism-strong rounded-3xl p-12 mx-auto max-w-5xl mb-16">
                <div class="inline-block mb-8">
                    <div class="w-24 h-24 glass-morphism rounded-full flex items-center justify-center mx-auto floating-icon animate-pulse3d">
                        <i class="fas fa-paper-plane text-4xl text-purple-600"></i>
                    </div>
                </div>
                <h1 class="text-black text-6xl md:text-8xl font-black mb-8 tracking-tight">
                    Contactez-nous
                </h1>
                <p class="text-2xl md:text-3xl glass-text-secondary max-w-4xl mx-auto leading-relaxed mb-8">
                    Nous sommes là pour vous accompagner. Votre voix compte, votre sécurité nous importe.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div class="glass-morphism rounded-full px-6 py-3 flex items-center">
                        <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                        <span class="text-sm font-medium glass-text-dark">Communication sécurisée</span>
                    </div>
                    <div class="glass-morphism rounded-full px-6 py-3 flex items-center">
                        <i class="fas fa-clock mr-2 text-blue-600"></i>
                        <span class="text-sm font-medium glass-text-dark">Réponse sous 2h</span>
                    </div>
                    <div class="glass-morphism rounded-full px-6 py-3 flex items-center">
                        <i class="fas fa-user-secret mr-2 text-purple-600"></i>
                        <span class="text-sm font-medium glass-text-dark">Option anonyme</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Enhanced Information Cards avec glassmorphism coloré -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Support Card -->
                    <div class="contact-card glass-colored rounded-3xl p-8 group animate-slideInLeft" style="background: rgba(59, 130, 246, 0.2);">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 glass-morphism rounded-2xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-headset text-3xl text-blue-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Support Technique</h3>
                        </div>
                        <p class="text-white/90 mb-6 leading-relaxed">Notre équipe d'experts est disponible pour résoudre tous vos problèmes techniques.</p>
                        <div class="space-y-4">
                            <div class="flex items-center text-white group-hover:text-blue-100 transition-colors">
                                <div class="w-10 h-10 glass-morphism rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-envelope text-blue-600"></i>
                                </div>
                                <span class="font-medium">support@signale-france.fr</span>
                                </div>
                                <div class="flex items-center text-white group-hover:text-blue-100 transition-colors">
                                    <div class="w-10 h-10 glass-morphism rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-clock text-green-600"></i>
                                    </div>
                                    <span>Lun-Ven: 9h-18h</span>
                                </div>
                                <div class="flex items-center text-white group-hover:text-blue-100 transition-colors">
                                    <div class="w-10 h-10 glass-morphism rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-bolt text-yellow-500"></i>
                                    </div>
                                    <span>Réponse sous 2h</span>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Card -->
                        <div class="contact-card glass-colored rounded-3xl p-8 group animate-slideInLeft" style="animation-delay: 0.2s; background: rgba(239, 68, 68, 0.2);">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 glass-morphism rounded-2xl flex items-center justify-center mr-4">
                                    <i class="fas fa-exclamation-triangle text-3xl text-red-500 animate-pulse"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white">Urgence</h3>
                            </div>
                            <p class="text-white/90 mb-6 leading-relaxed">Pour les situations critiques nécessitant une intervention immédiate.</p>
                            <div class="glass-morphism rounded-lg p-4">
                                <div class="text-sm space-y-2 glass-text-dark">
                                    <div><strong>📞 15</strong> - SAMU</div>
                                    <div><strong>📞 17</strong> - Police</div>
                                    <div><strong>📞 18</strong> - Pompiers</div>
                                    <div><strong>📱 112</strong> - Urgence européen</div>
                                </div>
                            </div>
                        </div>

                        <!-- Anonymous Card -->
                        <div class="contact-card glass-colored rounded-3xl p-8 group animate-slideInLeft" style="animation-delay: 0.4s; background: rgba(139, 92, 246, 0.2);">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 glass-morphism rounded-2xl flex items-center justify-center mr-4 group-hover:rotate-12 transition-transform duration-300">
                                    <i class="fas fa-user-secret text-3xl text-purple-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white">Contact Anonyme</h3>
                            </div>
                            <p class="text-white/90 mb-6 leading-relaxed">Votre confidentialité est notre priorité absolue.</p>
                            <div class="space-y-3">
                                <div class="flex items-center text-white">
                                    <i class="fas fa-check mr-3 text-green-400"></i>
                                    <span>Aucune donnée conservée</span>
                                </div>
                                <div class="flex items-center text-white">
                                    <i class="fas fa-check mr-3 text-green-400"></i>
                                    <span>Chiffrement sécurisé</span>
                                </div>
                                <div class="flex items-center text-white">
                                    <i class="fas fa-check mr-3 text-green-400"></i>
                                    <span>Protection garantie</span>
                                </div>
                            </div>
                        </div>

                        <!-- Social Networks -->
                        <div class="contact-card glass-morphism-strong rounded-3xl p-8 animate-slideInLeft" style="animation-delay: 0.6s;">
                            <h3 class="text-xl font-bold glass-text-dark mb-6 flex items-center">
                                <i class="fas fa-share-alt mr-3 text-purple-600"></i>
                                Suivez-nous
                            </h3>
                            <div class="flex space-x-4">
                                <a href="#" class="w-12 h-12 glass-morphism rounded-xl flex items-center justify-center hover:scale-110 transition-transform duration-300">
                                    <i class="fab fa-facebook-f text-blue-600"></i>
                                </a>
                                <a href="#" class="w-12 h-12 glass-morphism rounded-xl flex items-center justify-center hover:scale-110 transition-transform duration-300">
                                    <i class="fab fa-twitter text-blue-400"></i>
                                </a>
                                <a href="#" class="w-12 h-12 glass-morphism rounded-xl flex items-center justify-center hover:scale-110 transition-transform duration-300">
                                    <i class="fab fa-linkedin-in text-blue-700"></i>
                                </a>
                                <a href="#" class="w-12 h-12 glass-morphism rounded-xl flex items-center justify-center hover:scale-110 transition-transform duration-300">
                                    <i class="fab fa-youtube text-red-600"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Contact Form avec texte noir -->
                    <div class="lg:col-span-2 animate-slideInRight">
                        <div class="glass-morphism-strong rounded-3xl p-10">
                            <div class="mb-10">
                                <h2 class="text-4xl font-bold glass-text-dark mb-4 flex items-center">
                                    <i class="fas fa-paper-plane text-blue-600 mr-4"></i>
                                    Envoyez-nous un message
                                </h2>
                                <p class="glass-text-secondary text-lg leading-relaxed">Remplissez le formulaire ci-dessous et nous vous répondrons rapidement.</p>
                            </div>

                            <!-- Enhanced Notifications avec texte noir -->
                            <?php if ($message_sent): ?>
                                <div class="notification success">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="glass-text-dark font-bold text-lg">Message envoyé avec succès !</h4>
                                            <p class="glass-text-secondary">Nous avons reçu votre message et vous répondrons rapidement.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div class="notification error">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="glass-text-dark font-bold text-lg">Erreur</h4>
                                            <p class="glass-text-secondary"><?php echo htmlspecialchars($error_message); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-8" id="contactForm">
                                <!-- Enhanced Anonymous Option avec texte noir -->
                                <div class="glass-morphism rounded-2xl p-6 border border-purple-400/30">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-user-secret text-purple-600 text-2xl mr-4"></i>
                                            <div>
                                                <label for="anonyme" class="glass-text-dark font-semibold text-lg cursor-pointer">
                                                    Envoyer anonymement
                                                </label>
                                                <p class="glass-text-muted text-sm">Votre identité sera protégée</p>
                                            </div>
                                        </div>
                                        <div class="toggle-container">
                                            <input type="checkbox" name="anonyme" id="anonyme" class="toggle-input sr-only" <?php echo (isset($_POST['anonyme']) && $_POST['anonyme']) ? 'checked' : ''; ?>>
                                            <span class="toggle-slider"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Type de demande -->
                                <div class="form-group">
                                    <label for="type_demande" class="form-label">
                                        <i class="fas fa-tag mr-2 text-blue-600"></i>Type de demande *
                                    </label>
                                    <div class="relative">
                                        <i class="form-icon fas fa-tags"></i>
                                        <select name="type_demande" id="type_demande" required class="form-input">
                                            <option value="">Sélectionnez le type de demande</option>
                                            <option value="support_technique" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'support_technique') ? 'selected' : ''; ?>>🔧 Support technique</option>
                                            <option value="question_generale" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'question_generale') ? 'selected' : ''; ?>>❓ Question générale</option>
                                            <option value="suggestion" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'suggestion') ? 'selected' : ''; ?>>💡 Suggestion</option>
                                            <option value="signalement_probleme" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'signalement_probleme') ? 'selected' : ''; ?>>⚠️ Problème</option>
                                            <option value="partenariat" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'partenariat') ? 'selected' : ''; ?>>🤝 Partenariat</option>
                                            <option value="autre" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'autre') ? 'selected' : ''; ?>>📝 Autre</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Nom et Email -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="form-group">
                                        <label for="nom" class="form-label">
                                            <i class="fas fa-user mr-2 text-blue-600"></i>Nom complet *
                                        </label>
                                        <div class="relative">
                                            <i class="form-icon fas fa-user"></i>
                                            <input type="text" name="nom" id="nom" required 
                                                   value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>"
                                                   class="form-input" 
                                                   placeholder="Votre nom complet">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope mr-2 text-blue-600"></i>Email (facultatif)
                                        </label>
                                        <div class="relative">
                                            <i class="form-icon fas fa-envelope"></i>
                                            <input type="email" name="email" id="email" 
                                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                                   class="form-input" 
                                                   placeholder="votre@email.com">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sujet -->
                                <div class="form-group">
                                    <label for="sujet" class="form-label">
                                        <i class="fas fa-heading mr-2 text-blue-600"></i>Sujet *
                                    </label>
                                    <div class="relative">
                                        <i class="form-icon fas fa-heading"></i>
                                        <input type="text" name="sujet" id="sujet" required 
                                               value="<?php echo htmlspecialchars($_POST['sujet'] ?? ''); ?>"
                                               class="form-input" 
                                               placeholder="Résumé de votre demande">
                                    </div>
                                </div>

                                <!-- Message -->
                                <div class="form-group">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment-alt mr-2 text-blue-600"></i>Message *
                                    </label>
                                    <div class="relative">
                                        <i class="form-icon fas fa-comment-alt" style="top: 1.5rem;"></i>
                                        <textarea name="message" id="message" rows="8" required 
                                                  class="form-input resize-none" 
                                                  placeholder="Décrivez votre demande en détail..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                        <div class="character-counter" id="charCounter">
                                            Minimum 10 caractères
                                        </div>
                                        <div class="progress-container">
                                            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Enhanced Submit Button -->
                                <div class="flex items-center justify-between pt-8">
                                    <p class="glass-text-muted text-sm flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                        Les champs marqués d'un * sont obligatoires
                                    </p>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-paper-plane mr-3"></i>
                                        Envoyer le message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Enhanced FAQ Section avec texte noir -->
                <div class="mt-24">
                    <div class="glass-morphism-strong rounded-3xl p-12">
                        <div class="text-center mb-16">
                            <h2 class="text-5xl font-bold glass-text-dark mb-6">
                                <i class="fas fa-question-circle text-blue-600 mr-4"></i>
                                Questions fréquentes
                            </h2>
                            <p class="text-xl glass-text-secondary leading-relaxed max-w-2xl mx-auto">
                                Trouvez rapidement des réponses aux questions les plus courantes
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div class="faq-card rounded-2xl p-8 text-center hover:scale-105 transition-transform duration-300">
                                <div class="w-16 h-16 glass-morphism rounded-2xl flex items-center justify-center mb-6 mx-auto">
                                    <i class="fas fa-plus-circle text-3xl text-blue-600"></i>
                                </div>
                                <h3 class="font-bold glass-text-dark mb-4 text-xl">
                                    Comment créer un signalement ?
                                </h3>
                                <p class="glass-text-secondary mb-6 leading-relaxed">
                                    Cliquez sur "Créer un signalement" et remplissez le formulaire détaillé.
                                </p>
                                <a href="guides.php" class="inline-flex items-center text-blue-600 font-bold hover:text-blue-700 transition-colors">
                                    Voir le guide
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>

                            <div class="faq-card rounded-2xl p-8 text-center hover:scale-105 transition-transform duration-300">
                                <div class="w-16 h-16 glass-morphism rounded-2xl flex items-center justify-center mb-6 mx-auto">
                                    <i class="fas fa-clock text-3xl text-green-600"></i>
                                </div>
                                <h3 class="font-bold glass-text-dark mb-4 text-xl">
                                    Délai de traitement ?
                                </h3>
                                <p class="glass-text-secondary mb-6 leading-relaxed">
                                    Les signalements sont traités sous 24-48h selon leur priorité.
                                </p>
                                <a href="faq.php" class="inline-flex items-center text-green-600 font-bold hover:text-green-700 transition-colors">
                                    En savoir plus
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>

                            <div class="faq-card rounded-2xl p-8 text-center hover:scale-105 transition-transform duration-300">
                                <div class="w-16 h-16 glass-morphism rounded-2xl flex items-center justify-center mb-6 mx-auto">
                                    <i class="fas fa-eye-slash text-3xl text-purple-600"></i>
                                </div>
                                <h3 class="font-bold glass-text-dark mb-4 text-xl">
                                    Contact anonyme ?
                                </h3>
                                <p class="glass-text-secondary mb-6 leading-relaxed">
                                    Oui, contactez-nous anonymement en cochant l'option dédiée.
                                </p>
                                <a href="faq.php" class="inline-flex items-center text-purple-600 font-bold hover:text-purple-700 transition-colors">
                                    Plus d'infos
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>

                        <div class="text-center mt-12">
                            <a href="faq.php" class="btn-primary">
                                <i class="fas fa-list mr-3"></i>
                                Voir toutes les FAQ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Enhanced JavaScript with modern features
        document.addEventListener('DOMContentLoaded', function() {
            initializeParticles();
            initializeFormEnhancements();
            initializeAnimations();
            initializeToggle();
        });

        // Particle System
        function initializeParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                createParticle(particlesContainer);
            }

            setInterval(() => {
                if (particlesContainer.children.length < particleCount) {
                    createParticle(particlesContainer);
                }
            }, 3000);
        }

        function createParticle(container) {
            const particle = document.createElement('div');
            particle.className = 'particle';

            const size = Math.random() * 4 + 2;
            const x = Math.random() * 100;
            const duration = Math.random() * 10 + 10;

            particle.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                left: ${x}%;
                animation-duration: ${duration}s;
                animation-delay: ${Math.random() * 5}s;
            `;

            container.appendChild(particle);

            setTimeout(() => {
                if (particle.parentNode) {
                    particle.remove();
                }
            }, duration * 1000);
        }

        // Enhanced Form Features
        function initializeFormEnhancements() {
            const messageTextarea = document.getElementById('message');
            const charCounter = document.getElementById('charCounter');
            const progressBar = document.getElementById('progressBar');

            messageTextarea.addEventListener('input', function() {
                const minLength = 10;
                const maxLength = 1000;
                const currentLength = this.value.length;
                const percentage = Math.min((currentLength / minLength) * 100, 100);

                progressBar.style.width = `${percentage}%`;

                if (currentLength < minLength) {
                    charCounter.textContent = `${currentLength}/${minLength} caractères minimum`;
                    charCounter.style.color = '#e53e3e';
                    progressBar.style.background = 'linear-gradient(90deg, #e53e3e, #fc8181)';
                } else if (currentLength <= maxLength) {
                    charCounter.textContent = `${currentLength} caractères`;
                    charCounter.style.color = '#38a169';
                    progressBar.style.background = 'var(--primary-gradient)';
                } else {
                    charCounter.textContent = `${currentLength}/${maxLength} - Trop long`;
                    charCounter.style.color = '#ed8936';
                    progressBar.style.background = 'linear-gradient(90deg, #ed8936, #f6ad55)';
                }
            });

            // Trigger initial update
            messageTextarea.dispatchEvent(new Event('input'));
        }

        // Enhanced Toggle
        function initializeToggle() {
            const toggleInput = document.getElementById('anonyme');
            const formInputs = document.querySelectorAll('.form-input');

            toggleInput.addEventListener('change', function() {
                const isAnonymous = this.checked;

                // Update placeholders for anonymous mode
                const nomInput = document.getElementById('nom');
                const emailInput = document.getElementById('email');

                if (isAnonymous) {
                    nomInput.placeholder = 'Sera remplacé par "Utilisateur anonyme"';
                    emailInput.placeholder = 'Non conservé en mode anonyme';
                } else {
                    nomInput.placeholder = 'Votre nom complet';
                    emailInput.placeholder = 'votre@email.com';
                }
            });
        }

        // Enhanced Animations
        function initializeAnimations() {
            // Intersection Observer for scroll animations
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

            // Observe animated elements
            document.querySelectorAll('.contact-card, .faq-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(50px) scale(0.9)';
                card.style.transition = `all 0.8s cubic-bezier(0.23, 1, 0.320, 1) ${index * 0.1}s`;
                observer.observe(card);
            });

            // Parallax effect for orbs
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const orbs = document.querySelectorAll('.orb');

                orbs.forEach((orb, index) => {
                    const speed = (index + 1) * 0.1;
                    orb.style.transform = `translateY(${scrolled * speed}px) scale(${1 + scrolled * 0.0001})`;
                });
            });
        }

        // Enhanced Form Submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;

            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-3"></i>Envoi en cours...';
            submitBtn.disabled = true;

            // Create confirmation modal
            createConfirmationModal();
        });

        function createConfirmationModal() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4';
            modal.style.animation = 'fadeIn 0.4s ease-out';

            modal.innerHTML = `
                <div class="glass-morphism-strong rounded-3xl p-8 max-w-md w-full" style="animation: slideInUp 0.5s cubic-bezier(0.23, 1, 0.320, 1);">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 glass-morphism rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-paper-plane text-3xl text-purple-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold glass-text-dark mb-2">Confirmer l'envoi</h3>
                        <p class="glass-text-secondary">Êtes-vous sûr de vouloir envoyer ce message ?</p>
                    </div>

                    <div class="flex space-x-4">
                        <button onclick="cancelSend()" class="flex-1 glass-morphism glass-text-dark font-semibold py-3 px-6 rounded-xl hover:bg-white/50 transition-all duration-300">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </button>
                        <button onclick="confirmSend()" class="flex-1 btn-primary">
                            <i class="fas fa-check mr-2"></i>Confirmer
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
        }

        function cancelSend() {
            const modal = document.querySelector('.fixed.inset-0');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = 'auto';

                    // Reset submit button
                    const submitBtn = document.querySelector('button[type="submit"]');
                    submitBtn.classList.remove('loading');
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-3"></i>Envoyer le message';
                    submitBtn.disabled = false;
                }, 300);
            }
        }

        function confirmSend() {
            const modal = document.querySelector('.fixed.inset-0');
            const form = document.getElementById('contactForm');

            // Show loading in modal
            modal.querySelector('.glass-morphism-strong').innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 glass-morphism rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold glass-text-dark mb-2">Envoi en cours...</h3>
                    <p class="glass-text-secondary">Veuillez patienter</p>
                </div>
            `;

            // Submit form after delay
            setTimeout(() => {
                form.submit();
            }, 1500);
        }

        // CSS Animation Keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            @keyframes slideInUp {
                from { opacity: 0; transform: translateY(50px) scale(0.9); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .animate-slideInUp { animation: slideInUp 0.8s ease-out; }
            .animate-slideInLeft { animation: slideInLeft 0.8s ease-out; }
            .animate-slideInRight { animation: slideInRight 0.8s ease-out; }
            .animate-pulse3d { animation: pulse3d 3s ease-in-out infinite; }
        `;
        document.head.appendChild(style);
    </script>

<?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>