<?php
include("Inc/Constants/db.php");
try {
    $db = connect_db();

    // Check existing columns
    $stmt = $db->query("PRAGMA table_info(chat_messages)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

    if (!in_array('edited_at', $columns)) {
        $db->exec("ALTER TABLE chat_messages ADD COLUMN edited_at DATETIME DEFAULT NULL");
        echo "Added edited_at column.\n";
    }

    if (!in_array('reply_to_id', $columns)) {
        $db->exec("ALTER TABLE chat_messages ADD COLUMN reply_to_id INTEGER DEFAULT NULL");
        echo "Added reply_to_id column.\n";
    }

    echo "Database schema update complete.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
