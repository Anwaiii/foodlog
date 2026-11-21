@extends('layouts.app', ['navActive' => 'stats'])
@section('title', '統計ダッシュボード — FoodLog')

@section('content')
  <section class="stats-hero">
    <div class="hero-tag">📊 統計ダッシュボード</div>
    <h1>グルメ記録の <span class="grad">統計データ</span></h1>
    <p>レビュー投稿の傾向やカテゴリ分布、高評価レストランをひと目で確認できます。</p>
  </section>

  <main class="section">
    <!-- サマリーカード -->
    <div class="stats-summary" id="stats-summary">
      <div class="stats-card">
        <div class="stats-card-icon">🏪</div>
        <div class="stats-card-val" id="s-restaurants">0</div>
        <div class="stats-card-label">登録店舗数</div>
      </div>
      <div class="stats-card">
        <div class="stats-card-icon">📝</div>
        <div class="stats-card-val" id="s-reviews">0</div>
        <div class="stats-card-label">総レビュー数</div>
      </div>
      <div class="stats-card">
        <div class="stats-card-icon">⭐</div>
        <div class="stats-card-val" id="s-avg-rating">—</div>
        <div class="stats-card-label">平均評価</div>
      </div>
      <div class="stats-card">
        <div class="stats-card-icon">📅</div>
        <div class="stats-card-val" id="s-latest">—</div>
        <div class="stats-card-label">最新レビュー日</div>
      </div>
    </div>

    <!-- グラフ行 -->
    <div class="stats-charts">
      <div class="stats-panel">
        <h3>📈 月別レビュー数（直近6か月）</h3>
        <div class="chart-container" id="monthly-chart"></div>
      </div>

      <div class="stats-panel">
        <h3>🏷️ カテゴリ別店舗数</h3>
        <div class="chart-container" id="category-chart"></div>
      </div>
    </div>

    <!-- ランキング -->
    <div class="stats-panel" style="margin-top:1.5rem;">
      <h3>🏆 高評価レストラン TOP 5</h3>
      <div id="ranking-list"></div>
    </div>
  </main>
@endsection

@section('scripts')
  <script src="/foodlog/assets/js/stats.js?v=20261122b"></script>
@endsection
