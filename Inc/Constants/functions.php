<?php
// Inc/Constants/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Génère un token CSRF et le stocke en session
 * @return string Le token généré
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF
 * @param string $token Le token à vérifier
 * @return bool True si valide, False sinon
 */
function verify_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Ajoute une notification à la session
 * @param string $message Le message
 * @param string $type Le type (success, error, warning, info)
 */
function add_notification($message, $type = 'info')
{
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Retourne le badge HTML correspondant au rôle de l'utilisateur
 * @param string $role Le rôle de l'utilisateur
 * @return string Le badge HTML
 */
function getRoleBadge($role)
{
    $badges = [
        'admin' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-crown mr-1"></i>Administrateur</span>',
        'moderator' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-shield-alt mr-1"></i>Modérateur</span>',
        'developer' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-code mr-1"></i>Développeur</span>',
        'opj' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"><i class="fas fa-badge mr-1"></i>OPJ</span>',
        'avocat' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-balance-scale mr-1"></i>Avocat</span>',
        'journaliste' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-newspaper mr-1"></i>Journaliste</span>',
        'magistrat' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-gavel mr-1"></i>Magistrat</span>',
        'psychologue' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800"><i class="fas fa-brain mr-1"></i>Psychologue</span>',
        'association' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800"><i class="fas fa-hands-helping mr-1"></i>Association</span>',
        'rgpd' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"><i class="fas fa-user-shield mr-1"></i>RGPD</span>',
        'user' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-user mr-1"></i>Utilisateur</span>'
    ];
    return $badges[$role] ?? $badges['user'];
}

/**
 * Formate une date en format "il y a X temps"
 * @param string $datetime La date à formater
 * @return string La date formatée
 */
function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);
    if ($time < 60) {
        return 'maintenant';
    }
    if ($time < 3600) {
        return floor($time / 60) . 'm';
    }
    if ($time < 86400) {
        return floor($time / 3600) . 'h';
    }
    if ($time < 2592000) {
        return floor($time / 86400) . 'j';
    }
    if ($time < 31536000) {
        return floor($time / 2592000) . ' mois';
    }
    return floor($time / 31536000) . ' ans';
}