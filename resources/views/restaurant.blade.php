@extends('layouts.app')
@section('title', 'レストラン詳細 — FoodLog')

@section('content')
  <div class="detail-header">
    <div class="detail-banner-placeholder" id="banner-placeholder" style="display:none;"></div>
    <img class="detail-banner" id="banner-img" src="" alt="" style="display:none;" />
    <div class="detail-overlay"></div>
    <div class="detail-header-content">
      <a href="/foodlog/" class="back-btn">← ホームへ戻る</a>
      <div class="detail-category" id="detail-category"></div>
      <h1 class="detail-name" id="detail-name">読み込み中…</h1>
      <p class="detail-desc" id="detail-desc"></p>
      <p class="detail-address" id="detail-address" style="font-size:0.9rem;color:var(--muted);margin-top:0.5rem;"></p>
      <div style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-top:1rem;align-items:center;">
        <button type="button" class="detail-fav-btn" id="detail-fav-btn">🤍 お気に入り (0)</button>
        @if(Auth::check() && Auth::user()->isAdmin())
          <button class="edit-info-btn" id="edit-info-btn">✏️ 店舗情報を編集</button>
          <button class="edit-info-btn delete-restaurant-btn" id="delete-restaurant-btn">🗑 店舗を削除</button>
        @endif
      </div>
    </div>
  </div>

  <div class="detail-body">
    <section class="reviews-panel">
      <div class="reviews-section-header">
        <div class="reviews-title-group">
          <h2 style="margin-bottom:0;">📖 レビュー一覧</h2>
          <span class="badge" id="review-count-badge">0件</span>
        </div>
        <div class="reviews-toolbar" id="reviews-toolbar">
          <select id="review-sort" class="review-control" aria-label="レビュー並び替え">
            <option value="date_desc">📅 新しい順</option>
            <option value="date_asc">📅 古い順</option>
            <option value="rating_desc">⭐ 星が高い順</option>
            <option value="rating_asc">⭐ 星が低い順</option>
            <option value="likes_desc">👍 役に立った順</option>
          </select>
          <select id="review-rating-filter" class="review-control" aria-label="星数フィルター">
            <option value="all">★ すべて</option>
            <option value="5">★5 のみ</option>
            <option value="4">★4 以上</option>
            <option value="3">★3 以上</option>
            <option value="2">★2 以下</option>
          </select>
          <button type="button" id="review-photo-only" class="review-toggle-btn" aria-pressed="false">📷 写真あり</button>
          @auth
            <button type="button" id="review-my-only" class="review-toggle-btn" aria-pressed="false">👤 自分のみ</button>
          @endauth
          <input type="search" id="review-keyword" class="review-search-input" placeholder="🔍 店内検索..." aria-label="レビューキーワード検索" />
          <button type="button" id="review-filter-reset" class="review-reset-btn" title="条件をリセット" style="display:none;">✕</button>
        </div>
        @auth
          <button class="add-btn" id="add-review-btn">＋ レビューを投稿</button>
        @endauth
      </div>
      <div id="reviews-list">
        <div class="reviews-empty"><div class="empty-icon">⏳</div><p>読み込み中…</p></div>
      </div>
    </section>
  </div>
@endsection

