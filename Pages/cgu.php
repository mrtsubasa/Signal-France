<?php
session_start();

// Gestion de l'authentification
$user = null;


include '../Inc/Components/header.php';
include '../Inc/Components/nav.php';
?>

<style>
/* Styles spécifiques pour la page CGU */
.cgu-container {
    background: linear-gradient(135deg, #000091 0%, #1e40af 50%, #3b82f6 100%);
    min-height: 100vh;
    position: relative;
    overflow: hidden;
}

.cgu-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    pointer-events: none;
}

.glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
}

.section-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(0, 0, 145, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.section-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 40px rgba(0, 0, 145, 0.1);
}

.tricolor-border {
    position: relative;
}

.tricolor-border::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #000091 33.33%, #ffffff 33.33%, #ffffff 66.66%, #e1000f 66.66%);
    border-radius: 12px 12px 0 0;
}

.animate-fade-in {
    animation: fadeInUp 0.8s ease-out forwards;
}

.animate-fade-in-delay {
    animation: fadeInUp 0.8s ease-out 0.2s forwards;
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.scroll-indicator {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #000091, #e1000f);
    transform-origin: left;
    transform: scaleX(0);
    z-index: 1000;
    transition: transform 0.1s ease-out;
}

.floating-particles {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
}

.particle {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}
</style>

<!-- Indicateur de progression de lecture -->
<div class="scroll-indicator" id="scrollIndicator"></div>

<div class="cgu-container">
    <!-- Particules flottantes -->
    <div class="floating-particles">
        <div class="particle w-2 h-2" style="left: 10%; top: 20%; animation-delay: 0s;"></div>
        <div class="particle w-3 h-3" style="left: 80%; top: 30%; animation-delay: 2s;"></div>
        <div class="particle w-1 h-1" style="left: 60%; top: 60%; animation-delay: 4s;"></div>
        <div class="particle w-2 h-2" style="left: 30%; top: 80%; animation-delay: 1s;"></div>
        <div class="particle w-1 h-1" style="left: 90%; top: 70%; animation-delay: 3s;"></div>
    </div>
    
    <div class="relative z-10 pt-32 pb-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- En-tête principal -->
            <div class="text-center mb-16 animate-fade-in">
                <div class="glass-card rounded-3xl p-12 mb-8">
                    <div class="mb-6">
                        <span class="inline-block bg-white bg-opacity-20 text-white px-6 py-3 rounded-full text-sm font-medium backdrop-blur-sm">
                            🇫🇷 Signale France
                        </span>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 text-white drop-shadow-2xl">
                        Conditions Générales d'Utilisation
                    </h1>
                    <p class="text-xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
                        Service public numérique Signale France
                    </p>
                    <div class="mt-8 text-sm text-blue-200">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Dernière mise à jour : <?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Navigation rapide -->
            <div class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-france-blue mb-6 flex items-center">
                        <i class="fas fa-list-ul mr-3 text-france-red"></i>
                        Navigation rapide
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="#article1" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <i class="fas fa-chevron-right mr-3 text-france-blue group-hover:translate-x-1 transition-transform"></i>
                            <span class="text-gray-700 group-hover:text-france-blue">1. Objet et champ d'application</span>
                        </a>
                        <a href="#article2" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <i class="fas fa-chevron-right mr-3 text-france-blue group-hover:translate-x-1 transition-transform"></i>
                            <span class="text-gray-700 group-hover:text-france-blue">2. Définitions</span>
                        </a>
                        <a href="#article3" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <i class="fas fa-chevron-right mr-3 text-france-blue group-hover:translate-x-1 transition-transform"></i>
                            <span class="text-gray-700 group-hover:text-france-blue">3. Accès au service</span>
                        </a>
                        <a href="#article4" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <i class="fas fa-chevron-right mr-3 text-france-blue group-hover:translate-x-1 transition-transform"></i>
                            <span class="text-gray-700 group-hover:text-france-blue">4. Obligations de l'utilisateur</span>
                        </a>
                        <a href="#article5" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <i class="fas fa-chevron-right mr-3 text-france-blue group-hover:translate-x-1 transition-transform"></i>
                            <span class="text-gray-700 group-hover:text-france-blue">5. Protection des données</span>
                        </a>
                        <a href="#article6" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <i class="fas fa-chevron-right mr-3 text-france-blue group-hover:translate-x-1 transition-transform"></i>
                            <span class="text-gray-700 group-hover:text-france-blue">6. Responsabilité</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Article 1 -->
            <div id="article1" class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-3xl font-bold text-france-blue mb-6 flex items-center">
                        <span class="bg-france-red text-white w-10 h-10 rounded-full flex items-center justify-center mr-4 text-lg">1</span>
                        Objet et champ d'application
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <p class="mb-6">
                            Les présentes Conditions Générales d'Utilisation (CGU) régissent l'utilisation du service public numérique 
                            <strong class="text-france-blue">Signale France</strong>, plateforme nationale d'alerte et d'information pour la sécurité des citoyens.
                        </p>
                        <p class="mb-6">
                            Ce service, mis en œuvre par l'État français, a pour mission de :
                        </p>
                        <ul class="list-disc list-inside mb-6 space-y-2">
                            <li>Diffuser des alertes de sécurité en temps réel</li>
                            <li>Permettre aux citoyens de signaler des incidents</li>
                            <li>Faciliter la recherche de personnes disparues</li>
                            <li>Coordonner les services d'urgence</li>
                        </ul>
                        <div class="bg-blue-50 border-l-4 border-france-blue p-6 rounded-r-lg">
                            <p class="text-france-blue font-semibold mb-2">
                                <i class="fas fa-info-circle mr-2"></i>
                                Important
                            </p>
                            <p class="text-gray-700">
                                L'utilisation de ce service implique l'acceptation pleine et entière des présentes CGU. 
                                En cas de non-acceptation, l'utilisateur doit s'abstenir d'utiliser le service.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Article 2 -->
            <div id="article2" class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-3xl font-bold text-france-blue mb-6 flex items-center">
                        <span class="bg-france-red text-white w-10 h-10 rounded-full flex items-center justify-center mr-4 text-lg">2</span>
                        Définitions
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h4 class="font-bold text-france-blue mb-3">Service</h4>
                                <p>La plateforme Signale France accessible via le site web et les applications mobiles.</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h4 class="font-bold text-france-blue mb-3">Utilisateur</h4>
                                <p>Toute personne physique ou morale utilisant le service, qu'elle soit authentifiée ou non.</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h4 class="font-bold text-france-blue mb-3">Alerte</h4>
                                <p>Information de sécurité diffusée par les autorités compétentes via le service.</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h4 class="font-bold text-france-blue mb-3">Signalement</h4>
                                <p>Information transmise par un utilisateur concernant un incident ou une situation d'urgence.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Article 3 -->
            <div id="article3" class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-3xl font-bold text-france-blue mb-6 flex items-center">
                        <span class="bg-france-red text-white w-10 h-10 rounded-full flex items-center justify-center mr-4 text-lg">3</span>
                        Accès au service
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <h4 class="text-xl font-semibold text-france-blue mb-4">3.1 Conditions d'accès</h4>
                        <p class="mb-6">
                            Le service est accessible gratuitement à tous les citoyens français et aux personnes résidant sur le territoire français. 
                            Certaines fonctionnalités peuvent nécessiter une authentification.
                        </p>
                        
                        <h4 class="text-xl font-semibold text-france-blue mb-4">3.2 Création de compte</h4>
                        <p class="mb-4">
                            Pour accéder aux fonctionnalités avancées, l'utilisateur peut créer un compte en fournissant :
                        </p>
                        <ul class="list-disc list-inside mb-6 space-y-2">
                            <li>Une adresse email valide</li>
                            <li>Un mot de passe sécurisé</li>
                            <li>Des informations d'identification personnelles</li>
                        </ul>
                        
                        <div class="bg-red-50 border-l-4 border-france-red p-6 rounded-r-lg">
                            <p class="text-france-red font-semibold mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Attention
                            </p>
                            <p class="text-gray-700">
                                La fourniture d'informations fausses ou trompeuses est passible de sanctions pénales 
                                conformément à l'article 441-1 du Code pénal.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Article 4 -->
            <div id="article4" class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-3xl font-bold text-france-blue mb-6 flex items-center">
                        <span class="bg-france-red text-white w-10 h-10 rounded-full flex items-center justify-center mr-4 text-lg">4</span>
                        Obligations de l'utilisateur
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <h4 class="text-xl font-semibold text-france-blue mb-4">4.1 Usage conforme</h4>
                        <p class="mb-6">
                            L'utilisateur s'engage à utiliser le service de manière conforme à sa destination et aux présentes CGU. 
                            Il est notamment interdit de :
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                                <h5 class="font-semibold text-france-red mb-2">❌ Interdictions absolues</h5>
                                <ul class="text-sm space-y-1">
                                    <li>• Diffuser de fausses alertes</li>
                                    <li>• Usurper l'identité d'autrui</li>
                                    <li>• Perturber le fonctionnement du service</li>
                                    <li>• Accéder illégalement aux données</li>
                                </ul>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                <h5 class="font-semibold text-green-700 mb-2">✅ Bonnes pratiques</h5>
                                <ul class="text-sm space-y-1">
                                    <li>• Signaler uniquement des faits avérés</li>
                                    <li>• Respecter la vie privée d'autrui</li>
                                    <li>• Utiliser un langage approprié</li>
                                    <li>• Coopérer avec les autorités</li>
                                </ul>
                            </div>
                        </div>
                        
                        <h4 class="text-xl font-semibold text-france-blue mb-4">4.2 Responsabilité du contenu</h4>
                        <p class="mb-6">
                            L'utilisateur est seul responsable du contenu qu'il publie ou transmet via le service. 
                            Il garantit que ce contenu ne porte pas atteinte aux droits de tiers et respecte la législation en vigueur.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Article 5 -->
            <div id="article5" class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-3xl font-bold text-france-blue mb-6 flex items-center">
                        <span class="bg-france-red text-white w-10 h-10 rounded-full flex items-center justify-center mr-4 text-lg">5</span>
                        Protection des données personnelles
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <h4 class="text-xl font-semibold text-france-blue mb-4">5.1 Conformité RGPD</h4>
                        <p class="mb-6">
                            Le traitement des données personnelles s'effectue dans le respect du Règlement Général sur la Protection des Données (RGPD) 
                            et de la loi Informatique et Libertés modifiée.
                        </p>
                        
                        <div class="bg-blue-50 p-6 rounded-xl mb-6">
                            <h5 class="font-semibold text-france-blue mb-3">
                                <i class="fas fa-shield-alt mr-2"></i>
                                Vos droits
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong>Droit d'accès :</strong> Consulter vos données
                                </div>
                                <div>
                                    <strong>Droit de rectification :</strong> Corriger vos données
                                </div>
                                <div>
                                    <strong>Droit à l'effacement :</strong> Supprimer vos données
                                </div>
                                <div>
                                    <strong>Droit d'opposition :</strong> Refuser le traitement
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="text-xl font-semibold text-france-blue mb-4">5.2 Finalités du traitement</h4>
                        <p class="mb-4">Les données collectées sont utilisées exclusivement pour :</p>
                        <ul class="list-disc list-inside mb-6 space-y-2">
                            <li>La gestion des comptes utilisateurs</li>
                            <li>La diffusion d'alertes personnalisées</li>
                            <li>Le traitement des signalements</li>
                            <li>L'amélioration du service</li>
                            <li>Le respect des obligations légales</li>
                        </ul>
                        
                        <div class="bg-gray-100 p-6 rounded-xl">
                            <p class="text-sm text-gray-600">
                                <strong>Contact DPO :</strong> dpo@signale-france.gouv.fr<br>
                                <strong>CNIL :</strong> Déclaration n° [À compléter]
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Article 6 -->
            <div id="article6" class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-3xl font-bold text-france-blue mb-6 flex items-center">
                        <span class="bg-france-red text-white w-10 h-10 rounded-full flex items-center justify-center mr-4 text-lg">6</span>
                        Responsabilité et garanties
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <h4 class="text-xl font-semibold text-france-blue mb-4">6.1 Disponibilité du service</h4>
                        <p class="mb-6">
                            L'État s'efforce d'assurer la disponibilité du service 24h/24 et 7j/7. Toutefois, 
                            des interruptions peuvent survenir pour des raisons de maintenance, de mise à jour ou de force majeure.
                        </p>
                        
                        <h4 class="text-xl font-semibold text-france-blue mb-4">6.2 Limitation de responsabilité</h4>
                        <p class="mb-6">
                            L'État ne saurait être tenu responsable des dommages directs ou indirects résultant de :
                        </p>
                        <ul class="list-disc list-inside mb-6 space-y-2">
                            <li>L'utilisation ou l'impossibilité d'utiliser le service</li>
                            <li>La transmission d'informations erronées par les utilisateurs</li>
                            <li>Les défaillances techniques indépendantes de sa volonté</li>
                            <li>Les actes de tiers non autorisés</li>
                        </ul>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-lg">
                            <p class="text-yellow-800 font-semibold mb-2">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Clause importante
                            </p>
                            <p class="text-gray-700">
                                En cas d'urgence vitale, contactez immédiatement les services d'urgence (15, 17, 18, 112) 
                                plutôt que d'utiliser exclusivement cette plateforme.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informations légales -->
            <div class="mb-12 animate-fade-in-delay">
                <div class="section-card tricolor-border rounded-2xl p-8">
                    <h2 class="text-3xl font-bold text-france-blue mb-6 flex items-center">
                        <i class="fas fa-gavel mr-4 text-france-red"></i>
                        Informations légales
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-semibold text-france-blue mb-3">Éditeur du service</h4>
                            <p class="text-gray-700 mb-4">
                                Ministère de l'Intérieur<br>
                                Place Beauvau<br>
                                75008 Paris<br>
                                France
                            </p>
                            
                            <h4 class="font-semibold text-france-blue mb-3">Directeur de publication</h4>
                            <p class="text-gray-700">
                                Ministre de l'Intérieur
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-france-blue mb-3">Hébergement</h4>
                            <p class="text-gray-700 mb-4">
                                Hébergement sécurisé<br>
                                Infrastructure gouvernementale<br>
                                Certification SecNumCloud
                            </p>
                            
                            <h4 class="font-semibold text-france-blue mb-3">Contact technique</h4>
                            <p class="text-gray-700">
                                support@signale-france.gouv.fr<br>
                                Assistance 24h/24
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Acceptation et contact -->
            <div class="text-center animate-fade-in-delay">
                <div class="glass-card rounded-3xl p-12">
                    <h2 class="text-3xl font-bold text-white mb-6">
                        Acceptation des conditions
                    </h2>
                    <p class="text-blue-100 mb-8 max-w-3xl mx-auto leading-relaxed">
                        En utilisant le service Signale France, vous reconnaissez avoir lu, compris et accepté 
                        l'intégralité des présentes Conditions Générales d'Utilisation.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row justify-center gap-6">
                        <a href="../index.php" 
                           class="bg-white text-france-blue px-8 py-4 text-lg font-semibold rounded-xl hover:bg-gray-50 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-3">
                            <i class="fas fa-home"></i>
                            Retour à l'accueil
                        </a>
                        
                        <a href="contact.php" 
                           class="bg-france-red text-white px-8 py-4 text-lg font-semibold rounded-xl hover:bg-red-700 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 flex items-center justify-center gap-3">
                            <i class="fas fa-envelope"></i>
                            Nous contacter
                        </a>
                    </div>
                    
                    <div class="mt-8 text-sm text-blue-200">
                        <p>Version 1.0 - Mise à jour le <?php echo date('d/m/Y'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Indicateur de progression de lecture
window.addEventListener('scroll', function() {
    const scrollTop = window.pageYOffset;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = (scrollTop / docHeight) * 100;
    document.getElementById('scrollIndicator').style.transform = `scaleX(${scrollPercent / 100})`;
});

// Animation au scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observer tous les éléments avec animation
document.querySelectorAll('.animate-fade-in-delay').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.8s ease-out';
    observer.observe(el);
});

// Smooth scroll pour la navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<?php include '../Inc/Components/footers.php'; ?>