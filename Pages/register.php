<?php
include_once('../Inc/Components/header.php');
include_once('../Inc/Components/nav.php');
?>

<!-- Fond avec gradient aux couleurs E Conscience -->
<div class="min-h-screen relative overflow-hidden">
    <!-- Gradient de fond officiel E Conscience -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 animate-gradient-x"></div>

    <!-- Particules flottantes aux couleurs officielles -->
    <div class="absolute inset-0">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white/20 rounded-full animate-float"></div>
        <div class="absolute top-1/3 right-1/3 w-1 h-1 bg-blue-300/30 rounded-full animate-float-delayed"></div>
        <div class="absolute bottom-1/4 left-1/3 w-3 h-3 bg-red-300/20 rounded-full animate-float-slow"></div>
        <div class="absolute top-1/2 right-1/4 w-1.5 h-1.5 bg-blue-400/25 rounded-full animate-float"></div>
        <div class="absolute bottom-1/3 right-1/2 w-2.5 h-2.5 bg-white/15 rounded-full animate-float-delayed"></div>
    </div>

    <!-- Overlay tricolore subtil -->
    <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-white/5 to-red-600/10"></div>

    <!-- Contenu principal -->
    <div class="relative z-10 flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo et titre E Conscience -->
            <div class="text-center transform transition-all duration-1000 ease-out">
                <div
                    class="mx-auto h-20 w-20 bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl flex items-center justify-center mb-6 shadow-2xl transform hover:scale-110 transition-all duration-300 hover:rotate-3">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                </div>
                <h2
                    class="text-4xl font-bold text-white mb-3 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                    Rejoindre E Conscience
                </h2>
                <p class="text-blue-100 text-lg font-medium">Créer votre compte sécurisé</p>
                <div class="mt-4 flex justify-center space-x-2">
                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
                    <div class="w-2 h-2 bg-white rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                    <div class="w-2 h-2 bg-red-600 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                </div>
            </div>

            <!-- Formulaire d'inscription avec glassmorphism -->
            <div
                class="backdrop-blur-xl bg-white/10 rounded-3xl shadow-2xl border border-white/20 overflow-hidden transform transition-all duration-700 ease-out hover:scale-105">
                <!-- Header du formulaire -->
                <div class="px-8 py-6 bg-gradient-to-r from-blue-600/20 to-blue-700/10 border-b border-white/20">
                    <h3 class="text-xl font-semibold text-white text-center flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Inscription
                    </h3>
                </div>

                <div class="px-8 py-8">
                    <form class="space-y-6" method="POST" action="../Inc/Traitement/register.php">
                        <div class="space-y-1">
                            <label for="username" class="block text-sm font-medium text-white/90 mb-2">
                                <i class="fas fa-user mr-2 text-blue-300"></i>Nom d'utilisateur
                            </label>
                            <div class="relative group">
                                <input type="text" id="username" name="username" required placeholder="Votre pseudo"
                                    class="w-full px-4 py-4 bg-white/10 border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all duration-300 backdrop-blur-sm group-hover:bg-white/15">
                                <div
                                    class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-600/20 to-blue-700/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label for="email" class="block text-sm font-medium text-white/90 mb-2">
                                <i class="fas fa-envelope mr-2 text-blue-300"></i>Adresse email
                            </label>
                            <div class="relative group">
                                <input type="email" id="email" name="email" required
                                    placeholder="votre.email@exemple.com"
                                    class="w-full px-4 py-4 bg-white/10 border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all duration-300 backdrop-blur-sm group-hover:bg-white/15">
                                <div
                                    class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-600/20 to-blue-700/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label for="password" class="block text-sm font-medium text-white/90 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-400"></i>Mot de passe
                            </label>
                            <div class="relative group">
                                <input type="password" id="password" name="password" required placeholder="••••••••••••"
                                    class="w-full px-4 py-4 bg-white/10 border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all duration-300 backdrop-blur-sm group-hover:bg-white/15">
                                <button type="button" onclick="togglePassword('password', 'toggleIcon')"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/60 hover:text-white transition-colors duration-200">
                                    <i id="toggleIcon" class="fas fa-eye"></i>
                                </button>
                                <div
                                    class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-600/20 to-blue-700/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label for="confirm_password" class="block text-sm font-medium text-white/90 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-400"></i>Confirmer le mot de passe
                            </label>
                            <div class="relative group">
                                <input type="password" id="confirm_password" name="confirm_password" required
                                    placeholder="••••••••••••"
                                    class="w-full px-4 py-4 bg-white/10 border border-white/30 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all duration-300 backdrop-blur-sm group-hover:bg-white/15">
                                <button type="button" onclick="togglePassword('confirm_password', 'toggleConfirmIcon')"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/60 hover:text-white transition-colors duration-200">
                                    <i id="toggleConfirmIcon" class="fas fa-eye"></i>
                                </button>
                                <div
                                    class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-600/20 to-blue-700/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                </div>
                            </div>
                        </div>

                        <div class="text-sm text-center">
                            <span class="text-white/80">Déjà inscrit ?</span>
                            <a href="login.php"
                                class="ml-1 text-blue-300 hover:text-blue-100 transition-colors duration-200 hover:underline font-medium">
                                Connectez-vous ici
                            </a>
                        </div>

                        <button type="submit"
                            class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-4">
                                <i class="fas fa-user-plus group-hover:rotate-12 transition-transform duration-300"></i>
                            </span>
                            <span class="relative">
                                Créer mon compte
                                <div
                                    class="absolute inset-0 bg-white/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                </div>
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Message d'information aux couleurs E Conscience -->
            <div class="text-center transform transition-all duration-1000 ease-out">
                <div class="backdrop-blur-lg bg-white/10 border border-white/20 rounded-2xl p-6 shadow-xl">
                    <div class="flex items-center justify-center mb-3">
                        <div
                            class="w-8 h-8 bg-gradient-to-r from-blue-600 to-blue-700 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-shield-alt text-white text-sm"></i>
                        </div>
                        <h4 class="text-white font-semibold">Inscription sécurisée</h4>
                    </div>
                    <p class="text-white/80 text-sm leading-relaxed">
                        En créant un compte, vous acceptez nos <a href="cgu.php"
                            class="text-blue-300 hover:underline">Conditions Générales d'Utilisation</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles CSS personnalisés aux couleurs E Conscience -->
