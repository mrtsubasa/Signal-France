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

// Récupérer la liste des utilisateurs pour la sidebar
try {
    $db = connect_db();

    // Récupérer tous les utilisateurs (sauf soi-même)
    $allUsers = $db->prepare("SELECT id, username, avatar, role, is_active FROM users WHERE id != ? ORDER BY is_active DESC, username ASC");
    $allUsers->execute([$user_id]);
    $allUsersList = $allUsers->fetchAll(PDO::FETCH_ASSOC);

    // Séparer les utilisateurs en ligne et hors ligne
    $onlineUsers = array_filter($allUsersList, function ($u) {
        return $u['is_active'] == 1;
    });

    $offlineUsers = array_filter($allUsersList, function ($u) {
        return $u['is_active'] == 0;
    });

} catch (PDOException $e) {
    error_log("Erreur chat (liste utilisateurs): " . $e->getMessage());
    $allUsersList = [];
    $onlineUsers = [];
    $offlineUsers = [];
}

// Définir les variables utilisateur à partir du global $user (fourni par nav.php)
$username = $user['username'] ?? '';
$user_avatar = $user['avatar'] ?? 'default.png';
$role = $user['role'] ?? 'user';

// Récupérer le token CSRF
$csrf_token = generate_csrf_token();
?>

