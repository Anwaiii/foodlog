@extends('layouts.app')
@section('title', __('レストラン詳細 — FoodLog'))

@section('content')
  <div class="detail-header">
    <div class="detail-banner-placeholder" id="banner-placeholder" style="display:none;"></div>
    <img class="detail-banner" id="banner-img" src="" alt="" style="display:none;" />
    <div class="detail-overlay"></div>
    <div class="detail-header-content">
      <a href="/foodlog/" class="back-btn">{{ __('← ホームへ戻る') }}</a>
      <div class="detail-category" id="detail-category"></div>
      <div class="detail-meta-tags" id="detail-meta-tags"></div>
      <h1 class="detail-name" id="detail-name">{{ __('読み込み中…') }}</h1>
      <p class="detail-desc" id="detail-desc"></p>
      <a class="detail-address-link" id="detail-address" href="#" target="_blank" rel="noopener noreferrer" style="display:none;"></a>
      <div style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-top:1rem;align-items:center;">
        <button type="button" class="detail-fav-btn" id="detail-fav-btn">{{ __('🤍 お気に入り') }}</button>
        <button type="button" class="detail-share-btn" id="copy-url-btn">{{ __('🔗 URLをコピー') }}</button>
        @if(Auth::check() && Auth::user()->isAdmin())
          <button class="edit-info-btn" id="edit-info-btn">{{ __('✏️ 店舗情報を編集') }}</button>
          <button class="edit-info-btn delete-restaurant-btn" id="delete-restaurant-btn">{{ __('🗑 店舗を削除') }}</button>
        @endif
      </div>
    </div>
  </div>

  <div class="detail-body">
    <section class="reviews-panel">
      <div class="reviews-section-header">
        <div class="reviews-title-group">
          <h2 style="margin-bottom:0;">{{ __('📖 レビュー一覧') }}</h2>
          <span class="badge" id="review-count-badge">0</span>
        </div>
        <div class="reviews-toolbar" id="reviews-toolbar">
          <select id="review-sort" class="review-control" aria-label="{{ __('並び替え') }}">
            <option value="date_desc">{{ __('📅 新しい順') }}</option>
            <option value="date_asc">{{ __('📅 古い順') }}</option>
            <option value="rating_desc">{{ __('⭐ 星が高い順') }}</option>
            <option value="rating_asc">{{ __('⭐ 星が低い順') }}</option>
            <option value="likes_desc">{{ __('👍 役に立った順') }}</option>
          </select>
          <select id="review-rating-filter" class="review-control" aria-label="{{ __('評価') }}">
            <option value="all">{{ __('★ すべて') }}</option>
            <option value="5">{{ __('★5 のみ') }}</option>
            <option value="4">{{ __('★4 以上') }}</option>
            <option value="3">{{ __('★3 以上') }}</option>
            <option value="2">{{ __('★2 以下') }}</option>
          </select>
          <button type="button" id="review-photo-only" class="review-toggle-btn" aria-pressed="false">{{ __('📷 写真あり') }}</button>
          @auth
            <button type="button" id="review-my-only" class="review-toggle-btn" aria-pressed="false">{{ __('👤 自分のみ') }}</button>
          @endauth
          <input type="search" id="review-keyword" class="review-search-input" placeholder="{{ __('🔍 店内検索...') }}" aria-label="{{ __('🔍 店内検索...') }}" />
          <button type="button" id="review-filter-reset" class="review-reset-btn" title="{{ __('条件をリセット') }}" style="display:none;">✕</button>
        </div>
        @auth
          <button class="add-btn" id="add-review-btn">{{ __('＋ レビューを投稿') }}</button>
        @endauth
      </div>
      <div id="reviews-list">
        <div class="reviews-empty"><div class="empty-icon">⏳</div><p>{{ __('読み込み中…') }}</p></div>
      </div>
    </section>
  </div>
@endsection

