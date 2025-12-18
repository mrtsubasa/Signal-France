<?php
include("Inc/Constants/db.php");
try {
    $db = connect_db();
    $count = $db->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
    echo "Message count: $count\n";
    if ($count > 0) {
        $last = $db->query("SELECT * FROM chat_messages ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        print_r($last);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
