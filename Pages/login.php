<?php include_once('../Inc/Components/header.php'); ?>
<?php include_once('../Inc/Components/nav.php'); ?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo et titre -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-blue-900 rounded-full flex items-center justify-center mb-6 shadow-lg">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Accès Professionnel</h2>
            <p class="text-gray-600">Plateforme sécurisée - Signale France</p>
        </div>
        
        <!-- Formulaire de connexion -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200">
            <div class="px-8 py-8">
                <form class="space-y-6" method="POST" action="../Inc/Traitement/traitement_co.php">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-blue-900"></i>Adresse email professionnelle
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required 
                               placeholder="votre.email@entreprise.com"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-blue-900"></i>Mot de passe
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   placeholder="••••••••••••"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-900 transition-colors">
                                <i id="toggleIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Options supplémentaires -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" 
                                   name="remember-me" 
                                   type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                                Se souvenir de moi
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="text-blue-600 hover:text-blue-800 transition-colors">
                                Mot de passe oublié ?
                            </a>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt group-hover:text-blue-100 transition-colors"></i>
                        </span>
                        Accéder à la plateforme
                    </button>
                </form>
            </div>
            
            <!-- Informations de sécurité -->
            <div class="px-8 py-4 bg-gray-50 rounded-b-2xl border-t border-gray-200">
                <div class="flex items-center justify-center space-x-4 text-xs text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt mr-1 text-green-600"></i>
                        <span>Connexion sécurisée</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-1 text-blue-600"></i>
                        <span>Session 8h</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-user-tie mr-1 text-purple-600"></i>
                        <span>Accès pro uniquement</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Message d'information -->
        <div class="text-center">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    Cette plateforme est réservée aux professionnels autorisés.
                    <br>
                    Pour toute assistance, contactez votre administrateur système.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Script pour toggle password -->
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
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

// Animation d'entrée
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.bg-white.rounded-2xl');
    form.style.opacity = '0';
    form.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        form.style.transition = 'all 0.6s ease-out';
        form.style.opacity = '1';
        form.style.transform = 'translateY(0)';
    }, 100);
});
</script>

<?php include_once('../Inc/Components/footers.php'); ?>
<?php include_once('../Inc/Components/footer.php'); ?>