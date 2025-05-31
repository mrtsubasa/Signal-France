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
        .notification {
            animation: slideInDown 0.5s ease-out;
        }
        .notification.hide {
            animation: slideOutUp 0.5s ease-in forwards;
        }
        @keyframes slideInDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideOutUp {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(-100%); opacity: 0; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Container pour les notifications -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>