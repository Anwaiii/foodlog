<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>FoodLog — Uber Eats Review Tracker</title>
  <meta name="description" content="Track and review your Uber Eats orders." />
  <link rel="stylesheet" href="/foodlog/assets/css/style.css?v=20261122f" />
</head>
<body>

  @include('partials.nav', ['active' => 'home', 'badgeId' => 'total-reviews-badge'])

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
        <div class="stat-label">Last Review</div>
      </div>
    </div>
  </section>

  <main class="section">
    <!-- Search & Filters (integrated from Search screen) -->
    <div class="search-bar-wrapper" style="margin-bottom:1.25rem;max-width:100%;">
      <input type="search" class="search-input" id="search-input"
             placeholder="🔍 Search restaurants, categories, reviews..." />
    </div>
    <div class="filter-bar" id="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Category</label>
        <div class="filter-chips" id="category-chips">
          <button type="button" class="filter-chip active" data-category="all">All</button>
        </div>
      </div>
      <div class="filter-group">
        <label class="filter-label">Min Rating</label>
        <div class="filter-chips" id="rating-chips">
          <button type="button" class="filter-chip active" data-rating="0">All</button>
          <button type="button" class="filter-chip" data-rating="3">★3+</button>
          <button type="button" class="filter-chip" data-rating="4">★4+</button>
          <button type="button" class="filter-chip" data-rating="5">★5</button>
        </div>
      </div>
      <div class="filter-group">
        <label class="filter-label">Sort by</label>
        <div class="filter-chips" id="sort-chips">
          <button type="button" class="filter-chip active" data-sort="default">Default</button>
          <button type="button" class="filter-chip" data-sort="reviews">Most Reviews</button>
          <button type="button" class="filter-chip" data-sort="rating">Highest Rated</button>
          <button type="button" class="filter-chip" data-sort="newest">Newest</button>
        </div>
      </div>
    </div>

    <div class="section-header">
      <div class="section-title">Restaurants <span id="restaurant-count"></span></div>
      @if(Auth::check() && Auth::user()->isAdmin())
        <button class="add-btn" id="add-restaurant-btn">＋ Add Restaurant</button>
      @endif
    </div>
    <div class="restaurant-grid" id="restaurant-grid">
      <div class="empty-state"><div class="empty-icon">⏳</div><p>Loading…</p></div>
    </div>
    <div id="home-pagination"></div>
  </main>

  <footer><p>FoodLog — Data saved in MySQL database 🗄️</p></footer>

  @if(Auth::check() && Auth::user()->isAdmin())
  <!-- Add Restaurant Modal -->
  <div class="modal-overlay" id="add-modal">
    <div class="modal">
      <h2>Add New Restaurant</h2>
      <form id="add-restaurant-form" enctype="multipart/form-data">
        <div class="form-group">
          <label for="r-name">
            Restaurant Name
            <span class="char-count" id="r-name-count">0 / 50</span>
          </label>
          <input type="text" id="r-name" name="name" placeholder="e.g. Spicy Ramen Hub" maxlength="50" required />
        </div>
        <div class="form-group">
          <label for="r-category">
            Category
            <span class="char-count" id="r-category-count">0 / 30</span>
          </label>
          <input type="text" id="r-category" name="category" placeholder="e.g. Japanese, Burgers…" maxlength="30" required />
        </div>
        <div class="form-group">
          <label for="r-desc">
            Short Description
            <span class="char-count" id="r-desc-count">0 / 200</span>
          </label>
          <textarea id="r-desc" name="description" class="impression-textarea" placeholder="e.g. Authentic tonkotsu ramen" maxlength="200"></textarea>
        </div>
        <div class="form-group">
          <label for="r-address">
            Address
            <span class="char-count" id="r-address-count">0 / 100</span>
          </label>
          <input type="text" id="r-address" name="address" placeholder="e.g. 東京都渋谷区神宮前1-2-3" maxlength="100" />
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
  @endif

  <script>
    window.AUTH = {
      loggedIn: {{ Auth::check() ? 'true' : 'false' }},
      isAdmin: {{ Auth::check() && Auth::user()->isAdmin() ? 'true' : 'false' }},
      userId: {{ Auth::check() ? Auth::id() : 'null' }}
    };
  </script>
  <script src="/foodlog/assets/js/pagination.js?v=20261122a"></script>
  <script src="/foodlog/assets/js/app.js?v=20261122c"></script>
</body>
</html>