<style>
    @keyframes gradient-x {

        0%,
        100% {
            background-size: 200% 200%;
            background-position: left center;
        }

        50% {
            background-size: 200% 200%;
            background-position: right center;
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-20px) rotate(180deg);
        }
    }

    @keyframes float-delayed {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-15px) rotate(-180deg);
        }
    }

    @keyframes float-slow {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-10px) rotate(90deg);
        }
    }

    .animate-gradient-x {
        animation: gradient-x 15s ease infinite;
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: float-delayed 8s ease-in-out infinite;
    }

    .animate-float-slow {
        animation: float-slow 10s ease-in-out infinite;
    }

    /* Animation d'entrée en cascade */
    .space-y-8>* {
        opacity: 0;
        transform: translateY(20px);
        animation: slideInUp 0.8s ease-out forwards;
    }

    .space-y-8>*:nth-child(1) {
        animation-delay: 0.1s;
    }

    .space-y-8>*:nth-child(2) {
        animation-delay: 0.3s;
    }

    .space-y-8>*:nth-child(3) {
        animation-delay: 0.5s;
    }

    @keyframes slideInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- JavaScript pour les interactions -->
<script>
    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Animation d'entrée au chargement
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        form.style.opacity = '0';
        form.style.transform = 'translateY(20px)';
        setTimeout(() => {
            form.style.transition = 'all 0.8s ease-out';
            form.style.opacity = '1';
            form.style.transform = 'translateY(0)';
        }, 200);
    });
</script>

<?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>