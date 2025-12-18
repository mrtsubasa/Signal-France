<?php
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notre Équipe - Signal France</title>
    <link rel="stylesheet" href="../Assets/Css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'marianne-blue': '#000091',
                        'marianne-red': '#e1000f',
                        'marianne-white': '#ffffff',
                        'secondary-blue': '#6a6af4',
                        'light-gray': '#f5f5fe',
                        'dark-gray': '#1e1e1e'
                    },
                    fontFamily: {
                        'display': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'fade-in': 'fadeIn 1s ease-out',
                        'scale-in': 'scaleIn 0.6s ease-out',
                        'glow': 'glow 2s ease-in-out infinite alternate'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 145, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 145, 0.2);
            border-color: rgba(0, 0, 145, 0.3);
            background: rgba(255, 255, 255, 0.35);
        }

        .hero-bg {
            background: linear-gradient(135deg, #000091 0%, #6a6af4 25%, #000091 50%, #6a6af4 75%, #000091 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .floating-element {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-20px) rotate(5deg);
            }

            66% {
                transform: translateY(-10px) rotate(-3deg);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 20px rgba(0, 0, 145, 0.3);
            }

            to {
                box-shadow: 0 0 30px rgba(0, 0, 145, 0.6);
            }
        }

        .team-avatar {
            background: linear-gradient(135deg, #000091, #6a6af4);
            position: relative;
            overflow: hidden;
        }

        .team-avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s;
            opacity: 0;
        }

        .glass-card:hover .team-avatar::before {
            opacity: 1;
            animation: shine 1.5s ease-in-out;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #000091, #6a6af4, #000091, transparent);
            margin: 4rem 0;
        }

        .btn-modern {
            background: linear-gradient(135deg, #000091 0%, #6a6af4 100%);
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 145, 0.4);
        }

        .btn-accent {
            background: linear-gradient(135deg, #e1000f 0%, #ff1a2e 100%);
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-accent:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(225, 0, 15, 0.4);
        }

        .marianne-band {
            height: 8px;
            background: linear-gradient(to right, #000091 33.33%, #ffffff 33.33%, #ffffff 66.66%, #e1000f 66.66%);
            width: 100%;
        }

        .text-gradient {
            background: linear-gradient(135deg, #000091, #6a6af4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .parallax-bg {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .value-icon {
            transition: all 0.4s ease;
        }

        .value-card:hover .value-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stats-counter {
            font-size: 3rem;
            font-weight: 800;
            color: #000091;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <?php include '../Inc/Components/nav.php'; ?>

    <div class="marianne-band"></div>

    <!-- Hero Section -->
    <section class="hero-bg text-white py-24 relative overflow-hidden">
        <!-- Floating Elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="floating-element absolute top-20 left-10 text-6xl">
                <i class="fas fa-users"></i>
            </div>
            <div class="floating-element absolute top-32 right-20 text-4xl" style="animation-delay: -2s;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="floating-element absolute bottom-20 left-1/4 text-5xl" style="animation-delay: -4s;">
                <i class="fas fa-heart"></i>
            </div>
            <div class="floating-element absolute top-1/2 right-1/4 text-3xl" style="animation-delay: -6s;">
                <i class="fas fa-star"></i>
            </div>
        </div>

        <!-- Geometric Shapes -->
        <div class="absolute inset-0 overflow-hidden">
            <div
                class="absolute top-0 left-0 w-72 h-72 bg-white opacity-5 rounded-full -translate-x-1/2 -translate-y-1/2">
            </div>
            <div
                class="absolute bottom-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full translate-x-1/2 translate-y-1/2">
            </div>
        </div>

        <div class="container mx-auto px-6 text-center relative z-10">
            <div class="fade-in-up">
                <h1 class="text-6xl md:text-7xl font-black mb-6 tracking-tight">
                    Notre <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-200">Équipe</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-4xl mx-auto font-light leading-relaxed">
                    Une équipe d'experts passionnés, dédiée à la protection et au bien-être de tous les citoyens
                    français
                </p>
                <div class="w-32 h-1 bg-gradient-to-r from-transparent via-white to-transparent mx-auto mb-8"></div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-8 max-w-2xl mx-auto mt-12">
                    <div class="fade-in-up" style="animation-delay: 0.2s;">
                        <div class="stats-counter text-white">6</div>
                        <p class="text-blue-200 font-medium">Experts</p>
                    </div>
                    <div class="fade-in-up" style="animation-delay: 0.4s;">
                        <div class="stats-counter text-white">24/7</div>
                        <p class="text-blue-200 font-medium">Support</p>
                    </div>
                    <div class="fade-in-up" style="animation-delay: 0.6s;">
                        <div class="stats-counter text-white">100%</div>
                        <p class="text-blue-200 font-medium">Dévoués</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-24 relative">
        <div class="container mx-auto px-6">
            <!-- Direction -->
            <div class="mb-24 fade-in-up">
                <div class="text-center mb-16">
                    <h2 class="text-5xl font-black text-gradient mb-4">
                        Direction
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light">
                        Leadership visionnaire pour guider Signal France vers l'excellence et l'innovation
                    </p>
                </div>

                <div class="flex justify-center">
                    <div class="glass-card rounded-3xl p-10 max-w-lg text-center">
                        <div
                            class="team-avatar w-40 h-40 rounded-full mx-auto mb-8 flex items-center justify-center relative">
                            <i class="fas fa-crown text-5xl text-white relative z-10"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-marianne-blue mb-3">Directeur Général</h3>
                        <p class="text-marianne-red font-semibold text-lg mb-4">Jean Dupont</p>
                        <p class="text-gray-600 leading-relaxed text-lg">
                            Visionnaire et stratège, il dirige Signal France avec passion et détermination pour créer un
                            environnement numérique plus sûr pour tous.
                        </p>
                        <div class="mt-6 flex justify-center space-x-4">
                            <div class="w-3 h-3 bg-marianne-blue rounded-full animate-pulse"></div>
                            <div class="w-3 h-3 bg-marianne-red rounded-full animate-pulse"
                                style="animation-delay: 0.2s;"></div>
                            <div class="w-3 h-3 bg-secondary-blue rounded-full animate-pulse"
                                style="animation-delay: 0.4s;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-divider"></div>

            <!-- Technical Team -->
            <div class="mb-24 fade-in-up">
                <div class="text-center mb-16">
                    <h2 class="text-5xl font-black text-gradient mb-4">
                        Équipe Technique
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light">
                        Innovation et excellence technique au cœur de nos solutions digitales
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <div class="glass-card rounded-3xl p-8 text-center fade-in-up" style="animation-delay: 0.1s;">
                        <div class="team-avatar w-28 h-28 rounded-full mx-auto mb-6 flex items-center justify-center">
                            <i class="fas fa-laptop-code text-3xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-marianne-blue mb-2">Développeur Principal</h3>
                        <p class="text-marianne-red font-semibold mb-4">Luca / Tsubasa</p>
                        <p class="text-gray-600 leading-relaxed">
                            Expert en développement full-stack, architecte de nos solutions innovantes et performantes.
                        </p>
                    </div>

                </div>
            </div>

            <div class="section-divider"></div>

            <!-- Support Team -->
            <div class="mb-24 fade-in-up">
                <div class="text-center mb-16">
                    <h2 class="text-5xl font-black text-gradient mb-4">
                        Support & Modération
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light">
                        Accompagnement bienveillant et protection de notre communauté
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-10 max-w-4xl mx-auto">
                    <div class="glass-card rounded-3xl p-8 text-center fade-in-up" style="animation-delay: 0.1s;">
                        <div class="team-avatar w-28 h-28 rounded-full mx-auto mb-6 flex items-center justify-center">
                            <i class="fas fa-headset text-3xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-marianne-blue mb-2">Responsable Support</h3>
                        <p class="text-marianne-red font-semibold mb-4">Marie Dubois</p>
                        <p class="text-gray-600 leading-relaxed">
                            Dédiée à l'assistance personnalisée et à la satisfaction complète de nos utilisateurs.
                        </p>
                    </div>

                    <div class="glass-card rounded-3xl p-8 text-center fade-in-up" style="animation-delay: 0.2s;">
                        <div class="team-avatar w-28 h-28 rounded-full mx-auto mb-6 flex items-center justify-center">
                            <i class="fas fa-shield-alt text-3xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-marianne-blue mb-2">Modérateur</h3>
                        <p class="text-marianne-red font-semibold mb-4">Lucas Moreau</p>
                        <p class="text-gray-600 leading-relaxed">
                            Veille constante pour maintenir un environnement sain, respectueux et sécurisé.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="bg-gradient-to-br from-slate-50 to-blue-50 py-24">
        <div class="container mx-auto px-6">
            <div class="text-center mb-20 fade-in-up">
                <h2 class="text-5xl font-black text-gradient mb-6">
                    Nos Valeurs
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto font-light">
                    Les principes fondamentaux qui guident chacune de nos actions quotidiennes
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <div class="value-card text-center fade-in-up" style="animation-delay: 0.1s;">
                    <div
                        class="value-icon w-24 h-24 bg-gradient-to-br from-marianne-blue to-secondary-blue rounded-2xl mx-auto mb-8 flex items-center justify-center">
                        <i class="fas fa-shield-alt text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-marianne-blue mb-4">Sécurité</h3>
                    <p class="text-gray-600 leading-relaxed text-lg">
                        Protection absolue et confidentialité garantie des données de nos utilisateurs
                    </p>
                </div>

                <div class="value-card text-center fade-in-up" style="animation-delay: 0.2s;">
                    <div
                        class="value-icon w-24 h-24 bg-gradient-to-br from-marianne-red to-red-500 rounded-2xl mx-auto mb-8 flex items-center justify-center">
                        <i class="fas fa-heart text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-marianne-blue mb-4">Bienveillance</h3>
                    <p class="text-gray-600 leading-relaxed text-lg">
                        Accompagnement empathique et respectueux de chaque membre de notre communauté
                    </p>
                </div>

                <div class="value-card text-center fade-in-up" style="animation-delay: 0.3s;">
                    <div
                        class="value-icon w-24 h-24 bg-gradient-to-br from-marianne-blue to-secondary-blue rounded-2xl mx-auto mb-8 flex items-center justify-center">
                        <i class="fas fa-rocket text-3xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-marianne-blue mb-4">Innovation</h3>
                    <p class="text-gray-600 leading-relaxed text-lg">
                        Amélioration continue et innovation constante de nos services et technologies
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="hero-bg py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <div class="fade-in-up">
                <h2 class="text-5xl font-black text-white mb-8">
                    Rejoignez l'Aventure
                </h2>
                <p class="text-xl text-blue-100 mb-12 max-w-3xl mx-auto font-light">
                    Vous partagez nos valeurs ? Vous souhaitez contribuer à un internet plus sûr et plus respectueux ?
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <button
                        class="btn-modern text-white px-10 py-5 rounded-full font-semibold text-lg inline-flex items-center justify-center">
                        <i class="fas fa-envelope mr-3"></i>
                        Nous Contacter
                    </button>
                    <button
                        class="btn-accent text-white px-10 py-5 rounded-full font-semibold text-lg inline-flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-3"></i>
                        Candidater
                    </button>
                </div>
            </div>
        </div>
    </section>



    <div id="notification-container" class="fixed top-4 right-4 z-50"></div>

    <script>
        // Enhanced scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 100);
                }
            });
        }, observerOptions);

        // Counter animation
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 20);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Fade in animations
            const fadeElements = document.querySelectorAll('.fade-in-up');
            fadeElements.forEach(el => observer.observe(el));

            // Counter animations
            const counters = document.querySelectorAll('.stats-counter');
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target.textContent;
                        if (target === '6') animateCounter(entry.target, 6);
                        else if (target === '24/7') entry.target.textContent = '24/7';
                        else if (target === '100%') entry.target.textContent = '100%';
                    }
                });
            });

            counters.forEach(counter => counterObserver.observe(counter));

            // Smooth scroll for buttons
            document.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', (e) => {
                    if (button.textContent.includes('Contacter')) {
                        window.location.href = 'contact.php';
                    } else if (button.textContent.includes('Candidater')) {
                        window.location.href = 'mailto:recrutement@signalfrance.fr';
                    }
                });
            });
        });
    </script>


    <?php require_once '../Inc/Components/footers.php'; ?>
    <?php require_once '../Inc/Components/footer.php'; ?>
    <?php require_once '../Inc/Traitement/create_log.php'; ?>