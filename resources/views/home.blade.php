@extends('layouts.app', ['navActive' => 'home'])
@section('title', __('FoodLog — グルメレビュー＆レストラン記録'))
@section('meta')
  <meta name="description" content="{{ __('気になったレストラン・注文メニュー・正直な感想や写真をまとめて記録し、いつでも振り返ることができます。') }}" />
@endsection

@section('content')
  <section class="hero">
    <div class="hero-tag">{{ __('🍴 パーソナル・グルメログ') }}</div>
    <h1>{{ __('お気に入りの') }}<span class="grad">{{ __('美味しい体験') }}</span>{{ __('を') }}<br/>{{ __('ずっと残そう') }}</h1>
    <p>{{ __('気になったレストラン・注文メニュー・正直な感想や写真をまとめて記録し、いつでも振り返ることができます。') }}</p>
    <div class="hero-stats">
      <div>
        <div class="stat-val" id="stat-restaurants">0</div>
        <div class="stat-label">{{ __('登録店舗数') }}</div>
      </div>
      <div>
        <div class="stat-val" id="stat-reviews">0</div>
        <div class="stat-label">{{ __('総レビュー数') }}</div>
      </div>
      <div>
        <div class="stat-val" id="stat-latest">—</div>
        <div class="stat-label">{{ __('最新レビュー日') }}</div>
      </div>
    </div>
  </section>

  <main class="section">
    <div class="search-bar-wrapper" style="margin-bottom:1.25rem;max-width:100%;">
      <input type="search" class="search-input" id="search-input"
             placeholder="{{ __('🔍 店名・カテゴリ・メニュー・感想を検索...') }}" />
    </div>
    <div class="filter-bar" id="filter-bar">
      <div class="filter-group">
        <label class="filter-label">{{ __('カテゴリ') }}</label>
        <div class="filter-chips" id="category-chips">
          <button type="button" class="filter-chip active" data-category="all">{{ __('すべて') }}</button>
        </div>
      </div>
      <div class="filter-group">
        <label class="filter-label">{{ __('評価') }}</label>
        <div class="filter-chips" id="rating-chips">
          <button type="button" class="filter-chip active" data-rating="0">{{ __('すべて') }}</button>
          <button type="button" class="filter-chip" data-rating="3">{{ __('★3以上') }}</button>
          <button type="button" class="filter-chip" data-rating="4">{{ __('★4以上') }}</button>
          <button type="button" class="filter-chip" data-rating="5">{{ __('★5') }}</button>
        </div>
      </div>
      <div class="filter-group">
        <label class="filter-label">{{ __('並び替え') }}</label>
        <div class="filter-chips" id="sort-chips">
          <button type="button" class="filter-chip active" data-sort="default">{{ __('標準') }}</button>
          <button type="button" class="filter-chip" data-sort="reviews">{{ __('レビュー数順') }}</button>
          <button type="button" class="filter-chip" data-sort="rating">{{ __('高評価順') }}</button>
          <button type="button" class="filter-chip" data-sort="newest">{{ __('新着順') }}</button>
        </div>
      </div>
      @auth
      <div class="filter-group">
        <label class="filter-label">{{ __('絞り込み') }}</label>
        <div class="filter-chips">
          <button type="button" class="filter-chip" id="fav-only-chip" aria-pressed="false">{{ __('❤️ お気に入りのみ') }}</button>
        </div>
      </div>
      @endauth
    </div>

    <div class="section-header">
      <div class="section-title">{{ __('レストラン一覧') }} <span id="restaurant-count"></span></div>
      @if(Auth::check() && Auth::user()->isAdmin())
        <button class="add-btn" id="add-restaurant-btn">{{ __('＋ 店舗を追加') }}</button>
      @endif
    </div>
    <div class="restaurant-grid" id="restaurant-grid">
      <div class="empty-state"><div class="empty-icon">⏳</div><p>{{ __('読み込み中…') }}</p></div>
    </div>
    <div id="home-pagination"></div>
  </main>
@endsection

@section('modals')
  @if(Auth::check() && Auth::user()->isAdmin())
  <div class="modal-overlay" id="add-modal">
    <div class="modal">
      <h2>{{ __('新しいレストランを追加') }}</h2>
      <form id="add-restaurant-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="r-name">
            {{ __('店名') }}
            <span class="char-count" id="r-name-count">0 / 50</span>
          </label>
          <input type="text" id="r-name" name="name" maxlength="50" required />
        </div>
        <div class="form-group">
          <label for="r-category">
            {{ __('カテゴリ') }}
            <span class="char-count" id="r-category-count">0 / 30</span>
          </label>
          <input type="text" id="r-category" name="category" placeholder="Japanese, Italian…" maxlength="30" required />
        </div>
        <div class="form-group">
          <label for="r-desc">
            {{ __('紹介文') }}
            <span class="char-count" id="r-desc-count">0 / 200</span>
          </label>
          <textarea id="r-desc" name="description" class="impression-textarea" maxlength="200"></textarea>
        </div>
        <div class="form-group">
          <label for="r-address">
            {{ __('住所') }}
            <span class="char-count" id="r-address-count">0 / 100</span>
          </label>
          <input type="text" id="r-address" name="address" maxlength="100" />
        </div>
        <div class="form-group">
          <label for="r-image">{{ __('写真') }}</label>
          <div class="upload-area" id="add-upload-area">
            <div class="upload-placeholder" id="add-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>{{ __('クリックまたはドラッグ＆ドロップで写真を選択') }}</span>
            </div>
            <img id="add-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
            <input type="file" id="r-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-modal-btn">{{ __('キャンセル') }}</button>
          <button type="submit" class="btn-submit">{{ __('店舗を登録') }}</button>
        </div>
      </form>
    </div>
  </div>
  @endif
@endsection

@section('scripts')
  <script>
    window.AUTH = {
      loggedIn: {{ Auth::check() ? 'true' : 'false' }},
      isAdmin: {{ Auth::check() && Auth::user()->isAdmin() ? 'true' : 'false' }},
      userId: {{ Auth::check() ? Auth::id() : 'null' }}
    };
  </script>
  <script src="/foodlog/assets/js/pagination.js?v=20261122c"></script>
  <script src="/foodlog/assets/js/app.js?v=20261122f"></script>
@endsection
