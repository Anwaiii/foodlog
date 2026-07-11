<?php
// 既存レストランの画像パスを更新するマイグレーションスクリプト
require_once __DIR__ . '/models/Database.php';

$pdo = Database::connect();

$updates = [
    ['name' => 'Napoli Pizza',        'image' => 'assets/images/pizza.png'],
    ['name' => 'Bangkok Curry House', 'image' => 'assets/images/curry.png'],
    ['name' => 'Seoul BBQ Garden',    'image' => 'assets/images/korean_bbq.png'],
];

$updated = 0;
foreach ($updates as $u) {
    $stmt = $pdo->prepare("UPDATE restaurants SET image = ? WHERE name = ? AND (image IS NULL OR image = '')");
    $stmt->execute([$u['image'], $u['name']]);
    $updated += $stmt->rowCount();
}

// Also remove emoji column usage - update all restaurants to remove emoji if needed
$pdo->exec("UPDATE restaurants SET emoji = NULL WHERE emoji IS NOT NULL");

echo "<h2>✅ マイグレーション完了</h2>";
echo "<p>{$updated} 件のレストランの画像を更新しました。</p>";
echo "<p><a href='/foodlog/'>→ アプリを開く</a></p>";
