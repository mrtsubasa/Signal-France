<?php
// Inc/Constants/LoginThrottler.php

class LoginThrottler
{
    private $db;
    private $maxAttempts = 5;
    private $lockoutTime = 900; // 15 minutes in seconds

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->initTable();
    }

    private function initTable()
    {
        // Create table if not exists
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            ip_address VARCHAR(45) NOT NULL,
            attempts INTEGER DEFAULT 0,
            last_attempt DATETIME,
            PRIMARY KEY (ip_address)
        )";
        $this->db->exec($sql);
    }

    public function checkRateLimit($ip)
    {
        $stmt = $this->db->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $attempts = $result['attempts'];
            $lastAttempt = strtotime($result['last_attempt']);

            // Reset if lockout time has passed
            if ((time() - $lastAttempt) > $this->lockoutTime) {
                $this->resetAttempts($ip);
                return true;
            }

            if ($attempts >= $this->maxAttempts) {
                return false; // Rate limited
            }
        }
        return true;
    }

    public function incrementAttempts($ip)
    {
        // SQLite upsert syntax or check/update
        $stmt = $this->db->prepare("SELECT 1 FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        if ($stmt->fetch()) {
            $stmt = $this->db->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = datetime('now') WHERE ip_address = ?");
            $stmt->execute([$ip]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO login_attempts (ip_address, attempts, last_attempt) VALUES (?, 1, datetime('now'))");
            $stmt->execute([$ip]);
        }
    }

    public function resetAttempts($ip)
    {
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
    }

    public function getRemainingTime($ip)
    {
        $stmt = $this->db->prepare("SELECT last_attempt FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $last = strtotime($result['last_attempt']);
            $remaining = $this->lockoutTime - (time() - $last);
            return max(0, $remaining);
        }
        return 0;
    }
}
?>