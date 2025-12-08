<?php
session_start();
require_once '../Inc/Constants/db.php';

header('Content-Type: application/json');

try {
    $conn = connect_db();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_POST['action'])) {
        throw new Exception('Action non spécifiée');
    }

    switch ($_POST['action']) {
        case 'get_signalement':
            if (!isset($_POST['signalement_id'])) {
                throw new Exception('ID de signalement manquant');
            }

            $signalementId = (int)$_POST['signalement_id'];

            if ($signalementId <= 0) {
                throw new Exception('ID de signalement invalide');
            }

            $stmt = $conn->prepare("
                SELECT s.*, 
                       u.username as auteur_username, 
                       u.email as auteur_email,
                       u.organization as auteur_organization,
                       t.username as traite_par_username, 
                       t.email as traite_par_email,
                       t.organization as traite_par_organization,
                       CASE 
                           WHEN s.anonyme = 1 THEN 'Anonyme'
                           WHEN u.username IS NOT NULL THEN u.username
                           ELSE 'Utilisateur supprimé'
                       END as auteur_complet,
                       CASE 
                           WHEN s.nom IS NOT NULL AND s.prenom IS NOT NULL THEN CONCAT(s.prenom, ' ', s.nom)
                           WHEN s.titre IS NOT NULL THEN s.titre
                           ELSE 'Signalement sans titre'
                       END as titre_complet
                FROM signalements s 
                LEFT JOIN users u ON s.user_id = u.id 
                LEFT JOIN users t ON s.traite_par = t.id
                WHERE s.id = ?
            ");
            $stmt->execute([$signalementId]);
            $signalement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$signalement) {
                throw new Exception('Signalement non trouvé');
            }

            // Formatage des données pour l'affichage
            $signalement['date_signalement_formatted'] = date('d/m/Y H:i', strtotime($signalement['date_signalement']));
            if ($signalement['date_traitement']) {
                $signalement['date_traitement_formatted'] = date('d/m/Y H:i', strtotime($signalement['date_traitement']));
            }

            // Gestion des images et preuves
            if ($signalement['images']) {
                $signalement['images_array'] = json_decode($signalement['images'], true) ?: [];
            } else {
                $signalement['images_array'] = [];
            }

            if ($signalement['preuves']) {
                $signalement['preuves_array'] = json_decode($signalement['preuves'], true) ?: [];
            } else {
                $signalement['preuves_array'] = [];
            }

            echo json_encode([
                'success' => true,
                'signalement' => $signalement
            ]);
            break;

        case 'autocomplete_nom_prenom':
            if (!isset($_POST['query'])) {
                throw new Exception('Requête manquante');
            }

            $query = trim($_POST['query']);
            $limit = isset($_POST['limit']) ? min((int)$_POST['limit'], 10) : 5;

            if (strlen($query) < 2) {
                echo json_encode(['suggestions' => []]);
                break;
            }

            // Séparer la requête en mots pour recherche plus précise
            $words = explode(' ', $query);
            $conditions = [];
            $params = [];

            // Recherche exacte sur le nom complet (priorité la plus élevée)
            $conditions[] = "CONCAT(prenom, ' ', nom) = ?";
            $params[] = $query;

            // Recherche par mots individuels
            if (count($words) >= 2) {
                // Si on a deux mots, essayer prénom + nom
                $prenom = $words[0];
                $nom = $words[1];

                $conditions[] = "(prenom LIKE ? AND nom LIKE ?)";
                $params[] = "$prenom%";
                $params[] = "$nom%";
            } else {
                // Sinon chercher dans nom OU prénom
                $conditions[] = "nom LIKE ?";
                $params[] = "$query%";

                $conditions[] = "prenom LIKE ?";
                $params[] = "$query%";
            }

            // Recherche générale (moins prioritaire)
            $conditions[] = "CONCAT(prenom, ' ', nom) LIKE ?";
            $params[] = "%$query%";

            $whereClause = implode(' OR ', $conditions);
            $params[] = $limit;

            $stmt = $conn->prepare("
                SELECT DISTINCT 
                    CONCAT(prenom, ' ', nom) as nom_complet,
                    nom,
                    prenom,
                    id, 
                    statut, 
                    date_signalement,
                    type_incident
                FROM signalements 
                WHERE ($whereClause)
                    AND nom IS NOT NULL 
                    AND prenom IS NOT NULL
                ORDER BY 
                    CASE 
                        WHEN CONCAT(prenom, ' ', nom) = ? THEN 1
                        WHEN (prenom = ? OR nom = ?) THEN 2
                        WHEN (prenom LIKE ? OR nom LIKE ?) THEN 3
                        WHEN CONCAT(prenom, ' ', nom) LIKE ? THEN 4
                        ELSE 5
                    END,
                    date_signalement DESC
                LIMIT ?
            ");

            // Paramètres pour ORDER BY
            $additionalParams = [
                $query,                     // Nom complet exact
                $query, $query,             // Prénom ou nom exact
                "$query%", "$query%",       // Prénom ou nom commence par
                "%$query%",                 // Nom complet contient
                $limit                      // Limite
            ];

            $stmt->execute(array_merge($params, $additionalParams));
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['suggestions' => $suggestions]);
            break;

        case 'autocomplete_titre':
            if (!isset($_POST['query'])) {
                throw new Exception('Requête manquante');
            }

            $query = trim($_POST['query']);
            $limit = isset($_POST['limit']) ? min((int)$_POST['limit'], 10) : 5;

            if (strlen($query) < 2) {
                echo json_encode(['suggestions' => []]);
                break;
            }

            // Recherche par titre ou nom/prénom
            $stmt = $conn->prepare("
                SELECT DISTINCT 
                    CASE 
                        WHEN nom IS NOT NULL AND prenom IS NOT NULL THEN CONCAT(prenom, ' ', nom)
                        WHEN titre IS NOT NULL THEN titre
                        ELSE 'Signalement sans titre'
                    END as titre,
                    id, 
                    statut, 
                    date_signalement
                FROM signalements 
                WHERE (
                    titre = ? OR 
                    titre LIKE ? OR 
                    titre LIKE ? OR
                    nom = ? OR 
                    prenom = ? OR
                    CONCAT(prenom, ' ', nom) = ? OR
                    nom LIKE ? OR 
                    prenom LIKE ? OR
                    CONCAT(prenom, ' ', nom) LIKE ?
                )
                ORDER BY 
                    CASE 
                        WHEN titre = ? THEN 1
                        WHEN CONCAT(prenom, ' ', nom) = ? THEN 2
                        WHEN (nom = ? OR prenom = ?) THEN 3
                        WHEN titre LIKE ? THEN 4
                        WHEN CONCAT(prenom, ' ', nom) LIKE ? THEN 5
                        WHEN (nom LIKE ? OR prenom LIKE ?) THEN 6
                        ELSE 7
                    END,
                    date_signalement DESC
                LIMIT ?
            ");

            // Paramètres pour les conditions WHERE
            $params = [
                $query,                     // titre exact
                "$query%",                  // titre commence par
                "%$query%",                 // titre contient
                $query, $query,             // nom ou prénom exact
                $query,                     // nom complet exact
                "$query%", "$query%",       // nom ou prénom commence par
                "%$query%",                 // nom complet contient

                // Paramètres pour ORDER BY
                $query,                     // titre exact
                $query,                     // nom complet exact
                $query, $query,             // nom ou prénom exact
                "$query%",                  // titre commence par
                "$query%",                  // nom complet commence par
                "$query%", "$query%",       // nom ou prénom commence par
                $limit                      // limite
            ];

            $stmt->execute($params);
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['suggestions' => $suggestions]);
            break;

        case 'quick_search':
            if (!isset($_POST['query'])) {
                throw new Exception('Requête manquante');
            }

            $query = trim($_POST['query']);
            $limit = isset($_POST['limit']) ? min((int)$_POST['limit'], 50) : 20;

            if (strlen($query) < 2) {
                echo json_encode([
                    'success' => true,
                    'suggestions' => [],
                    'count' => 0,
                    'query' => $query
                ]);
                break;
            }

            // Séparer la requête en mots pour une recherche plus précise
            $words = explode(' ', $query);
            $conditions = [];
            $params = [];

            // 1. Recherche par correspondance exacte (très haute priorité)
            if (count($words) >= 2) {
                // Recherche par prénom et nom si deux mots ou plus
                $prenom = $words[0];
                $nom = implode(' ', array_slice($words, 1));

                $conditions[] = "(prenom = ? AND nom = ?)";
                $params[] = $prenom;
                $params[] = $nom;

                // Essayer également l'inverse (nom puis prénom)
                $nom2 = $words[0];
                $prenom2 = implode(' ', array_slice($words, 1));

                $conditions[] = "(prenom = ? AND nom = ?)";
                $params[] = $prenom2;
                $params[] = $nom2;
            }

            // 2. Recherche par correspondance partielle sur nom/prénom (haute priorité)
            if (count($words) >= 2) {
                // Correspondance partielle prénom + nom
                $prenom = $words[0];
                $nom = implode(' ', array_slice($words, 1));

                $conditions[] = "(prenom LIKE ? AND nom LIKE ?)";
                $params[] = "$prenom%";
                $params[] = "$nom%";

                // Essayer également l'inverse
                $nom2 = $words[0];
                $prenom2 = implode(' ', array_slice($words, 1));

                $conditions[] = "(prenom LIKE ? AND nom LIKE ?)";
                $params[] = "$prenom2%";
                $params[] = "$nom2%";
            } else {
                // Un seul mot : chercher dans nom OU prénom
                $conditions[] = "(nom LIKE ? OR prenom LIKE ?)";
                $params[] = "$query%";
                $params[] = "$query%";
            }

            // 3. Recherche par correspondance nom complet (priorité moyenne)
            $conditions[] = "CONCAT(prenom, ' ', nom) LIKE ?";
            $params[] = "%$query%";

            // 4. Recherche par titre exact (priorité moyenne)
            $conditions[] = "titre = ?";
            $params[] = $query;

            // 5. Recherche par titre partiel (priorité moyenne)
            $conditions[] = "titre LIKE ?";
            $params[] = "%$query%";

            // 6. Autres champs (priorité basse)
            $otherFields = ['type_incident', 'description', 'localisation', 'lieu'];
            foreach ($otherFields as $field) {
                $conditions[] = "$field LIKE ?";
                $params[] = "%$query%";
            }

            // Construire la requête complète
            $whereClause = implode(' OR ', $conditions);

            $stmt = $conn->prepare("
                SELECT 
                    id, 
                    titre,
                    nom,
                    prenom,
                    COALESCE(CONCAT(prenom, ' ', nom), titre, 'Sans titre') as nom_complet,
                    type_incident, 
                    statut, 
                    date_signalement, 
                    priorite,
                    description,
                    localisation,
                    lieu
                FROM signalements 
                WHERE $whereClause
                ORDER BY 
                    CASE 
                        WHEN (prenom = ? AND nom = ?) THEN 1
                        WHEN (nom = ? OR prenom = ?) THEN 2
                        WHEN CONCAT(prenom, ' ', nom) = ? THEN 3
                        WHEN titre = ? THEN 4
                        WHEN (prenom LIKE ? AND nom LIKE ?) THEN 5
                        WHEN (nom LIKE ? OR prenom LIKE ?) THEN 6
                        WHEN CONCAT(prenom, ' ', nom) LIKE ? THEN 7
                        WHEN titre LIKE ? THEN 8
                        ELSE 9
                    END,
                    date_signalement DESC
                LIMIT ?
            ");

            // Paramètres pour ORDER BY (avec priorité de tri)
            $orderParams = [];

            // Prénom et nom exacts
            if (count($words) >= 2) {
                $orderParams[] = $words[0];                          // prenom exact
                $orderParams[] = implode(' ', array_slice($words, 1)); // nom exact
            } else {
                $orderParams[] = "";
                $orderParams[] = "";
            }

            $orderParams[] = $query;        // nom OU prénom exact
            $orderParams[] = $query;        // nom OU prénom exact
            $orderParams[] = $query;        // nom complet exact
            $orderParams[] = $query;        // titre exact

            // Prénom et nom partiels
            if (count($words) >= 2) {
                $orderParams[] = $words[0] . "%";                    // prenom partiel
                $orderParams[] = implode(' ', array_slice($words, 1)) . "%"; // nom partiel
            } else {
                $orderParams[] = "";
                $orderParams[] = "";
            }

            $orderParams[] = "$query%";     // nom OU prénom partiel
            $orderParams[] = "$query%";     // nom OU prénom partiel
            $orderParams[] = "%$query%";    // nom complet contient
            $orderParams[] = "%$query%";    // titre contient
            $orderParams[] = $limit;        // limite

            // Fusionner tous les paramètres et exécuter la requête
            $stmt->execute(array_merge($params, $orderParams));
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format des résultats pour l'affichage
            foreach ($suggestions as &$s) {
                // Date formatée
                $s['date_formatted'] = date('d/m/Y H:i', strtotime($s['date_signalement']));

                // Nom complet ou titre pour l'affichage
                if (!empty($s['nom']) && !empty($s['prenom'])) {
                    $s['nom_complet'] = $s['prenom'] . ' ' . $s['nom'];
                } elseif (!empty($s['titre'])) {
                    $s['nom_complet'] = $s['titre'];
                } else {
                    $s['nom_complet'] = 'Signalement #' . $s['id'];
                }

                // Description courte pour l'aperçu
                if (!empty($s['description'])) {
                    $s['description_courte'] = mb_substr($s['description'], 0, 100) . (mb_strlen($s['description']) > 100 ? '...' : '');
                } else {
                    $s['description_courte'] = 'Aucune description';
                }
            }

            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions,
                'count' => count($suggestions),
                'query' => $query
            ]);
            break;

        case 'search_by_person':
            if (!isset($_POST['nom']) && !isset($_POST['prenom'])) {
                throw new Exception('Nom ou prénom requis');
            }

            $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
            $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
            $limit = isset($_POST['limit']) ? min((int)$_POST['limit'], 50) : 20;

            // Requête plus stricte pour retourner uniquement les correspondances pertinentes
            $conditions = [];
            $params = [];

            if (!empty($nom) && !empty($prenom)) {
                // Si on a nom ET prénom, on cherche les deux
                $conditions[] = "(nom LIKE ? AND prenom LIKE ?)";
                $params[] = "%$nom%";
                $params[] = "%$prenom%";
            } else {
                // Sinon on cherche celui qui est fourni
                if (!empty($nom)) {
                    $conditions[] = "nom LIKE ?";
                    $params[] = "%$nom%";
                }

                if (!empty($prenom)) {
                    $conditions[] = "prenom LIKE ?";
                    $params[] = "%$prenom%";
                }
            }

            if (empty($conditions)) {
                throw new Exception('Au moins un critère de recherche requis');
            }

            $whereClause = implode(' OR ', $conditions);
            $params[] = $limit;

            $stmt = $conn->prepare("
                SELECT 
                    id,
                    nom,
                    prenom,
                    CONCAT(prenom, ' ', nom) as nom_complet,
                    type_incident,
                    statut,
                    priorite,
                    date_signalement,
                    description,
                    localisation,
                    lieu,
                    incident_context,
                    plateforme
                FROM signalements 
                WHERE $whereClause
                ORDER BY 
                    CASE
                        WHEN (nom = ? AND prenom = ?) THEN 1
                        WHEN nom = ? THEN 2
                        WHEN prenom = ? THEN 3
                        WHEN (nom LIKE ? AND prenom LIKE ?) THEN 4
                        WHEN nom LIKE ? THEN 5
                        WHEN prenom LIKE ? THEN 6
                        ELSE 7
                    END,
                    date_signalement DESC
                LIMIT ?
            ");

            // Paramètres pour le tri
            $orderParams = [];
            $orderParams[] = $nom;        // nom exact
            $orderParams[] = $prenom;     // prénom exact
            $orderParams[] = $nom;        // nom exact seul
            $orderParams[] = $prenom;     // prénom exact seul
            $orderParams[] = "%$nom%";    // nom contient
            $orderParams[] = "%$prenom%"; // prénom contient
            $orderParams[] = "%$nom%";    // nom contient seul
            $orderParams[] = "%$prenom%"; // prénom contient seul
            $orderParams[] = $limit;      // limite

            $stmt->execute(array_merge($params, $orderParams));
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format des résultats pour l'affichage
            foreach ($results as &$r) {
                $r['date_formatted'] = date('d/m/Y H:i', strtotime($r['date_signalement']));

                if (!empty($r['description'])) {
                    $r['description_courte'] = mb_substr($r['description'], 0, 100) . (mb_strlen($r['description']) > 100 ? '...' : '');
                } else {
                    $r['description_courte'] = 'Aucune description';
                }
            }

            echo json_encode([
                'success' => true,
                'results' => $results,
                'count' => count($results),
                'query' => ['nom' => $nom, 'prenom' => $prenom]
            ]);
            break;

        case 'get_stats':
            $stats = [];

            // Statistiques par statut
            $stmt = $conn->query("SELECT statut, COUNT(*) as count FROM signalements GROUP BY statut");
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques par priorité
            $stmt = $conn->query("SELECT priorite, COUNT(*) as count FROM signalements GROUP BY priorite");
            $stats['by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques par type d'incident
            $stmt = $conn->query("SELECT type_incident, COUNT(*) as count FROM signalements GROUP BY type_incident ORDER BY count DESC LIMIT 10");
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques par contexte d'incident
            $stmt = $conn->query("SELECT incident_context, COUNT(*) as count FROM signalements GROUP BY incident_context");
            $stats['by_context'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques par nom/prénom (personnes les plus signalées)
            $stmt = $conn->query("
                SELECT 
                    CONCAT(prenom, ' ', nom) as nom_complet,
                    nom,
                    prenom,
                    COUNT(*) as count 
                FROM signalements 
                WHERE nom IS NOT NULL AND prenom IS NOT NULL
                GROUP BY nom, prenom 
                ORDER BY count DESC 
                LIMIT 10
            ");
            $stats['most_reported'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Total
            $stmt = $conn->query("SELECT COUNT(*) as total FROM signalements");
            $stats['total'] = $stmt->fetchColumn();

            // Signalements récents (dernières 24h)
            $stmt = $conn->query("SELECT COUNT(*) as recent FROM signalements WHERE date_signalement >= datetime('now', '-1 day')");
            $stats['recent'] = $stmt->fetchColumn();

            // Signalements avec nom/prénom vs anciens avec titre
            $stmt = $conn->query("SELECT COUNT(*) as with_names FROM signalements WHERE nom IS NOT NULL AND prenom IS NOT NULL");
            $stats['with_names'] = $stmt->fetchColumn();

            $stmt = $conn->query("SELECT COUNT(*) as with_title_only FROM signalements WHERE (nom IS NULL OR prenom IS NULL) AND titre IS NOT NULL");
            $stats['with_title_only'] = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>