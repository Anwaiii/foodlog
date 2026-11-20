<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ログイン — FoodLog</title>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261120e" />
</head>
<body>

  @include('partials.nav', ['active' => '', 'badgeId' => null])

  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">🔑 Member Login</div>
        <h1>FoodLog にログイン</h1>
        <p>会員ID または メールアドレスとパスワードを入力してください。</p>
      </div>

      @if($errors->any())
        <div class="alert-error">
          @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="/foodlog/login">
        @csrf
        <div class="form-group">
          <label for="login_id">会員ID または メールアドレス</label>
          <input type="text" id="login_id" name="login_id" value="{{ old('login_id') }}"
                 placeholder="e.g. admin01 / user01@foodlog.local" required autofocus />
        </div>
        <div class="form-group">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" placeholder="パスワードを入力" required />
        </div>
        <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;margin-top:0.5rem;">
          ログイン
        </button>
      </form>

      <div class="auth-footer">
        <a href="/foodlog/" class="back-link">← ホーム画面に戻る</a>
      </div>
    </div>
  </main>

  <footer><p>FoodLog — Data saved in MySQL database 🗄️</p></footer>

</body>
</html>