<script src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js" type="module"></script>
<style>
    :root {
        --discord-bg: #36393f;
        --discord-sidebar: #2f3136;
        --discord-input: #40444b;
        --discord-text: #dcddde;
        --discord-muted: #72767d;
        --discord-accent: #5865f2;
        --discord-green: #3ba55d;
        --discord-hover: #32353b;
    }

    body {
        background: var(--discord-bg);
        color: var(--discord-text);
    }

    .chat-container {
        height: calc(100vh - 80px);
        background: var(--discord-bg);
    }

    .sidebar {
        background: var(--discord-sidebar);
        border-right: 1px solid #202225;
    }

    .messages-container {
        height: calc(100% - 80px);
        background: var(--discord-bg);
    }

    .message-bubble {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .typing-indicator {
        animation: pulse 1.5s infinite;
    }

    .online-dot {
        animation: pulse 2s infinite;
    }

    .chat-input {
        background: var(--discord-input);
        border: none;
        color: var(--discord-text);
    }

    .chat-input:focus {
        outline: none;
        box-shadow: 0 0 0 2px var(--discord-accent);
    }

    .user-item:hover {
        background: var(--discord-hover);
    }

    .message-own {
        background: var(--discord-accent);
    }

    .message-other {
        background: var(--discord-input);
    }

    .emoji-picker {
        position: absolute;
        bottom: 70px;
        right: 20px;
        z-index: 1000;
        display: none;
    }

    .file-preview {
        max-width: 300px;
        max-height: 200px;
        border-radius: 8px;
    }

    .file-upload-area {
        border: 2px dashed var(--discord-muted);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .file-upload-area.dragover {
        border-color: var(--discord-accent);
        background: rgba(88, 101, 242, 0.1);
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .scrollbar-thin {
        scrollbar-width: thin;
        scrollbar-color: var(--discord-muted) transparent;
    }

    .scrollbar-thin::-webkit-scrollbar {
        width: 8px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: var(--discord-muted);
        border-radius: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #8e9297;
    }
</style>

<body>
    <main>
        <div class="chat-container flex">
            <!-- Sidebar des utilisateurs -->
            <div class="w-80 sidebar flex flex-col">
                <!-- Header sidebar -->
                <div class="p-4 border-b border-gray-700">
                    <h2 class="text-white font-bold text-lg flex items-center">
                        <i class="fas fa-users mr-2 text-green-400"></i>
                        Membres
                        <span class="ml-2 bg-blue-500 px-2 py-1 rounded-full text-xs"><?= count($allUsersList) ?></span>
                    </h2>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="flex-1 overflow-y-auto p-3 space-y-1 scrollbar-thin">
                    <!-- Utilisateurs en ligne -->
                    <?php if (!empty($onlineUsers)): ?>
                        <div class="mb-4">
                            <h3 class="text-green-400 font-semibold text-sm mb-2 flex items-center">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                En ligne (<?= count($onlineUsers) ?>)
                            </h3>
                            <?php foreach ($onlineUsers as $userItem): ?>
                                <div class="flex items-center p-2 rounded-lg cursor-pointer transition-all duration-200 user-item"
                                    data-user-id="<?= $userItem['id'] ?>"
                                    data-username="<?= htmlspecialchars($userItem['username']) ?>">
                                    <div class="relative">
                                        <img src="../Assets/Images/avatars/<?= htmlspecialchars($userItem['avatar']) ?>"
                                            alt="Avatar" class="w-10 h-10 rounded-full object-cover">
                                        <div
                                            class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-800 online-dot">
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="font-medium text-gray-200 text-sm">
                                            <?= htmlspecialchars($userItem['username']) ?>
                                        </p>
                                        <p class="text-xs text-gray-400 capitalize flex items-center">
                                            <i
                                                class="fas fa-circle text-xs mr-1 <?= $userItem['role'] === 'admin' ? 'text-red-400' : ($userItem['role'] === 'moderator' ? 'text-yellow-400' : 'text-blue-400') ?>"></i>
                                            <?= htmlspecialchars($userItem['role']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Utilisateurs hors ligne -->
                    <?php if (!empty($offlineUsers)): ?>
                        <div class="mb-4">
                            <h3 class="text-gray-500 font-semibold text-sm mb-2 flex items-center">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                Hors ligne (<?= count($offlineUsers) ?>)
                            </h3>
                            <?php foreach ($offlineUsers as $userItem): ?>
                                <div class="flex items-center p-2 rounded-lg cursor-pointer transition-all duration-200 user-item opacity-60"
                                    data-user-id="<?= $userItem['id'] ?>"
                                    data-username="<?= htmlspecialchars($userItem['username']) ?>">
                                    <div class="relative">
                                        <img src="../Assets/Images/avatars/<?= htmlspecialchars($userItem['avatar']) ?>"
                                            alt="Avatar" class="w-10 h-10 rounded-full object-cover grayscale">
                                        <div
                                            class="absolute -bottom-1 -right-1 w-3 h-3 bg-gray-500 rounded-full border-2 border-gray-800">
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="font-medium text-gray-400 text-sm">
                                            <?= htmlspecialchars($userItem['username']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 capitalize flex items-center">
                                            <i
                                                class="fas fa-circle text-xs mr-1 <?= $userItem['role'] === 'admin' ? 'text-red-400' : ($userItem['role'] === 'moderator' ? 'text-yellow-400' : 'text-blue-400') ?>"></i>
                                            <?= htmlspecialchars($userItem['role']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($allUsersList)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-user-slash text-2xl mb-2"></i>
                            <p class="text-sm">Aucun utilisateur trouvé</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Zone de chat principale -->
            <div class="flex-1 flex flex-col">
                <!-- Header du chat -->
                <div class="p-4 border-b border-gray-700 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="relative">
                            <img src="../Assets/Images/avatars/<?= htmlspecialchars($user_avatar) ?>" alt="Mon Avatar"
                                class="w-8 h-8 rounded-full object-cover">
                            <div
                                class="absolute -bottom-1 -right-1 w-2 h-2 bg-green-400 rounded-full border border-gray-800">
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-bold text-white flex items-center">
                                <i class="fas fa-hashtag mr-1 text-gray-400"></i>
                                général
                            </h3>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($username) ?> •
                                <?= count($onlineUsers) + 1 ?> en ligne • <?= count($allUsersList) + 1 ?> membres
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="searchMessages()"
                            class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200"
                            title="Rechercher">
                            <i class="fas fa-search"></i>
                        </button>
                        <button onclick="openSettings()"
                            class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200"
                            title="Paramètres">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>

                <!-- Zone des messages -->
                <div class="messages-container flex-1 overflow-y-auto p-4 scrollbar-thin" id="messages-container">
                    <!-- Les messages seront chargés ici via JavaScript -->
                    <div id="messages-list"></div>

                    <!-- Indicateur de frappe -->
                    <div id="typing-indicator" class="hidden mb-4">
                        <div class="flex items-center space-x-2 text-gray-400 text-sm">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full typing-indicator"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full typing-indicator"
                                    style="animation-delay: 0.2s"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full typing-indicator"
                                    style="animation-delay: 0.4s"></div>
                            </div>
                            <span id="typing-user">Quelqu'un est en train d'écrire...</span>
                        </div>
                    </div>
                </div>

                <!-- Zone de saisie -->
                <div class="p-4">
                    <!-- Zone de drag & drop pour fichiers -->
                    <div id="file-upload-area" class="file-upload-area p-4 mb-3 text-center text-gray-400 hidden">
                        <i class="fas fa-cloud-upload-alt text-2xl mb-2"></i>
                        <p>Glissez vos fichiers ici ou cliquez pour sélectionner</p>
                        <p class="text-xs mt-1">Images, documents, vidéos (max 10MB)</p>
                    </div>

                    <!-- Prévisualisation des fichiers -->
                    <div id="file-preview" class="mb-3 hidden">
                        <div class="flex items-center space-x-3 p-3 bg-gray-700 rounded-lg">
                            <div id="preview-content"></div>
                            <button id="remove-file" class="text-red-400 hover:text-red-300">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <form id="chat-form" class="relative">
                        <div class="flex items-end space-x-3">
                            <div class="flex-1 relative">
                                <input type="text" id="message-input"
                                    placeholder="Écrivez votre message dans #général..."
                                    class="w-full px-4 py-3 chat-input rounded-lg transition-all duration-200 pr-20">

                                <!-- Boutons dans l'input -->
                                <div
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                                    <button type="button" id="emoji-btn"
                                        class="text-gray-400 hover:text-yellow-400 transition-colors duration-200"
                                        title="Emojis">
                                        <i class="fas fa-smile text-lg"></i>
                                    </button>
                                    <button type="button" id="file-btn"
                                        class="text-gray-400 hover:text-blue-400 transition-colors duration-200"
                                        title="Joindre un fichier">
                                        <i class="fas fa-paperclip text-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" id="send-button"
                                class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-lg font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>

                        <!-- Input file caché -->
                        <input type="file" id="file-input" class="hidden" accept="image/*,video/*,.pdf,.doc,.docx,.txt"
                            multiple>
                    </form>
                </div>
            </div>
        </div>

        <!-- Emoji Picker -->
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
        const csrfToken = '<?php echo $csrf_token; ?>';
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
        document.addEventListener('DOMContentLoaded', function () {
            loadMessages();
            setInterval(loadMessages, 3000); // Actualiser toutes les 3 secondes

            // Gestion de la frappe
            messageInput.addEventListener('input', handleTyping);
            messageInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                }
            });

            // Gestion du formulaire
            chatForm.addEventListener('submit', function (e) {
                e.preventDefault();
                sendMessage();
            });

            // Gestion des emojis
            emojiBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                emojiPicker.style.display = emojiPicker.style.display === 'block' ? 'none' : 'block';
            });

            // Fermer emoji picker en cliquant ailleurs
            document.addEventListener('click', function () {
                emojiPicker.style.display = 'none';
            });

            // Sélection d'emoji
            document.querySelector('emoji-picker').addEventListener('emoji-click', function (event) {
                messageInput.value += event.detail.unicode;
                messageInput.focus();
                emojiPicker.style.display = 'none';
            });

            // Gestion des fichiers
            fileBtn.addEventListener('click', function () {
                fileInput.click();
            });

            fileInput.addEventListener('change', handleFileSelect);
            removeFileBtn.addEventListener('click', clearFileSelection);

            // Drag & Drop
            messagesContainer.addEventListener('dragover', function (e) {
                e.preventDefault();
                fileUploadArea.classList.remove('hidden');
                fileUploadArea.classList.add('dragover');
            });

            messagesContainer.addEventListener('dragleave', function (e) {
                if (!messagesContainer.contains(e.relatedTarget)) {
                    fileUploadArea.classList.add('hidden');
                    fileUploadArea.classList.remove('dragover');
                }
            });

            messagesContainer.addEventListener('drop', function (e) {
                e.preventDefault();
                fileUploadArea.classList.add('hidden');
                fileUploadArea.classList.remove('dragover');

                const files = Array.from(e.dataTransfer.files);
                handleFiles(files);
            });

            // Sélection d'utilisateur
            document.querySelectorAll('.user-item').forEach(item => {
                item.addEventListener('click', function () {
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
                body: 'action=get_messages&last_id=' + lastMessageId + '&csrf_token=' + csrfToken
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        addMessageToChat(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    scrollToBottom();
                } else if (!data.success) {
                    console.error('Erreur chargement messages:', data.message);
                }
            })
            .catch(error => {
                console.error('Erreur Fetch messages:', error);
            });
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

            formData.append('csrf_token', csrfToken);

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
                body: `action=edit_message&message_id=${messageId}&message=${encodeURIComponent(newMessage)}&csrf_token=${csrfToken}`
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
            notification.className = `notification ${type === 'error' ? 'bg-red-500' :
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

            modal.addEventListener('click', function (e) {
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