@section('modals')
  @auth
  <!-- レビュー投稿モーダル -->
  <div class="modal-overlay" id="add-review-modal">
    <div class="modal">
      <h2>📝 レビューを投稿</h2>
      <form id="review-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="rv-date">訪問・注文日</label>
          <input type="date" id="rv-date" name="date" required />
        </div>
        <div class="form-group">
          <label for="rv-order">
            注文したメニュー
            <span class="char-count" id="order-count">0 / 200</span>
          </label>
          <textarea id="rv-order" name="order_details" class="order-textarea" rows="3"
            placeholder="例：濃厚豚骨ラーメン&#10;焼き餃子" maxlength="200" required></textarea>
        </div>
        <div class="form-group">
          <label for="rv-impression">
            感想・レビュー
            <span class="char-count" id="impression-count">0 / 500</span>
          </label>
          <textarea id="rv-impression" name="impression" class="impression-textarea"
            placeholder="味・ボリューム・コスパなどの感想を書いてください"
            maxlength="500" required></textarea>
        </div>
        <div class="form-group">
          <label for="rv-rating">評価（1〜5 ⭐）</label>
          <input type="number" id="rv-rating" name="rating" min="1" max="5" placeholder="5" required />
        </div>
        <div class="form-group">
          <label>写真（任意・1枚まで）</label>
          <div class="upload-area" id="rv-upload-area">
            <div class="upload-placeholder" id="rv-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>クリックまたはドラッグ＆ドロップで写真を選択</span>
            </div>
            <img id="rv-image-preview" class="upload-preview" style="display:none;" alt="プレビュー" />
            <input type="file" id="rv-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-add-review-btn">キャンセル</button>
          <button type="submit" class="btn-submit">投稿する</button>
        </div>
      </form>
    </div>
  </div>
  @endauth

  @if(Auth::check() && Auth::user()->isAdmin())
  <!-- 店舗編集モーダル -->
  <div class="modal-overlay" id="edit-modal">
    <div class="modal">
      <h2>✏️ 店舗情報を編集</h2>
      <form id="edit-restaurant-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="edit-name">
            店名
            <span class="char-count" id="edit-name-count">0 / 50</span>
          </label>
          <input type="text" id="edit-name" name="name" maxlength="50" required />
        </div>
        <div class="form-group">
          <label for="edit-category">
            カテゴリ
            <span class="char-count" id="edit-category-count">0 / 30</span>
          </label>
          <input type="text" id="edit-category" name="category" maxlength="30" required />
        </div>
        <div class="form-group">
          <label for="edit-desc">
            紹介文
            <span class="char-count" id="edit-desc-count">0 / 200</span>
          </label>
          <textarea id="edit-desc" name="description" class="impression-textarea" maxlength="200"></textarea>
        </div>
        <div class="form-group">
          <label for="edit-address">
            住所
            <span class="char-count" id="edit-address-count">0 / 100</span>
          </label>
          <input type="text" id="edit-address" name="address" maxlength="100" />
        </div>
        <div class="form-group">
          <label>写真 <span style="color:var(--muted);font-weight:400;">（未選択なら現在の写真を維持）</span></label>
          <div class="upload-area" id="edit-upload-area">
            <div class="upload-placeholder" id="edit-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>クリックまたはドラッグ＆ドロップで写真を変更</span>
            </div>
            <img id="edit-image-preview" class="upload-preview" style="display:none;" alt="プレビュー" />
            <input type="file" id="edit-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-edit-btn">キャンセル</button>
          <button type="submit" class="btn-submit">変更を保存</button>
        </div>
      </form>
    </div>
  </div>
  @endif

  @auth
  <!-- レビュー編集モーダル -->
  <div class="modal-overlay" id="edit-review-modal">
    <div class="modal">
      <h2>✏️ レビューを編集</h2>
      <form id="edit-review-form" enctype="multipart/form-data">
        <input type="hidden" id="edit-review-id" />
        <div class="form-group">
          <label for="edit-rv-date">訪問・注文日</label>
          <input type="date" id="edit-rv-date" name="date" required />
        </div>
        <div class="form-group">
          <label for="edit-rv-order">
            注文したメニュー
            <span class="char-count" id="edit-order-count">0 / 200</span>
          </label>
          <textarea id="edit-rv-order" name="order_details" class="order-textarea" rows="3" maxlength="200" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit-rv-impression">
            感想・レビュー
            <span class="char-count" id="edit-impression-count">0 / 500</span>
          </label>
          <textarea id="edit-rv-impression" name="impression" class="impression-textarea" maxlength="500" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit-rv-rating">評価（1〜5 ⭐）</label>
          <input type="number" id="edit-rv-rating" name="rating" min="1" max="5" placeholder="5" required />
        </div>
        <div class="form-group">
          <label>写真（任意・1枚まで）</label>
          <div class="upload-area" id="edit-rv-upload-area">
            <div class="upload-placeholder" id="edit-rv-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>クリックまたはドラッグ＆ドロップで写真を変更</span>
            </div>
            <img id="edit-rv-image-preview" class="upload-preview" style="display:none;" alt="プレビュー" />
            <input type="file" id="edit-rv-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-edit-review-btn">キャンセル</button>
          <button type="submit" class="btn-submit">変更を保存</button>
        </div>
      </form>
    </div>
  </div>
  @endauth

  <!-- 写真拡大モーダル -->
  <div class="modal-overlay" id="photo-lightbox-modal">
    <div class="photo-lightbox-box">
      <button type="button" class="photo-lightbox-close" id="photo-lightbox-close" aria-label="閉じる">✕</button>
      <img id="photo-lightbox-img" class="photo-lightbox-img" src="" alt="拡大レビュー写真" />
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
  <script src="/foodlog/assets/js/pagination.js?v=20261122b"></script>
  <script src="/foodlog/assets/js/restaurant.js?v=20261122d"></script>
@endsection
