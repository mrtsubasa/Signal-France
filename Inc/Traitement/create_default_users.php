<?php
require_once("../Constants/db.php");

function createDefaultUsers()
{
    try {
        $db = connect_db();

        // Définition des rôles disponibles avec niveaux d'accès
        $availableRoles = [
            // Rôles système
            'admin' => [
                'name' => 'Administrateur',
                'access_level' => 'full',
                'description' => 'Accès complet au système'
            ],
            'moderator' => [
                'name' => 'Modérateur',
                'access_level' => 'full',
                'description' => 'Modération et gestion des contenus'
            ],
            'developer' => [
                'name' => 'Développeur',
                'access_level' => 'full',
                'description' => 'Accès technique et développement'
            ],
            'user' => [
                'name' => 'Utilisateur',
                'access_level' => 'basic',
                'description' => 'Utilisateur standard'
            ],
            'guest' => [
                'name' => 'Invité',
                'access_level' => 'limited',
                'description' => 'Accès limité en lecture'
            ],

            // Rôles professionnels judiciaires
            'opj' => [
                'name' => 'Officier de Police Judiciaire',
                'access_level' => 'full',
                'description' => 'Accès complet - Compétence enquête, croisement données, interpellation'
            ],
            'avocat' => [
                'name' => 'Avocat / Juriste Accrédité',
                'access_level' => 'full_on_request',
                'description' => 'Accès complet sur demande motivée - Défense et accompagnement victime'
            ],
            'journaliste' => [
                'name' => 'Journaliste d\'Investigation Accrédité',
                'access_level' => 'partial',
                'description' => 'Accès partiel - Enquêtes d\'intérêt public sans données personnelles complètes'
            ],
            'magistrat' => [
                'name' => 'Magistrat / Greffier',
                'access_level' => 'full',
                'description' => 'Accès complet - Cadre judiciaire et instruction'
            ],
            'psychologue' => [
                'name' => 'Psychologue / Travailleur Social Agréé',
                'access_level' => 'limited_anonymous',
                'description' => 'Accès limité anonymisé - Détection schémas comportementaux'
            ],
            'association' => [
                'name' => 'Responsable Association Protection',
                'access_level' => 'partial_validated',
                'description' => 'Accès partiel - Signalements validés + motif + ville'
            ],
            'rgpd' => [
                'name' => 'Responsable RGPD / Sécurité',
                'access_level' => 'technical_only',
                'description' => 'Accès technique uniquement - Logs et anonymisation'
            ]
        ];

        // Create table if not exists (SQLite syntax)
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(255) NOT NULL,
            access_level VARCHAR(50) DEFAULT 'basic',
            accreditation VARCHAR(255) DEFAULT NULL,
            organization VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            banner VARCHAR(255) DEFAULT NULL,
            bio VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(255) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            city VARCHAR(255) DEFAULT NULL,
            country VARCHAR(255) DEFAULT NULL,
            zip VARCHAR(255) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            linkedin VARCHAR(255) DEFAULT NULL,
            twitter VARCHAR(255) DEFAULT NULL,
            github VARCHAR(255) DEFAULT NULL,
            token VARCHAR(255) DEFAULT NULL,
            token_expiry DATETIME DEFAULT NULL,
            last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_active INTEGER DEFAULT 0,
            is_verified INTEGER DEFAULT 0,
            is_banned INTEGER DEFAULT 0,
            is_deleted INTEGER DEFAULT 0,
            is_blacklisted INTEGER DEFAULT 0,
            is_public INTEGER DEFAULT 0,
            require_password_change INTEGER DEFAULT 0
        )");

        // Default users array avec nouveaux rôles
        $users = [
            [
                'username' => 'tsubasa',
                'password' => password_hash('MikkyMyLove091002*', PASSWORD_DEFAULT),
                'email' => 'mr.tsubasa@vk.com',
                'role' => 'admin',
                'organization' => 'E Conscience Dev Team'
            ],
            [
                'username' => 'malisou',
                'password' => password_hash('Signalefrance2025', PASSWORD_DEFAULT),
                'email' => 'malisou@signalefrance.fr',
                'role' => 'admin',
                'organization' => 'E Conscience'
            ]
        ];

        $createdUsers = [];
        $existingUsers = [];
        $invalidRoles = [];

        foreach ($users as $user) {
            // Vérifier si le rôle est valide
            if (!array_key_exists($user['role'], $availableRoles)) {
                $invalidRoles[] = [
                    'username' => $user['username'],
                    'invalid_role' => $user['role'],
                    'available_roles' => array_keys($availableRoles)
                ];
                continue;
            }

            // Vérifier si l'utilisateur existe déjà
            $checkSmt = $db->prepare('SELECT username FROM users WHERE username = :username OR email = :email');
            $checkSmt->execute(['username' => $user['username'], 'email' => $user['email']]);
            $existingUser = $checkSmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $existingUsers[] = [
                    'username' => $user['username'],
                    'role' => $availableRoles[$user['role']]['name'],
                    'access_level' => $availableRoles[$user['role']]['access_level']
                ];
            } else {
                // Insérer le nouvel utilisateur avec les nouvelles données
                $insertSmt = $db->prepare('INSERT INTO users (username, password, email, role, access_level, organization, accreditation) VALUES (:username, :password, :email, :role, :access_level, :organization, :accreditation)');
                if (
                    $insertSmt->execute([
                        'username' => $user['username'],
                        'password' => $user['password'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'access_level' => $availableRoles[$user['role']]['access_level'],
                        'organization' => $user['organization'] ?? null,
                        'accreditation' => $user['accreditation'] ?? null
                    ])
                ) {
                    $createdUsers[] = [
                        'username' => $user['username'],
                        'role' => $availableRoles[$user['role']]['name'],
                        'access_level' => $availableRoles[$user['role']]['access_level'],
                        'organization' => $user['organization'] ?? 'Non spécifiée'
                    ];
                }
            }
        }

        return [
            'status' => 'success',
            'message' => 'Traitement terminé avec succès.',
            'available_roles' => $availableRoles,
            'createdUsers' => $createdUsers,
            'existingUsers' => $existingUsers,
            'invalidRoles' => $invalidRoles,
            'total_created' => count($createdUsers),
            'total_existing' => count($existingUsers),
            'total_invalid' => count($invalidRoles)
        ];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Erreur lors de la création des utilisateurs : ' . $e->getMessage(),
            'createdUsers' => [],
            'existingUsers' => [],
            'invalidRoles' => []
        ];
    }
}

