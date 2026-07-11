<?php
class Database {
    private static $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            $host    = 'localhost';
            $dbname  = 'foodlog';
            $user    = 'root';
            $pass    = '';
            $charset = 'utf8mb4';
            try {
                self::$instance = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
        return self::$instance;
    }
}
