<?php
// DBとテーブルを自動作成するセットアップスクリプト
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // DB作成
    $pdo->exec("CREATE DATABASE IF NOT EXISTS foodlog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE foodlog");

    // restaurants テーブル
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        description TEXT,
        image VARCHAR(255),
        emoji VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // reviews テーブル
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT NOT NULL,
        date DATE NOT NULL,
        order_details TEXT NOT NULL,
        impression TEXT NOT NULL,
        rating TINYINT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    )");

    // シードデータ（初回のみ）
    $count = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO restaurants (name, category, description, image, emoji) VALUES
            ('Spicy Ramen Hub',    'Japanese', 'Rich tonkotsu broth, handmade noodles & premium toppings', 'images/ramen.png',  '🍜'),
            ('Smash Burger Co.',   'American', 'Double smash patties, American cheese & secret sauce',     'images/burger.png', '🍔'),
            ('Sakura Sushi',       'Japanese', 'Premium nigiri, maki rolls & omakase platters',            'images/sushi.png',  '🍣'),
            ('Napoli Pizza',       'Italian',  'Wood-fired Neapolitan pizza with San Marzano tomatoes',    NULL,                '🍕'),
            ('Bangkok Curry House','Thai',      'Authentic Thai green & red curries with jasmine rice',     NULL,                '🍛'),
            ('Seoul BBQ Garden',   'Korean',   'Premium wagyu bulgogi, samgyeopsal & banchan sides',       NULL,                '🥩')
        ");
    }

    echo "<h2>✅ セットアップ完了！</h2>";
    echo "<p>データベース <strong>foodlog</strong> とテーブルを作成しました。</p>";
    echo "<p><a href='/foodlog/'>→ アプリを開く</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ エラー</h2><pre>" . $e->getMessage() . "</pre>";
}
