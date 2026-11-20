  <nav class="nav">
    <a href="/foodlog/" class="nav-brand">
      <span class="logo-icon">🍜</span>
      <span>FoodLog</span>
    </a>
    <div class="nav-links">
      <a href="/foodlog/" class="nav-link {{ ($active ?? '') === 'home' ? 'active' : '' }}">Home</a>
      <a href="/foodlog/stats.php" class="nav-link {{ ($active ?? '') === 'stats' ? 'active' : '' }}">Stats</a>
      <a href="/foodlog/search.php" class="nav-link {{ ($active ?? '') === 'search' ? 'active' : '' }}">Search</a>
    </div>
    <div class="nav-right">
      @auth
        <a href="/foodlog/member" class="nav-user-pill" title="会員情報の確認">
          <span>{{ Auth::user()->isAdmin() ? '👑' : '👤' }}</span>
          <span>{{ Auth::user()->nickname }}</span>
        </a>
      @else
        <a href="/foodlog/login" class="nav-user-pill guest" title="ログイン画面へ">
          <span>👤</span>
          <span>ゲスト</span>
        </a>
      @endauth
      <div class="hamburger-wrapper" id="hamburger-wrapper">
        <button type="button" class="hamburger-btn" id="hamburger-btn" aria-label="Menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
        <div class="hamburger-menu" id="hamburger-menu">
          @auth
            <div class="menu-user-info">
              <div class="menu-user-name">{{ Auth::user()->nickname }}</div>
              <div class="menu-user-role">
                @if(Auth::user()->isAdmin())
                  <span class="role-badge admin">👑 管理者</span>
                @else
                  <span class="role-badge general">一般会員</span>
                @endif
              </div>
            </div>
            <div class="menu-divider"></div>
            <a href="/foodlog/member" class="menu-item">👤 会員情報の確認</a>
            <form method="POST" action="/foodlog/logout" style="margin:0;">
              @csrf
              <button type="submit" class="menu-item menu-logout">🚪 ログアウト</button>
            </form>
          @else
            <a href="/foodlog/login" class="menu-item">🔑 ログイン</a>
          @endauth
        </div>
      </div>
    </div>
  </nav>
  <script>
    (function () {
      const btn = document.getElementById('hamburger-btn');
      const menu = document.getElementById('hamburger-menu');
      const wrapper = document.getElementById('hamburger-wrapper');
      if (!btn || !menu) return;
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = menu.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
      document.addEventListener('click', function (e) {
        if (wrapper && !wrapper.contains(e.target)) {
          menu.classList.remove('open');
          btn.classList.remove('open');
          btn.setAttribute('aria-expanded', 'false');
        }
      });
    })();
  </script>
