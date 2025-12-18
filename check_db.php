<?php
include("Inc/Constants/db.php");
try {
    $db = connect_db();
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables) . "\n";

    if (in_array('chat_messages', $tables)) {
        $schema = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='chat_messages'")->fetchColumn();
        echo "Schema chat_messages:\n$schema\n";
    } else {
        echo "Table chat_messages does not exist!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
