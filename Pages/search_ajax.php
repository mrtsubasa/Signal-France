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
                       END as auteur_complet
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
            
            $stmt = $conn->prepare("
                SELECT DISTINCT titre, id, statut, date_signalement
                FROM signalements 
                WHERE titre LIKE ? OR titre LIKE ? OR titre LIKE ?
                ORDER BY 
                    CASE 
                        WHEN titre = ? THEN 1
                        WHEN titre LIKE ? THEN 2
                        ELSE 3
                    END,
                    date_signalement DESC
                LIMIT ?
            ");
            
            $exactMatch = $query;
            $startsWith = "$query%";
            $contains = "%$query%";
            
            $stmt->execute([$exactMatch, $startsWith, $contains, $exactMatch, $startsWith, $limit]);
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['suggestions' => $suggestions]);
            break;
            
        case 'quick_search':
            if (!isset($_POST['query'])) {
                throw new Exception('Requête manquante');
            }
            
            $query = trim($_POST['query']);
            $limit = isset($_POST['limit']) ? min((int)$_POST['limit'], 20) : 10;
            
            if (strlen($query) < 2) {
                echo json_encode(['suggestions' => []]);
                break;
            }
            
            $stmt = $conn->prepare("
                SELECT id, titre, type_incident, statut, date_signalement, priorite
                FROM signalements 
                WHERE titre LIKE ? OR type_incident LIKE ? OR description LIKE ? OR localisation LIKE ? OR lieu LIKE ?
                ORDER BY date_signalement DESC
                LIMIT ?
            ");
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
            $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['suggestions' => $suggestions]);
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
            
            // Total
            $stmt = $conn->query("SELECT COUNT(*) as total FROM signalements");
            $stats['total'] = $stmt->fetchColumn();
            
            // Signalements récents (dernières 24h)
            $stmt = $conn->query("SELECT COUNT(*) as recent FROM signalements WHERE date_signalement >= datetime('now', '-1 day')");
            $stats['recent'] = $stmt->fetchColumn();
            
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