<?php
require_once __DIR__ . '/../models/Restaurant.php';

class RestaurantController {

    private Restaurant $model;
    private string $uploadDir;
    private string $uploadPath; // relative path stored in DB

    public function __construct() {
        $this->model     = new Restaurant();
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
            'DELETE' => $id ? $this->destroy($id) : $this->methodNotAllowed(),
            default  => $this->methodNotAllowed(),
        };
    }

    private function index(): void {
        echo json_encode($this->model->getAll());
    }

    private function store(): void {
        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $desc     = trim($_POST['description'] ?? '');

        if (!$name || !$category) {
            http_response_code(400);
            echo json_encode(['error' => 'name and category are required']);
            return;
        }

        $imagePath = $this->handleUpload();
        echo json_encode($this->model->create($name, $category, $desc, $imagePath));
    }

    private function update(int $id): void {
        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $desc     = trim($_POST['description'] ?? '');

        if (!$name || !$category) {
            http_response_code(400);
            echo json_encode(['error' => 'name and category are required']);
            return;
        }

        // Only update image if a new file was provided
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->handleUpload();
        }

        $result = $this->model->update($id, $name, $category, $desc, $imagePath);
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'Restaurant not found']);
            return;
        }
        echo json_encode($result);
    }

    private function handleUpload(): ?string {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file    = $_FILES['image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) return null;

        // MIMEタイプをファイルの実体から検証（拡張子偽装対策）
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMime)) return null;

        $filename = 'restaurant_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $filename)) {
            return $this->uploadPath . $filename;
        }
        return null;
    }

    private function destroy(int $id): void {
        $deleted = $this->model->delete($id);
        if (!$deleted) {
            http_response_code(404);
            echo json_encode(['error' => 'Restaurant not found']);
            return;
        }
        echo json_encode(['deleted' => $id]);
    }

    private function methodNotAllowed(): void {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}
