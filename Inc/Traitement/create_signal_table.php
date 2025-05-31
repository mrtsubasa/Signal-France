<?php
require_once("../Constants/db.php");

function createSignalementsTable() {
    try {
        $db = connect_db();
        
        // Créer la table signalements avec les NOUVELLES colonnes
        $sql = "CREATE TABLE IF NOT EXISTS signalements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            titre VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            localisation VARCHAR(255),
            type_incident VARCHAR(255) NOT NULL,
            incident_context VARCHAR(20) DEFAULT 'irl',
            plateforme VARCHAR(100) NULL,
            lieu VARCHAR(255) NULL,
            latitude DECIMAL(10, 8),
            longitude DECIMAL(11, 8),
            statut VARCHAR(50) DEFAULT 'en_attente',
            auteur VARCHAR(255) NULL,
            priorite VARCHAR(20) DEFAULT 'normale',
            date_signalement DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_traitement DATETIME NULL,
            traite_par INTEGER NULL,
            commentaire_traitement TEXT NULL,
            images TEXT NULL,
            anonyme INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            email_contact VARCHAR(255) NULL,
            preuves TEXT NULL,

            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (traite_par) REFERENCES users(id)
        )";
        
        $result = $db->exec($sql);
        
        if ($result !== false) {
            echo "Table 'signalements' créée avec succès";
            
            // Ajouter les nouvelles colonnes si la table existe déjà
            addMissingColumns($db);
            
            // Ajouter quelques données de test
            addTestData($db);
        } else {
            echo "Erreur lors de la création de la table";
        }
        
    } catch (Exception $e) {
        echo "Erreur de connexion à la base de données: " . $e->getMessage();
    }
}

// Nouvelle fonction pour ajouter les colonnes manquantes
function addMissingColumns($db) {
    try {
        // Vérifier et ajouter incident_context
        $db->exec("ALTER TABLE signalements ADD COLUMN incident_context VARCHAR(20) DEFAULT 'irl'");
        echo "Colonne incident_context ajoutée.\n";
    } catch (Exception $e) {
        // Colonne existe déjà
    }
    
    try {
        // Vérifier et ajouter plateforme
        $db->exec("ALTER TABLE signalements ADD COLUMN plateforme VARCHAR(100) NULL");
        echo "Colonne plateforme ajoutée.\n";
    } catch (Exception $e) {
        // Colonne existe déjà
    }
    
    try {
        // Vérifier et ajouter lieu
        $db->exec("ALTER TABLE signalements ADD COLUMN lieu VARCHAR(255) NULL");
        echo "Colonne lieu ajoutée.\n";
    } catch (Exception $e) {
        // Colonne existe déjà
    }
}

function addTestData($db) {
    try {
        // Vérifier si des données existent déjà
        $stmt = $db->prepare("SELECT COUNT(*) FROM signalements");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Ajouter des signalements de test
            $testSignalements = [
                [
                    'user_id' => 1,
                    'type' => 'Incident de sécurité',
                    'titre' => 'Comportement suspect dans le parc',
                    'description' => 'Individu rôdant autour des aires de jeux avec un comportement inquiétant',
                    'localisation' => 'Parc Central, Paris 15ème',
                    'statut' => 'en_attente',
                    'priorite' => 'haute',
                    'anonyme' => 0
                ],
                [
                    'user_id' => 2,
                    'type' => 'Fraude',
                    'titre' => 'Tentative d\'escroquerie téléphonique',
                    'description' => 'Appel frauduleux se faisant passer pour la banque demandant des codes',
                    'localisation' => 'Lyon 3ème',
                    'statut' => 'en_cours',
                    'priorite' => 'normale',
                    'anonyme' => 1
                ],
                [
                    'user_id' => 1,
                    'type' => 'Cybercriminalité',
                    'titre' => 'Site web frauduleux',
                    'description' => 'Site imitant une boutique en ligne connue pour voler les données bancaires',
                    'localisation' => 'En ligne',
                    'statut' => 'resolu',
                    'priorite' => 'urgente',
                    'anonyme' => 0
                ],
                [
                    'user_id' => 2,
                    'type' => 'Violence',
                    'titre' => 'Agression dans le métro',
                    'description' => 'Témoin d\'une agression verbale et physique dans la rame de métro ligne 1',
                    'localisation' => 'Métro Châtelet, Paris',
                    'statut' => 'en_attente',
                    'priorite' => 'haute',
                    'anonyme' => 1
                ],
                [
                    'user_id' => 1,
                    'type' => 'Trafic de drogue',
                    'titre' => 'Activité suspecte dans l\'immeuble',
                    'description' => 'Va-et-vient suspect avec des échanges rapides dans le hall d\'immeuble',
                    'localisation' => 'Marseille 13ème',
                    'statut' => 'en_cours',
                    'priorite' => 'normale',
                    'anonyme' => 0
                ]
            ];
            
            $insertSql = "INSERT INTO signalements (user_id, type, titre, description, localisation, statut, priorite, anonyme) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($insertSql);
            
            foreach ($testSignalements as $signalement) {
                $stmt->execute([
                    $signalement['user_id'],
                    $signalement['type'],
                    $signalement['titre'],
                    $signalement['description'],
                    $signalement['localisation'],
                    $signalement['statut'],
                    $signalement['priorite'],
                    $signalement['anonyme']
                ]);
            }
            
            echo "<br>Données de test ajoutées avec succès (" . count($testSignalements) . " signalements)";
        } else {
            echo "<br>Données déjà présentes dans la table (" . $count . " signalements)";
        }
        
    } catch (Exception $e) {
        echo "<br>Erreur lors de l'ajout des données de test: " . $e->getMessage();
    }
}

// Exécuter la fonction si le fichier est appelé directement
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    createSignalementsTable();
}
?>