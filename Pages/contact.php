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
    <?php include '../Inc/Components/header.php'; ?>
    <?php include '../Inc/Components/nav.php'; ?>

    <div class="min-h-screen py-12">
        <div class="container mx-auto px-4">
            <!-- En-tête -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-envelope text-blue-600 mr-3"></i>
                    Contactez-nous
                </h1>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Nous sommes là pour vous aider. N'hésitez pas à nous contacter pour toute question, suggestion ou problème technique.
                </p>
            </div>

            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Informations de contact -->
                    <div class="lg:col-span-1">
                        <div class="space-y-6">
                            <!-- Carte Support -->
                            <div class="contact-card bg-white rounded-xl shadow-lg p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-headset text-blue-600 text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-800">Support Technique</h3>
                                </div>
                                <p class="text-gray-600 mb-4">Notre équipe technique est disponible pour vous aider.</p>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm text-gray-700">
                                        <i class="fas fa-envelope mr-3 text-blue-600"></i>
                                        <span>support@signale-france.fr</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-700">
                                        <i class="fas fa-clock mr-3 text-blue-600"></i>
                                        <span>Lun-Ven: 9h-18h</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Carte Urgence -->
                            <div class="contact-card bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-exclamation-triangle text-white text-xl pulse-animation"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold">Urgence</h3>
                                </div>
                                <p class="mb-4 opacity-90">Pour les situations d'urgence nécessitant une intervention immédiate.</p>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-phone mr-3"></i>
                                        <span>📞 15 (SAMU) - 17 (Police) - 18 (Pompiers)</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-mobile-alt mr-3"></i>
                                        <span>📱 112 (Numéro d'urgence européen)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Carte Anonymat -->
                            <div class="contact-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-user-secret text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold">Contact Anonyme</h3>
                                </div>
                                <p class="mb-4 opacity-90">Vous pouvez nous contacter de manière totalement anonyme.</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-check mr-2"></i>
                                        <span>Aucune donnée personnelle conservée</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-check mr-2"></i>
                                        <span>Email facultatif</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-check mr-2"></i>
                                        <span>Confidentialité garantie</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Réseaux sociaux -->
                            <div class="contact-card bg-white rounded-xl shadow-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                    <i class="fas fa-share-alt mr-2 text-purple-600"></i>
                                    Suivez-nous
                                </h3>
                                <div class="flex space-x-4">
                                    <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white hover:bg-blue-700 transition-colors">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#" class="w-10 h-10 bg-blue-400 rounded-lg flex items-center justify-center text-white hover:bg-blue-500 transition-colors">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" class="w-10 h-10 bg-blue-800 rounded-lg flex items-center justify-center text-white hover:bg-blue-900 transition-colors">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                    <a href="#" class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center text-white hover:bg-red-700 transition-colors">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de contact -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <div class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                                    <i class="fas fa-paper-plane text-blue-600 mr-3"></i>
                                    Envoyez-nous un message
                                </h2>
                                <p class="text-gray-600">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</p>
                            </div>

                            <?php if ($message_sent): ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                        <div>
                                            <h4 class="text-green-800 font-semibold">Message envoyé avec succès !</h4>
                                            <p class="text-green-700 text-sm">Nous vous répondrons dans les plus brefs délais.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                                        <div>
                                            <h4 class="text-red-800 font-semibold">Erreur</h4>
                                            <p class="text-red-700 text-sm"><?php echo htmlspecialchars($error_message); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-6">
                                <!-- Option anonyme -->
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="anonyme" id="anonyme" class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2" <?php echo (isset($_POST['anonyme']) && $_POST['anonyme']) ? 'checked' : ''; ?>>
                                        <label for="anonyme" class="ml-3 text-sm font-medium text-purple-800">
                                            <i class="fas fa-user-secret mr-2"></i>
                                            Envoyer ce message de manière anonyme
                                        </label>
                                    </div>
                                    <div class="anonyme-notice mt-2 text-xs text-purple-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        En mode anonyme, votre nom sera masqué et votre email ne sera pas conservé.
                                    </div>
                                </div>

                                <!-- Type de demande -->
                                <div>
                                    <label for="type_demande" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-tag mr-2 text-blue-600"></i>
                                        Type de demande *
                                    </label>
                                    <select name="type_demande" id="type_demande" required class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Sélectionnez le type de demande</option>
                                        <option value="support_technique" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'support_technique') ? 'selected' : ''; ?>>Support technique</option>
                                        <option value="question_generale" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'question_generale') ? 'selected' : ''; ?>>Question générale</option>
                                        <option value="suggestion" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'suggestion') ? 'selected' : ''; ?>>Suggestion d'amélioration</option>
                                        <option value="signalement_probleme" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'signalement_probleme') ? 'selected' : ''; ?>>Signaler un problème</option>
                                        <option value="partenariat" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'partenariat') ? 'selected' : ''; ?>>Partenariat</option>
                                        <option value="autre" <?php echo (isset($_POST['type_demande']) && $_POST['type_demande'] === 'autre') ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>

                                <!-- Nom et Email -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-user mr-2 text-blue-600"></i>
                                            Nom complet *
                                        </label>
                                        <input type="text" name="nom" id="nom" required 
                                               value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>"
                                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="Votre nom complet">
                                        <p class="text-xs text-gray-500 mt-1 nom-notice">Ce nom sera masqué si vous choisissez l'option anonyme</p>
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-envelope mr-2 text-blue-600"></i>
                                            Adresse email <span class="text-gray-500">(facultatif)</span>
                                        </label>
                                        <input type="email" name="email" id="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="votre@email.com">
                                        <p class="text-xs text-gray-500 mt-1 email-notice">Nécessaire uniquement si vous souhaitez une réponse</p>
                                    </div>
                                </div>

                                <!-- Sujet -->
                                <div>
                                    <label for="sujet" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-heading mr-2 text-blue-600"></i>
                                        Sujet *
                                    </label>
                                    <input type="text" name="sujet" id="sujet" required 
                                           value="<?php echo htmlspecialchars($_POST['sujet'] ?? ''); ?>"
                                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                           placeholder="Résumé de votre demande">
                                </div>

                                <!-- Message -->
                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-comment-alt mr-2 text-blue-600"></i>
                                        Message *
                                    </label>
                                    <textarea name="message" id="message" rows="6" required 
                                              class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" 
                                              placeholder="Décrivez votre demande en détail..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Minimum 10 caractères</p>
                                </div>

                                <!-- Bouton d'envoi -->
                                <div class="flex items-center justify-between pt-4">
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Les champs marqués d'un * sont obligatoires
                                    </p>
                                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-300 transform hover:scale-105">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Envoyer le message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Section FAQ rapide -->
                <div class="mt-12">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                                <i class="fas fa-question-circle text-blue-600 mr-3"></i>
                                Questions fréquentes
                            </h2>
                            <p class="text-gray-600">Trouvez rapidement des réponses aux questions les plus courantes</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-blue-50 rounded-lg p-6">
                                <h3 class="font-semibold text-blue-800 mb-2">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Comment créer un signalement ?
                                </h3>
                                <p class="text-blue-700 text-sm mb-3">Cliquez sur "Créer un signalement" et remplissez le formulaire avec tous les détails nécessaires.</p>
                                <a href="guides.php" class="text-blue-600 text-sm font-medium hover:text-blue-800">
                                    Voir le guide complet →
                                </a>
                            </div>

                            <div class="bg-green-50 rounded-lg p-6">
                                <h3 class="font-semibold text-green-800 mb-2">
                                    <i class="fas fa-clock mr-2"></i>
                                    Délai de traitement ?
                                </h3>
                                <p class="text-green-700 text-sm mb-3">Les signalements sont généralement traités sous 24-48h selon leur priorité.</p>
                                <a href="faq.php" class="text-green-600 text-sm font-medium hover:text-green-800">
                                    En savoir plus →
                                </a>
                            </div>

                            <div class="bg-purple-50 rounded-lg p-6">
                                <h3 class="font-semibold text-purple-800 mb-2">
                                    <i class="fas fa-eye-slash mr-2"></i>
                                    Contact anonyme ?
                                </h3>
                                <p class="text-purple-700 text-sm mb-3">Oui, vous pouvez nous contacter de manière totalement anonyme en cochant l'option correspondante.</p>
                                <a href="faq.php" class="text-purple-600 text-sm font-medium hover:text-purple-800">
                                    Plus d'infos →
                                </a>
                            </div>
                        </div>

                        <div class="text-center mt-8">
                            <a href="faq.php" class="inline-flex items-center bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                <i class="fas fa-list mr-2"></i>
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
        // Gestion de l'option anonyme
        const anonymeCheckbox = document.getElementById('anonyme');
        const anonymeNotice = document.querySelector('.anonyme-notice');
        const nomNotice = document.querySelector('.nom-notice');
        const emailNotice = document.querySelector('.email-notice');
        const emailInput = document.getElementById('email');
        
        function toggleAnonymeMode() {
            if (anonymeCheckbox.checked) {
                anonymeNotice.classList.add('show');
                nomNotice.textContent = 'Ce nom sera remplacé par "Utilisateur anonyme"';
                nomNotice.className = 'text-xs text-purple-600 mt-1 nom-notice';
                emailNotice.textContent = 'Cet email ne sera pas conservé en mode anonyme';
                emailNotice.className = 'text-xs text-purple-600 mt-1 email-notice';
                emailInput.placeholder = 'Ne sera pas conservé (mode anonyme)';
            } else {
                anonymeNotice.classList.remove('show');
                nomNotice.textContent = 'Ce nom sera visible par les administrateurs';
                nomNotice.className = 'text-xs text-gray-500 mt-1 nom-notice';
                emailNotice.textContent = 'Nécessaire uniquement si vous souhaitez une réponse';
                emailNotice.className = 'text-xs text-gray-500 mt-1 email-notice';
                emailInput.placeholder = 'votre@email.com';
            }
        }
        
        anonymeCheckbox.addEventListener('change', toggleAnonymeMode);
        
        // Initialiser l'état au chargement
        toggleAnonymeMode();

        // Animation au scroll
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

        // Observer tous les éléments avec animation
        document.querySelectorAll('.contact-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Validation en temps réel
        document.getElementById('message').addEventListener('input', function() {
            const minLength = 10;
            const currentLength = this.value.length;
            const helpText = this.nextElementSibling;
            
            if (currentLength < minLength) {
                helpText.textContent = `Minimum 10 caractères (${currentLength}/${minLength})`;
                helpText.className = 'text-xs text-red-500 mt-1';
            } else {
                helpText.textContent = `${currentLength} caractères`;
                helpText.className = 'text-xs text-green-500 mt-1';
            }
        });
    
    </script>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include_once('../Inc/Components/footer.php'); ?>