<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Search — FoodLog</title>
  <link rel="stylesheet" href="/foodlog/assets/css/style.css" />
</head>
<body>

  <nav class="nav">
    <a href="/foodlog/" class="nav-brand">
      <span class="logo-icon">🍜</span>
      <span>FoodLog</span>
    </a>
    <div class="nav-links">
      <a href="/foodlog/" class="nav-link">Home</a>
      <a href="/foodlog/stats.php" class="nav-link">Stats</a>
      <a href="/foodlog/search.php" class="nav-link active">Search</a>
    </div>
    <div class="nav-right">
      <span class="badge" id="total-reviews-badge">0 Reviews</span>
    </div>
  </nav>

  <section class="search-hero">
    <div class="hero-tag">🔍 Find Restaurants</div>
    <h1>Search & <span class="grad">Filter</span></h1>
    <div class="search-bar-wrapper">
      <input type="text" class="search-input" id="search-input"
             placeholder="Search restaurants, categories, reviews..." autofocus />
    </div>
  </section>

  <main class="section">
    <!-- Filters -->
    <div class="filter-bar" id="filter-bar">
      <div class="filter-group">
        <label class="filter-label">Category</label>
        <div class="filter-chips" id="category-chips">
          <button class="filter-chip active" data-category="all">All</button>
        </div>
      </div>
      <div class="filter-group">
        <label class="filter-label">Min Rating</label>
        <div class="filter-chips" id="rating-chips">
          <button class="filter-chip active" data-rating="0">All</button>
          <button class="filter-chip" data-rating="3">★3+</button>
          <button class="filter-chip" data-rating="4">★4+</button>
          <button class="filter-chip" data-rating="5">★5</button>
        </div>
      </div>
      <div class="filter-group">
        <label class="filter-label">Sort by</label>
        <div class="filter-chips" id="sort-chips">
          <button class="filter-chip active" data-sort="reviews">Most Reviews</button>
          <button class="filter-chip" data-sort="rating">Highest Rated</button>
          <button class="filter-chip" data-sort="newest">Newest</button>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="section-header">
      <div class="section-title">Results <span id="result-count"></span></div>
    </div>
    <div class="restaurant-grid" id="search-results">
      <div class="empty-state"><div class="empty-icon">⏳</div><p>Loading…</p></div>
    </div>
  </main>

  <footer><p>FoodLog — Data saved in MySQL database 🗄️</p></footer>

  <script src="/foodlog/assets/js/search.js"></script>
</body>
</html>
