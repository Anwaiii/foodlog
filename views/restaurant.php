<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Restaurant — FoodLog</title>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css" />
</head>
<body>

  <nav class="nav">
    <a href="/foodlog/" class="nav-brand">
      <span class="logo-icon">🍜</span>
      <span>FoodLog</span>
    </a>
    <div class="nav-right">
      <span class="badge" id="review-count-badge">0 Reviews</span>
    </div>
  </nav>

  <div class="detail-header">
    <div class="detail-banner-placeholder" id="banner-placeholder" style="display:none;"></div>
    <img class="detail-banner" id="banner-img" src="" alt="" style="display:none;" />
    <div class="detail-overlay"></div>
    <div class="detail-header-content">
      <a href="/foodlog/" class="back-btn">← Back to Home</a>
      <div class="detail-category" id="detail-category"></div>
      <h1 class="detail-name" id="detail-name">Loading…</h1>
      <p class="detail-desc" id="detail-desc"></p>
      <p class="detail-address" id="detail-address" style="font-size:0.9rem;color:var(--muted);margin-top:0.5rem;"></p>
      <div style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-top:1rem;">
        <button class="edit-info-btn" id="edit-info-btn">✏️ Edit Info</button>
        <button class="edit-info-btn delete-restaurant-btn" id="delete-restaurant-btn">🗑 Delete</button>
      </div>
    </div>
  </div>

  <div class="detail-body">
    <div class="detail-grid">
      <aside class="review-form-card">
        <h2>📝 Log an Order</h2>
        <form id="review-form" enctype="multipart/form-data">
          <div class="form-group">
            <label for="rv-date">Date</label>
            <input type="date" id="rv-date" required />
          </div>
          <div class="form-group">
            <label for="rv-order">
              What did you order?
              <span class="char-count" id="order-count">0 / 200</span>
            </label>
            <textarea id="rv-order" class="order-textarea" rows="3"
              placeholder="e.g. Tonkotsu Ramen&#10;Gyoza&#10;Karaage" maxlength="200" required></textarea>
          </div>
          <div class="form-group">
            <label for="rv-impression">
              Your Impression
              <span class="char-count" id="impression-count">0 / 500</span>
            </label>
            <textarea id="rv-impression" class="impression-textarea"
              placeholder="How was it? Taste, delivery time, value…"
              maxlength="500" required></textarea>
          </div>
          <div class="form-group">
            <label for="rv-rating">Rating (1–5 ⭐)</label>
            <input type="number" id="rv-rating" name="rating" min="1" max="5" placeholder="5" required />
          </div>
          <div class="form-group">
            <label>Photo (optional, max 1)</label>
            <div class="upload-area" id="rv-upload-area">
              <div class="upload-placeholder" id="rv-upload-placeholder">
                <span class="upload-icon">📷</span>
                <span>Click or drag to upload photo</span>
              </div>
              <img id="rv-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
              <input type="file" id="rv-image" name="image" accept="image/*" style="display:none;" />
            </div>
          </div>
          <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;">Save Review</button>
        </form>
      </aside>

      <section class="reviews-panel">
        <h2>📖 Order History</h2>
        <div id="reviews-list">
          <div class="reviews-empty"><div class="empty-icon">⏳</div><p>Loading…</p></div>
        </div>
      </section>
    </div>
  </div>

  <footer><p>FoodLog — Data saved in MySQL database 🗄️</p></footer>

  <!-- Edit Restaurant Modal -->
  <div class="modal-overlay" id="edit-modal">
    <div class="modal">
      <h2>✏️ Edit Restaurant Info</h2>
      <form id="edit-restaurant-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="edit-name">
            Restaurant Name
            <span class="char-count" id="edit-name-count">0 / 50</span>
          </label>
          <input type="text" id="edit-name" name="name" maxlength="50" required />
        </div>
        <div class="form-group">
          <label for="edit-category">
            Category
            <span class="char-count" id="edit-category-count">0 / 200</span>
          </label>
          <input type="text" id="edit-category" name="category" maxlength="200" required />
        </div>
        <div class="form-group">
          <label for="edit-desc">
            Description
            <span class="char-count" id="edit-desc-count">0 / 200</span>
          </label>
          <textarea id="edit-desc" name="description" class="impression-textarea" maxlength="200"></textarea>
        </div>
        <div class="form-group">
          <label for="edit-address">
            Address
            <span class="char-count" id="edit-address-count">0 / 100</span>
          </label>
          <input type="text" id="edit-address" name="address" maxlength="100" />
        </div>
        <div class="form-group">
          <label>Photo <span style="color:var(--muted);font-weight:400;">(leave empty to keep current)</span></label>
          <div class="upload-area" id="edit-upload-area">
            <div class="upload-placeholder" id="edit-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>Click or drag to change photo</span>
            </div>
            <img id="edit-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
            <input type="file" id="edit-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-edit-btn">Cancel</button>
          <button type="submit" class="btn-submit">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Review Modal -->
  <div class="modal-overlay" id="edit-review-modal">
    <div class="modal">
      <h2>✏️ Edit Review</h2>
      <form id="edit-review-form" enctype="multipart/form-data">
        <input type="hidden" id="edit-review-id" />
        <div class="form-group">
          <label for="edit-rv-date">Date</label>
          <input type="date" id="edit-rv-date" required />
        </div>
        <div class="form-group">
          <label for="edit-rv-order">
            What did you order?
            <span class="char-count" id="edit-order-count">0 / 200</span>
          </label>
          <textarea id="edit-rv-order" class="order-textarea" rows="3" placeholder="e.g. Tonkotsu Ramen&#10;Gyoza" maxlength="200" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit-rv-impression">
            Your Impression
            <span class="char-count" id="edit-impression-count">0 / 500</span>
          </label>
          <textarea id="edit-rv-impression" class="impression-textarea" placeholder="How was it?" maxlength="500" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit-rv-rating">Rating (1–5 ⭐)</label>
          <input type="number" id="edit-rv-rating" name="rating" min="1" max="5" placeholder="5" required />
        </div>
        <div class="form-group">
          <label>Photo (optional, max 1)</label>
          <div class="upload-area" id="edit-rv-upload-area">
            <div class="upload-placeholder" id="edit-rv-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>Click or drag to change photo</span>
            </div>
            <img id="edit-rv-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
            <input type="file" id="edit-rv-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-edit-review-btn">Cancel</button>
          <button type="submit" class="btn-submit">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script src="/foodlog/assets/js/restaurant.js"></script>
</body>
</html>
