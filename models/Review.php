<?php
require_once __DIR__ . '/Database.php';

class Review {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getByRestaurant(int $restaurantId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM reviews WHERE restaurant_id = ? ORDER BY date DESC, created_at DESC"
        );
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll();
    }

    public function create(int $restaurantId, string $date, string $orderDetails, string $impression, ?int $rating, ?string $image): array {
        $stmt = $this->db->prepare(
            "INSERT INTO reviews (restaurant_id, date, order_details, impression, rating, image) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$restaurantId, $date, $orderDetails, $impression, $rating, $image]);
        $id = $this->db->lastInsertId();
        $stmt2 = $this->db->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt2->execute([$id]);
        return $stmt2->fetch();
    }

    public function update(int $id, string $date, string $orderDetails, string $impression, ?int $rating, ?string $image): ?array {
        if ($image !== null) {
            $stmt = $this->db->prepare(
                "UPDATE reviews SET date=?, order_details=?, impression=?, rating=?, image=? WHERE id=?"
            );
            $stmt->execute([$date, $orderDetails, $impression, $rating, $image, $id]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE reviews SET date=?, order_details=?, impression=?, rating=? WHERE id=?"
            );
            $stmt->execute([$date, $orderDetails, $impression, $rating, $id]);
        }

        $stmt2 = $this->db->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt2->execute([$id]);
        return $stmt2->fetch() ?: null;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
