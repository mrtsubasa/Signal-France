<?php
session_start();
include("../Inc/Constants/db.php");
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';

?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow:
                    0 8px 32px rgba(0, 0, 145, 0.12),
                    0 2px 16px rgba(0, 0, 145, 0.08),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.8s ease;
        }

        .glass-card:hover::before {
            left: 100%;
        }

        .glass-card:hover {
            transform: translateY(-16px) scale(1.03);
            box-shadow:
                    0 32px 64px rgba(0, 0, 145, 0.2),
                    0 8px 32px rgba(0, 0, 145, 0.15),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border-color: rgba(0, 0, 145, 0.4);
            background: rgba(255, 255, 255, 0.25);
        }

        .hero-bg {
            background: linear-gradient(135deg,
            #000091 0%,
            #6a6af4 15%,
            #8b5cf6 30%,
            #000091 45%,
            #06b6d4 60%,
            #6a6af4 75%,
            #000091 100%);
            background-size: 300% 300%;
            animation: gradientShift 12s ease infinite;
            position: relative;
        }

        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 70% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            33% { background-position: 100% 50%; }
            66% { background-position: 50% 100%; }
        }

        .floating-element {
            animation: float 8s ease-in-out infinite;
        }

        .floating-element:nth-child(even) {
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); }
            25% { transform: translateY(-30px) rotate(5deg) scale(1.05); }
            50% { transform: translateY(-20px) rotate(-3deg) scale(0.95); }
            75% { transform: translateY(-40px) rotate(8deg) scale(1.02); }
        }

        @keyframes bounceGentle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes rotateSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .team-avatar {
            background: linear-gradient(135deg, #000091, #6a6af4, #8b5cf6);
            position: relative;
            overflow: hidden;
            box-shadow:
                    0 20px 40px rgba(0, 0, 145, 0.3),
                    0 8px 16px rgba(0, 0, 145, 0.2),
                    inset 0 2px 4px rgba(255, 255, 255, 0.2);
        }

        .team-avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: rotateSlow 8s linear infinite;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .glass-card:hover .team-avatar::before {
            opacity: 1;
        }

        .section-divider {
            height: 3px;
            background: linear-gradient(90deg,
            transparent,
            #000091 10%,
            #6a6af4 25%,
            #8b5cf6 50%,
            #6a6af4 75%,
            #000091 90%,
            transparent);
            margin: 5rem 0;
            border-radius: 2px;
            position: relative;
        }

        .section-divider::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 0;
            right: 0;
            height: 5px;
            background: inherit;
            filter: blur(2px);
            opacity: 0.5;
        }

        .btn-modern {
            background: linear-gradient(135deg, #000091 0%, #6a6af4 50%, #8b5cf6 100%);
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(0, 0, 145, 0.3);
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0, 0, 145, 0.4);
        }

        .btn-accent {
            background: linear-gradient(135deg, #e1000f 0%, #ff1a2e 50%, #ff4d6d 100%);
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(225, 0, 15, 0.3);
        }

        .btn-accent:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 20px 40px rgba(225, 0, 15, 0.4);
        }

        .value-icon {
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .value-card:hover .value-icon {
            transform: scale(1.15) rotate(10deg);
            filter: drop-shadow(0 10px 20px rgba(0, 0, 145, 0.3));
        }

        .stats-counter {
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 8px rgba(0, 0, 145, 0.2);
        }

        .text-gradient {
            background: linear-gradient(135deg, #000091, #6a6af4, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 200% 200%;
            animation: gradientShift 4s ease infinite;
        }

        .fade-in-up {
            opacity: 0;
            transform: translateY(40px);
            transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .team-card-director {
            background: linear-gradient(135deg,
            rgba(255, 255, 255, 0.3) 0%,
            rgba(255, 255, 255, 0.2) 100%);
            backdrop-filter: blur(30px);
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow:
                    0 25px 50px rgba(0, 0, 145, 0.2),
                    0 10px 25px rgba(0, 0, 145, 0.1),
                    inset 0 2px 4px rgba(255, 255, 255, 0.3);
        }

        .team-card-director:hover {
            transform: translateY(-20px) scale(1.05);
            box-shadow:
                    0 40px 80px rgba(0, 0, 145, 0.3),
                    0 20px 40px rgba(0, 0, 145, 0.2),
                    inset 0 2px 4px rgba(255, 255, 255, 0.4);
        }

        .scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            animation: bounceGentle 2s ease-in-out infinite;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            pointer-events: none;
        }

        .geometric-bg {
            position: absolute;
            inset: 0;
            overflow: hidden;
            opacity: 0.03;
        }

        .geometric-shape {
            position: absolute;
            border: 2px solid currentColor;
            animation: float 15s ease-in-out infinite;
        }

        .skill-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .skill-badge:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .stats-counter {
                font-size: 2.5rem;
            }

            .glass-card:hover {
                transform: translateY(-8px) scale(1.02);
            }

            .team-card-director:hover {
                transform: translateY(-10px) scale(1.02);
            }
        }

        /* Loading animation */
        .loading-shimmer {
            background: linear-gradient(90deg,
            rgba(255, 255, 255, 0.1) 25%,
            rgba(255, 255, 255, 0.3) 50%,
            rgba(255, 255, 255, 0.1) 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</head>
<main class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">


<div class="marianne-band"></div>

<!-- Hero Section Enhanced -->
<section class="hero-bg text-white py-32 relative overflow-hidden">
    <!-- Geometric Background -->
    <div class="geometric-bg">
        <div class="geometric-shape w-32 h-32 top-20 left-10 rotate-45"></div>
        <div class="geometric-shape w-24 h-24 top-40 right-20 rounded-full" style="animation-delay: -5s;"></div>
        <div class="geometric-shape w-40 h-40 bottom-20 left-1/4 rotate-12" style="animation-delay: -10s;"></div>
        <div class="geometric-shape w-20 h-20 top-1/2 right-1/4 rounded-full" style="animation-delay: -15s;"></div>
    </div>

    <!-- Enhanced Floating Elements -->
    <div class="absolute inset-0 opacity-8">
        <div class="floating-element absolute top-20 left-10 text-6xl text-white/20">
            <i class="fas fa-users"></i>
        </div>
        <div class="floating-element absolute top-32 right-20 text-4xl text-white/15" style="animation-delay: -2s;">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="floating-element absolute bottom-20 left-1/4 text-5xl text-white/20" style="animation-delay: -4s;">
            <i class="fas fa-heart"></i>
        </div>
        <div class="floating-element absolute top-1/2 right-1/4 text-3xl text-white/15" style="animation-delay: -6s;">
            <i class="fas fa-star"></i>
        </div>
        <div class="floating-element absolute top-1/3 left-1/3 text-4xl text-white/10" style="animation-delay: -8s;">
            <i class="fas fa-rocket"></i>
        </div>
    </div>

    <div class="container mx-auto px-6 text-center relative z-10">
        <div class="fade-in-up">
            <h1 class="text-6xl md:text-8xl font-black mb-8 tracking-tight leading-tight">
                Notre <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-blue-200 to-purple-200">Équipe</span>
            </h1>
            <p class="text-xl md:text-2xl mb-12 max-w-4xl mx-auto font-light leading-relaxed text-blue-100">
                Une équipe d'experts passionnés, dédiée à la protection et au bien-être de tous les citoyens français
            </p>
            <div class="w-40 h-1 bg-gradient-to-r from-transparent via-white to-transparent mx-auto mb-12 rounded-full"></div>

            <!-- Enhanced Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-4xl mx-auto mt-16">
                <div class="fade-in-up glass-card rounded-2xl p-8" style="animation-delay: 0.2s;">
                    <div class="stats-counter mb-2">6</div>
                    <p class="text-blue-200 font-semibold text-lg">Experts Dédiés</p>
                    <div class="w-12 h-1 bg-gradient-to-r from-blue-400 to-purple-400 mx-auto mt-4 rounded-full"></div>
                </div>
                <div class="fade-in-up glass-card rounded-2xl p-8" style="animation-delay: 0.4s;">
                    <div class="stats-counter mb-2">24/7</div>
                    <p class="text-blue-200 font-semibold text-lg">Support Continu</p>
                    <div class="w-12 h-1 bg-gradient-to-r from-purple-400 to-pink-400 mx-auto mt-4 rounded-full"></div>
                </div>
                <div class="fade-in-up glass-card rounded-2xl p-8" style="animation-delay: 0.6s;">
                    <div class="stats-counter mb-2">100%</div>
                    <p class="text-blue-200 font-semibold text-lg">Engagement Total</p>
                    <div class="w-12 h-1 bg-gradient-to-r from-pink-400 to-red-400 mx-auto mt-4 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator text-white/60">
        <i class="fas fa-chevron-down text-2xl"></i>
    </div>
</section>

<!-- Team Section Enhanced -->
<section class="py-32 relative">
    <div class="container mx-auto px-6">
        <!-- Direction Enhanced -->
        <div class="mb-32 fade-in-up">
            <div class="text-center mb-20">
                <h2 class="text-6xl font-black text-gradient mb-6">
                    Direction
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light leading-relaxed">
                    Leadership visionnaire pour guider Signal France vers l'excellence et l'innovation
                </p>
            </div>

            <div class="flex justify-center">
                <div class="team-card-director rounded-3xl p-12 max-w-2xl text-center transition-all duration-500">
                    <div class="team-avatar w-48 h-48 rounded-full mx-auto mb-10 flex items-center justify-center relative">
                        <i class="fas fa-crown text-6xl text-white relative z-10"></i>
                        <div class="absolute inset-0 rounded-full bg-gradient-to-r from-yellow-400/20 to-orange-400/20 animate-pulse-slow"></div>
                    </div>
                    <h3 class="text-4xl font-black text-marianne-blue mb-4">Directeur Général</h3>
                    <p class="text-marianne-red font-bold text-xl mb-6">Jean Dupont</p>
                    <p class="text-gray-600 leading-relaxed text-lg mb-8">
                        Visionnaire et stratège, il dirige Signal France avec passion et détermination pour créer un environnement numérique plus sûr pour tous.
                    </p>

                    <!-- Skills badges -->
                    <div class="flex flex-wrap justify-center gap-3 mb-8">
                        <span class="skill-badge px-4 py-2 rounded-full text-sm font-semibold text-marianne-blue">Leadership</span>
                        <span class="skill-badge px-4 py-2 rounded-full text-sm font-semibold text-marianne-blue">Stratégie</span>
                        <span class="skill-badge px-4 py-2 rounded-full text-sm font-semibold text-marianne-blue">Innovation</span>
                    </div>

                    <div class="flex justify-center space-x-6">
                        <div class="w-4 h-4 bg-marianne-blue rounded-full animate-pulse"></div>
                        <div class="w-4 h-4 bg-marianne-red rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                        <div class="w-4 h-4 bg-secondary-blue rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        <!-- Technical Team Enhanced -->
        <div class="mb-32 fade-in-up">
            <div class="text-center mb-20">
                <h2 class="text-6xl font-black text-gradient mb-6">
                    Équipe Technique
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light leading-relaxed">
                    Innovation et excellence technique au cœur de nos solutions digitales
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-12">
                <div class="glass-card rounded-3xl p-10 text-center fade-in-up" style="animation-delay: 0.1s;">
                    <div class="team-avatar w-32 h-32 rounded-full mx-auto mb-8 flex items-center justify-center">
                        <i class="fas fa-laptop-code text-4xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-marianne-blue mb-3">Développeur Principal</h3>
                    <p class="text-marianne-red font-semibold mb-6">Luca / Tsubasa</p>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Expert en développement full-stack, architecte de nos solutions innovantes et performantes.
                    </p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="skill-badge px-3 py-1 rounded-full text-xs font-medium text-marianne-blue">PHP</span>
                        <span class="skill-badge px-3 py-1 rounded-full text-xs font-medium text-marianne-blue">JavaScript</span>
                        <span class="skill-badge px-3 py-1 rounded-full text-xs font-medium text-marianne-blue">React</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        <!-- Support Team Enhanced -->
        <div class="mb-32 fade-in-up">
            <div class="text-center mb-20">
                <h2 class="text-6xl font-black text-gradient mb-6">
                    Support & Modération
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light leading-relaxed">
                    Accompagnement bienveillant et protection de notre communauté
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 max-w-5xl mx-auto">
                <div class="glass-card rounded-3xl p-10 text-center fade-in-up" style="animation-delay: 0.1s;">
                    <div class="team-avatar w-32 h-32 rounded-full mx-auto mb-8 flex items-center justify-center">
                        <i class="fas fa-headset text-4xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-marianne-blue mb-3">Responsable Support</h3>
                    <p class="text-marianne-red font-semibold mb-6">Marie Dubois</p>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Dédiée à l'assistance personnalisée et à la satisfaction complète de nos utilisateurs.
                    </p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="skill-badge px-3 py-1 rounded-full text-xs font-medium text-marianne-blue">Support Client</span>
                        <span class="skill-badge px-3 py-1 rounded-full text-xs font-medium text-marianne-blue">Communication</span>
                    </div>
                </div>

                <div class="glass-card rounded-3xl p-10 text-center fade-in-up" style="animation-delay: 0.2s;">
                    <div class="team-avatar w-32 h-32 rounded-full mx-auto mb-8 flex items-center justify-center">
                        <i class="fas fa-shield-alt text-4xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-marianne-blue mb-3">Modérateur</h3>
                    <p class="text-marianne-red font-semibold mb-6">Lucas Moreau</p>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Veille constante pour maintenir un environnement sain, respectueux et sécurisé.
                    </p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="skill-badge px-3 py-1 rounded-full text-xs font-medium text-marianne-blue">Modération</span>
                        <span class="skill-badge px-3 py-1 rounded-full text-xs font-medium text-marianne-blue">Sécurité</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section Enhanced -->
<section class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-32 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, #000091 2px, transparent 2px); background-size: 60px 60px;"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center mb-24 fade-in-up">
            <h2 class="text-6xl font-black text-gradient mb-8">
                Nos Valeurs
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light leading-relaxed">
                Les principes fondamentaux qui guident chacune de nos actions quotidiennes
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-16">
            <div class="value-card text-center fade-in-up" style="animation-delay: 0.1s;">
                <div class="value-icon w-28 h-28 bg-gradient-to-br from-marianne-blue via-secondary-blue to-accent-purple rounded-3xl mx-auto mb-10 flex items-center justify-center shadow-2xl">
                    <i class="fas fa-shield-alt text-4xl text-white"></i>
                </div>
                <h3 class="text-3xl font-bold text-marianne-blue mb-6">Sécurité</h3>
                <p class="text-gray-600 leading-relaxed text-lg">
                    Protection absolue et confidentialité garantie des données de nos utilisateurs
                </p>
                <div class="w-16 h-1 bg-gradient-to-r from-marianne-blue to-secondary-blue mx-auto mt-6 rounded-full"></div>
            </div>

            <div class="value-card text-center fade-in-up" style="animation-delay: 0.2s;">
                <div class="value-icon w-28 h-28 bg-gradient-to-br from-marianne-red via-red-500 to-pink-500 rounded-3xl mx-auto mb-10 flex items-center justify-center shadow-2xl">
                    <i class="fas fa-heart text-4xl text-white"></i>
                </div>
                <h3 class="text-3xl font-bold text-marianne-blue mb-6">Bienveillance</h3>
                <p class="text-gray-600 leading-relaxed text-lg">
                    Accompagnement empathique et respectueux de chaque membre de notre communauté
                </p>
                <div class="w-16 h-1 bg-gradient-to-r from-marianne-red to-pink-500 mx-auto mt-6 rounded-full"></div>
            </div>

            <div class="value-card text-center fade-in-up" style="animation-delay: 0.3s;">
                <div class="value-icon w-28 h-28 bg-gradient-to-br from-accent-cyan via-secondary-blue to-accent-purple rounded-3xl mx-auto mb-10 flex items-center justify-center shadow-2xl">
                    <i class="fas fa-rocket text-4xl text-white"></i>
                </div>
                <h3 class="text-3xl font-bold text-marianne-blue mb-6">Innovation</h3>
                <p class="text-gray-600 leading-relaxed text-lg">
                    Amélioration continue et innovation constante de nos services et technologies
                </p>
                <div class="w-16 h-1 bg-gradient-to-r from-accent-cyan to-accent-purple mx-auto mt-6 rounded-full"></div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section Enhanced -->
<section class="hero-bg py-32 relative overflow-hidden">
    <div class="absolute inset-0 bg-black/30"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <div class="fade-in-up">
            <h2 class="text-6xl font-black text-white mb-10">
                Rejoignez l'Aventure
            </h2>
            <p class="text-xl text-blue-100 mb-16 max-w-3xl mx-auto font-light leading-relaxed">
                Vous partagez nos valeurs ? Vous souhaitez contribuer à un internet plus sûr et plus respectueux ?
            </p>
            <div class="flex flex-col sm:flex-row gap-8 justify-center">
                <button class="btn-modern text-white px-12 py-6 rounded-full font-bold text-lg inline-flex items-center justify-center group">
                    <i class="fas fa-envelope mr-4 group-hover:animate-bounce-gentle"></i>
                    Nous Contacter
                </button>
                <button class="btn-accent text-white px-12 py-6 rounded-full font-bold text-lg inline-flex items-center justify-center group">
                    <i class="fas fa-paper-plane mr-4 group-hover:animate-bounce-gentle"></i>
                    Candidater
                </button>
            </div>
        </div>
    </div>
</section>

<div id="notification-container" class="fixed top-4 right-4 z-50"></div>
</main>
<script>
    // Enhanced scroll animations with intersection observer
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, index * 150);
            }
        });
    }, observerOptions);

    // Enhanced counter animation with easing
    function animateCounter(element, target, duration = 2000) {
        let startTime = null;
        let startValue = 0;

        function updateCounter(currentTime) {
            if (!startTime) startTime = currentTime;
            const progress = Math.min((currentTime - startTime) / duration, 1);

            // Easing function for smooth animation
            const easedProgress = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.floor(startValue + (target - startValue) * easedProgress);

            if (typeof target === 'number') {
                element.textContent = currentValue;
            } else {
                element.textContent = target; // For non-numeric values like "24/7"
            }

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }

        requestAnimationFrame(updateCounter);
    }

    // Particle system for hero background
    function createParticles() {
        const heroSection = document.querySelector('.hero-bg');
        const particleCount = 20;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.width = Math.random() * 4 + 2 + 'px';
            particle.style.height = particle.style.width;
            particle.style.animationDuration = Math.random() * 3 + 2 + 's';
            particle.style.animationDelay = Math.random() * 2 + 's';
            heroSection.appendChild(particle);
        }
    }

    // Enhanced smooth scroll
    function smoothScrollTo(element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize animations
        const fadeElements = document.querySelectorAll('.fade-in-up');
        fadeElements.forEach(el => observer.observe(el));

        // Initialize particle system
        createParticles();

        // Enhanced counter animations
        const counters = document.querySelectorAll('.stats-counter');
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const text = entry.target.textContent.trim();
                    if (text === '6') {
                        animateCounter(entry.target, 6, 1500);
                    } else if (text === '24/7') {
                        setTimeout(() => {
                            entry.target.textContent = '24/7';
                        }, 800);
                    } else if (text === '100%') {
                        animateCounter(entry.target, 100, 2000);
                        setTimeout(() => {
                            entry.target.textContent = '100%';
                        }, 2000);
                    }
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => counterObserver.observe(counter));

        // Enhanced button interactions
        document.querySelectorAll('button, .btn-modern, .btn-accent').forEach(button => {
            button.addEventListener('mouseenter', () => {
                button.style.filter = 'brightness(1.1)';
            });

            button.addEventListener('mouseleave', () => {
                button.style.filter = 'brightness(1)';
            });

            button.addEventListener('click', (e) => {
                // Ripple effect
                const ripple = document.createElement('div');
                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                        pointer-events: none;
                    `;

                button.style.position = 'relative';
                button.style.overflow = 'hidden';
                button.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);

                // Navigation logic
                if (button.textContent.includes('Contacter')) {
                    setTimeout(() => window.location.href = 'contact.php', 300);
                } else if (button.textContent.includes('Candidater')) {
                    setTimeout(() => window.location.href = 'mailto:recrutement@signalfrance.fr', 300);
                }
            });
        });

        // Add ripple animation keyframes
        const style = document.createElement('style');
        style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
        document.head.appendChild(style);

        // Parallax effect for floating elements
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.floating-element');

            parallaxElements.forEach((element, index) => {
                const speed = 0.5 + (index * 0.1);
                element.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * 0.1}deg)`;
            });
        });
    });
</script>

<?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include('../Inc/Traitement/create_log.php'); ?>

