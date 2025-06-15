<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
$nom = '';  // NOUVEAU
$prenom = '';  // NOUVEAU
$anonyme = false;
$confirm = false;
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
            $nom = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');  // NOUVEAU
            $prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');  // NOUVEAU
            $anonyme = isset($_POST['anonyme']) && $_POST['anonyme'] === 'on';
         
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
            // NOUVELLE VALIDATION : Nom et prénom requis si pas anonyme
            if (!$anonyme && (empty($nom) || empty($prenom))) {
                throw new Exception('Le nom et le prénom sont obligatoires si vous n\'êtes pas anonyme');
            }

            // Insert into database - MISE À JOUR avec les nouvelles colonnes
            $req = $conn->prepare("INSERT INTO signalements (user_id, type_incident, titre, description, localisation, statut, priorite, anonyme, images, preuves, incident_context, email_contact, auteur, nom, prenom, plateforme, lieu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $req->execute([$id, $type_incident, $titre, $description, $lieu, 'en_attente', $priorite, $anonyme, $photo, $preuve, $contexte, $email, $auteur, $nom, $prenom, $plateforme, $lieu]);

            $success_message = "Votre signalement a été envoyé avec succès. Nous vous recontacterons dans les plus brefs délais.";
            
            // Reset form variables after successful submission
            $titre = $type_incident = $contexte = $plateforme = $lieu = $description = $photo = $preuve = $email = $priorite = $nom = $prenom = '';
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
            <!-- Page Header avec design amélioré -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-900 to-blue-700 rounded-full mb-6 shadow-lg">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-900 to-blue-700 bg-clip-text text-transparent mb-4">Signaler un incident</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Aidez-nous à améliorer la sécurité numérique en signalant les incidents. Votre signalement contribue à protéger la communauté.</p>
                <div class="mt-6 flex justify-center space-x-8 text-sm text-gray-500">
                    <div class="flex items-center">
                        <i class="fas fa-lock text-green-600 mr-2"></i>
                        <span>Données sécurisées</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-user-shield text-blue-600 mr-2"></i>
                        <span>Anonymat possible</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock text-purple-600 mr-2"></i>
                        <span>Traitement rapide</span>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages avec design amélioré -->
            <?php if ($success_message): ?>
                <div class="mb-8 p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Signalement envoyé avec succès</h3>
                            <p class="mt-1 text-sm text-green-700"><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="mb-8 p-6 bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erreur lors de l'envoi</h3>
                            <p class="mt-1 text-sm text-red-700"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Main Form avec design amélioré -->
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                <!-- Marianne Decorative Bar -->
                <div class="h-3 bg-gradient-to-r from-blue-900 via-white to-red-600"></div>
                
                <form method="POST" action="signal.php" class="p-10 space-y-8">
                    <!-- NOUVEAU: Section Informations personnelles -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-user text-blue-600 mr-3"></i>
                            Informations personnelles
                            <span class="ml-2 text-sm font-normal text-gray-500">(masquées si anonyme)</span>
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="personal-info-section">
                            <!-- Prénom -->
                            <div class="space-y-3">
                                <label for="prenom" class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-user text-blue-600 mr-2"></i>Prénom *
                                </label>
                                <input type="text" 
                                       id="prenom" 
                                       name="prenom" 
                                       value="<?= htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') ?>" 
                                       class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700" 
                                       placeholder="Votre prénom" 
                                       maxlength="50" 
                                       <?= !$anonyme ? 'required' : '' ?>>
                            </div>
                            
                            <!-- Nom -->
                            <div class="space-y-3">
                                <label for="nom" class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-user text-blue-600 mr-2"></i>Nom *
                                </label>
                                <input type="text" 
                                       id="nom" 
                                       name="nom" 
                                       value="<?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') ?>" 
                                       class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700" 
                                       placeholder="Votre nom de famille" 
                                       maxlength="50" 
                                       <?= !$anonyme ? 'required' : '' ?>>
                            </div>
                        </div>
                        
                        <!-- Email dans la même section -->
                        <div class="mt-6 space-y-3" id="email-section" style="display: <?= $anonyme ? 'none' : 'block' ?>">
                            <label for="email" class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-envelope text-blue-600 mr-2"></i>Adresse email *
                            </label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" 
                                   class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700" 
                                   placeholder="Entrez votre adresse email" <?= !$anonyme ? 'required' : '' ?>>
                        </div>
                    </div>

                   <!-- Section Personne à signaler (remplace le titre) -->
<div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl p-6 border border-red-200">
    <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-user-times text-red-600 mr-3"></i>
        Personne à signaler *
        <span class="ml-2 text-sm font-normal text-gray-500">(informations obligatoires)</span>
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Prénom de la personne à signaler -->
        <div class="space-y-3">
            <label for="prenom" class="block text-sm font-semibold text-gray-700">
                <i class="fas fa-user text-red-600 mr-2"></i>Prénom de la personne *
            </label>
            <input type="text" 
                   id="prenom" 
                   name="prenom" 
                   value="<?= htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') ?>" 
                   class="w-full px-4 py-3 bg-white border-2 border-red-200 rounded-xl focus:border-red-600 focus:ring-4 focus:ring-red-600/20 transition-all duration-300 hover:border-red-400 hover:shadow-md focus:shadow-lg text-gray-700" 
                   placeholder="Prénom de la personne à signaler" 
                   maxlength="50" 
                   required>
        </div>
        
        <!-- Nom de la personne à signaler -->
        <div class="space-y-3">
            <label for="nom" class="block text-sm font-semibold text-gray-700">
                <i class="fas fa-user text-red-600 mr-2"></i>Nom de la personne *
            </label>
            <input type="text" 
                   id="nom" 
                   name="nom" 
                   value="<?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') ?>" 
                   class="w-full px-4 py-3 bg-white border-2 border-red-200 rounded-xl focus:border-red-600 focus:ring-4 focus:ring-red-600/20 transition-all duration-300 hover:border-red-400 hover:shadow-md focus:shadow-lg text-gray-700" 
                   placeholder="Nom de famille de la personne à signaler" 
                   maxlength="50" 
                   required>
        </div>
    </div>
    
    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
            <div class="text-sm text-yellow-800">
                <p class="font-medium">Information importante :</p>
                <p>Assurez-vous que les informations saisies sont exactes. Un signalement avec de fausses informations peut avoir des conséquences légales.</p>
            </div>
        </div>
    </div>
</div>


<!-- Anonymous Checkbox avec design amélioré -->
<div class="flex items-center space-x-4 p-6 bg-gradient-to-r from-gray-50 to-slate-50 border-2 border-gray-200 rounded-2xl hover:shadow-md transition-all duration-300">
    <div class="flex items-center">
        <input type="checkbox" id="anonyme" name="anonyme" <?= $anonyme ? 'checked' : '' ?> 
               class="w-6 h-6 text-blue-600 border-2 border-gray-300 rounded-lg focus:ring-blue-600 focus:ring-2 transition-all duration-200">
    </div>
    <div class="flex-1">
        <label for="anonyme" class="text-base font-medium text-gray-700 cursor-pointer">
            <i class="fas fa-user-secret text-gray-600 mr-3"></i>Signalement anonyme
        </label>
        <p class="text-sm text-gray-500 mt-1">Vos informations personnelles ne seront pas enregistrées</p>
    </div>
</div>



                    <!-- Context Selection avec design amélioré -->
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-gray-800 mb-4">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Contexte de l'incident *
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="contexte" value="irl" <?= $contexte === 'irl' ? 'checked' : '' ?> 
                                       class="sr-only peer" required>
                                <div class="flex items-center p-6 border-2 border-gray-200 rounded-2xl transition-all duration-300 
                                            peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:shadow-lg
                                            hover:border-blue-400 hover:shadow-md group-hover:scale-[1.02] bg-white">
                                    <div class="flex items-center justify-center w-6 h-6 mr-4">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full transition-all duration-200 
                                                    peer-checked:border-blue-600 peer-checked:bg-blue-600 
                                                    peer-checked:shadow-[inset_0_0_0_2px_white]"></div>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-users text-blue-600 mr-4 text-xl"></i>
                                        <div>
                                            <span class="text-base font-semibold text-gray-800">Dans la vraie vie</span>
                                            <p class="text-sm text-gray-500 mt-1">Incident physique ou en personne</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="contexte" value="virtuel" <?= $contexte === 'virtuel' ? 'checked' : '' ?> 
                                       class="sr-only peer" required>
                                <div class="flex items-center p-6 border-2 border-gray-200 rounded-2xl transition-all duration-300 
                                            peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:shadow-lg
                                            hover:border-blue-400 hover:shadow-md group-hover:scale-[1.02] bg-white">
                                    <div class="flex items-center justify-center w-6 h-6 mr-4">
                                        <div class="w-5 h-5 border-2 border-gray-400 rounded-full transition-all duration-200 
                                                    peer-checked:border-blue-600 peer-checked:bg-blue-600 
                                                    peer-checked:shadow-[inset_0_0_0_2px_white]"></div>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-globe text-blue-600 mr-4 text-xl"></i>
                                        <div>
                                            <span class="text-base font-semibold text-gray-800">En ligne/Virtuel</span>
                                            <p class="text-sm text-gray-500 mt-1">Incident numérique ou sur internet</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- ... existing code ... -->
                    <!-- Incident Type -->
                    <div class="space-y-3">
                        <label for="type_incident" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Type d'incident *
                        </label>
                        <select name="type_incident" id="type_incident" required
                                class="w-full px-4 py-4 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700 text-base">
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
                            <i class="fas fa-globe text-blue-600 mr-2"></i>Plateforme concernée
                        </label>
                        <select name="plateforme" id="plateforme"
                                class="w-full px-4 py-4 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700 text-base">
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
                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Lieu de l'incident
                        </label>
                        <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-4 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700 text-base" 
                               placeholder="Ville, région ou URL du site web">
                    </div>

                    <!-- Description avec design amélioré -->
                    <div class="space-y-3">
                        <label for="description" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-align-left text-blue-600 mr-2"></i>Description détaillée *
                        </label>
                        <div class="relative">
                            <textarea id="description" name="description" rows="8" 
                                      class="w-full px-4 py-4 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg resize-none text-gray-700 text-base leading-relaxed" 
                                      placeholder="Décrivez l'incident en détail : que s'est-il passé, quand, comment avez-vous été affecté, quelles sont les conséquences..." 
                                      maxlength="2000" 
                                      minlength="20" 
                                      required><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                            <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1 text-xs text-gray-500 border border-gray-200">
                                <span id="char-count">0</span>/2000
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i>Minimum 20 caractères requis. Plus vous êtes précis, mieux nous pourrons vous aider.
                        </div>
                    </div>

                    <!-- Photo Evidence avec design amélioré -->
                    <div class="space-y-3">
                        <label for="photo" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-camera text-blue-600 mr-2"></i>Photo/Capture d'écran
                        </label>
                        <input type="url" id="photo" name="photo" value="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-4 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700 text-base" 
                               placeholder="URL de la photo ou capture d'écran (optionnel)">
                        <div class="text-xs text-gray-500 flex items-center">
                            <i class="fas fa-shield-alt mr-2 text-green-500"></i>Les preuves visuelles renforcent votre signalement
                        </div>
                    </div>

                    <!-- Additional Proof avec design amélioré -->
                    <div class="space-y-3">
                        <label for="preuve" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-file-alt text-blue-600 mr-2"></i>Preuves supplémentaires
                        </label>
                        <input type="url" id="preuve" name="preuve" value="<?= htmlspecialchars($preuve, ENT_QUOTES, 'UTF-8') ?>" 
                               class="w-full px-4 py-4 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700 text-base" 
                               placeholder="URL vers des documents, emails, etc. (optionnel)">
                    </div>

                    <!-- Priority avec design amélioré -->
                    <div class="space-y-3">
                        <label for="priorite" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Priorité *
                        </label>
                        <select name="priorite" id="priorite" required
                                class="w-full px-4 py-4 bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/20 transition-all duration-300 hover:border-blue-400 hover:shadow-md focus:shadow-lg text-gray-700 text-base">
                            <option value="">Sélectionnez une priorité</option>
                            <option value="normale" <?= $priorite === 'normale' ? 'selected' : '' ?>>🟢 Normale - Incident sans danger immédiat</option>
                            <option value="haute" <?= $priorite === 'haute' ? 'selected' : '' ?>>🟡 Haute - Incident préoccupant</option>
                            <option value="urgente" <?= $priorite === 'urgente' ? 'selected' : '' ?>>🔴 Urgente - Danger immédiat</option>
                        </select>
                    </div>

                    <!-- Confirm Checkbox avec design amélioré -->
<div class="flex items-center space-x-4 p-6 bg-gradient-to-r from-gray-50 to-slate-50 border-2 border-gray-200 rounded-2xl hover:shadow-md transition-all duration-300">
    <div class="flex items-center">
        <input type="checkbox" id="confirm" name="confirm" <?= $confirm ? 'checked' : '' ?> 
               class="w-6 h-6 text-blue-600 border-2 border-gray-300 rounded-lg focus:ring-blue-600 focus:ring-2 transition-all duration-200">
    </div>
    <div class="flex-1">
        <label for="confirm" class="text-base font-medium text-gray-700 cursor-pointer">
        <i class="fa-solid fa-shield text-green-600 mr-3"></i>Confirmation du signalement
        </label>
        <p class="text-sm text-gray-500 mt-1">Je jure sur l'honneur que les informations signales sont toutes authentiques.</p>
    </div>
</div>
                    <!-- Submit Button avec design amélioré -->
                    <div class="pt-8">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-blue-900 via-blue-800 to-blue-700 hover:from-blue-800 hover:via-blue-700 hover:to-blue-600 text-white font-bold py-5 px-8 rounded-2xl transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-blue-600/50 active:scale-[0.98] text-lg">
                            <i class="fas fa-paper-plane mr-3"></i>Envoyer le signalement
                            <div class="text-sm font-normal mt-1 opacity-90">Votre signalement sera traité dans les plus brefs délais</div>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Section d'aide avec design amélioré -->
            <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 border border-blue-100">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-question-circle text-blue-600 mr-3"></i>
                    Besoin d'aide ?
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-phone text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Urgence</h4>
                        <p class="text-sm text-gray-600">En cas de danger immédiat, contactez le <strong>3919</strong> ou le <strong>17</strong></p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-comments text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Support</h4>
                        <p class="text-sm text-gray-600">Notre équipe est disponible pour vous accompagner</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-book text-white"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Guide</h4>
                        <p class="text-sm text-gray-600">Consultez notre <a href="faq.php" class="text-blue-600 hover:underline">FAQ</a> pour plus d'informations</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>


// ... existing code ...

// NOUVEAU: Validation de la checkbox de confirmation
function initConfirmationValidation() {
    const form = document.querySelector('form');
    const confirmCheckbox = document.getElementById('confirm');
    const submitButton = document.querySelector('button[type="submit"]');
    
    if (form && confirmCheckbox) {
        // Validation lors de la soumission du formulaire
        form.addEventListener('submit', function(e) {
            if (!confirmCheckbox.checked) {
                e.preventDefault();
                
                // Afficher un message d'erreur
                showConfirmationError();
                
                // Faire défiler vers la checkbox
                confirmCheckbox.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Ajouter un effet visuel d'erreur
                addErrorEffect(confirmCheckbox.closest('.flex'));
                
                return false;
            }
        });
        
        // Mettre à jour l'état du bouton de soumission
        confirmCheckbox.addEventListener('change', function() {
            updateSubmitButton();
            
            // Supprimer l'effet d'erreur si la checkbox est cochée
            if (this.checked) {
                removeErrorEffect(this.closest('.flex'));
                hideConfirmationError();
            }
        });
        
        // Initialiser l'état du bouton
        updateSubmitButton();
    }
}

// Fonction pour afficher un message d'erreur
function showConfirmationError() {
    // Supprimer le message existant s'il y en a un
    hideConfirmationError();
    
    const confirmSection = document.getElementById('confirm').closest('.flex');
    const errorMessage = document.createElement('div');
    errorMessage.id = 'confirmation-error';
    errorMessage.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-lg';
    errorMessage.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
            <div>
                <h4 class="text-sm font-medium text-red-800">Confirmation requise</h4>
                <p class="text-sm text-red-700 mt-1">Vous devez confirmer l'authenticité des informations avant de soumettre le signalement.</p>
            </div>
        </div>
    `;
    
    confirmSection.parentNode.insertBefore(errorMessage, confirmSection.nextSibling);
}

// Fonction pour masquer le message d'erreur
function hideConfirmationError() {
    const errorMessage = document.getElementById('confirmation-error');
    if (errorMessage) {
        errorMessage.remove();
    }
}

// Fonction pour ajouter un effet visuel d'erreur
function addErrorEffect(element) {
    element.classList.add('border-red-300', 'bg-red-50');
    element.classList.remove('border-gray-200', 'bg-gradient-to-r', 'from-gray-50', 'to-slate-50');
    
    // Animation de secousse
    element.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        element.style.animation = '';
    }, 500);
}

// Fonction pour supprimer l'effet visuel d'erreur
function removeErrorEffect(element) {
    element.classList.remove('border-red-300', 'bg-red-50');
    element.classList.add('border-gray-200', 'bg-gradient-to-r', 'from-gray-50', 'to-slate-50');
}

// Fonction pour mettre à jour l'état du bouton de soumission
function updateSubmitButton() {
    const confirmCheckbox = document.getElementById('confirm');
    const submitButton = document.querySelector('button[type="submit"]');
    
    if (submitButton && confirmCheckbox) {
        if (confirmCheckbox.checked) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            submitButton.classList.add('hover:shadow-lg', 'hover:scale-105');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            submitButton.classList.remove('hover:shadow-lg', 'hover:scale-105');
        }
    }
}


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

// NOUVEAU: Gestion des champs personnels et anonymat
function initPersonalInfoToggle() {
    const anonymeCheckbox = document.getElementById('anonyme');
    const emailSection = document.getElementById('email-section');
    const emailInput = document.getElementById('email');
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    const personalInfoSection = document.getElementById('personal-info-section');
    
    if (anonymeCheckbox && emailSection && emailInput && nomInput && prenomInput) {
        anonymeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Mode anonyme
                emailSection.style.display = 'none';
                emailInput.removeAttribute('required');
                emailInput.value = '';
                nomInput.removeAttribute('required');
                nomInput.value = '';
                prenomInput.removeAttribute('required');
                prenomInput.value = '';
                
                // Ajouter un effet visuel pour indiquer que les champs sont désactivés
                personalInfoSection.style.opacity = '0.5';
                personalInfoSection.style.pointerEvents = 'none';
            } else {
                // Mode normal
                emailSection.style.display = 'block';
                emailInput.setAttribute('required', 'required');
                nomInput.setAttribute('required', 'required');
                prenomInput.setAttribute('required', 'required');
                
                // Restaurer l'apparence normale
                personalInfoSection.style.opacity = '1';
                personalInfoSection.style.pointerEvents = 'auto';
            }
        });
    }
}

// Validation améliorée du formulaire
function initFormValidation() {
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const anonymeCheckbox = document.getElementById('anonyme');
            const nomInput = document.getElementById('nom');
            const prenomInput = document.getElementById('prenom');
            const emailInput = document.getElementById('email');
            
            // Validation personnalisée pour les champs nom/prénom si pas anonyme
            if (!anonymeCheckbox.checked) {
                if (!nomInput.value.trim() || !prenomInput.value.trim()) {
                    e.preventDefault();
                    alert('Le nom et le prénom sont obligatoires si vous n\'êtes pas anonyme.');
                    return false;
                }
                
                if (!emailInput.value.trim()) {
                    e.preventDefault();
                    alert('L\'adresse email est obligatoire si vous n\'êtes pas anonyme.');
                    return false;
                }
            }
        });
    }
}

// Initialize all functions when DOM is loaded
// Mettre à jour la fonction d'initialisation
document.addEventListener('DOMContentLoaded', function() {
    initCharacterCounter();
    initPlatformToggle();
    initIncidentTypeToggle();
    initPersonalInfoToggle();
    initConfirmationValidation(); // NOUVEAU
});
</script>

<?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include('../Inc/Traitement/create_log.php'); ?>