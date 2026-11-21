  <nav class="nav">
    <a href="/foodlog/" class="nav-brand">
      <span class="logo-icon">🍜</span>
      <span>FoodLog</span>
    </a>
    <div class="nav-links">
      <a href="/foodlog/" class="nav-link {{ ($active ?? '') === 'home' ? 'active' : '' }}">{{ __('ホーム') }}</a>
      <a href="/foodlog/stats.php" class="nav-link {{ ($active ?? '') === 'stats' ? 'active' : '' }}">{{ __('統計') }}</a>
    </div>
    <div class="nav-right">
      <a href="/foodlog/locale/{{ app()->getLocale() === 'en' ? 'ja' : 'en' }}"
         class="lang-toggle-btn"
         title="{{ app()->getLocale() === 'en' ? '日本語に切替' : 'Switch to English' }}">
        🌐 {{ app()->getLocale() === 'en' ? 'JP' : 'EN' }}
      </a>
      <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" title="{{ __('ライト / ダークモード切替') }}" aria-label="{{ __('テーマ切替') }}">🌙</button>
      @auth
        <a href="/foodlog/member" class="nav-user-pill" title="{{ __('会員情報の確認') }}">
          <span>{{ Auth::user()->isAdmin() ? '👑' : '👤' }}</span>
          <span>{{ Auth::user()->nickname }}</span>
        </a>
      @else
        <a href="/foodlog/login" class="nav-user-pill guest" title="{{ __('ログイン') }}">
          <span>👤</span>
          <span>{{ __('ゲスト') }}</span>
        </a>
      @endauth
      <div class="hamburger-wrapper" id="hamburger-wrapper">
        <button type="button" class="hamburger-btn" id="hamburger-btn" aria-label="{{ __('メニュー') }}" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
        <div class="hamburger-menu" id="hamburger-menu">
          @auth
            <div class="menu-user-info">
              <div class="menu-user-name">{{ Auth::user()->nickname }}</div>
              <div class="menu-user-role">
                @if(Auth::user()->isAdmin())
                  <span class="role-badge admin">👑 {{ __('管理者') }}</span>
                @else
                  <span class="role-badge general">{{ __('一般会員') }}</span>
                @endif
              </div>
            </div>
            <div class="menu-divider"></div>
            <a href="/foodlog/member" class="menu-item">{{ __('👤 会員情報の確認') }}</a>
            <a href="/foodlog/favorites" class="menu-item">{{ __('❤️ お気に入り一覧') }}</a>
            <a href="/foodlog/users/{{ Auth::id() }}" class="menu-item">{{ __('📖 自分のレビュー履歴') }}</a>
            @if(Auth::user()->isAdmin())
              <a href="/foodlog/admin/members" class="menu-item">{{ __('🛠️ 会員管理（管理者）') }}</a>
            @endif
            <div class="menu-divider"></div>
            <form method="POST" action="/foodlog/logout" style="margin:0;">
              @csrf
              <button type="submit" class="menu-item menu-logout">{{ __('🚪 ログアウト') }}</button>
            </form>
          @else
            <a href="/foodlog/login" class="menu-item">{{ __('🔑 ログイン') }}</a>
            <a href="/foodlog/register" class="menu-item">{{ __('✨ 新規会員登録') }}</a>
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
      if (btn && menu) {
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
      }

      const themeBtn = document.getElementById('theme-toggle-btn');
      function syncThemeIcon() {
        if (!themeBtn) return;
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        themeBtn.textContent = isLight ? '☀️' : '🌙';
        themeBtn.title = isLight ? window.t('ダークモードへ切替') : window.t('ライトモードへ切替');
      }
      syncThemeIcon();
      if (themeBtn) {
        themeBtn.addEventListener('click', function () {
          const isLight = document.documentElement.getAttribute('data-theme') === 'light';
          if (isLight) {
            document.documentElement.removeAttribute('data-theme');
            try { localStorage.setItem('foodlog_theme', 'dark'); } catch (e) {}
          } else {
            document.documentElement.setAttribute('data-theme', 'light');
            try { localStorage.setItem('foodlog_theme', 'light'); } catch (e) {}
          }
          syncThemeIcon();
        });
      }
    })();
  </script>
