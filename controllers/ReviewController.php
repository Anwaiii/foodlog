<?php
require_once __DIR__ . '/../models/Review.php';

class ReviewController {

    private Review $model;

    public function __construct() {
        $this->model = new Review();
    }

    public function handleRequest(): void {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        match ($_SERVER['REQUEST_METHOD']) {
            'GET'    => $this->index(),
            'POST'   => $id ? $this->update($id) : $this->store(),
            'DELETE' => $this->destroy(),
            default  => $this->methodNotAllowed(),
        };
    }

    private function index(): void {
        $restaurantId = intval($_GET['restaurant_id'] ?? 0);
        if (!$restaurantId) {
            http_response_code(400);
            echo json_encode(['error' => 'restaurant_id required']);
            return;
        }
        echo json_encode($this->model->getByRestaurant($restaurantId));
    }

    private function store(): void {
        $data         = json_decode(file_get_contents('php://input'), true);
        $restaurantId = intval($data['restaurant_id'] ?? 0);
        $date         = $data['date'] ?? '';
        $orderDetails = trim($data['order_details'] ?? '');
        $impression   = trim($data['impression'] ?? '');
        $rating       = isset($data['rating']) && $data['rating'] !== '' ? intval($data['rating']) : null;

        if (!$restaurantId || !$date || !$orderDetails || !$impression) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        echo json_encode($this->model->create($restaurantId, $date, $orderDetails, $impression, $rating));
    }

    private function update(int $id): void {
        $data         = json_decode(file_get_contents('php://input'), true);
        $date         = $data['date'] ?? '';
        $orderDetails = trim($data['order_details'] ?? '');
        $impression   = trim($data['impression'] ?? '');
        $rating       = isset($data['rating']) && $data['rating'] !== '' ? intval($data['rating']) : null;

        if (!$date || !$orderDetails || !$impression) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $result = $this->model->update($id, $date, $orderDetails, $impression, $rating);
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'Review not found']);
            return;
        }
        echo json_encode($result);
    }

    private function destroy(): void {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            return;
        }
        $deleted = $this->model->delete($id);
        echo json_encode(['deleted' => $deleted ? $id : null]);
    }

    private function methodNotAllowed(): void {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}
