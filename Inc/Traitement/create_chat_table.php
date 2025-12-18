<?php
include("../Constants/db.php");

try {
    $db = connect_db();

    // Créer la table chat_messages avec support des fichiers
    $sql = "
        CREATE TABLE IF NOT EXISTS chat_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            message TEXT,
            files TEXT, -- JSON pour stocker les informations des fichiers
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            edited_at DATETIME DEFAULT NULL,
            reply_to_id INTEGER DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (reply_to_id) REFERENCES chat_messages(id) ON DELETE SET NULL
        )
    ";

    $db->exec($sql);

    // Créer le dossier uploads/chat s'il n'existe pas
    $uploadDir = '../../uploads/chat';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    echo "Table chat_messages créée avec succès et dossier uploads configuré !";

} catch (PDOException $e) {
    echo "Erreur lors de la création de la table: " . $e->getMessage();
}
?>