@section('modals')
  @auth
  <div class="modal-overlay" id="add-review-modal">
    <div class="modal">
      <h2>{{ __('📝 レビューを投稿') }}</h2>
      <form id="review-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="rv-date">{{ __('訪問・注文日') }}</label>
          <input type="date" id="rv-date" name="date" required />
        </div>
        <div class="form-group">
          <label for="rv-order">
            {{ __('注文したメニュー') }}
            <span class="char-count" id="order-count">0 / 200</span>
          </label>
          <textarea id="rv-order" name="order_details" class="order-textarea" rows="3" maxlength="200" required></textarea>
        </div>
        <div class="form-group">
          <label for="rv-impression">
            {{ __('感想・レビュー') }}
            <span class="char-count" id="impression-count">0 / 500</span>
          </label>
          <textarea id="rv-impression" name="impression" class="impression-textarea" maxlength="500" required></textarea>
        </div>
        <div class="form-group">
          <label for="rv-rating">{{ __('評価（1〜5 ⭐）') }}</label>
          <input type="number" id="rv-rating" name="rating" min="1" max="5" placeholder="5" required />
        </div>
        <div class="form-group">
          <label>{{ __('写真（任意・1枚まで）') }}</label>
          <div class="upload-area" id="rv-upload-area">
            <div class="upload-placeholder" id="rv-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>{{ __('クリックまたはドラッグ＆ドロップで写真を選択') }}</span>
            </div>
            <img id="rv-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
            <input type="file" id="rv-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-add-review-btn">{{ __('キャンセル') }}</button>
          <button type="submit" class="btn-submit">{{ __('投稿する') }}</button>
        </div>
      </form>
    </div>
  </div>
  @endauth

  @if(Auth::check() && Auth::user()->isAdmin())
  <div class="modal-overlay" id="edit-modal">
    <div class="modal">
      <h2>{{ __('✏️ 店舗情報を編集') }}</h2>
      <form id="edit-restaurant-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="edit-name">
            {{ __('店名') }}
            <span class="char-count" id="edit-name-count">0 / 50</span>
          </label>
          <input type="text" id="edit-name" name="name" maxlength="50" required />
        </div>
        <div class="form-group">
          <label for="edit-category">
            {{ __('カテゴリ') }}
            <span class="char-count" id="edit-category-count">0 / 30</span>
          </label>
          <input type="text" id="edit-category" name="category" maxlength="30" required />
        </div>
        <div class="form-row-2col">
          <div class="form-group">
            <label for="edit-area">{{ __('エリア') }}</label>
            <input type="text" id="edit-area" name="area" placeholder="{{ __('例: 渋谷、新宿、銀座') }}" maxlength="50" />
          </div>
          <div class="form-group">
            <label for="edit-price">{{ __('予算帯') }}</label>
            <select id="edit-price" name="price_range" class="form-select">
              <option value="">{{ __('未設定') }}</option>
              <option value="〜¥1,000">〜¥1,000</option>
              <option value="¥1,000〜¥3,000">¥1,000〜¥3,000</option>
              <option value="¥3,000〜">¥3,000〜</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="edit-desc">
            {{ __('紹介文') }}
            <span class="char-count" id="edit-desc-count">0 / 200</span>
          </label>
          <textarea id="edit-desc" name="description" class="impression-textarea" maxlength="200"></textarea>
        </div>
        <div class="form-group">
          <label for="edit-address">
            {{ __('住所') }}
            <span class="char-count" id="edit-address-count">0 / 100</span>
          </label>
          <input type="text" id="edit-address" name="address" maxlength="100" />
        </div>
        <div class="form-group">
          <label>{{ __('写真') }} <span style="color:var(--muted);font-weight:400;">{{ __('（未選択なら現在の写真を維持）') }}</span></label>
          <div class="upload-area" id="edit-upload-area">
            <div class="upload-placeholder" id="edit-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>{{ __('クリックまたはドラッグ＆ドロップで写真を変更') }}</span>
            </div>
            <img id="edit-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
            <input type="file" id="edit-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-edit-btn">{{ __('キャンセル') }}</button>
          <button type="submit" class="btn-submit">{{ __('変更を保存') }}</button>
        </div>
      </form>
    </div>
  </div>
  @endif

  @auth
  <div class="modal-overlay" id="edit-review-modal">
    <div class="modal">
      <h2>{{ __('✏️ レビューを編集') }}</h2>
      <form id="edit-review-form" enctype="multipart/form-data">
        <input type="hidden" id="edit-review-id" />
        <div class="form-group">
          <label for="edit-rv-date">{{ __('訪問・注文日') }}</label>
          <input type="date" id="edit-rv-date" name="date" required />
        </div>
        <div class="form-group">
          <label for="edit-rv-order">
            {{ __('注文したメニュー') }}
            <span class="char-count" id="edit-order-count">0 / 200</span>
          </label>
          <textarea id="edit-rv-order" name="order_details" class="order-textarea" rows="3" maxlength="200" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit-rv-impression">
            {{ __('感想・レビュー') }}
            <span class="char-count" id="edit-impression-count">0 / 500</span>
          </label>
          <textarea id="edit-rv-impression" name="impression" class="impression-textarea" maxlength="500" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit-rv-rating">{{ __('評価（1〜5 ⭐）') }}</label>
          <input type="number" id="edit-rv-rating" name="rating" min="1" max="5" placeholder="5" required />
        </div>
        <div class="form-group">
          <label>{{ __('写真（任意・1枚まで）') }}</label>
          <div class="upload-area" id="edit-rv-upload-area">
            <div class="upload-placeholder" id="edit-rv-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>{{ __('クリックまたはドラッグ＆ドロップで写真を変更') }}</span>
            </div>
            <img id="edit-rv-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
            <input type="file" id="edit-rv-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-edit-review-btn">{{ __('キャンセル') }}</button>
          <button type="submit" class="btn-submit">{{ __('変更を保存') }}</button>
        </div>
      </form>
    </div>
  </div>
  @endauth

  <div class="modal-overlay" id="photo-lightbox-modal">
    <div class="photo-lightbox-box">
      <button type="button" class="photo-lightbox-close" id="photo-lightbox-close" aria-label="Close">✕</button>
      <img id="photo-lightbox-img" class="photo-lightbox-img" src="" alt="Enlarged review photo" />
      <p id="photo-lightbox-caption" class="photo-lightbox-caption"></p>
    </div>
  </div>
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
  <script src="/foodlog/assets/js/restaurant.js?v=20261122h"></script>
@endsection
