<?php include_once('Inc/Components/header.php'); ?>
<?php include_once('Inc/Components/nav.php'); ?>

<?php include('Inc/Constants/db.php');

try {
$conn = connect_db();
if (!$conn) {
    throw new Exception('Impossible de se connecter à la base de données');
}

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $conn = connect_db();
    $req = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $req->execute([$_SESSION['user_id']]);
    $dataUser = $req->fetch(PDO::FETCH_ASSOC);
    
    if ($dataUser) {
        $user = $dataUser;
        $id = $dataUser['id'];
        $username = $dataUser['username'];
        $email = $dataUser['email'];
        $role = $dataUser['role'];
        $avatar = $dataUser['avatar'];
        $organization = $dataUser['organization'] ?? '';
        $accreditation = $dataUser['accreditation'] ?? '';
        $phone = $dataUser['phone'] ?? '';
        $address = $dataUser['address'] ?? '';
        $city = $dataUser['city'] ?? '';
        $bio = $dataUser['bio'] ?? '';
        $created_at = $dataUser['created_at'];
        $last_activity = $dataUser['last_activity'];
        $github = $dataUser['github']?? '';
        $linkedin = $dataUser['linkedin']?? '';
        $website = $dataUser['website']?? '';
    }
} else if (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
    $cookieToken = $_COOKIE['user_token'];
    if (strlen($cookieToken) >= 32) {
        $conn = connect_db();
        $hashedToken = hash('sha256', $cookieToken);
        $req = $conn->prepare("SELECT * FROM users WHERE token = ? AND (token_expiry IS NULL OR token_expiry > datetime('now'))");
        $req->execute([$hashedToken]);
        $dataUser = $req->fetch(PDO::FETCH_ASSOC);
        
        if ($dataUser) {
            // Token valide, restaurer la session
            session_start();
            $_SESSION['user_id'] = $dataUser['id'];
            $_SESSION['user_email'] = $dataUser['email'];
            $_SESSION['user_username'] = $dataUser['username'];
            $_SESSION['user_role'] = $dataUser['role'];
            $_SESSION['user_avatar'] = $dataUser['avatar'];
            
            $user = $dataUser;
            $id = $dataUser['id'];
            $username = $dataUser['username'];
            $email = $dataUser['email'];
            $role = $dataUser['role'];
            $avatar = $dataUser['avatar'];
            $organization = $dataUser['organization'] ?? '';
            $accreditation = $dataUser['accreditation'] ?? '';
            $phone = $dataUser['phone'] ?? '';
            $address = $dataUser['address'] ?? '';
            $city = $dataUser['city'] ?? '';
            $bio = $dataUser['bio'] ?? '';
            $created_at = $dataUser['created_at'];
            $last_activity = $dataUser['last_activity'];
            $github = $dataUser['github']?? '';
            $linkedin = $dataUser['linkedin']?? '';
            $website = $dataUser['website']?? '';
            $token = $cookieToken;
        } else {
            setcookie('user_token', '', time() - 3600, '/');
            setcookie('user_pseudo', '', time() - 3600, '/');
        }
    }
}
} catch (Exception $e) {
$_SESSION['error'] = $e->getMessage();
exit;
}

;?>

