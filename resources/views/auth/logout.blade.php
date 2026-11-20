<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ログアウト完了 — FoodLog</title>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261120e" />
</head>
<body>

  @include('partials.nav', ['active' => '', 'badgeId' => null])

  <main class="auth-container">
    <div class="auth-card" style="text-align:center;">
      <div style="font-size:3rem;margin-bottom:1rem;">👋</div>
      <h1 style="font-size:1.6rem;font-weight:800;margin-bottom:0.6rem;">ログアウトしました</h1>
      <p style="color:var(--muted);font-size:0.95rem;margin-bottom:2rem;">
        ご利用ありがとうございました。またのご利用をお待ちしております。
      </p>
      <div style="display:flex;gap:0.75rem;flex-wrap:wrap;justify-content:center;">
        <a href="/foodlog/" class="btn-cancel" style="text-decoration:none;text-align:center;">ホームへ戻る</a>
        <a href="/foodlog/login" class="btn-submit" style="text-decoration:none;text-align:center;">再ログイン</a>
      </div>
    </div>
  </main>

  <footer><p>FoodLog — Data saved in MySQL database 🗄️</p></footer>

</body>
</html>
