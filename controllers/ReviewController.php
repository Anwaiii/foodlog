<?php
require_once __DIR__ . '/../models/Review.php';

class ReviewController {

    private Review $model;
    private string $uploadDir;
    private string $uploadPath;

    public function __construct() {
        $this->model = new Review();
        $this->uploadDir  = __DIR__ . '/../assets/images/';
        $this->uploadPath = 'assets/images/';
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
        // FormData（multipart）で受け取る
        $restaurantId = intval($_POST['restaurant_id'] ?? 0);
        $date         = $_POST['date'] ?? '';
        $orderDetails = trim($_POST['order_details'] ?? '');
        $impression   = trim($_POST['impression'] ?? '');
        $rating       = isset($_POST['rating']) && $_POST['rating'] !== '' ? intval($_POST['rating']) : null;

        if (!$restaurantId || !$date || !$orderDetails || !$impression) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $imagePath = $this->handleUpload();
        echo json_encode($this->model->create($restaurantId, $date, $orderDetails, $impression, $rating, $imagePath));
    }

    private function update(int $id): void {
        // FormData（multipart）で受け取る
        $date         = $_POST['date'] ?? '';
        $orderDetails = trim($_POST['order_details'] ?? '');
        $impression   = trim($_POST['impression'] ?? '');
        $rating       = isset($_POST['rating']) && $_POST['rating'] !== '' ? intval($_POST['rating']) : null;

        if (!$date || !$orderDetails || !$impression) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->handleUpload();
        }

        $result = $this->model->update($id, $date, $orderDetails, $impression, $rating, $imagePath);
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

    private function handleUpload(): ?string {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file    = $_FILES['image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) return null;

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMime)) return null;

        $filename = 'review_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $filename)) {
            return $this->uploadPath . $filename;
        }
        return null;
    }

    private function methodNotAllowed(): void {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}
