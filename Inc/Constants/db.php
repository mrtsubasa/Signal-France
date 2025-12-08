<?php
// Connexion à la base de données
if (!function_exists('connect_db')) {
    function connect_db() {
        try {
            $dbpath = __DIR__ . '/../Db/db.sqlite';
            $conn = new PDO("sqlite:$dbpath");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $conn;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}
?>