// Fonction utilitaire pour obtenir les rôles disponibles
function getAvailableRoles()
{
    return [
        // Rôles système
        'admin' => ['name' => 'Administrateur', 'access_level' => 'full'],
        'moderator' => ['name' => 'Modérateur', 'access_level' => 'full'],
        'developer' => ['name' => 'Développeur', 'access_level' => 'technical'],
        'user' => ['name' => 'Utilisateur', 'access_level' => 'basic'],
        'guest' => ['name' => 'Invité', 'access_level' => 'limited'],

        // Rôles professionnels
        'opj' => ['name' => 'Officier de Police Judiciaire', 'access_level' => 'full'],
        'avocat' => ['name' => 'Avocat / Juriste Accrédité', 'access_level' => 'full_on_request'],
        'journaliste' => ['name' => 'Journaliste d\'Investigation', 'access_level' => 'partial'],
        'magistrat' => ['name' => 'Magistrat / Greffier', 'access_level' => 'full'],
        'psychologue' => ['name' => 'Psychologue / Travailleur Social', 'access_level' => 'limited_anonymous'],
        'association' => ['name' => 'Responsable Association Protection', 'access_level' => 'partial_validated'],
        'rgpd' => ['name' => 'Responsable RGPD / Sécurité', 'access_level' => 'technical_only']
    ];
}

// Fonction pour valider un rôle
function isValidRole($role)
{
    $availableRoles = getAvailableRoles();
    return array_key_exists($role, $availableRoles);
}

// Fonction pour obtenir le niveau d'accès d'un rôle
function getRoleAccessLevel($role)
{
    $availableRoles = getAvailableRoles();
    return $availableRoles[$role]['access_level'] ?? 'basic';
}

// Exécution si appelé directement
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    session_start();

    $result = createDefaultUsers();

    if ($result['status'] === 'success') {
        $message = $result['message'] . ' Créés: ' . $result['total_created'] . ', Existants: ' . $result['total_existing'];
        if ($result['total_invalid'] > 0) {
            $message .= ', Rôles invalides: ' . $result['total_invalid'];
        }

        $_SESSION['notification'] = [
            'message' => $message,
            'type' => 'success'
        ];
        echo json_encode($result);
    } else {
        $_SESSION['notification'] = [
            'message' => $result['message'],
            'type' => 'error'
        ];
        echo json_encode($result);
    }
}
?>