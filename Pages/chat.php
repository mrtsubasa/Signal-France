<?php
session_start();
include("../Inc/Constants/db.php");
require_once '../Inc/Components/header.php';
require_once '../Inc/Components/nav.php';


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur connecté
try {
    $db = connect_db();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $dataUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
    if ($dataUser) {
        $user = $dataUser;
        $id = $dataUser['id'];
        $username = $dataUser['username'];
        $email = $dataUser['email'];
        $role = $dataUser['role'];
        $banner = $dataUser['banner'];
        $avatar = $dataUser['avatar'];
        $user_avatar = $dataUser['avatar'];
        $organization = $dataUser['organization'] ?? '';
        $accreditation = $dataUser['accreditation'] ?? '';
        $phone = $dataUser['phone'] ?? '';
        $address = $dataUser['address'] ?? '';
        $city = $dataUser['city'] ?? '';
        $bio = $dataUser['bio'] ?? '';
        $created_at = $dataUser['created_at'];
        $last_activity = $dataUser['last_activity'];
        $github = $dataUser['github'] ?? '';
        $linkedin = $dataUser['linkedin'] ?? '';
        $website = $dataUser['website'] ?? '';
        $active = $dataUser['is_active'] ?? 1;
        $verified = $dataUser['is_verified'] ?? 0;
        $is_public = $dataUser['is_public'] ?? 0;
        $blacklisted = $dataUser['is_blacklisted'] ?? 0;
    } else {
        // Utilisateur non trouvé, rediriger vers login
        header('Location: login.php');
        exit;
    }
    
    // Récupérer les utilisateurs en ligne pour la sidebar
    $allUsers = $db->prepare("SELECT id, username, avatar, role, is_active FROM users WHERE id != ? ORDER BY is_active DESC, username ASC");
    $allUsers->execute([$user_id]);
    $allUsersList = $allUsers->fetchAll(PDO::FETCH_ASSOC);
    
    // Séparer les utilisateurs en ligne et hors ligne
    $onlineUsers = array_filter($allUsersList, function($user) {
        return $user['is_active'] == 1;
    });
    
    $offlineUsers = array_filter($allUsersList, function($user) {
        return $user['is_active'] == 0;
    });
    
} catch (PDOException $e) {
    error_log("Erreur chat: " . $e->getMessage());
    $allUsersList = [];
    $onlineUsers = [];
    $offlineUsers = [];
}
?>

