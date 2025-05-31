<?php
require_once '../Constants/db.php';

try {
    $pdo = connect_db();
    $sql = "
        CREATE TABLE IF NOT EXISTS messages_contact (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            email TEXT,
            type_demande TEXT NOT NULL,
            sujet TEXT NOT NULL,
            message TEXT NOT NULL,
            anonyme INTEGER DEFAULT 0,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            statut TEXT DEFAULT 'nouveau',
            repondu INTEGER DEFAULT 0,
            ip_address TEXT,
            user_agent TEXT
        )
    ";
    
    $pdo->exec($sql);
    echo "Table messages_contact créée avec succès !";
    
} catch (PDOException $e) {
    echo "Erreur lors de la création de la table : " . $e->getMessage();
}
?>