@extends('layouts.app', ['navActive' => 'stats'])
@section('lang', 'en')
@section('title', 'Stats — FoodLog')

@section('content')
  <section class="stats-hero">
    <div class="hero-tag">📊 Dashboard</div>
    <h1>Your Food <span class="grad">Statistics</span></h1>
    <p>Visualize your dining habits and discover patterns.</p>
  </section>

  <main class="section">
    <!-- Summary Cards -->
    <div class="stats-summary" id="stats-summary">
      <div class="stats-card">
        <div class="stats-card-icon">🏪</div>
        <div class="stats-card-val" id="s-restaurants">0</div>
        <div class="stats-card-label">Restaurants</div>
      </div>
      <div class="stats-card">
        <div class="stats-card-icon">📝</div>
        <div class="stats-card-val" id="s-reviews">0</div>
        <div class="stats-card-label">Total Reviews</div>
      </div>
      <div class="stats-card">
        <div class="stats-card-icon">⭐</div>
        <div class="stats-card-val" id="s-avg-rating">—</div>
        <div class="stats-card-label">Avg Rating</div>
      </div>
      <div class="stats-card">
        <div class="stats-card-icon">📅</div>
        <div class="stats-card-val" id="s-latest">—</div>
        <div class="stats-card-label">Last Review</div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="stats-charts">
      <!-- Monthly Orders -->
      <div class="stats-panel">
        <h3>📈 Monthly Orders (last 6 months)</h3>
        <div class="chart-container" id="monthly-chart"></div>
      </div>

      <!-- Category Breakdown -->
      <div class="stats-panel">
        <h3>🏷️ Restaurants by Category</h3>
        <div class="chart-container" id="category-chart"></div>
      </div>
    </div>

    <!-- Ranking -->
    <div class="stats-panel" style="margin-top:1.5rem;">
      <h3>🏆 Top Rated Restaurants</h3>
      <div id="ranking-list"></div>
    </div>
  </main>
@endsection

@section('scripts')
  <script src="/foodlog/assets/js/stats.js?v=20261120f"></script>
@endsection
