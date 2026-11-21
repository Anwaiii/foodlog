<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>会員情報の確認 — FoodLog</title>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261122c" />
</head>
<body>

  @include('partials.nav', ['active' => '', 'badgeId' => null])

  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">👤 Member Profile</div>
        <h1>会員情報の確認</h1>
        <p>現在ログイン中の会員情報です。</p>
      </div>

      <div class="profile-list">
        <div class="profile-row">
          <span class="profile-label">会員ID</span>
          <span class="profile-value">{{ $member->member_id }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">名前</span>
          <span class="profile-value">{{ $member->name }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">名前（カナ）</span>
          <span class="profile-value">{{ $member->name_kana }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">ニックネーム</span>
          <span class="profile-value">{{ $member->nickname }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">メールアドレス</span>
          <span class="profile-value">{{ $member->email }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">会員タイプ</span>
          <span class="profile-value">
            @if($member->isAdmin())
              <span class="role-badge admin">👑 管理者</span>
            @else
              <span class="role-badge general">一般会員</span>
            @endif
          </span>
        </div>
        <div class="profile-row">
          <span class="profile-label">登録日時</span>
          <span class="profile-value">{{ $member->created_at?->format('Y/m/d H:i') }}</span>
        </div>
      </div>

      <div class="modal-actions" style="margin-top:1.8rem;">
        <a href="/foodlog/" class="btn-cancel" style="text-decoration:none;text-align:center;">ホームへ戻る</a>
        <a href="/foodlog/member/password" class="btn-submit" style="text-decoration:none;text-align:center;">🔒 パスワード変更</a>
      </div>
    </div>
  </main>

  <footer><p>FoodLog — Data saved in MySQL database 🗄️</p></footer>

</body>
</html>
