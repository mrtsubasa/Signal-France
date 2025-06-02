<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Clarity">
    <meta name="description" content="Clarity-Corp est une entreprise spécialisée dans le développement de logiciels.">
    <meta name="keywords" content="innovation, services, solutions d'affaires, informatique, developpement, dev-web">
    <title>Signale France</title>
    <link rel="stylesheet" href="Assets/Css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'france-blue': '#000091',
                        'france-red': '#e1000f'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
       /* Conteneur principal des notifications */
#notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 420px;
    pointer-events: none;
}

/* Base de la notification */
.notification {
    position: relative;
    margin-bottom: 12px;
    padding: 0;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: auto;
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    max-width: 100%;
    word-wrap: break-word;
}

/* Animation d'entrée */
.notification.show {
    transform: translateX(0);
    opacity: 1;
}

/* Animation de sortie */
.notification.hide {
    transform: translateX(100%);
    opacity: 0;
    margin-bottom: 0;
    padding-top: 0;
    padding-bottom: 0;
}

/* Types de notifications */
.notification.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: rgba(16, 185, 129, 0.3);
}

.notification.error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border-color: rgba(239, 68, 68, 0.3);
}

.notification.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-color: rgba(245, 158, 11, 0.3);
}

.notification.info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-color: rgba(59, 130, 246, 0.3);
}

/* Contenu de la notification */
.notification-content {
    padding: 16px 20px;
    color: white;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    position: relative;
}

/* Icône */
.notification-icon {
    font-size: 20px;
    flex-shrink: 0;
    margin-top: 2px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

/* Texte principal */
.notification-body {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 14px;
    line-height: 1.4;
    margin-bottom: 4px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.notification-details {
    font-size: 12px;
    opacity: 0.9;
    line-height: 1.3;
    margin-top: 4px;
}

/* Bouton d'action */
.notification-action {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
    backdrop-filter: blur(10px);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.notification-action:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.notification-action:active {
    transform: translateY(0);
}

/* Bouton de fermeture */
.notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    padding: 4px;
    margin: -4px;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.notification-close:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

/* Barre de progression */
.notification-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.2);
    width: 100%;
    overflow: hidden;
}

.notification-progress-bar {
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    width: 100%;
    transform: translateX(-100%);
    transition: transform linear;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

/* Effet de pulsation pour les notifications importantes */
.notification.pulse {
    animation: notificationPulse 2s infinite;
}

@keyframes notificationPulse {
    0%, 100% {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    }
    50% {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 20px rgba(255, 255, 255, 0.1);
    }
}

/* Effet de survol */
.notification:hover {
    transform: translateX(-5px) scale(1.02);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.notification.hide:hover {
    transform: translateX(100%);
}

/* Responsive */
@media (max-width: 480px) {
    #notification-container {
        top: 10px;
        right: 10px;
        left: 10px;
        max-width: none;
    }
    
    .notification {
        margin-bottom: 8px;
    }
    
    .notification-content {
        padding: 14px 16px;
    }
    
    .notification-title {
        font-size: 13px;
    }
    
    .notification-details {
        font-size: 11px;
    }
}

/* Thème sombre */
@media (prefers-color-scheme: dark) {
    .notification {
        backdrop-filter: blur(20px);
        border-color: rgba(255, 255, 255, 0.1);
    }
}

/* Accessibilité */
@media (prefers-reduced-motion: reduce) {
    .notification {
        transition: opacity 0.2s ease;
    }
    
    .notification.show {
        transform: none;
    }
    
    .notification.hide {
        transform: none;
    }
    
    .notification:hover {
        transform: none;
    }
}

/* Focus pour l'accessibilité */
.notification:focus-within {
    outline: 2px solid rgba(255, 255, 255, 0.5);
    outline-offset: 2px;
}
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Container pour les notifications -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>