<main class="flex-grow">
    <!-- Section Hero modernisée -->
    <section class="relative bg-gradient-to-br from-france-blue via-blue-800 to-indigo-900 text-white py-24 px-5 overflow-hidden">
        <!-- Éléments décoratifs -->
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-france-blue via-white to-france-red"></div>
        
        <!-- Particules flottantes -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white bg-opacity-30 rounded-full animate-pulse"></div>
            <div class="absolute top-3/4 right-1/4 w-3 h-3 bg-white bg-opacity-20 rounded-full animate-bounce"></div>
            <div class="absolute top-1/2 right-1/3 w-1 h-1 bg-white bg-opacity-40 rounded-full animate-ping"></div>
        </div>
        
        <div class="max-w-7xl mx-auto text-center relative z-10">
            <div class="mb-8">
                <span class="inline-block bg-white bg-opacity-20 text-white px-4 py-2 rounded-full text-sm font-medium mb-6 backdrop-blur-sm">
                    🇫🇷 Service Public Numérique
                </span>
            </div>
            
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-8 drop-shadow-2xl">
                <span class="bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                    Signale France
                </span>
            </h1>
            
            <p class="text-xl md:text-2xl max-w-4xl mx-auto mb-12 leading-relaxed drop-shadow-lg font-light">
                Le système national d'alerte et d'information pour la sécurité des citoyens.
                <span class="block mt-2 text-lg opacity-90">Restez informé, restez en sécurité.</span>
            </p>
            
            <!-- Statistiques en temps réel -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 max-w-4xl mx-auto">
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                    <div class="text-3xl font-bold text-white">24/7</div>
                    <div class="text-sm text-blue-100">Surveillance continue</div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                    <div class="text-3xl font-bold text-white">100%</div>
                    <div class="text-sm text-blue-100">Territoire couvert</div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                    <div class="text-3xl font-bold text-white">< 2min</div>
                    <div class="text-sm text-blue-100">Temps de réponse</div>
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row justify-center gap-6">
                <?php if (isset($user)) { ?>
                    <button onclick="window.location.href='Pages/search.php'" 
                            class="group bg-white text-france-blue px-8 py-4 text-lg font-semibold rounded-xl hover:bg-gray-50 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 min-w-64 flex items-center justify-center gap-3">
                        <i class="fas fa-search group-hover:scale-110 transition-transform"></i>
                        Rechercher une personne
                    </button>
                    
                    <button onclick="window.location.href='Pages/report.php'" 
                            class="group bg-france-red text-white px-8 py-4 text-lg font-semibold rounded-xl hover:bg-red-700 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 min-w-64 flex items-center justify-center gap-3">
                        <i class="fas fa-exclamation-triangle group-hover:scale-110 transition-transform"></i>
                        Signaler un incident
                    </button>
                    
                    <?php if ($user['role'] === 'journaliste') { ?>
                        <button onclick="window.location.href='Pages/create_post.php'" 
                                class="group bg-gradient-to-r from-purple-600 to-purple-700 text-white px-8 py-4 text-lg font-semibold rounded-xl hover:from-purple-700 hover:to-purple-800 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 min-w-64 flex items-center justify-center gap-3">
                            <i class="fas fa-edit group-hover:scale-110 transition-transform"></i>
                            Publier un article
                        </button>
                    <?php } ?>
                <?php } else { ?>

                    <button onclick="window.location.href='Pages/search.php'" 
                            class="group bg-white text-france-blue px-8 py-4 text-lg font-semibold rounded-xl hover:bg-gray-50 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 min-w-64 flex items-center justify-center gap-3">
                        <i class="fas fa-search group-hover:scale-110 transition-transform"></i>
                        Rechercher une personne
                    </button>
                    
                    <button onclick="window.location.href='Pages/report.php'" 
                            class="group bg-france-red text-white px-8 py-4 text-lg font-semibold rounded-xl hover:bg-red-700 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 min-w-64 flex items-center justify-center gap-3">
                        <i class="fas fa-exclamation-triangle group-hover:scale-110 transition-transform"></i>
                        Signaler un incident
                    </button>
                    <button onclick="window.location.href='Pages/login.php'" 
                            class="group bg-white text-france-blue px-8 py-4 text-lg font-semibold rounded-xl hover:bg-gray-50 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 min-w-64 flex items-center justify-center gap-3">
                        <i class="fas fa-sign-in-alt group-hover:scale-110 transition-transform"></i>
                        Se connecter
                    </button>
                    
                <?php } ?>
            </div>
        </div>
    </section>
    
    <!-- Section Services -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Nos Services</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Des outils modernes pour votre sécurité et celle de vos proches</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-france-blue rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-search text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Recherche Avancée</h3>
                    <p class="text-gray-600 mb-6">Retrouvez rapidement des personnes grâce à notre système de recherche intelligent et sécurisé.</p>
                    <a href="#" class="text-france-blue font-semibold hover:underline flex items-center gap-2">
                        En savoir plus <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>
                
                <!-- Service 2 -->
                <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-france-red rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Signalement Rapide</h3>
                    <p class="text-gray-600 mb-6">Signalez immédiatement tout incident ou situation dangereuse aux autorités compétentes.</p>
                    <a href="#" class="text-france-blue font-semibold hover:underline flex items-center gap-2">
                        En savoir plus <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>
                
                <!-- Service 3 -->
                <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Sécurité Garantie</h3>
                    <p class="text-gray-600 mb-6">Vos données sont protégées par les plus hauts standards de sécurité gouvernementaux.</p>
                    <a href="#" class="text-france-blue font-semibold hover:underline flex items-center gap-2">
                        En savoir plus <i class="fas fa-arrow-right text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Actualités -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Actualités & Alertes</h2>
                <p class="text-xl text-gray-600">Restez informé des dernières informations de sécurité</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Article 1 -->
                <article class="group bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="h-48 bg-gradient-to-br from-france-blue to-blue-600 flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-4xl"></i>
                    </div>
                    <div class="p-6">
                        <span class="inline-block bg-france-blue text-white px-3 py-1 rounded-full text-sm font-medium mb-3">Alerte</span>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-france-blue transition-colors">Mise à jour du système</h3>
                        <p class="text-gray-600 mb-4">Nouvelles fonctionnalités de sécurité disponibles...</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Il y a 2 heures</span>
                            <a href="#" class="text-france-blue font-semibold hover:underline">Lire plus</a>
                        </div>
                    </div>
                </article>
                
                <!-- Article 2 -->
                <article class="group bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="h-48 bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                        <i class="fas fa-check-circle text-white text-4xl"></i>
                    </div>
                    <div class="p-6">
                        <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium mb-3">Info</span>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-france-blue transition-colors">Maintenance programmée</h3>
                        <p class="text-gray-600 mb-4">Intervention technique prévue ce weekend...</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Il y a 1 jour</span>
                            <a href="#" class="text-france-blue font-semibold hover:underline">Lire plus</a>
                        </div>
                    </div>
                </article>
           
            </div>
        </div>
    </section>
    
    <!-- Section CTA -->
    <section class="py-20 bg-gradient-to-r from-france-blue to-blue-800 text-white">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold mb-6">Rejoignez la communauté Signale France</h2>
            <p class="text-xl mb-8 opacity-90">Ensemble, construisons un environnement plus sûr pour tous</p>

            <?php  if (isset($user)) { ?>
                <button onclick="window.location.href='Pages/profile.php'"
                        class="bg-white text-france-blue px-8 py-4 text-lg font-semibold rounded-xl hover:bg-gray-100 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                    Accéder à mon profil
                </button>
            <?php } ?>
        </div>
    </section>
</main>

<?php include_once('Inc/Components/footers.php'); ?>
<?php include_once('Inc/Components/footer.php'); ?>
<?php include('Inc/Traitement/create_log.php'); ?>