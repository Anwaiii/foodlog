<?php
require_once __DIR__ . '/Database.php';

class Restaurant {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT r.*, COUNT(rv.id) AS review_count
            FROM restaurants r
            LEFT JOIN reviews rv ON r.id = rv.restaurant_id
            GROUP BY r.id
            ORDER BY r.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT r.*, COUNT(rv.id) AS review_count
            FROM restaurants r
            LEFT JOIN reviews rv ON r.id = rv.restaurant_id
            WHERE r.id = ?
            GROUP BY r.id
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(string $name, string $category, string $description, ?string $image): array {
        $stmt = $this->db->prepare(
            "INSERT INTO restaurants (name, category, description, image) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $category, $description, $image]);
        $id = $this->db->lastInsertId();
        return $this->getById($id);
    }

    public function update(int $id, string $name, string $category, string $description, ?string $image): ?array {
        if ($image !== null) {
            $stmt = $this->db->prepare(
                "UPDATE restaurants SET name=?, category=?, description=?, image=? WHERE id=?"
            );
            $stmt->execute([$name, $category, $description, $image, $id]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE restaurants SET name=?, category=?, description=? WHERE id=?"
            );
            $stmt->execute([$name, $category, $description, $id]);
        }
        return $this->getById($id);
    }
}
