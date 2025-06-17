<?php
include_once('../Inc/Components/header.php');
include_once('../Inc/Components/nav.php');
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $_SESSION['notification'] = [
        'message' => 'Votre session a expiré après 30 minutes d\'inactivité. Veuillez vous reconnecter.',
        'type' => 'warning'
    ];
}
?>

    <!-- Meta viewport pour mobile parfait -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Container principal avec design moderne -->
    <div class="login-container">
        <!-- Background dynamique -->
        <div class="background-wrapper">
            <!-- Gradients animés -->
            <div class="bg-layer bg-layer-1"></div>
            <div class="bg-layer bg-layer-2"></div>
            <div class="bg-layer bg-layer-3"></div>

            <!-- Particules géométriques -->
            <div class="particles-container">
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                <div class="particle particle-4"></div>
                <div class="particle particle-5"></div>
                <div class="particle particle-6"></div>
            </div>

            <!-- Grille hexagonale -->
            <div class="hex-grid"></div>
        </div>

        <!-- Contenu principal -->
        <div class="main-content">
            <!-- Navigation en haut pour mobile -->
            <nav class="mobile-nav">
                <div class="nav-brand">
                    <div class="brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="brand-text">Signale France</span>
                </div>
                <div class="nav-status">
                    <div class="status-indicator online"></div>
                    <span class="status-text">Actif</span>
                </div>
            </nav>

            <!-- Section Hero -->
            <section class="hero-section">
                <div class="hero-content">
                    <!-- Logo principal -->
                    <div class="logo-container">
                        <div class="logo-wrapper">
                            <div class="logo-bg"></div>
                            <div class="logo-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div class="logo-pulse"></div>
                        </div>
                    </div>

                    <!-- Titre et description -->
                    <div class="hero-text">
                        <h1 class="main-title">
                            <span class="title-line">Signale</span>
                            <span class="title-line title-accent">France</span>
                        </h1>
                        <p class="main-subtitle">
                            Plateforme Officielle de Signalement Numérique
                        </p>
                        <div class="title-decoration">
                            <div class="decoration-line"></div>
                            <div class="decoration-dot"></div>
                            <div class="decoration-line"></div>
                        </div>
                    </div>

                    <!-- Badges de confiance -->
                    <div class="trust-badges">
                        <div class="badge badge-secure">
                            <div class="badge-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                </svg>
                            </div>
                            <span>Sécurisé</span>
                        </div>
                        <div class="badge badge-certified">
                            <div class="badge-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span>Certifié</span>
                        </div>
                        <div class="badge badge-france">
                            <div class="badge-icon french-flag">
                                <div class="flag-blue"></div>
                                <div class="flag-white"></div>
                                <div class="flag-red"></div>
                            </div>
                            <span>France</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section Formulaire -->
            <section class="form-section">
                <div class="form-container">
                    <!-- En-tête du formulaire -->
                    <div class="form-header">
                        <div class="form-title-wrapper">
                            <div class="form-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <circle cx="12" cy="16" r="1"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>
                            <h2 class="form-title">Connexion Sécurisée</h2>
                        </div>
                        <p class="form-subtitle">Accédez à votre espace professionnel</p>
                    </div>

                    <!-- Formulaire -->
                    <form class="login-form" method="POST" action="../Inc/Traitement/traitement_co.php" novalidate>
                        <!-- Email Field -->
                        <div class="field-group">
                            <label for="email" class="field-label">
                            <span class="label-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                                <span class="label-text">Email professionnel</span>
                            </label>
                            <div class="field-wrapper">
                                <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="field-input"
                                        placeholder="votre.email@entreprise.com"
                                        required
                                        autocomplete="email"
                                        spellcheck="false"
                                >
                                <div class="field-focus-line"></div>
                                <div class="field-validation">
                                    <div class="validation-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <polyline points="20,6 9,17 4,12"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="field-error"></div>
                        </div>

                        <!-- Password Field -->
                        <div class="field-group">
                            <label for="password" class="field-label">
                            <span class="label-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <circle cx="12" cy="16" r="1"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                                <span class="label-text">Mot de passe</span>
                            </label>
                            <div class="field-wrapper">
                                <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="field-input"
                                        placeholder="••••••••••••••••"
                                        required
                                        autocomplete="current-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                                <div class="field-focus-line"></div>
                                <div class="field-validation">
                                    <div class="validation-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <polyline points="20,6 9,17 4,12"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="field-error"></div>
                        </div>

                        <!-- Options -->
                        <div class="form-options">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="remember-me" name="remember-me" class="checkbox-input">
                                <label for="remember-me" class="checkbox-label">
                                    <div class="checkbox-custom">
                                        <svg class="checkbox-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <polyline points="20,6 9,17 4,12"/>
                                        </svg>
                                    </div>
                                    <span class="checkbox-text">Se souvenir de moi</span>
                                </label>
                            </div>
                            <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="submit-btn">
                            <span class="btn-bg"></span>
                            <span class="btn-content">
                            <span class="btn-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                    <polyline points="10,17 15,12 10,7"/>
                                    <line x1="15" y1="12" x2="3" y2="12"/>
                                </svg>
                            </span>
                            <span class="btn-text">Accéder à la plateforme</span>
                            <span class="btn-loading">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" opacity="0.3"/>
                                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </span>
                        </span>
                            <div class="btn-ripple"></div>
                        </button>
                    </form>

                    <!-- Footer du formulaire -->
                    <div class="form-footer">
                        <div class="security-info">
                            <div class="info-item">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                    </svg>
                                </div>
                                <div class="info-content">
                                    <div class="info-title">Chiffrement SSL/TLS</div>
                                    <div class="info-subtitle">Connexion sécurisée</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12,6 12,12 16,14"/>
                                    </svg>
                                </div>
                                <div class="info-content">
                                    <div class="info-title">Session 8 heures</div>
                                    <div class="info-subtitle">Expiration automatique</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>

            <!-- Section Stats -->
            <section class="stats-section">
                <div class="stats-container">
                    <div class="stats-header">
                        <h3 class="stats-title">Plateforme de Confiance</h3>
                        <p class="stats-subtitle">Métriques en temps réel</p>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card uptime">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">99.9%</div>
                                <div class="stat-label">Disponibilité</div>
                            </div>
                            <div class="stat-trend up">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 14l5-5 5 5z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="stat-card support">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12,6 12,12 16,14"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">24/7</div>
                                <div class="stat-label">Support</div>
                            </div>
                            <div class="stat-trend stable">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 12l4-4 4 4"/>
                                </svg>
                            </div>
                        </div>

                        <div class="stat-card security">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">AES-256</div>
                                <div class="stat-label">Chiffrement</div>
                            </div>
                            <div class="stat-trend up">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 14l5-5 5 5z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="stat-card compliance">
                            <div class="stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">RGPD</div>
                                <div class="stat-label">Conforme</div>
                            </div>
                            <div class="stat-trend up">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 14l5-5 5 5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- CSS Ultra-Moderne et Responsive -->
    <style>
        /* Reset et variables CSS */
        :root {
            /* Couleurs principales */
            --primary-blue: #1e40af;
            --secondary-blue: #3b82f6;
            --accent-blue: #60a5fa;
            --light-blue: #93c5fd;
            --red-france: #ef4444;
            --white: #ffffff;

            /* Couleurs neutres */
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;

            /* Espacements */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            --space-3xl: 4rem;

            /* Rayons de bordure */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --radius-3xl: 2rem;

            /* Ombres */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);

            /* Transitions */
            --transition-fast: 150ms ease;
            --transition-normal: 300ms ease;
            --transition-slow: 500ms ease;

            /* Z-index */
            --z-background: -1;
            --z-normal: 1;
            --z-elevated: 10;
            --z-overlay: 100;
            --z-modal: 1000;
        }

        /* Reset global */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            line-height: 1.15;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--white);
            background-color: var(--gray-900);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        /* Container principal */
        .login-container {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Background animé */
        .background-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: var(--z-background);
            overflow: hidden;
        }

        .bg-layer {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.8;
        }

        .bg-layer-1 {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--gray-900) 50%, var(--secondary-blue) 100%);
            animation: gradientShift 20s ease-in-out infinite;
        }

        .bg-layer-2 {
            background: radial-gradient(ellipse at 20% 80%, var(--accent-blue) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 20%, var(--red-france) 0%, transparent 50%);
            opacity: 0.3;
            animation: gradientFloat 15s ease-in-out infinite reverse;
        }

        .bg-layer-3 {
            background: conic-gradient(from 180deg at 50% 50%, var(--primary-blue) 0deg, var(--secondary-blue) 180deg, var(--primary-blue) 360deg);
            opacity: 0.1;
            animation: gradientRotate 30s linear infinite;
        }

        /* Particules géométriques */
        .particles-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: var(--white);
            border-radius: 50%;
            opacity: 0.1;
        }

        .particle-1 {
            width: 4px;
            height: 4px;
            top: 20%;
            left: 10%;
            animation: particleFloat 8s ease-in-out infinite;
        }

        .particle-2 {
            width: 8px;
            height: 8px;
            top: 60%;
            right: 15%;
            animation: particleFloat 12s ease-in-out infinite reverse;
        }

        .particle-3 {
            width: 6px;
            height: 6px;
            bottom: 30%;
            left: 20%;
            animation: particleFloat 10s ease-in-out infinite;
        }

        .particle-4 {
            width: 3px;
            height: 3px;
            top: 40%;
            right: 30%;
            animation: particleFloat 15s ease-in-out infinite reverse;
        }

        .particle-5 {
            width: 5px;
            height: 5px;
            bottom: 20%;
            left: 70%;
            animation: particleFloat 9s ease-in-out infinite;
        }

        .particle-6 {
            width: 7px;
            height: 7px;
            top: 70%;
            right: 10%;
            animation: particleFloat 11s ease-in-out infinite reverse;
        }

        /* Grille hexagonale */
        .hex-grid {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                    radial-gradient(circle at 25px 25px, var(--white) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.02;
            animation: gridPulse 20s ease-in-out infinite;
        }

        /* Contenu principal */
        .main-content {
            position: relative;
            z-index: var(--z-normal);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            padding: var(--space-md);
            gap: var(--space-2xl);
        }

        /* Navigation mobile */
        .mobile-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-md) var(--space-lg);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-2xl);
            margin-bottom: var(--space-lg);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .brand-icon {
            width: 2rem;
            height: 2rem;
            padding: var(--space-xs);
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg {
            width: 1.25rem;
            height: 1.25rem;
            color: var(--white);
        }

        .brand-text {
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--white);
        }

        .nav-status {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: statusPulse 2s ease-in-out infinite;
        }

        .status-text {
            font-size: 0.875rem;
            color: var(--gray-300);
            font-weight: 500;
        }

        /* Section Hero */
        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: var(--space-xl) 0;
        }

        .hero-content {
            max-width: 600px;
            width: 100%;
        }

        /* Logo principal */
        .logo-container {
            margin-bottom: var(--space-2xl);
        }

        .logo-wrapper {
            position: relative;
            display: inline-block;
            animation: logoEntrance 1s ease-out;
        }

        .logo-bg {
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            border-radius: var(--radius-3xl);
            filter: blur(20px);
            opacity: 0.3;
            animation: logoPulse 4s ease-in-out infinite;
        }

        .logo-icon {
            position: relative;
            width: 5rem;
            height: 5rem;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            border-radius: var(--radius-3xl);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-2xl);
            transition: transform var(--transition-normal);
        }

        .logo-icon:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .logo-icon svg {
            width: 2.5rem;
            height: 2.5rem;
            color: var(--white);
            stroke-width: 2;
        }

        .logo-pulse {
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid var(--secondary-blue);
            border-radius: var(--radius-3xl);
            animation: pulseBorder 3s ease-in-out infinite;
        }

        /* Texte hero */
        .hero-text {
            margin-bottom: var(--space-2xl);
        }

        .main-title {
            font-size: clamp(2.5rem, 8vw, 4rem);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: var(--space-lg);
            letter-spacing: -0.02em;
        }

        .title-line {
            display: block;
            background: linear-gradient(135deg, var(--white) 0%, var(--light-blue) 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleSlide 1s ease-out 0.5s both;
        }

        .title-accent {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--accent-blue) 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation-delay: 0.7s;
        }

        .main-subtitle {
            font-size: clamp(1rem, 3vw, 1.25rem);
            color: var(--gray-300);
            font-weight: 500;
            line-height: 1.6;
            animation: subtitleFade 1s ease-out 1s both;
        }

        .title-decoration {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            margin-top: var(--space-lg);
            animation: decorationExpand 1s ease-out 1.2s both;
        }

        .decoration-line {
            width: 3rem;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--secondary-blue), transparent);
        }

        .decoration-dot {
            width: 8px;
            height: 8px;
            background: var(--secondary-blue);
            border-radius: 50%;
            animation: dotPulse 2s ease-in-out infinite;
        }

        /* Badges de confiance */
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: var(--space-md);
            flex-wrap: wrap;
            animation: badgesSlide 1s ease-out 1.4s both;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-sm) var(--space-md);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            font-size: 0.875rem;
            font-weight: 600;
            transition: all var(--transition-normal);
        }

        .badge:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .badge-icon {
            width: 1rem;
            height: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-secure .badge-icon {
            color: #10b981;
        }

        .badge-certified .badge-icon {
            color: var(--secondary-blue);
        }

        .french-flag {
            display: flex;
            width: 1rem;
            height: 0.75rem;
            border-radius: 2px;
            overflow: hidden;
        }

        .flag-blue,
        .flag-white,
        .flag-red {
            flex: 1;
            height: 100%;
        }

        .flag-blue { background: var(--primary-blue); }
        .flag-white { background: var(--white); }
        .flag-red { background: var(--red-france); }

        /* Section Formulaire */
        .form-section {
            display: flex;
            justify-content: center;
            padding: var(--space-xl) 0;
        }

        .form-container {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-3xl);
            padding: var(--space-2xl);
            box-shadow: var(--shadow-2xl);
            animation: formSlideUp 1s ease-out 0.8s both;
        }

        /* En-tête du formulaire */
        .form-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
        }

        .form-title-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-md);
            margin-bottom: var(--space-md);
        }

        .form-icon {
            width: 2.5rem;
            height: 2.5rem;
            padding: var(--space-sm);
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
        }

        .form-icon svg {
            width: 1.5rem;
            height: 1.5rem;
            color: var(--white);
            stroke-width: 2;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--white);
            margin: 0;
        }

        .form-subtitle {
            font-size: 1rem;
            color: var(--gray-300);
            font-weight: 500;
            margin: 0;
        }

        /* Formulaire */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
        }

        /* Groupes de champs */
        .field-group {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        .field-label {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-200);
            margin-bottom: var(--space-xs);
        }

        .label-icon {
            width: 1rem;
            height: 1rem;
            color: var(--secondary-blue);
        }

        .label-icon svg {
            width: 100%;
            height: 100%;
            stroke-width: 2;
        }

        /* Wrapper des champs */
        .field-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-input {
            width: 100%;
            padding: 1rem 1.25rem;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            color: var(--white);
            font-size: 1rem;
            font-weight: 500;
            transition: all var(--transition-normal);
            outline: none;
        }

        .field-input::placeholder {
            color: var(--gray-400);
            font-weight: 400;
        }

        .field-input:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .field-input:valid:not(:placeholder-shown) {
            border-color: #10b981;
        }

        .field-input:invalid:not(:placeholder-shown) {
            border-color: var(--red-france);
        }

        /* Ligne de focus */
        .field-focus-line {
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--secondary-blue), var(--accent-blue));
            transition: all var(--transition-normal);
            transform: translateX(-50%);
            border-radius: 1px;
        }

        .field-input:focus + .field-focus-line {
            width: 100%;
        }

        /* Validation */
        .field-validation {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all var(--transition-normal);
        }

        .field-input:valid:not(:placeholder-shown) ~ .field-validation {
            opacity: 1;
        }

        .validation-icon {
            width: 1.25rem;
            height: 1.25rem;
            color: #10b981;
        }

        .validation-icon svg {
            width: 100%;
            height: 100%;
            stroke-width: 2;
        }

        /* Toggle mot de passe */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: var(--space-xs);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .password-toggle:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.1);
        }

        .password-toggle svg {
            width: 1.25rem;
            height: 1.25rem;
            stroke-width: 2;
        }

        .password-toggle .eye-closed {
            display: none;
        }

        .password-toggle.active .eye-open {
            display: none;
        }

        .password-toggle.active .eye-closed {
            display: block;
        }

        /* Erreurs */
        .field-error {
            font-size: 0.875rem;
            color: var(--red-france);
            font-weight: 500;
            min-height: 1.25rem;
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            opacity: 0;
            transform: translateY(-5px);
            transition: all var(--transition-normal);
        }

        .field-error.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Options du formulaire */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--space-md);
        }

        /* Checkbox personnalisée */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
        }

        .checkbox-input {
            display: none;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            cursor: pointer;
            user-select: none;
        }

        .checkbox-custom {
            position: relative;
            width: 1.25rem;
            height: 1.25rem;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-sm);
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkbox-input:checked + .checkbox-label .checkbox-custom {
            background: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }

        .checkbox-icon {
            width: 0.875rem;
            height: 0.875rem;
            color: var(--white);
            opacity: 0;
            transform: scale(0);
            transition: all var(--transition-normal);
            stroke-width: 3;
        }

        .checkbox-input:checked + .checkbox-label .checkbox-icon {
            opacity: 1;
            transform: scale(1);
        }

        .checkbox-text {
            font-size: 0.875rem;
            color: var(--gray-300);
            font-weight: 500;
        }

        /* Lien mot de passe oublié */
        .forgot-link {
            font-size: 0.875rem;
            color: var(--secondary-blue);
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-fast);
        }

        .forgot-link:hover {
            color: var(--accent-blue);
            text-decoration: underline;
        }

        /* Bouton de soumission */
        .submit-btn {
            position: relative;
            width: 100%;
            padding: 1.25rem 2rem;
            background: transparent;
            border: none;
            border-radius: var(--radius-xl);
            cursor: pointer;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--white);
            overflow: hidden;
            transition: all var(--transition-normal);
            transform-style: preserve-3d;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            transition: all var(--transition-normal);
        }

        .submit-btn:hover .btn-bg {
            background: linear-gradient(135deg, var(--accent-blue), var(--secondary-blue));
        }

        .btn-content {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            z-index: 2;
        }

        .btn-icon {
            width: 1.25rem;
            height: 1.25rem;
            transition: transform var(--transition-normal);
        }

        .btn-icon svg {
            width: 100%;
            height: 100%;
            stroke-width: 2;
        }

        .submit-btn:hover .btn-icon {
            transform: translateX(4px);
        }

        .btn-loading {
            width: 1.25rem;
            height: 1.25rem;
            display: none;
        }

        .btn-loading svg {
            width: 100%;
            height: 100%;
            animation: spin 1s linear infinite;
        }

        .submit-btn.loading .btn-icon {
            display: none;
        }

        .submit-btn.loading .btn-loading {
            display: block;
        }

        .btn-text {
            white-space: nowrap;
        }

        /* Effet ripple */
        .btn-ripple {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .submit-btn:active .btn-ripple {
            animation: ripple 0.6s ease-out;
        }

        /* Footer du formulaire */
        .form-footer {
            margin-top: var(--space-2xl);
            padding-top: var(--space-2xl);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .security-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-lg);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-md);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-xl);
            transition: all var(--transition-normal);
        }

        .info-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .info-icon {
            width: 2rem;
            height: 2rem;
            padding: var(--space-xs);
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-icon svg {
            width: 1.25rem;
            height: 1.25rem;
            color: var(--white);
        }

        .french-flag-mini {
            display: flex;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .mini-blue,
        .mini-white,
        .mini-red {
            width: 0.5rem;
            height: 1.25rem;
        }

        .mini-blue { background: var(--primary-blue); }
        .mini-white { background: var(--white); }
        .mini-red { background: var(--red-france); }

        .info-content {
            flex: 1;
        }

        .info-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.125rem;
        }

        .info-subtitle {
            font-size: 0.75rem;
            color: var(--gray-400);
            font-weight: 500;
        }

        /* Section Stats */
        .stats-section {
            padding: var(--space-xl) 0;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
            animation: statsHeaderSlide 1s ease-out 1.6s both;
        }

        .stats-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--white);
            margin-bottom: var(--space-sm);
        }

        .stats-subtitle {
            font-size: 1rem;
            color: var(--gray-300);
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-lg);
            animation: statsGridSlide 1s ease-out 1.8s both;
        }

        .stat-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-xl);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-2xl);
            transition: all var(--transition-normal);
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-xl);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            padding: var(--space-sm);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .uptime .stat-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .support .stat-icon {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
        }

        .security .stat-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .compliance .stat-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-icon svg {
            width: 1.5rem;
            height: 1.5rem;
            color: var(--white);
            stroke-width: 2;
        }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 900;
            color: var(--white);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-300);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-trend {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-trend.up {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .stat-trend.stable {
            background: rgba(59, 130, 246, 0.2);
            color: var(--secondary-blue);
        }

        .stat-trend svg {
            width: 1rem;
            height: 1rem;
        }

        /* Animations */
        @keyframes gradientShift {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(1deg); }
        }

        @keyframes gradientFloat {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-20px) translateX(10px); }
        }

        @keyframes gradientRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes particleFloat {
            0%, 100% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
                opacity: 0.1;
            }
            50% {
                transform: translateY(-30px) translateX(20px) rotate(180deg);
                opacity: 0.3;
            }
        }

        @keyframes gridPulse {
            0%, 100% { opacity: 0.02; }
            50% { opacity: 0.05; }
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        @keyframes logoEntrance {
            from {
                opacity: 0;
                transform: scale(0.5) rotateY(180deg);
            }
            to {
                opacity: 1;
                transform: scale(1) rotateY(0deg);
            }
        }

        @keyframes logoPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.5;
            }
        }

        @keyframes pulseBorder {
            0%, 100% {
                opacity: 0;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        @keyframes titleSlide {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes subtitleFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes decorationExpand {
            from {
                opacity: 0;
                transform: scaleX(0);
            }
            to {
                opacity: 1;
                transform: scaleX(1);
            }
        }

        @keyframes dotPulse {
            0%, 100% {
                opacity: 0.5;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        @keyframes badgesSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes formSlideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes statsHeaderSlide {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes statsGridSlide {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes ripple {
            from {
                width: 0;
                height: 0;
                opacity: 1;
            }
            to {
                width: 300px;
                height: 300px;
                opacity: 0;
            }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive Design Ultra-Optimisé */

        /* Écrans très petits (320px-480px) */
        @media (max-width: 480px) {
            :root {
                --space-xs: 0.125rem;
                --space-sm: 0.25rem;
                --space-md: 0.5rem;
                --space-lg: 0.75rem;
                --space-xl: 1rem;
                --space-2xl: 1.5rem;
                --space-3xl: 2rem;
            }

            .main-content {
                padding: var(--space-sm);
                gap: var(--space-lg);
            }

            .mobile-nav {
                padding: var(--space-sm) var(--space-md);
                margin-bottom: var(--space-md);
            }

            .brand-text {
                font-size: 1rem;
            }

            .hero-section {
                padding: var(--space-md) 0;
            }

            .logo-icon {
                width: 4rem;
                height: 4rem;
            }

            .logo-icon svg {
                width: 2rem;
                height: 2rem;
            }

            .main-title {
                font-size: 2rem;
                margin-bottom: var(--space-md);
            }

            .main-subtitle {
                font-size: 0.875rem;
            }

            .trust-badges {
                gap: var(--space-sm);
            }

            .badge {
                padding: var(--space-xs) var(--space-sm);
                font-size: 0.75rem;
            }

            .form-container {
                padding: var(--space-lg);
            }

            .form-title {
                font-size: 1.5rem;
            }

            .form-title-wrapper {
                flex-direction: column;
                gap: var(--space-sm);
            }

            .field-input {
                padding: 0.875rem 1rem;
                font-size: 0.875rem;
            }

            .submit-btn {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: var(--space-md);
            }

            .stat-card {
                padding: var(--space-md);
            }

            .stat-icon {
                width: 2.5rem;
                height: 2.5rem;
            }

            .stat-icon svg {
                width: 1.25rem;
                height: 1.25rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .security-info {
                gap: var(--space-md);
            }

            .info-item {
                padding: var(--space-sm);
                flex-direction: column;
                text-align: center;
                gap: var(--space-sm);
            }

            .info-icon {
                width: 1.5rem;
                height: 1.5rem;
            }

            .info-title {
                font-size: 0.75rem;
            }

            .info-subtitle {
                font-size: 0.625rem;
            }
        }

        /* Écrans petits (481px-768px) */
        @media (min-width: 481px) and (max-width: 768px) {
            .main-content {
                padding: var(--space-md);
                gap: var(--space-xl);
            }

            .mobile-nav {
                padding: var(--space-md) var(--space-lg);
            }

            .hero-section {
                padding: var(--space-lg) 0;
            }

            .logo-icon {
                width: 4.5rem;
                height: 4.5rem;
            }

            .main-title {
                font-size: 3rem;
            }

            .form-container {
                padding: var(--space-xl);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .security-info {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-item {
                flex-direction: row;
                text-align: left;
            }
        }

        /* Écrans moyens (769px-1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .main-content {
                max-width: 800px;
                margin: 0 auto;
                padding: var(--space-lg);
            }

            .mobile-nav {
                display: none;
            }

            .hero-content {
                max-width: 700px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .security-info {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Écrans larges (1025px+) */
        @media (min-width: 1025px) {
            .main-content {
                max-width: 1200px;
                margin: 0 auto;
                padding: var(--space-xl);
                gap: var(--space-3xl);
            }

            .mobile-nav {
                display: none;
            }

            .hero-section {
                padding: var(--space-2xl) 0;
            }

            .form-section {
                padding: var(--space-2xl) 0;
            }

            .stats-section {
                padding: var(--space-2xl) 0;
            }

            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .security-info {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Écrans très larges (1440px+) */
        @media (min-width: 1440px) {
            .main-content {
                max-width: 1400px;
            }

            .hero-content {
                max-width: 800px;
            }

            .form-container {
                max-width: 520px;
            }
        }

        /* Mode paysage mobile */
        @media (orientation: landscape) and (max-height: 500px) {
            .main-content {
                gap: var(--space-md);
            }

            .hero-section {
                padding: var(--space-sm) 0;
            }

            .logo-icon {
                width: 3rem;
                height: 3rem;
            }

            .logo-icon svg {
                width: 1.5rem;
                height: 1.5rem;
            }

            .main-title {
                font-size: 2rem;
                margin-bottom: var(--space-sm);
            }

            .main-subtitle {
                font-size: 0.875rem;
            }

            .trust-badges {
                margin-top: var(--space-sm);
            }

            .form-container {
                padding: var(--space-lg);
            }

            .stats-section {
                display: none;
            }
        }

        /* Accessibilité - Réduction de mouvement */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .particle {
                display: none;
            }

            .bg-layer-2,
            .bg-layer-3 {
                animation: none;
            }
        }

        /* Mode sombre forcé */
        @media (prefers-color-scheme: dark) {
            /* Le design est déjà sombre, pas de changements nécessaires */
        }

        /* Impression */
        @media print {
            .login-container {
                background: white !important;
                color: black !important;
            }

            .background-wrapper,
            .particles-container,
            .hex-grid {
                display: none !important;
            }

            .main-content {
                background: white !important;
                color: black !important;
            }

            .form-container {
                background: white !important;
                border: 2px solid black !important;
                box-shadow: none !important;
            }

            .submit-btn,
            .badge,
            .stat-card {
                background: white !important;
                border: 1px solid black !important;
                color: black !important;
            }

            .stats-section {
                page-break-before: always;
            }
        }

        /* États de focus pour l'accessibilité */
        .field-input:focus,
        .checkbox-input:focus + .checkbox-label .checkbox-custom,
        .submit-btn:focus,
        .forgot-link:focus {
            outline: 2px solid var(--secondary-blue);
            outline-offset: 2px;
        }

        /* États hover pour touch devices */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:hover,
            .info-item:hover,
            .badge:hover {
                transform: none;
                background: rgba(255, 255, 255, 0.08);
            }

            .submit-btn:hover {
                transform: none;
                box-shadow: var(--shadow-lg);
            }

            .submit-btn:active {
                transform: scale(0.98);
            }
        }

        /* Performance - Will-change pour les éléments animés */
        .bg-layer,
        .particle,
        .logo-bg,
        .logo-pulse,
        .decoration-dot,
        .submit-btn,
        .stat-card {
            will-change: transform;
        }

        /* Fallbacks pour les navigateurs anciens */
        @supports not (backdrop-filter: blur(20px)) {
            .form-container,
            .mobile-nav,
            .badge,
            .stat-card {
                background: rgba(30, 64, 175, 0.9);
            }
        }

        @supports not (background-clip: text) {
            .title-line {
                color: var(--white);
            }
        }

        /* Optimisations pour les connexions lentes */
        @media (prefers-reduced-data: reduce) {
            .background-wrapper {
                background: var(--primary-blue);
            }

            .bg-layer-2,
            .bg-layer-3,
            .particles-container,
            .hex-grid {
                display: none;
            }

            .form-container {
                box-shadow: var(--shadow-md);
            }
        }

        /* États de chargement */
        .loading-state {
            pointer-events: none;
            opacity: 0.7;
        }

        .loading-state .submit-btn {
            cursor: wait;
        }

        .loading-state .field-input {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Animation d'erreur pour les champs */
        .field-error-shake {
            animation: fieldShake 0.5s ease-in-out;
        }

        @keyframes fieldShake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        /* Animation de succès */
        .field-success {
            animation: fieldSuccess 0.5s ease-in-out;
        }

        @keyframes fieldSuccess {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* Indicateur de force du mot de passe */
        .password-strength {
            height: 4px;
            background: var(--gray-700);
            border-radius: 2px;
            margin-top: var(--space-xs);
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            border-radius: 2px;
            transition: all var(--transition-normal);
            width: 0%;
        }

        .password-strength-bar.weak {
            background: var(--red-france);
            width: 25%;
        }

        .password-strength-bar.medium {
            background: #f59e0b;
            width: 50%;
        }

        .password-strength-bar.strong {
            background: #10b981;
            width: 100%;
        }

        /* Notification toast */
        .toast-notification {
            position: fixed;
            top: var(--space-lg);
            right: var(--space-lg);
            padding: var(--space-md) var(--space-lg);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-xl);
            color: var(--white);
            font-weight: 500;
            z-index: var(--z-modal);
            transform: translateX(100%);
            transition: transform var(--transition-normal);
        }

        .toast-notification.show {
            transform: translateX(0);
        }

        .toast-notification.success {
            border-left: 4px solid #10b981;
        }

        .toast-notification.error {
            border-left: 4px solid var(--red-france);
        }

        .toast-notification.warning {
            border-left: 4px solid #f59e0b;
        }

        /* Loader global */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gray-900);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: var(--z-modal);
            opacity: 1;
            transition: opacity var(--transition-slow);
        }

        .page-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader-spinner {
            width: 3rem;
            height: 3rem;
            border: 3px solid rgba(59, 130, 246, 0.2);
            border-top: 3px solid var(--secondary-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Smooth scrolling pour toute la page */
        html {
            scroll-behavior: smooth;
        }

        /* Optimisation des polices */
        @font-face {
            font-family: 'System';
            src: local('.SFNS-Regular'), local('.SFNSText-Regular'),
            local('.HelveticaNeueDeskInterface-Regular'),
            local('.LucidaGrandeUI'), local('Segoe UI'),
            local('Ubuntu'), local('Roboto-Regular'), local('DroidSans'),
            local('Tahoma');
            font-weight: 400;
            font-display: swap;
        }

        /* Classes utilitaires */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .no-scroll {
            overflow: hidden;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        .fade-out {
            animation: fadeOut 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        /* Fin du CSS */
    </style>

    <!-- JavaScript Ultra-Moderne et Fonctionnel -->
    <script>
        'use strict';

        // Configuration globale
        const APP_CONFIG = {
            animation: {
                duration: 300,
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
            },
            validation: {
                email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                password: {
                    minLength: 8,
                    requireSpecial: false
                }
            },
            storage: {
                prefix: 'signale_france_',
                expiry: 30 * 24 * 60 * 60 * 1000 // 30 jours
            }
        };

        // Utilitaires
        const Utils = {
            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            },

            throttle(func, limit) {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            },

            animate(element, keyframes, options = {}) {
                if (!element || typeof element.animate !== 'function') return;

                const defaultOptions = {
                    duration: APP_CONFIG.animation.duration,
                    easing: APP_CONFIG.animation.easing,
                    fill: 'both'
                };

                return element.animate(keyframes, { ...defaultOptions, ...options });
            },

            setStorage(key, value, expiry = APP_CONFIG.storage.expiry) {
                const item = {
                    value,
                    expiry: Date.now() + expiry
                };
                localStorage.setItem(APP_CONFIG.storage.prefix + key, JSON.stringify(item));
            },

            getStorage(key) {
                try {
                    const item = JSON.parse(localStorage.getItem(APP_CONFIG.storage.prefix + key));
                    if (!item) return null;

                    if (Date.now() > item.expiry) {
                        localStorage.removeItem(APP_CONFIG.storage.prefix + key);
                        return null;
                    }

                    return item.value;
                } catch {
                    return null;
                }
            },

            removeStorage(key) {
                localStorage.removeItem(APP_CONFIG.storage.prefix + key);
            }
        };

        // Gestionnaire de validation
        class FormValidator {
            constructor() {
                this.rules = new Map();
                this.errors = new Map();
            }

            addRule(field, validator, message) {
                if (!this.rules.has(field)) {
                    this.rules.set(field, []);
                }
                this.rules.get(field).push({ validator, message });
            }

            validate(field, value) {
                const fieldRules = this.rules.get(field);
                if (!fieldRules) return true;

                for (const rule of fieldRules) {
                    if (!rule.validator(value)) {
                        this.errors.set(field, rule.message);
                        return false;
                    }
                }

                this.errors.delete(field);
                return true;
            }

            validateAll(formData) {
                let isValid = true;
                for (const [field, value] of Object.entries(formData)) {
                    if (!this.validate(field, value)) {
                        isValid = false;
                    }
                }
                return isValid;
            }

            getError(field) {
                return this.errors.get(field);
            }

            clearErrors() {
                this.errors.clear();
            }
        }

        // Gestionnaire de notifications
        class NotificationManager {
            constructor() {
                this.container = this.createContainer();
            }

            createContainer() {
                const container = document.createElement('div');
                container.className = 'notification-container';
                container.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            pointer-events: none;
        `;
                document.body.appendChild(container);
                return container;
            }

            show(message, type = 'info', duration = 5000) {
                const toast = document.createElement('div');
                toast.className = `toast-notification ${type}`;
                toast.textContent = message;
                toast.style.pointerEvents = 'auto';

                this.container.appendChild(toast);

                // Animation d'entrée
                requestAnimationFrame(() => {
                    toast.classList.add('show');
                });

                // Auto-suppression
                setTimeout(() => {
                    this.hide(toast);
                }, duration);

                return toast;
            }

            hide(toast) {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }

        // Gestionnaire de formulaire principal
        class LoginForm {
            constructor() {
                this.form = document.querySelector('.login-form');
                this.emailInput = document.getElementById('email');
                this.passwordInput = document.getElementById('password');
                this.rememberCheckbox = document.getElementById('remember-me');
                this.submitButton = document.querySelector('.submit-btn');

                this.validator = new FormValidator();
                this.notifications = new NotificationManager();
                this.isSubmitting = false;

                this.init();
            }

            init() {
                this.setupValidation();
                this.setupEventListeners();
                this.loadSavedCredentials();
                this.initPasswordStrength();
            }

            setupValidation() {
                // Règles de validation
                this.validator.addRule('email',
                    value => APP_CONFIG.validation.email.test(value),
                    'Veuillez entrer une adresse email valide'
                );

                this.validator.addRule('password',
                    value => value.length >= APP_CONFIG.validation.password.minLength,
                    `Le mot de passe doit contenir au moins ${APP_CONFIG.validation.password.minLength} caractères`
                );

                if (APP_CONFIG.validation.password.requireSpecial) {
                    this.validator.addRule('password',
                        value => /[!@#$%^&*(),.?":{}|<>]/.test(value),
                        'Le mot de passe doit contenir au moins un caractère spécial'
                    );
                }
            }

            setupEventListeners() {
                // Validation en temps réel
                this.emailInput.addEventListener('input',
                    Utils.debounce(() => this.validateField('email'), 300)
                );

                this.passwordInput.addEventListener('input',
                    Utils.debounce(() => this.validateField('password'), 300)
                );

                // Soumission du formulaire
                this.form.addEventListener('submit', this.handleSubmit.bind(this));

                // Remember me
                this.rememberCheckbox.addEventListener('change', this.handleRememberMe.bind(this));

                // Gestion des erreurs réseau
                window.addEventListener('online', () => {
                    this.notifications.show('Connexion internet rétablie', 'success');
                });

                window.addEventListener('offline', () => {
                    this.notifications.show('Connexion internet perdue', 'warning');
                });
            }

            validateField(fieldName) {
                const input = fieldName === 'email' ? this.emailInput : this.passwordInput;
                const value = input.value.trim();
                const isValid = this.validator.validate(fieldName, value);

                this.updateFieldUI(input, isValid, this.validator.getError(fieldName));

                if (fieldName === 'password') {
                    this.updatePasswordStrength(value);
                }

                return isValid;
            }

            updateFieldUI(input, isValid, errorMessage) {
                const fieldGroup = input.closest('.field-group');
                const errorElement = fieldGroup.querySelector('.field-error');

                // Supprimer les classes d'état précédentes
                input.classList.remove('field-error-shake', 'field-success');

                if (input.value.trim() === '') {
                    // Champ vide - état neutre
                    errorElement.textContent = '';
                    errorElement.classList.remove('show');
                    return;
                }

                if (isValid) {
                    // Champ valide
                    input.classList.add('field-success');
                    errorElement.textContent = '';
                    errorElement.classList.remove('show');
                } else {
                    // Champ invalide
                    input.classList.add('field-error-shake');
                    errorElement.textContent = errorMessage;
                    errorElement.classList.add('show');
                }
            }

            initPasswordStrength() {
                const strengthIndicator = document.createElement('div');
                strengthIndicator.className = 'password-strength';
                strengthIndicator.innerHTML = '<div class="password-strength-bar"></div>';

                const passwordGroup = this.passwordInput.closest('.field-group');
                passwordGroup.appendChild(strengthIndicator);

                this.strengthBar = strengthIndicator.querySelector('.password-strength-bar');
            }

            updatePasswordStrength(password) {
                if (!this.strengthBar) return;

                let strength = 0;
                let strengthClass = '';

                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

                if (strength <= 2) {
                    strengthClass = 'weak';
                } else if (strength <= 4) {
                    strengthClass = 'medium';
                } else {
                    strengthClass = 'strong';
                }

                this.strengthBar.className = `password-strength-bar ${strengthClass}`;
            }

            handleSubmit(event) {
                event.preventDefault();

                if (this.isSubmitting) return;

                const formData = {
                    email: this.emailInput.value.trim(),
                    password: this.passwordInput.value
                };

                // Validation complète
                if (!this.validator.validateAll(formData)) {
                    this.notifications.show('Veuillez corriger les erreurs dans le formulaire', 'error');
                    return;
                }

                // Vérification de la connexion
                if (!navigator.onLine) {
                    this.notifications.show('Aucune connexion internet disponible', 'error');
                    return;
                }

                this.submitForm(formData);
            }

            async submitForm(formData) {
                this.isSubmitting = true;
                this.setLoadingState(true);

                try {
                    // Sauvegarder les informations si demandé
                    if (this.rememberCheckbox.checked) {
                        this.saveCredentials(formData.email);
                    } else {
                        this.clearSavedCredentials();
                    }

                    // Simulation d'envoi (remplacez par votre logique réelle)
                    await this.simulateSubmission();

                    // Soumission réelle du formulaire
                    this.form.submit();

                } catch (error) {
                    console.error('Erreur de soumission:', error);
                    this.notifications.show('Une erreur est survenue. Veuillez réessayer.', 'error');
                    this.setLoadingState(false);
                    this.isSubmitting = false;
                }
            }

            async simulateSubmission() {
                // Simulation pour démonstration - remplacez par votre logique
                return new Promise(resolve => setTimeout(resolve, 1000));
            }

            setLoadingState(loading) {
                const btnText = this.submitButton.querySelector('.btn-text');
                const btnIcon = this.submitButton.querySelector('.btn-icon');
                const btnLoading = this.submitButton.querySelector('.btn-loading');

                if (loading) {
                    this.submitButton.classList.add('loading');
                    this.submitButton.disabled = true;
                    btnText.textContent = 'Connexion en cours...';
                    btnIcon.style.display = 'none';
                    btnLoading.style.display = 'block';
                } else {
                    this.submitButton.classList.remove('loading');
                    this.submitButton.disabled = false;
                    btnText.textContent = 'Accéder à la plateforme';
                    btnIcon.style.display = 'block';
                    btnLoading.style.display = 'none';
                }
            }

            handleRememberMe() {
                const isChecked = this.rememberCheckbox.checked;

                if (isChecked) {
                    Utils.setStorage('remember_preference', true);
                } else {
                    Utils.removeStorage('remember_preference');
                    this.clearSavedCredentials();
                }
            }

            saveCredentials(email) {
                Utils.setStorage('saved_email', email);
                Utils.setStorage('remember_preference', true);
            }

            loadSavedCredentials() {
                const rememberPreference = Utils.getStorage('remember_preference');
                const savedEmail = Utils.getStorage('saved_email');

                if (rememberPreference && savedEmail) {
                    this.emailInput.value = savedEmail;
                    this.rememberCheckbox.checked = true;

                    // Animation de remplissage
                    Utils.animate(this.emailInput, [
                        { transform: 'scale(1)' },
                        { transform: 'scale(1.02)' },
                        { transform: 'scale(1)' }
                    ], { duration: 200 });
                }
            }

            clearSavedCredentials() {
                Utils.removeStorage('saved_email');
                Utils.removeStorage('remember_preference');
            }
        }

        // Gestionnaire de toggle mot de passe
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');

            if (!passwordInput || !toggleButton) return;

            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            toggleButton.classList.toggle('active', isPassword);

            // Animation du bouton
            Utils.animate(toggleButton, [
                { transform: 'scale(1)' },
                { transform: 'scale(1.1)' },
                { transform: 'scale(1)' }
            ], { duration: 150 });
        }

        // Gestionnaire d'animations d'entrée
        class AnimationManager {
            constructor() {
                this.observer = new IntersectionObserver(
                    this.handleIntersection.bind(this),
                    { threshold: 0.1, rootMargin: '50px' }
                );

                this.init();
            }

            init() {
                // Observer les éléments animés
                const animatedElements = document.querySelectorAll(
                    '.hero-content, .form-container, .stats-container'
                );

                animatedElements.forEach(el => {
                    this.observer.observe(el);
                });

                // Animation de chargement de page
                this.animatePageLoad();
            }

            handleIntersection(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        this.observer.unobserve(entry.target);
                    }
                });
            }

            animatePageLoad() {
                // Masquer le loader
                setTimeout(() => {
                    const loader = document.querySelector('.page-loader');
                    if (loader) {
                        loader.classList.add('hidden');
                        setTimeout(() => {
                            loader.remove();
                        }, 500);
                    }
                }, 1000);

                // Animation en cascade des éléments
                const elements = [
                    '.logo-container',
                    '.hero-text',
                    '.trust-badges',
                    '.form-container',
                    '.stats-container'
                ];

                elements.forEach((selector, index) => {
                    const element = document.querySelector(selector);
                    if (element) {
                        setTimeout(() => {
                            element.classList.add('animate-in');
                        }, index * 200);
                    }
                });
            }
        }

        // Gestionnaire de performance
        class PerformanceManager {
            constructor() {
                this.init();
            }

            init() {
                // Lazy loading des images
                this.setupLazyLoading();

                // Préchargement des ressources critiques
                this.preloadCriticalResources();

                // Optimisation des animations
                this.optimizeAnimations();
            }

            setupLazyLoading() {
                if ('IntersectionObserver' in window) {
                    const images = document.querySelectorAll('img[data-src]');
                    const imageObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src;
                                img.classList.remove('lazy');
                                imageObserver.unobserve(img);
                            }
                        });
                    });

                    images.forEach(img => imageObserver.observe(img));
                }
            }

            preloadCriticalResources() {
                // Précharger les polices critiques
                const fontPreload = document.createElement('link');
                fontPreload.rel = 'preload';
                fontPreload.as = 'font';
                fontPreload.type = 'font/woff2';
                fontPreload.crossOrigin = 'anonymous';
                document.head.appendChild(fontPreload);
            }

            optimizeAnimations() {
                // Désactiver les animations si l'utilisateur préfère moins de mouvement
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    document.body.classList.add('reduced-motion');
                }

                // Optimisation des performances sur les appareils plus lents
                if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
                    document.body.classList.add('low-performance');
                }
            }
        }

        // Initialisation de l'application
        document.addEventListener('DOMContentLoaded', () => {
            // Initialisation des gestionnaires
            new LoginForm();
            new AnimationManager();
            new PerformanceManager();

            // Gestion des erreurs globales
            window.addEventListener('error', (event) => {
                console.error('Erreur JavaScript:', event.error);
            });

            window.addEventListener('unhandledrejection', (event) => {
                console.error('Promise rejetée:', event.reason);
                event.preventDefault();
            });
        });

        // Export pour utilisation externe si nécessaire
        window.SignaleFrance = {
            LoginForm,
            AnimationManager,
            PerformanceManager,
            Utils
        };
    </script>

<?php include_once('../Inc/Components/footer.php'); ?>
<?php include_once('../Inc/Components/footers.php'); ?>
<?php include('../Inc/Traitement/create_log.php'); ?>