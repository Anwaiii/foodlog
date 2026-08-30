<?php
// 住所カラムとレビュー画像カラムを追加するマイグレーション
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=foodlog;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $changes = 0;

    // restaurants に address カラムを追加
    $cols = $pdo->query("SHOW COLUMNS FROM restaurants LIKE 'address'")->fetchAll();
    if (count($cols) === 0) {
        $pdo->exec("ALTER TABLE restaurants ADD COLUMN address VARCHAR(100) AFTER description");
        $changes++;
        echo "<p>✅ restaurants.address カラムを追加しました。</p>";
    } else {
        echo "<p>ℹ️ restaurants.address は既に存在します。</p>";
    }

    // reviews に image カラムを追加
    $cols2 = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'image'")->fetchAll();
    if (count($cols2) === 0) {
        $pdo->exec("ALTER TABLE reviews ADD COLUMN image VARCHAR(255) AFTER rating");
        $changes++;
        echo "<p>✅ reviews.image カラムを追加しました。</p>";
    } else {
        echo "<p>ℹ️ reviews.image は既に存在します。</p>";
    }

    echo "<h2>" . ($changes > 0 ? "🎉 $changes 件の変更を適用しました" : "変更なし") . "</h2>";
    echo "<p><a href='/foodlog/'>→ アプリに戻る</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ エラー</h2><pre>" . $e->getMessage() . "</pre>";
}
