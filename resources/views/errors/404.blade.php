<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>404 — FoodLog</title>
  <script>
    (function () {
      try {
        if (localStorage.getItem('foodlog_theme') === 'light') {
          document.documentElement.setAttribute('data-theme', 'light');
        }
      } catch (e) {}
    })();
  </script>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261122j" />
</head>
<body>
  <main class="auth-container" style="min-height:82vh;">
    <div class="auth-card" style="text-align:center;">
      <div style="font-size:3.2rem;margin-bottom:0.5rem;">🍜</div>
      <div class="hero-tag">ERROR 404</div>
      <h1 style="margin-top:0.6rem;">{{ app()->getLocale() === 'en' ? 'Page Not Found' : 'ページが見つかりません' }}</h1>
      <p style="color:var(--muted);margin:0.8rem 0 1.6rem;">
        {{ app()->getLocale() === 'en' ? 'The page you are looking for may have been moved or deleted.' : 'お探しのページは移動または削除された可能性があります。' }}
      </p>
      <a href="/foodlog/" class="btn-submit" style="display:inline-block;text-decoration:none;padding:0.75rem 1.8rem;">
        {{ app()->getLocale() === 'en' ? '← Back to Home' : '← ホームへ戻る' }}
      </a>
    </div>
  </main>
</body>
</html>