<script src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js" type="module"></script>
    <style>
        :root {
            --discord-bg: #1a1b1e;
            --discord-sidebar: #1e1f22;
            --discord-input: #383a40;
            --discord-text: #f2f3f5;
            --discord-muted: #80848e;
            --discord-accent: #5865f2;
            --discord-green: #23a55a;
            --discord-hover: #2b2d31;
            --discord-border: #3f4147;
            --discord-red: #f23f43;
            --discord-yellow: #f0b90b;
            --discord-purple: #9c84ef;
            --gradient-primary: linear-gradient(135deg, #5865f2, #7289da);
            --gradient-secondary: linear-gradient(135deg, #23a55a, #2ecc71);
            --shadow-primary: 0 8px 32px rgba(88, 101, 242, 0.12);
            --shadow-secondary: 0 4px 16px rgba(0, 0, 0, 0.1);
            --blur-bg: rgba(30, 31, 34, 0.95);
        }

        body {
            background: var(--discord-bg);
            color: var(--discord-text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Enhanced Chat Container */
        .chat-container {
            height: calc(100vh - 80px);
            background: var(--discord-bg);
            position: relative;
            backdrop-filter: blur(20px);
        }

        /* Premium Sidebar */
        .sidebar {
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border-right: 1px solid var(--discord-border);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-secondary);
            position: relative;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(180deg, rgba(88, 101, 242, 0.1), transparent);
            pointer-events: none;
        }

        /* Enhanced Messages Container */
        .messages-container {
            height: calc(100% - 140px);
            background: var(--discord-bg);
            position: relative;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .messages-container::before {
            content: '';
            position: sticky;
            top: 0;
            display: block;
            height: 20px;
            background: linear-gradient(180deg, var(--discord-bg), transparent);
            z-index: 10;
            pointer-events: none;
        }

        /* Enhanced Message Bubbles */
        .message-bubble {
            animation: messageSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transition: all 0.3s ease;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid transparent;
        }

        .message-bubble:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-primary);
            border-color: rgba(88, 101, 242, 0.2);
        }

        .message-bubble::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .message-bubble:hover::before {
            opacity: 1;
        }

        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Enhanced Avatar */
        .user-avatar {
            position: relative;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.1);
        }

        .user-avatar::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .user-avatar:hover::after {
            opacity: 0.6;
        }

        /* Enhanced Online Status */
        .online-dot {
            animation: onlinePulse 2s infinite;
            box-shadow: 0 0 10px var(--discord-green);
        }

        @keyframes onlinePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 10px var(--discord-green);
            }
            50% {
                transform: scale(1.2);
                box-shadow: 0 0 20px var(--discord-green);
            }
        }

        /* Enhanced Chat Input */
        .chat-input {
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border: 2px solid var(--discord-border);
            color: var(--discord-text);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-secondary);
        }

        .chat-input:focus {
            outline: none;
            border-color: var(--discord-accent);
            box-shadow: 0 0 0 4px rgba(88, 101, 242, 0.2), var(--shadow-primary);
            transform: translateY(-2px);
        }

        .chat-input::placeholder {
            color: var(--discord-muted);
            transition: color 0.3s ease;
        }

        .chat-input:focus::placeholder {
            color: transparent;
        }

        /* Enhanced User Items */
        .user-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .user-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(88, 101, 242, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .user-item:hover::before {
            left: 100%;
        }

        .user-item:hover {
            background: var(--discord-hover);
            transform: translateX(8px);
            box-shadow: var(--shadow-secondary);
        }

        .user-item.selected {
            background: rgba(88, 101, 242, 0.15);
            border-left: 4px solid var(--discord-accent);
        }

        /* Enhanced Message Types */
        .message-own {
            background: linear-gradient(135deg, var(--discord-accent), #7289da);
            color: white;
            margin-left: auto;
            border-radius: 20px 20px 4px 20px;
            box-shadow: var(--shadow-primary);
        }

        .message-other {
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--discord-border);
            border-radius: 20px 20px 20px 4px;
            box-shadow: var(--shadow-secondary);
        }

        .message-system {
            background: linear-gradient(135deg, var(--discord-purple), #b794f6);
            color: white;
            text-align: center;
            border-radius: 20px;
            font-style: italic;
        }

        /* Enhanced Typing Indicator */
        .typing-indicator {
            animation: typingPulse 1.5s infinite ease-in-out;
        }

        @keyframes typingPulse {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .typing-container {
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border-radius: 20px;
            padding: 12px 20px;
            border: 1px solid var(--discord-border);
            animation: typingSlideIn 0.3s ease-out;
        }

        @keyframes typingSlideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Enhanced File Upload */
        .file-upload-area {
            border: 2px dashed var(--discord-muted);
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            position: relative;
            overflow: hidden;
        }

        .file-upload-area::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--discord-accent), var(--discord-purple));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .file-upload-area.dragover {
            border-color: var(--discord-accent);
            background: rgba(88, 101, 242, 0.1);
            transform: scale(1.02);
            box-shadow: var(--shadow-primary);
        }

        .file-upload-area.dragover::before {
            opacity: 0.1;
        }

        /* Enhanced File Preview */
        .file-preview {
            max-width: 300px;
            max-height: 200px;
            border-radius: 12px;
            box-shadow: var(--shadow-secondary);
            transition: all 0.3s ease;
        }

        .file-preview:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-primary);
        }

        /* Enhanced Buttons */
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-secondary);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-primary);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .send-button {
            background: var(--gradient-primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .send-button:hover {
            transform: scale(1.05) rotate(5deg);
            box-shadow: var(--shadow-primary);
        }

        /* Enhanced Sidebar Header */
        .sidebar-header {
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid var(--discord-border);
            position: relative;
        }

        .sidebar-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gradient-primary);
            opacity: 0.5;
        }

        /* Enhanced Chat Header */
        .chat-header {
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid var(--discord-border);
            box-shadow: var(--shadow-secondary);
            position: relative;
        }

        .chat-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient-primary);
            opacity: 0.3;
        }

        /* Enhanced Emoji Picker */
        .emoji-picker {
            position: absolute;
            bottom: 120px;
            right: 20px;
            z-index: 1000;
            display: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-primary);
            backdrop-filter: blur(25px);
            border: 1px solid var(--discord-border);
        }

        /* Enhanced Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 16px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            animation: notificationSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(25px);
            box-shadow: var(--shadow-primary);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes notificationSlideIn {
            from {
                transform: translateX(100%) scale(0.9);
                opacity: 0;
            }
            to {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
        }

        .notification.success {
            background: var(--gradient-secondary);
        }

        .notification.error {
            background: linear-gradient(135deg, var(--discord-red), #e53e3e);
        }

        .notification.info {
            background: var(--gradient-primary);
        }

        /* Enhanced Scrollbars */
        .scrollbar-enhanced {
            scrollbar-width: thin;
            scrollbar-color: var(--discord-accent) transparent;
        }

        .scrollbar-enhanced::-webkit-scrollbar {
            width: 12px;
        }

        .scrollbar-enhanced::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 6px;
        }

        .scrollbar-enhanced::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--discord-accent), var(--discord-purple));
            border-radius: 6px;
            border: 2px solid transparent;
            background-clip: content-box;
            transition: all 0.3s ease;
        }

        .scrollbar-enhanced::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #7289da, var(--discord-purple));
            transform: scale(1.1);
        }

        /* Role Indicators */
        .role-admin {
            border-left: 3px solid var(--discord-red);
        }

        .role-moderator {
            border-left: 3px solid var(--discord-yellow);
        }

        .role-user {
            border-left: 3px solid var(--discord-accent);
        }

        /* Message Actions */
        .message-actions {
            position: absolute;
            top: -15px;
            right: 20px;
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--discord-border);
            border-radius: 8px;
            padding: 4px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            box-shadow: var(--shadow-secondary);
        }

        .message-bubble:hover .message-actions {
            opacity: 1;
            transform: translateY(0);
        }

        .message-actions button {
            padding: 6px 8px;
            border: none;
            background: transparent;
            color: var(--discord-muted);
            border-radius: 4px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .message-actions button:hover {
            background: var(--discord-hover);
            color: var(--discord-text);
            transform: scale(1.1);
        }

        /* Enhanced Input Container */
        .chat-input-container {
            background: var(--blur-bg);
            backdrop-filter: blur(25px);
            border-top: 1px solid var(--discord-border);
            position: relative;
        }

        .chat-input-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gradient-primary);
            opacity: 0.3;
        }

        /* Status Indicators */
        .status-online {
            color: var(--discord-green);
            text-shadow: 0 0 10px var(--discord-green);
        }

        .status-away {
            color: var(--discord-yellow);
            text-shadow: 0 0 10px var(--discord-yellow);
        }

        .status-offline {
            color: var(--discord-muted);
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(88, 101, 242, 0.3);
            border-radius: 50%;
            border-top-color: var(--discord-accent);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Enhanced Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 80px;
                left: 0;
                width: 320px;
                height: calc(100vh - 80px);
                z-index: 1000;
                transform: translateX(-100%);
                box-shadow: var(--shadow-primary);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(5px);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }

            .chat-main {
                width: 100%;
                margin-left: 0;
            }

            .mobile-header {
                padding: 12px 16px;
                background: var(--blur-bg);
                backdrop-filter: blur(25px);
            }

            .message-bubble {
                margin-bottom: 12px;
                padding: 12px 16px;
            }

            .chat-input-container {
                padding: 12px 16px;
            }

            .chat-input {
                padding: 12px 16px;
                padding-right: 80px;
                font-size: 16px; /* Prevent zoom on iOS */
            }

            .send-button {
                padding: 12px;
            }

            .emoji-picker {
                bottom: 100px;
                right: 16px;
                left: 16px;
                width: auto;
            }

            .notification {
                left: 20px;
                right: 20px;
                transform: translateY(-100%);
            }

            .notification.show {
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100vw;
            }

            .message-bubble {
                margin-bottom: 8px;
                padding: 10px 12px;
            }

            .mobile-header {
                padding: 8px 12px;
            }

            .chat-input-container {
                padding: 8px 12px;
            }

            .user-item {
                padding: 8px 12px;
            }

            .sidebar-header {
                padding: 12px 16px;
            }
        }

        /* Dark mode enhancements */
        @media (prefers-color-scheme: dark) {
            :root {
                --discord-bg: #0d1117;
                --discord-sidebar: #161b22;
                --discord-input: #21262d;
                --blur-bg: rgba(22, 27, 34, 0.95);
            }
        }

        /* Focus visible for accessibility */
        .user-item:focus-visible,
        .message-bubble:focus-visible,
        .chat-input:focus-visible,
        button:focus-visible {
            outline: 2px solid var(--discord-accent);
            outline-offset: 2px;
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .message-bubble,
            .user-item,
            .typing-indicator,
            .online-dot {
                animation: none;
            }

            .message-bubble,
            .user-item,
            .chat-input,
            button {
                transition: none;
            }
        }
    </style>

    <body>
    <main>
        <!-- Enhanced Overlay pour mobile -->
        <div id="sidebar-overlay" class="sidebar-overlay"></div>

        <div class="chat-container flex">
            <!-- Enhanced Sidebar -->
            <div id="sidebar" class="w-80 md:w-80 sidebar flex flex-col">
                <!-- Enhanced Header sidebar -->
                <div class="sidebar-header p-4 border-b border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-white font-bold text-lg flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-green-500 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                                <i class="fas fa-users text-sm text-white"></i>
                            </div>
                            <span>Membres</span>
                            <span class="ml-3 bg-gradient-to-r from-blue-500 to-purple-500 px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                <?= count($allUsersList) ?>
                            </span>
                        </h2>
                        <button id="close-sidebar" class="md:hidden text-gray-400 hover:text-white p-2 hover:bg-gray-700 rounded-lg transition-all duration-200">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Search bar -->
                    <div class="mt-4 relative">
                        <input type="text"
                               id="user-search"
                               placeholder="Rechercher un membre..."
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Enhanced Liste des utilisateurs -->
                <div class="flex-1 overflow-y-auto p-4 space-y-2 scrollbar-enhanced">
                    <!-- Utilisateurs en ligne -->
                    <?php if (!empty($onlineUsers)): ?>
                        <div class="mb-6">
                            <h3 class="status-online font-semibold text-sm mb-3 flex items-center">
                                <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse shadow-lg"></div>
                                <span>En ligne (<?= count($onlineUsers) ?>)</span>
                            </h3>
                            <div class="space-y-1">
                                <?php foreach ($onlineUsers as $userItem): ?>
                                    <div class="user-item role-<?= $userItem['role'] ?> flex items-center p-3 rounded-lg cursor-pointer transition-all duration-300"
                                         data-user-id="<?= $userItem['id'] ?>"
                                         data-username="<?= htmlspecialchars($userItem['username']) ?>">
                                        <div class="relative user-avatar">
                                            <img src="../Assets/Images/avatars/<?= htmlspecialchars($userItem['avatar']) ?>"
                                                 alt="Avatar"
                                                 class="w-10 h-10 rounded-full object-cover ring-2 ring-transparent hover:ring-blue-400 transition-all duration-300">
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-gray-800 online-dot shadow-lg"></div>
                                        </div>
                                        <div class="ml-3 flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="font-semibold text-gray-200 text-sm truncate"><?= htmlspecialchars($userItem['username']) ?></p>
                                                <?php if ($userItem['role'] === 'admin'): ?>
                                                    <div class="w-4 h-4 bg-red-500 rounded-full flex items-center justify-center" title="Administrateur">
                                                        <i class="fas fa-crown text-xs text-white"></i>
                                                    </div>
                                                <?php elseif ($userItem['role'] === 'moderator'): ?>
                                                    <div class="w-4 h-4 bg-yellow-500 rounded-full flex items-center justify-center" title="Modérateur">
                                                        <i class="fas fa-shield-alt text-xs text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs text-gray-400 capitalize flex items-center mt-1">
                                            <div class="w-2 h-2 rounded-full mr-2 <?= $userItem['role'] === 'admin' ? 'bg-red-400' : ($userItem['role'] === 'moderator' ? 'bg-yellow-400' : 'bg-blue-400') ?>"></div>
                                            <?= htmlspecialchars($userItem['role']) ?>
                                            </p>
                                        </div>
                                        <div class="flex flex-col items-center space-y-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <button class="p-1 hover:bg-gray-600 rounded text-gray-400 hover:text-white transition-colors" title="Message privé">
                                                <i class="fas fa-comment text-xs"></i>
                                            </button>
                                            <button class="p-1 hover:bg-gray-600 rounded text-gray-400 hover:text-white transition-colors" title="Voir profil">
                                                <i class="fas fa-user text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Utilisateurs hors ligne -->
                    <?php if (!empty($offlineUsers)): ?>
                        <div class="mb-4">
                            <h3 class="status-offline font-semibold text-sm mb-3 flex items-center">
                                <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                                <span>Hors ligne (<?= count($offlineUsers) ?>)</span>
                            </h3>
                            <div class="space-y-1">
                                <?php foreach ($offlineUsers as $userItem): ?>
                                    <div class="user-item role-<?= $userItem['role'] ?> flex items-center p-3 rounded-lg cursor-pointer transition-all duration-300 opacity-60"
                                         data-user-id="<?= $userItem['id'] ?>"
                                         data-username="<?= htmlspecialchars($userItem['username']) ?>">
                                        <div class="relative user-avatar">
                                            <img src="../Assets/Images/avatars/<?= htmlspecialchars($userItem['avatar']) ?>"
                                                 alt="Avatar"
                                                 class="w-10 h-10 rounded-full object-cover grayscale hover:grayscale-0 transition-all duration-300">
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gray-500 rounded-full border-2 border-gray-800"></div>
                                        </div>
                                        <div class="ml-3 flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="font-medium text-gray-400 text-sm truncate"><?= htmlspecialchars($userItem['username']) ?></p>
                                                <?php if ($userItem['role'] === 'admin'): ?>
                                                    <div class="w-4 h-4 bg-red-500 rounded-full flex items-center justify-center opacity-50" title="Administrateur">
                                                        <i class="fas fa-crown text-xs text-white"></i>
                                                    </div>
                                                <?php elseif ($userItem['role'] === 'moderator'): ?>
                                                    <div class="w-4 h-4 bg-yellow-500 rounded-full flex items-center justify-center opacity-50" title="Modérateur">
                                                        <i class="fas fa-shield-alt text-xs text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs text-gray-500 capitalize flex items-center mt-1">
                                            <div class="w-2 h-2 rounded-full mr-2 bg-gray-500"></div>
                                            <?= htmlspecialchars($userItem['role']) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($allUsersList)): ?>
                        <div class="text-center py-12 text-gray-500">
                            <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user-slash text-2xl"></i>
                            </div>
                            <p class="text-sm font-medium">Aucun utilisateur trouvé</p>
                            <p class="text-xs mt-1">Essayez de rafraîchir la page</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced Zone de chat principale -->
            <div class="flex-1 flex flex-col chat-main">
                <!-- Enhanced Header du chat -->
                <div class="chat-header p-4 border-b border-gray-700 flex items-center justify-between mobile-header">
                    <div class="flex items-center">
                        <button id="open-sidebar" class="md:hidden mr-3 p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <div class="relative user-avatar">
                            <img src="../Assets/Images/avatars/<?= htmlspecialchars($user_avatar) ?>"
                                 alt="Mon Avatar"
                                 class="w-8 h-8 rounded-full object-cover ring-2 ring-green-400">
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border border-gray-800 online-dot"></div>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-bold text-white flex items-center text-base">
                                <div class="w-6 h-6 bg-gradient-to-r from-purple-500 to-blue-500 rounded-lg flex items-center justify-center mr-2">
                                    <i class="fas fa-hashtag text-xs text-white"></i>
                                </div>
                                <span>général</span>
                                <div class="ml-2 w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            </h3>
                            <p class="text-xs text-gray-400 hidden md:block">
                                <?= htmlspecialchars($username) ?> •
                                <span class="status-online"><?= count($onlineUsers) + 1 ?> en ligne</span> •
                                <?= count($allUsersList) + 1 ?> membres
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="searchMessages()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200 relative group" title="Rechercher">
                            <i class="fas fa-search"></i>
                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-black text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">Rechercher</span>
                        </button>
                        <button onclick="toggleNotifications()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200 relative group" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-black text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">Notifications</span>
                        </button>
                        <button onclick="openSettings()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200 relative group" title="Paramètres">
                            <i class="fas fa-cog"></i>
                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-black text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">Paramètres</span>
                        </button>
                    </div>
                </div>

                <!-- Enhanced Zone des messages -->
                <div class="messages-container flex-1 overflow-y-auto p-4 scrollbar-enhanced" id="messages-container">
                    <!-- Welcome message -->
                    <div class="text-center py-8 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-hashtag text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Bienvenue dans #général</h3>
                        <p class="text-gray-400 text-sm">C'est le début de votre conversation dans ce canal.</p>
                    </div>

                    <!-- Les messages seront chargés ici via JavaScript -->
                    <div id="messages-list"></div>

                    <!-- Enhanced Indicateur de frappe -->
                    <div id="typing-indicator" class="hidden mb-4">
                        <div class="typing-container flex items-center space-x-3">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-blue-400 rounded-full typing-indicator"></div>
                                <div class="w-2 h-2 bg-blue-400 rounded-full typing-indicator" style="animation-delay: 0.2s"></div>
                                <div class="w-2 h-2 bg-blue-400 rounded-full typing-indicator" style="animation-delay: 0.4s"></div>
                            </div>
                            <span id="typing-user" class="text-gray-400 text-sm font-medium">Quelqu'un est en train d'écrire...</span>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Zone de saisie -->
                <div class="chat-input-container p-4">
                    <!-- Enhanced Zone de drag & drop pour fichiers -->
                    <div id="file-upload-area" class="file-upload-area p-6 mb-4 text-center text-gray-400 hidden">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-cloud-upload-alt text-xl text-white"></i>
                        </div>
                        <p class="text-sm font-medium mb-1">Glissez vos fichiers ici ou cliquez pour sélectionner</p>
                        <p class="text-xs">Images, documents, vidéos (max 10MB)</p>
                    </div>

                    <!-- Enhanced Prévisualisation des fichiers -->
                    <div id="file-preview" class="mb-4 hidden">
                        <div class="flex items-center space-x-3 p-4 bg-gray-700 rounded-xl border border-gray-600">
                            <div id="preview-content" class="flex-1"></div>
                            <button id="remove-file" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-400/10 rounded-lg transition-all duration-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <form id="chat-form" class="relative">
                        <div class="flex items-end space-x-3">
                            <div class="flex-1 relative">
                                <input type="text"
                                       id="message-input"
                                       placeholder="Écrivez votre message dans #général..."
                                       class="w-full px-4 py-3 chat-input rounded-xl pr-20"
                                       autocomplete="off">

                                <!-- Enhanced Boutons dans l'input -->
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                                    <button type="button" id="emoji-btn" class="p-1 text-gray-400 hover:text-yellow-400 transition-all duration-200 hover:scale-110" title="Emojis">
                                        <i class="fas fa-smile text-lg"></i>
                                    </button>
                                    <button type="button" id="file-btn" class="p-1 text-gray-400 hover:text-blue-400 transition-all duration-200 hover:scale-110" title="Joindre un fichier">
                                        <i class="fas fa-paperclip text-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit"
                                    id="send-button"
                                    class="send-button bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white p-3 rounded-xl font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>

                        <!-- Input file caché -->
                        <input type="file" id="file-input" class="hidden" accept="image/*,video/*,.pdf,.doc,.docx,.txt" multiple>
                    </form>
                </div>
            </div>
        </div>

        <!-- Enhanced Emoji Picker -->
        <div id="emoji-picker" class="emoji-picker">
            <emoji-picker></emoji-picker>
        </div>
    </main>
    <script>
        // Variables globales
        const userId = <?= $user_id ?>;
        const username = '<?= addslashes($username) ?>';
        const userAvatar = '<?= addslashes($user_avatar) ?>';
        const userRole = '<?= addslashes($role) ?>';
        let lastMessageId = 0;
        let typingTimeout;
        let selectedFiles = [];
        
        // Éléments DOM
        const messagesContainer = document.getElementById('messages-container');
        const messagesList = document.getElementById('messages-list');
        const messageInput = document.getElementById('message-input');
        const chatForm = document.getElementById('chat-form');
        const sendButton = document.getElementById('send-button');
        const typingIndicator = document.getElementById('typing-indicator');
        const emojiBtn = document.getElementById('emoji-btn');
        const emojiPicker = document.getElementById('emoji-picker');
        const fileBtn = document.getElementById('file-btn');
        const fileInput = document.getElementById('file-input');
        const fileUploadArea = document.getElementById('file-upload-area');
        const filePreview = document.getElementById('file-preview');
        const previewContent = document.getElementById('preview-content');
        const removeFileBtn = document.getElementById('remove-file');
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadMessages();
    setInterval(loadMessages, 3000); // Actualiser toutes les 3 secondes
    
    // Gestion de la frappe
    messageInput.addEventListener('input', handleTyping);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
        }
    });
    
    // Gestion du formulaire
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
            
            // Gestion des emojis
            emojiBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                emojiPicker.style.display = emojiPicker.style.display === 'block' ? 'none' : 'block';
            });
            
            // Fermer emoji picker en cliquant ailleurs
            document.addEventListener('click', function() {
                emojiPicker.style.display = 'none';
            });
            
            // Sélection d'emoji
            document.querySelector('emoji-picker').addEventListener('emoji-click', function(event) {
                messageInput.value += event.detail.unicode;
                messageInput.focus();
                emojiPicker.style.display = 'none';
            });
            
            // Gestion des fichiers
            fileBtn.addEventListener('click', function() {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', handleFileSelect);
            removeFileBtn.addEventListener('click', clearFileSelection);
            
            // Drag & Drop
            messagesContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileUploadArea.classList.remove('hidden');
                fileUploadArea.classList.add('dragover');
            });
            
            messagesContainer.addEventListener('dragleave', function(e) {
                if (!messagesContainer.contains(e.relatedTarget)) {
                    fileUploadArea.classList.add('hidden');
                    fileUploadArea.classList.remove('dragover');
                }
            });
            
            messagesContainer.addEventListener('drop', function(e) {
                e.preventDefault();
                fileUploadArea.classList.add('hidden');
                fileUploadArea.classList.remove('dragover');
                
                const files = Array.from(e.dataTransfer.files);
                handleFiles(files);
            });
            
            // Sélection d'utilisateur
            document.querySelectorAll('.user-item').forEach(item => {
                item.addEventListener('click', function() {
                    const targetUsername = this.dataset.username;
                    messageInput.value = '@' + targetUsername + ' ';
                    messageInput.focus();
                });
            });
        });
        
        // Charger les messages
        function loadMessages() {
            fetch('chat_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_messages&last_id=' + lastMessageId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        addMessageToChat(message);
                        lastMessageId = Math.max(lastMessageId, message.id);
                    });
                    scrollToBottom();
                }
            })
            .catch(error => console.error('Erreur:', error));
        }
        
       // Modifier la fonction sendMessage pour gérer les réponses
       function sendMessage() {
            const message = messageInput.value.trim();
            if (!message && selectedFiles.length === 0) return;
            
            sendButton.disabled = true;
            
            const formData = new FormData();
            const replyPreview = document.getElementById('reply-preview');
            const replyToId = replyPreview?.dataset.replyToId;
            
            if (replyToId) {
                formData.append('action', 'reply_to_message');
                formData.append('reply_to_id', replyToId);
            } else {
                formData.append('action', 'send_message');
            }
            
            if (message) formData.append('message', message);
            
            // Ajouter les fichiers (seulement pour les nouveaux messages, pas les réponses)
            if (!replyToId) {
                selectedFiles.forEach((file, index) => {
                    formData.append('files[]', file);
                });
            }
            
            fetch('chat_ajax.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    clearFileSelection();
                    cancelReply();
                    loadMessages();
                } else {
                    showNotification('Erreur lors de l\'envoi: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            })
            .finally(() => {
                sendButton.disabled = false;
            });
        }
        
        function addMessageToChat(message) {
            const isOwnMessage = message.user_id == userId;
            const messageTime = new Date(message.created_at).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Gérer les réponses
            let replyHtml = '';
            if (message.reply_to_id && message.reply_to) {
                replyHtml = `
                    <div class="bg-gray-700 border-l-4 border-blue-400 p-2 mb-2 rounded-r text-sm">
                        <div class="flex items-center space-x-2 mb-1">
                            <i class="fas fa-reply text-blue-400"></i>
                            <span class="text-blue-400 font-semibold">${escapeHtml(message.reply_to.username)}</span>
                        </div>
                        <div class="text-gray-300">${formatMessage(message.reply_to.message)}</div>
                    </div>
                `;
            }
            
            const messageHtml = `
                <div class="message-bubble mb-4 hover:bg-gray-800 hover:bg-opacity-30 p-2 rounded-lg transition-colors duration-200 group" data-message-id="${message.id}">
                    <div class="flex items-start space-x-3">
                        <img src="../Assets/Images/avatars/${message.avatar}" 
                             alt="Avatar" 
                             class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline space-x-2 mb-1">
                                <span class="font-semibold text-white text-sm">${escapeHtml(message.username)}</span>
                                <span class="text-xs text-gray-400">${messageTime}</span>
                                ${message.edited_at ? '<span class="text-xs text-gray-500">(modifié)</span>' : ''}
                                
                                <!-- Boutons d'action -->
                                <div class="opacity-0 group-hover:opacity-100 flex items-center space-x-1 transition-opacity duration-200">
                                    <button onclick="replyToMessage(${message.id}, '${escapeHtml(message.username)}', '${escapeHtml(message.message)}')" 
                                            class="text-blue-400 hover:text-blue-300 text-xs" 
                                            title="Répondre">
                                        <i class="fas fa-reply"></i>
                                    </button>
                                    ${isOwnMessage ? `
                                        <button onclick="editMessage(${message.id}, '${escapeHtml(message.message)}')" 
                                                class="text-yellow-400 hover:text-yellow-300 text-xs" 
                                                title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    ` : ''}
                                    ${isOwnMessage || userRole === 'admin' || userRole === 'moderator' ? `
                                        <button onclick="deleteMessage(${message.id})" 
                                                class="text-red-400 hover:text-red-300 text-xs" 
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                            
                            ${replyHtml}
                            
                            <div class="text-gray-200 text-sm leading-relaxed" data-message-content>
                                ${formatMessage(message.message)}
                            </div>
                            
                            ${message.files ? formatFiles(message.files) : ''}
                        </div>
                    </div>
                </div>
            `;
            
            messagesList.insertAdjacentHTML('beforeend', messageHtml);
        }
        
        // Nouvelle fonction pour répondre à un message
        function replyToMessage(messageId, username, messageContent) {
            const replyPreview = document.getElementById('reply-preview') || createReplyPreview();
            
            replyPreview.innerHTML = `
                <div class="flex items-center justify-between bg-blue-600 bg-opacity-20 border-l-4 border-blue-400 p-3 rounded-r">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <i class="fas fa-reply text-blue-400"></i>
                            <span class="text-blue-400 font-semibold text-sm">Réponse à ${escapeHtml(username)}</span>
                        </div>
                        <div class="text-gray-300 text-sm truncate">${escapeHtml(messageContent.substring(0, 100))}${messageContent.length > 100 ? '...' : ''}</div>
                    </div>
                    <button onclick="cancelReply()" class="text-gray-400 hover:text-white ml-3">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            replyPreview.classList.remove('hidden');
            replyPreview.dataset.replyToId = messageId;
            messageInput.focus();
            messageInput.placeholder = `Répondre à ${username}...`;
        }
        
        // Créer l'élément de prévisualisation de réponse
        function createReplyPreview() {
            const replyPreview = document.createElement('div');
            replyPreview.id = 'reply-preview';
            replyPreview.className = 'mb-3 hidden';
            
            const chatForm = document.getElementById('chat-form');
            chatForm.parentNode.insertBefore(replyPreview, chatForm);
            
            return replyPreview;
        }
        
        // Annuler la réponse
        function cancelReply() {
            const replyPreview = document.getElementById('reply-preview');
            if (replyPreview) {
                replyPreview.classList.add('hidden');
                replyPreview.dataset.replyToId = '';
            }
            messageInput.placeholder = 'Écrivez votre message dans #général...';
        }
        
        // Nouvelle fonction pour modifier un message
        function editMessage(messageId, currentMessage) {
            const newMessage = prompt('Modifier le message:', currentMessage);
            if (!newMessage || newMessage.trim() === '' || newMessage === currentMessage) {
                return;
            }
            
            fetch('chat_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=edit_message&message_id=${messageId}&message=${encodeURIComponent(newMessage)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour le message dans le DOM
                    const messageElement = document.querySelector(`[data-message-id="${messageId}"] [data-message-content]`);
                    if (messageElement) {
                        messageElement.innerHTML = formatMessage(newMessage);
                    }
                    
                    // Ajouter l'indicateur de modification
                    const timeElement = messageElement.closest('.message-bubble').querySelector('.text-xs.text-gray-400');
                    if (timeElement && !timeElement.nextElementSibling?.textContent.includes('modifié')) {
                        timeElement.insertAdjacentHTML('afterend', '<span class="text-xs text-gray-500 ml-1">(modifié)</span>');
                    }
                    
                    showNotification('Message modifié', 'success');
                } else {
                    showNotification('Erreur: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        }
        
       
        // Formater le message (emojis, mentions, etc.)
        function formatMessage(text) {
            if (!text) return '';
            
            // Échapper le HTML
            text = escapeHtml(text);
            
            // Formater les mentions
            text = text.replace(/@(\w+)/g, '<span class="text-blue-400 font-semibold">@$1</span>');
            
            // Formater les liens
            text = text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-blue-400 underline hover:text-blue-300">$1</a>');
            
            return text;
        }
        
        // Formater les fichiers
        function formatFiles(files) {
            if (!files || files.length === 0) return '';
            
            return files.map(file => {
                if (file.type.startsWith('image/')) {
                    return `<img src="../uploads/chat/${file.name}" alt="Image" class="file-preview mt-2 cursor-pointer" onclick="openImageModal(this.src)">`;
                } else {
                    return `
                        <div class="mt-2 p-3 bg-gray-700 rounded-lg flex items-center space-x-3 max-w-sm">
                            <i class="fas fa-file text-blue-400 text-lg"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm font-medium truncate">${escapeHtml(file.original_name)}</p>
                                <p class="text-gray-400 text-xs">${formatFileSize(file.size)}</p>
                            </div>
                            <a href="../uploads/chat/${file.name}" download class="text-blue-400 hover:text-blue-300">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    `;
                }
            }).join('');
        }
        
        // Gestion des fichiers
        function handleFileSelect(e) {
            const files = Array.from(e.target.files);
            handleFiles(files);
        }
        
        function handleFiles(files) {
            // Filtrer et valider les fichiers
            const validFiles = files.filter(file => {
                if (file.size > 10 * 1024 * 1024) { // 10MB max
                    showNotification(`Fichier trop volumineux: ${file.name}`, 'error');
                    return false;
                }
                return true;
            });
            
            if (validFiles.length === 0) return;
            
            selectedFiles = validFiles;
            showFilePreview();
        }
        
        // Supprimer un message
        function deleteMessage(messageId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
                return;
            }
            
            fetch('chat_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_message&message_id=${messageId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Supprimer le message du DOM
                    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                    if (messageElement) {
                        messageElement.remove();
                    }
                    showNotification('Message supprimé', 'success');
                } else {
                    showNotification('Erreur: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        }

        // Rechercher des messages
        function searchMessages() {
            const searchTerm = prompt('Rechercher dans les messages:');
            if (!searchTerm || searchTerm.trim() === '') return;

            fetch('chat_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=search_messages&search=${encodeURIComponent(searchTerm)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher les résultats de recherche
                    displaySearchResults(data.messages, searchTerm);
                } else {
                    showNotification('Erreur: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        }

        // Enhanced JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            initializeEnhancedChat();
            initializeUserSearch();
            initializeMessageEffects();
            initializeKeyboardShortcuts();
        });

        function initializeEnhancedChat() {
            // Enhanced sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openBtn = document.getElementById('open-sidebar');
            const closeBtn = document.getElementById('close-sidebar');

            openBtn?.addEventListener('click', () => {
                sidebar.classList.add('open');
                overlay.classList.add('open');
            });

            closeBtn?.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });

            overlay?.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });

            // Enhanced message input
            const messageInput = document.getElementById('message-input');
            messageInput?.addEventListener('focus', function() {
                this.placeholder = 'Tapez votre message...';
            });

            messageInput?.addEventListener('blur', function() {
                this.placeholder = 'Écrivez votre message dans #général...';
            });

            // Enhanced user selection
            document.querySelectorAll('.user-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.user-item').forEach(u => u.classList.remove('selected'));
                    this.classList.add('selected');

                    // Scroll to user's messages
                    const username = this.dataset.username;
                    highlightUserMessages(username);
                });
            });
        }

        function initializeUserSearch() {
            const searchInput = document.getElementById('user-search');
            const userItems = document.querySelectorAll('.user-item');

            searchInput?.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();

                userItems.forEach(item => {
                    const username = item.dataset.username.toLowerCase();
                    const isVisible = username.includes(searchTerm);

                    item.style.display = isVisible ? 'flex' : 'none';

                    if (isVisible && searchTerm) {
                        item.classList.add('animate-pulse');
                        setTimeout(() => item.classList.remove('animate-pulse'), 1000);
                    }
                });
            });
        }

        function initializeMessageEffects() {
            // Enhanced message animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = Math.random() * 0.5 + 's';
                        entry.target.classList.add('animate-in');
                    }
                });
            }, { threshold: 0.1 });

            // Observe future messages
            const messagesList = document.getElementById('messages-list');
            if (messagesList) {
                new MutationObserver(mutations => {
                    mutations.forEach(mutation => {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === 1 && node.classList.contains('message-bubble')) {
                                observer.observe(node);
                            }
                        });
                    });
                }).observe(messagesList, { childList: true });
            }
        }

        function initializeKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + K for search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    document.getElementById('user-search')?.focus();
                }

                // Escape to close sidebar on mobile
                if (e.key === 'Escape') {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebar-overlay');
                    sidebar?.classList.remove('open');
                    overlay?.classList.remove('open');
                }
            });
        }

        function highlightUserMessages(username) {
            // Highlight messages from specific user
            document.querySelectorAll('.message-bubble').forEach(msg => {
                msg.classList.remove('highlighted');
                if (msg.dataset.username === username) {
                    msg.classList.add('highlighted');
                    setTimeout(() => msg.classList.remove('highlighted'), 3000);
                }
            });
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type} show`;
            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Enhanced functions for your existing functionality


        function toggleNotifications() {
            showNotification('Notifications activées', 'success');
        }



        // Afficher les résultats de recherche
        function displaySearchResults(messages, searchTerm) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-gray-800 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-white font-bold text-lg">Résultats pour "${escapeHtml(searchTerm)}"</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="space-y-3">
                        ${messages.length === 0 ? '<p class="text-gray-400 text-center">Aucun résultat trouvé</p>' : 
                          messages.map(msg => `
                            <div class="bg-gray-700 p-3 rounded">
                                <div class="flex items-center space-x-2 mb-1">
                                    <img src="../Assets/Images/avatars/${msg.avatar}" class="w-6 h-6 rounded-full">
                                    <span class="text-white font-semibold text-sm">${escapeHtml(msg.username)}</span>
                                    <span class="text-gray-400 text-xs">${new Date(msg.created_at).toLocaleString('fr-FR')}</span>
                                </div>
                                <div class="text-gray-200 text-sm">${formatMessage(msg.message)}</div>
                            </div>
                          `).join('')}
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        // Ouvrir les paramètres
        function openSettings() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-white font-bold text-lg">Paramètres du Chat</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-300 text-sm mb-2">Notifications sonores</label>
                            <input type="checkbox" id="soundNotifications" class="rounded">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm mb-2">Thème sombre</label>
                            <input type="checkbox" id="darkTheme" checked disabled class="rounded">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm mb-2">Affichage compact</label>
                            <input type="checkbox" id="compactMode" class="rounded">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="this.closest('.fixed').remove()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Fermer
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        function showFilePreview() {
            if (selectedFiles.length === 0) {
                filePreview.classList.add('hidden');
                return;
            }
            
            const file = selectedFiles[0]; // Afficher le premier fichier
            previewContent.innerHTML = '';
            
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'w-16 h-16 object-cover rounded';
                previewContent.appendChild(img);
            } else {
                const icon = document.createElement('i');
                icon.className = 'fas fa-file text-blue-400 text-2xl';
                previewContent.appendChild(icon);
            }
            
            const info = document.createElement('div');
            info.innerHTML = `
                <p class="text-white text-sm font-medium">${escapeHtml(file.name)}</p>
                <p class="text-gray-400 text-xs">${formatFileSize(file.size)}</p>
                ${selectedFiles.length > 1 ? `<p class="text-blue-400 text-xs">+${selectedFiles.length - 1} autres fichiers</p>` : ''}
            `;
            previewContent.appendChild(info);
            
            filePreview.classList.remove('hidden');
        }
        
        function clearFileSelection() {
            selectedFiles = [];
            fileInput.value = '';
            filePreview.classList.add('hidden');
        }
        
        // Utilitaires
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${
                type === 'error' ? 'bg-red-500' : 
                type === 'success' ? 'bg-green-500' : 
                'bg-blue-500'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 4000);
        }
        
        function openImageModal(src) {
            // Créer une modal pour afficher l'image en grand
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="relative max-w-4xl max-h-full p-4">
                    <img src="${src}" alt="Image" class="max-w-full max-h-full object-contain rounded-lg">
                    <button class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
            
            document.body.appendChild(modal);
        }
        
        // Gestion de la frappe
        function handleTyping() {
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                // Logique pour arrêter l'indicateur de frappe
            }, 1000);
        }
        
        // Faire défiler vers le bas
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Échapper le HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>

<?php require_once '../Inc/Components/footers.php'; ?>
<?php require_once '../Inc/Components/footer.php'; ?>
<?php include('../Inc/Traitement/create_log.php'); ?>