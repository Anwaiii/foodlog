<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FoodLog — Uber Eats Review Tracker</title>
  <meta name="description" content="Track and review your Uber Eats orders." />
  <link rel="stylesheet" href="/foodlog/assets/css/style.css" />
</head>
<body>

  <nav class="nav">
    <a href="/foodlog/" class="nav-brand">
      <span class="logo-icon">🍜</span>
      <span>FoodLog</span>
    </a>
    <div class="nav-center">
      <span class="screen-name-label">🏠 ホーム画面</span>
    </div>
    <div class="nav-right">
      <span class="badge" id="total-reviews-badge">0 Reviews</span>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-tag">🍴 Your Personal Food Diary</div>
    <h1>Track Every <span class="grad">Delicious</span><br/>Uber Eats Order</h1>
    <p>Keep a personal log of your favourite restaurants, dishes, and honest impressions — saved permanently in a database.</p>
    <div class="hero-stats">
      <div>
        <div class="stat-val" id="stat-restaurants">0</div>
        <div class="stat-label">Restaurants</div>
      </div>
      <div>
        <div class="stat-val" id="stat-reviews">0</div>
        <div class="stat-label">Total Reviews</div>
      </div>
      <div>
        <div class="stat-val" id="stat-latest">—</div>
        <div class="stat-label">Last Order</div>
      </div>
    </div>
  </section>

  <main class="section">
    <div class="section-header">
      <div class="section-title">Restaurants <span id="restaurant-count"></span></div>
      <button class="add-btn" id="add-restaurant-btn">＋ Add Restaurant</button>
    </div>
    <div class="restaurant-grid" id="restaurant-grid">
      <div class="empty-state"><div class="empty-icon">⏳</div><p>Loading…</p></div>
    </div>
  </main>

  <footer><p>FoodLog — Data saved in MySQL database 🗄️</p></footer>

  <!-- Add Restaurant Modal -->
  <div class="modal-overlay" id="add-modal">
    <div class="modal">
      <h2>Add New Restaurant</h2>
      <form id="add-restaurant-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="r-name">Restaurant Name *</label>
          <input type="text" id="r-name" name="name" placeholder="e.g. Spicy Ramen Hub" required />
        </div>
        <div class="form-group">
          <label for="r-category">Category *</label>
          <input type="text" id="r-category" name="category" placeholder="e.g. Japanese, Burgers…" required />
        </div>
        <div class="form-group">
          <label for="r-desc">Short Description</label>
          <input type="text" id="r-desc" name="description" placeholder="e.g. Authentic tonkotsu ramen" />
        </div>
        <div class="form-group">
          <label for="r-image">Photo</label>
          <div class="upload-area" id="add-upload-area">
            <div class="upload-placeholder" id="add-upload-placeholder">
              <span class="upload-icon">📷</span>
              <span>Click or drag to upload photo</span>
            </div>
            <img id="add-image-preview" class="upload-preview" style="display:none;" alt="Preview" />
            <input type="file" id="r-image" name="image" accept="image/*" style="display:none;" />
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancel-modal-btn">Cancel</button>
          <button type="submit" class="btn-submit">Add Restaurant</button>
        </div>
      </form>
    </div>
  </div>

  <script src="/foodlog/assets/js/app.js"></script>
</body>
</html>
