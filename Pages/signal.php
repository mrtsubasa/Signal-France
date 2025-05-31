<?php
session_start();
include_once('../Inc/Components/header.php');
include_once('../Inc/Components/nav.php');
include_once('../Inc/Constants/db.php');

// Initialize form variables with proper defaults
$titre = '';
$type_incident = '';
$contexte = '';
$plateforme = '';
$lieu = '';
$description = '';
$photo = '';
$preuve = '';
$email = '';
$priorite = '';
$anonyme = false;
$success_message = '';
$error_message = '';


try {
    $conn = connect_db();
    if (!$conn) {
        throw new Exception('Impossible de se connecter à la base de données');
    }
    
    // Get user ID if logged in
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $req = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $req->execute([$_SESSION['user_id']]);
        $dataUser = $req->fetch(PDO::FETCH_ASSOC);
        if ($dataUser) {
            $user = $dataUser;
            $id = $dataUser['id'];
            $auteur = $dataUser['username'];
        }
    } else {
        // For user not connected generate a random id 
        $id = uniqid();
        $auteur = 'Anonyme';
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validate and sanitize form inputs with proper null coalescing
            $titre = htmlspecialchars(trim($_POST['titre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $type_incident = htmlspecialchars(trim($_POST['type_incident'] ?? ''), ENT_QUOTES, 'UTF-8');
            $contexte = htmlspecialchars(trim($_POST['contexte'] ?? ''), ENT_QUOTES, 'UTF-8');
            $plateforme = htmlspecialchars(trim($_POST['plateforme'] ?? ''), ENT_QUOTES, 'UTF-8');
            $lieu = htmlspecialchars(trim($_POST['lieu'] ?? ''), ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
            $photo = htmlspecialchars(trim($_POST['photo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $preuve = htmlspecialchars(trim($_POST['preuve'] ?? ''), ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
            $priorite = htmlspecialchars(trim($_POST['priorite'] ?? 'normale'), ENT_QUOTES, 'UTF-8');
            $anonyme = isset($_POST['anonyme']) && $_POST['anonyme'] === 'on';
         
            // Validation
            if (empty($titre)) {
                throw new Exception('Le titre est obligatoire');
            }
            if (empty($type_incident)) {
                throw new Exception('Le type d\'incident est obligatoire');
            }
            if (empty($contexte)) {
                throw new Exception('Le contexte est obligatoire');
            }
            if (empty($description)) {
                throw new Exception('La description est obligatoire');
            }
            if (!$anonyme && empty($email)) {
                throw new Exception('L\'adresse email est obligatoire si vous n\'êtes pas anonyme');
            }
            if (empty($priorite)) {
                throw new Exception('La priorité est obligatoire');
            }

            // Insert into database - FIXED: Added missing $email parameter
            $req = $conn->prepare("INSERT INTO signalements (user_id, type_incident, titre, description, localisation, statut, priorite, anonyme, images, preuves, incident_context, email_contact, auteur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $req->execute([$id, $type_incident, $titre, $description, $lieu, 'en_attente', $priorite, $anonyme, $photo, $preuve, $contexte, $email, $auteur]);

            $success_message = "Votre signalement a été envoyé avec succès. Nous vous recontacterons dans les plus brefs délais.";
            
            // Reset form variables after successful submission
            $titre = $type_incident = $contexte = $plateforme = $lieu = $description = $photo = $preuve = $email = $priorite = '';
            $anonyme = false;
        
        } catch (Exception $e) {
            $error_message = "Erreur lors du traitement du formulaire: ". $e->getMessage();
        }
    }
} catch (Exception $e) {
    $error_message = "Erreur de connexion à la base de données: ". $e->getMessage();
}
?>

<main class="flex-grow">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Signaler un incident</h1>
                <p class="text-gray-600">Aidez-nous à améliorer la sécurité numérique en signalant les incidents</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-center">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-center">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- Main Form -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Marianne Decorative Bar -->
                <div class="h-2 bg-gradient-to-r from-blue-900 via-white to-red-600"></div>
                
                <form method="POST" action="signal.php" class="p-8 space-y-6">
                    <!-- Title -->
                    <div class="space-y-3">
                        <label for="titre" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-heading text-blue-900 mr-2"></i>Titre du signalement *
                        </label>
                        <input type="text" 
                               id="titre" 
                               name="titre" 
                               value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700" 
                               placeholder="Décrivez brièvement l'incident (ex: Tentative de phishing par email)" 
                               maxlength="150" 
                               minlength="10" 
                               required 
                               aria-describedby="titre-help">
                        <div id="titre-help" class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Entre 10 et 150 caractères. Soyez précis et concis.
                        </div>
                    </div>

                    <!-- Context Selection -->
                    <div class="space-y-4">
    <label class="block text-sm font-semibold text-gray-800 mb-3">
        <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Contexte de l'incident *
    </label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="relative cursor-pointer group">
            <input type="radio" name="contexte" value="irl" <?= $contexte === 'irl' ? 'checked' : '' ?> 
                   class="sr-only peer" required>
            <div class="flex items-center p-4 border-2 border-gray-200 rounded-lg transition-all duration-200 
                        peer-checked:border-blue-600 peer-checked:bg-blue-50 
                        hover:border-blue-400 hover:shadow-md group-hover:scale-[1.02]">
                <div class="flex items-center justify-center w-5 h-5 mr-3">
                    <div class="w-4 h-4 border-2 border-gray-400 rounded-full transition-all duration-200 
                                peer-checked:border-blue-600 peer-checked:bg-blue-600 
                                peer-checked:shadow-[inset_0_0_0_2px_white]"></div>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-users text-blue-600 mr-3 text-lg"></i>
                    <div>
                        <span class="text-sm font-medium text-gray-800">Dans la vraie vie</span>
                        <p class="text-xs text-gray-500 mt-1">Incident physique ou en personne</p>
                    </div>
                </div>
            </div>
        </label>
        
        <label class="relative cursor-pointer group">
            <input type="radio" name="contexte" value="virtuel" <?= $contexte === 'virtuel' ? 'checked' : '' ?> 
                   class="sr-only peer" required>
            <div class="flex items-center p-4 border-2 border-gray-200 rounded-lg transition-all duration-200 
                        peer-checked:border-blue-600 peer-checked:bg-blue-50 
                        hover:border-blue-400 hover:shadow-md group-hover:scale-[1.02]">
                <div class="flex items-center justify-center w-5 h-5 mr-3">
                    <div class="w-4 h-4 border-2 border-gray-400 rounded-full transition-all duration-200 
                                peer-checked:border-blue-600 peer-checked:bg-blue-600 
                                peer-checked:shadow-[inset_0_0_0_2px_white]"></div>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-globe text-blue-600 mr-3 text-lg"></i>
                    <div>
                        <span class="text-sm font-medium text-gray-800">En ligne/Virtuel</span>
                        <p class="text-xs text-gray-500 mt-1">Incident numérique ou sur internet</p>
                    </div>
                </div>
            </div>
        </label>
    </div>
</div>


                    <!-- Incident Type -->
                    <div class="space-y-3">
                        <label for="type_incident" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Type d'incident *
                        </label>
                        <select name="type_incident" id="type_incident" required
                                class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700">
                            <option value="">Sélectionnez un type d'incident</option>
                            
                            <!-- Options IRL -->
                            <optgroup label="Incidents dans la vraie vie (IRL)" id="irl-options" style="display: none;">
                                <option value="vol_identite" <?= $type_incident === 'vol_identite' ? 'selected' : '' ?>>Vol d'identité</option>
                                <option value="chantage" <?= $type_incident === 'chantage' ? 'selected' : '' ?>>Chantage</option>
                                <option value="harcelement_physique" <?= $type_incident === 'harcelement_physique' ? 'selected' : '' ?>>Harcèlement physique</option>
                                <option value="menaces" <?= $type_incident === 'menaces' ? 'selected' : '' ?>>Menaces</option>
                                <option value="fraude_bancaire" <?= $type_incident === 'fraude_bancaire' ? 'selected' : '' ?>>Fraude bancaire</option>
                                <option value="usurpation_physique" <?= $type_incident === 'usurpation_physique' ? 'selected' : '' ?>>Usurpation d'identité physique</option>
                                <option value="viol" <?= $type_incident === 'viol' ? 'selected' : '' ?>>Viol</option>
                                <option value="agression_sexuelle" <?= $type_incident === 'agression_sexuelle' ? 'selected' : '' ?>>Agression sexuelle</option>
                                <option value="violence_conjugale" <?= $type_incident === 'violence_conjugale' ? 'selected' : '' ?>>Violence conjugale</option>
                                <option value="violence_domestique" <?= $type_incident === 'violence_domestique' ? 'selected' : '' ?>>Violence domestique</option>
                                <option value="pedophilie" <?= $type_incident === 'pedophilie' ? 'selected' : '' ?>>Pédophilie</option>
                                <option value="exploitation_sexuelle" <?= $type_incident === 'exploitation_sexuelle' ? 'selected' : '' ?>>Exploitation sexuelle</option>
                                <option value="traite_humaine" <?= $type_incident === 'traite_humaine' ? 'selected' : '' ?>>Traite des êtres humains</option>
                                <option value="sequestration" <?= $type_incident === 'sequestration' ? 'selected' : '' ?>>Séquestration</option>
                                <option value="enlevement" <?= $type_incident === 'enlevement' ? 'selected' : '' ?>>Enlèvement</option>
                                <option value="torture" <?= $type_incident === 'torture' ? 'selected' : '' ?>>Torture</option>
                                <option value="mutilation" <?= $type_incident === 'mutilation' ? 'selected' : '' ?>>Mutilation</option>
                                <option value="tentative_meurtre" <?= $type_incident === 'tentative_meurtre' ? 'selected' : '' ?>>Tentative de meurtre</option>
                                <option value="autre_irl" <?= $type_incident === 'autre_irl' ? 'selected' : '' ?>>Autre incident IRL</option>
                            </optgroup>

                            <!-- Options Virtuelles -->
                            <optgroup label="Incidents virtuels/en ligne" id="virtuel-options" style="display: none;">
                                <option value="phishing" <?= $type_incident === 'phishing' ? 'selected' : '' ?>>Phishing/Hameçonnage</option>
                                <option value="malware" <?= $type_incident === 'malware' ? 'selected' : '' ?>>Malware/Virus</option>
                                <option value="usurpation_virtuelle" <?= $type_incident === 'usurpation_virtuelle' ? 'selected' : '' ?>>Usurpation d'identité en ligne</option>
                                <option value="arnaque_en_ligne" <?= $type_incident === 'arnaque_en_ligne' ? 'selected' : '' ?>>Arnaque en ligne</option>
                                <option value="piratage_compte" <?= $type_incident === 'piratage_compte' ? 'selected' : '' ?>>Piratage de compte</option>
                                <option value="harcelement_virtuel" <?= $type_incident === 'harcelement_virtuel' ? 'selected' : '' ?>>Harcèlement en ligne</option>
                                <option value="cyberbullying" <?= $type_incident === 'cyberbullying' ? 'selected' : '' ?>>Cyberharcèlement</option>
                                <option value="revenge_porn" <?= $type_incident === 'revenge_porn' ? 'selected' : '' ?>>Revenge porn</option>
                                <option value="sextorsion" <?= $type_incident === 'sextorsion' ? 'selected' : '' ?>>Sextorsion</option>
                                <option value="exploitation_sexuelle_en_ligne" <?= $type_incident === 'exploitation_sexuelle_en_ligne' ? 'selected' : '' ?>>Exploitation sexuelle en ligne</option>
                                <option value="pedopornographie" <?= $type_incident === 'pedopornographie' ? 'selected' : '' ?>>Pédopornographie</option>
                                <option value="grooming" <?= $type_incident === 'grooming' ? 'selected' : '' ?>>Grooming (prédation sexuelle)</option>
                                <option value="deepfake_sexuel" <?= $type_incident === 'deepfake_sexuel' ? 'selected' : '' ?>>Deepfake à caractère sexuel</option>
                                <option value="voyeurisme_numerique" <?= $type_incident === 'voyeurisme_numerique' ? 'selected' : '' ?>>Voyeurisme numérique</option>
                                <option value="diffusion_images_intimes" <?= $type_incident === 'diffusion_images_intimes' ? 'selected' : '' ?>>Diffusion d'images intimes</option>
                                <option value="deepfake" <?= $type_incident === 'deepfake' ? 'selected' : '' ?>>Deepfake</option>
                                <option value="doxxing" <?= $type_incident === 'doxxing' ? 'selected' : '' ?>>Doxxing</option>
                                <option value="ransomware" <?= $type_incident === 'ransomware' ? 'selected' : '' ?>>Ransomware</option>
                                <option value="autre_virtuel" <?= $type_incident === 'autre_virtuel' ? 'selected' : '' ?>>Autre incident virtuel</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Platform (conditional) -->
                    <div id="plateforme-section" class="space-y-3" style="display: <?= $contexte === 'virtuel' ? 'block' : 'none' ?>">
                        <label for="plateforme" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-globe text-blue-900 mr-2"></i>Plateforme concernée
                        </label>
                        <select name="plateforme" id="plateforme"
                                class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700">
                            <option value="">Sélectionnez une plateforme</option>
                            <optgroup label="Réseaux sociaux">
                                <option value="facebook" <?= $plateforme === 'facebook' ? 'selected' : '' ?>>Facebook</option>
                                <option value="instagram" <?= $plateforme === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                                <option value="twitter" <?= $plateforme === 'twitter' ? 'selected' : '' ?>>Twitter/X</option>
                                <option value="tiktok" <?= $plateforme === 'tiktok' ? 'selected' : '' ?>>TikTok</option>
                                <option value="snapchat" <?= $plateforme === 'snapchat' ? 'selected' : '' ?>>Snapchat</option>
                                <option value="linkedin" <?= $plateforme === 'linkedin' ? 'selected' : '' ?>>LinkedIn</option>
                                <option value="youtube" <?= $plateforme === 'youtube' ? 'selected' : '' ?>>YouTube</option>
                            </optgroup>
                            <optgroup label="Messagerie">
                                <option value="whatsapp" <?= $plateforme === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                                <option value="telegram" <?= $plateforme === 'telegram' ? 'selected' : '' ?>>Telegram</option>
                                <option value="discord" <?= $plateforme === 'discord' ? 'selected' : '' ?>>Discord</option>
                                <option value="signal" <?= $plateforme === 'signal' ? 'selected' : '' ?>>Signal</option>
                                <option value="messenger" <?= $plateforme === 'messenger' ? 'selected' : '' ?>>Messenger</option>
                            </optgroup>
                            <optgroup label="E-commerce">
                                <option value="leboncoin" <?= $plateforme === 'leboncoin' ? 'selected' : '' ?>>Leboncoin</option>
                                <option value="amazon" <?= $plateforme === 'amazon' ? 'selected' : '' ?>>Amazon</option>
                                <option value="ebay" <?= $plateforme === 'ebay' ? 'selected' : '' ?>>eBay</option>
                                <option value="vinted" <?= $plateforme === 'vinted' ? 'selected' : '' ?>>Vinted</option>
                            </optgroup>
                            <optgroup label="Autres">
                                <option value="email" <?= $plateforme === 'email' ? 'selected' : '' ?>>Email</option>
                                <option value="sms" <?= $plateforme === 'sms' ? 'selected' : '' ?>>SMS</option>
                                <option value="site_web" <?= $plateforme === 'site_web' ? 'selected' : '' ?>>Site web</option>
                                <option value="autre" <?= $plateforme === 'autre' ? 'selected' : '' ?>>Autre</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="space-y-3">
                        <label for="lieu" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-map-marker-alt text-blue-900 mr-2"></i>Lieu de l'incident
                        </label>
                        <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700" 
                               placeholder="Ville, région ou URL du site web">
                    </div>

                    <!-- Description -->
                    <div class="space-y-3">
                        <label for="description" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-align-left text-blue-900 mr-2"></i>Description détaillée *
                        </label>
                        <div class="relative">
                            <textarea id="description" name="description" rows="6" 
                                      class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl resize-none text-gray-700" 
                                      placeholder="Décrivez l'incident en détail : que s'est-il passé, quand, comment avez-vous été affecté..." 
                                      maxlength="2000" 
                                      minlength="20" 
                                      required><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                            <div class="absolute bottom-3 right-3 text-xs text-gray-500">
                                <span id="char-count">0</span>/2000
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>Minimum 20 caractères requis. Soyez le plus précis possible.
                        </div>
                    </div>

                    <!-- Photo Evidence -->
                    <div class="space-y-3">
                        <label for="photo" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-camera text-blue-900 mr-2"></i>Photo/Capture d'écran
                        </label>
                        <input type="url" id="photo" name="photo" value="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700" 
                               placeholder="URL de la photo ou capture d'écran">
                    </div>

                    <!-- Additional Proof -->
                    <div class="space-y-3">
                        <label for="preuve" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-file-alt text-blue-900 mr-2"></i>Preuves supplémentaires
                        </label>
                        <input type="url" id="preuve" name="preuve" value="<?= htmlspecialchars($preuve, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700" 
                               placeholder="URL vers des documents, emails, etc.">
                    </div>

                    <!-- Anonymous Checkbox -->
                    <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-gray-50 to-white border-2 border-gray-200 rounded-xl">
                        <input type="checkbox" id="anonyme" name="anonyme" <?= $anonyme ? 'checked' : '' ?> 
                               class="w-5 h-5 text-blue-900 border-2 border-gray-300 rounded focus:ring-blue-900 focus:ring-2">
                        <label for="anonyme" class="text-sm font-medium text-gray-700">
                            <i class="fas fa-user-secret text-gray-600 mr-2"></i>Signalement anonyme
                        </label>
                    </div>

                    <!-- Email (conditional) -->
                    <div id="email-section" class="space-y-3" style="display: <?= $anonyme ? 'none' : 'block' ?>">
                        <label for="email" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-envelope text-blue-900 mr-2"></i>Adresse email *
                        </label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700" 
                               placeholder="Entrez votre adresse email" <?= !$anonyme ? 'required' : '' ?>>
                    </div>

                    <!-- Priority -->
                    <div class="space-y-3">
                        <label for="priorite" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Priorité *
                        </label>
                        <select name="priorite" id="priorite" required
                                class="w-full px-4 py-3 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-900 focus:ring-4 focus:ring-blue-900/20 transition-all duration-300 hover:border-blue-700 hover:shadow-lg focus:shadow-xl text-gray-700">
                            <option value="">Sélectionnez une priorité</option>
                            <option value="normale" <?= $priorite === 'normale' ? 'selected' : '' ?>>Normale</option>
                            <option value="haute" <?= $priorite === 'haute' ? 'selected' : '' ?>>Haute</option>
                            <option value="urgente" <?= $priorite === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-blue-900 to-blue-800 hover:from-blue-800 hover:to-blue-700 text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-900/50 active:scale-95">
                            <i class="fas fa-paper-plane mr-2"></i>Envoyer le signalement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// Character counter for description
function initCharacterCounter() {
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.getElementById('char-count');
    
    if (descriptionTextarea && charCount) {
        function updateCharCount() {
            const count = Array.from(descriptionTextarea.value).length;
            charCount.textContent = count;
            
            if (count > 1800) {
                charCount.classList.add('text-red-500', 'font-bold');
                charCount.classList.remove('text-gray-500', 'text-orange-500');
            } else if (count > 1500) {
                charCount.classList.add('text-orange-500', 'font-semibold');
                charCount.classList.remove('text-gray-500', 'text-red-500');
            } else {
                charCount.classList.add('text-gray-500');
                charCount.classList.remove('text-red-500', 'text-orange-500', 'font-bold', 'font-semibold');
            }
        }
        
        descriptionTextarea.addEventListener('input', updateCharCount);
        descriptionTextarea.addEventListener('paste', () => setTimeout(updateCharCount, 10));
        updateCharCount();
    }
}

// Platform toggle based on context
function initPlatformToggle() {
    const contexteRadios = document.querySelectorAll('input[name="contexte"]');
    const plateformeSection = document.getElementById('plateforme-section');
    const plateformeSelect = document.querySelector('select[name="plateforme"]');
    
    if (contexteRadios.length && plateformeSection) {
        contexteRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'virtuel') {
                    plateformeSection.style.display = 'block';
                } else {
                    plateformeSection.style.display = 'none';
                    if (plateformeSelect) {
                        plateformeSelect.value = '';
                    }
                }
            });
        });
    }
}

// Incident type toggle based on context
function initIncidentTypeToggle() {
    const contexteRadios = document.querySelectorAll('input[name="contexte"]');
    const irlOptions = document.getElementById('irl-options');
    const virtuelOptions = document.getElementById('virtuel-options');
    const typeIncidentSelect = document.getElementById('type_incident');
    
    if (contexteRadios.length && irlOptions && virtuelOptions && typeIncidentSelect) {
        contexteRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                typeIncidentSelect.value = '';
                
                if (this.value === 'irl') {
                    irlOptions.style.display = 'block';
                    virtuelOptions.style.display = 'none';
                } else if (this.value === 'virtuel') {
                    irlOptions.style.display = 'none';
                    virtuelOptions.style.display = 'block';
                } else {
                    irlOptions.style.display = 'none';
                    virtuelOptions.style.display = 'none';
                }
            });
        });
    }
}

// Email toggle based on anonymous checkbox
function initEmailToggle() {
    const anonymeCheckbox = document.getElementById('anonyme');
    const emailSection = document.getElementById('email-section');
    const emailInput = document.getElementById('email');
    
    if (anonymeCheckbox && emailSection && emailInput) {
        anonymeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                emailSection.style.display = 'none';
                emailInput.removeAttribute('required');
                emailInput.value = '';
            } else {
                emailSection.style.display = 'block';
                emailInput.setAttribute('required', 'required');
            }
        });
    }
}

// Initialize all functions when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initCharacterCounter();
    initPlatformToggle();
    initIncidentTypeToggle();
    initEmailToggle();
});


</script>

<?php include_once('../Inc/Components/footer.php'); ?>