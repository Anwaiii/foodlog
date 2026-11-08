// ─── 統計ダッシュボード ──────────────────────────────────────────────────────

const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

async function loadData() {
  const [resR, resRv] = await Promise.all([
    fetch('/foodlog/api/restaurants.php'),
    fetch('/foodlog/api/restaurants.php'),
  ]);
  const restaurants = await resR.json();

  // 全レストランのレビューを取得
  const allReviews = [];
  for (const r of restaurants) {
    const rv = await fetch(`/foodlog/api/reviews.php?restaurant_id=${r.id}`);
    const reviews = await rv.json();
    allReviews.push(...reviews.map(review => ({ ...review, restaurant: r })));
  }

  return { restaurants, reviews: allReviews };
}

function renderSummary(restaurants, reviews) {
  document.getElementById('s-restaurants').textContent = restaurants.length;
  document.getElementById('s-reviews').textContent = reviews.length;
  document.getElementById('total-reviews-badge').textContent = reviews.length + ' Reviews';

  // Average rating
  const rated = reviews.filter(r => r.rating);
  if (rated.length > 0) {
    const avg = rated.reduce((sum, r) => sum + Number(r.rating), 0) / rated.length;
    document.getElementById('s-avg-rating').textContent = avg.toFixed(1);
  }

  // Latest order
  if (reviews.length > 0) {
    const dates = reviews.map(r => r.date).sort().reverse();
    const d = dates[0];
    document.getElementById('s-latest').textContent = d.replace(/-/g, '/');
  }
}

function renderMonthlyChart(reviews) {
  const container = document.getElementById('monthly-chart');

  // 直近6ヶ月のラベルを生成
  const months = [];
  const now = new Date();
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    const label = d.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
    months.push({ key, label, count: 0 });
  }

  // 集計
  reviews.forEach(rv => {
    const key = rv.date.substring(0, 7); // "2026-08"
    const m = months.find(x => x.key === key);
    if (m) m.count++;
  });

  const max = Math.max(...months.map(m => m.count), 1);

  container.innerHTML = months.map(m => `
    <div class="bar-row">
      <div class="bar-label">${m.label}</div>
      <div class="bar-track">
        <div class="bar-fill" style="width:${(m.count / max) * 100}%"></div>
      </div>
      <div class="bar-value">${m.count}</div>
    </div>
  `).join('');
}

function renderCategoryChart(restaurants) {
  const container = document.getElementById('category-chart');
  const cats = {};
  restaurants.forEach(r => {
    const cat = r.category || 'Other';
    cats[cat] = (cats[cat] || 0) + 1;
  });

  const sorted = Object.entries(cats).sort((a, b) => b[1] - a[1]);
  const max = Math.max(...sorted.map(x => x[1]), 1);
  const colors = ['#ff6b35', '#7c3aed', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444'];

  container.innerHTML = sorted.map(([cat, count], i) => `
    <div class="bar-row">
      <div class="bar-label">${cat}</div>
      <div class="bar-track">
        <div class="bar-fill" style="width:${(count / max) * 100}%;background:${colors[i % colors.length]}"></div>
      </div>
      <div class="bar-value">${count}</div>
    </div>
  `).join('');
}

function renderRanking(restaurants, reviews) {
  const container = document.getElementById('ranking-list');

  // レストランごとの平均評価を計算
  const stats = restaurants.map(r => {
    const rvs = reviews.filter(rv => String(rv.restaurant_id) === String(r.id));
    const rated = rvs.filter(rv => rv.rating);
    const avg = rated.length > 0
      ? rated.reduce((s, rv) => s + Number(rv.rating), 0) / rated.length
      : 0;
    return { ...r, avg, reviewCount: rvs.length };
  });

  const top = stats.filter(r => r.reviewCount > 0).sort((a, b) => b.avg - a.avg).slice(0, 5);

  if (top.length === 0) {
    container.innerHTML = '<div class="reviews-empty"><div class="empty-icon">📋</div><p>No reviews yet.</p></div>';
    return;
  }

  container.innerHTML = top.map((r, i) => {
    const stars = '⭐'.repeat(Math.round(r.avg));
    const imgSrc = r.image ? `/foodlog/${r.image}` : null;
    const gradient = GRADIENTS[i % GRADIENTS.length];
    const thumb = imgSrc
      ? `<img class="ranking-thumb" src="${imgSrc}" alt="${r.name}" />`
      : `<div class="ranking-thumb-placeholder" style="background:${gradient}">🍽️</div>`;
    return `
      <a href="/foodlog/restaurant.php?id=${r.id}" class="ranking-item">
        <div class="ranking-rank">#${i + 1}</div>
        ${thumb}
        <div class="ranking-info">
          <div class="ranking-name">${r.name}</div>
          <div class="ranking-meta">${stars} ${r.avg.toFixed(1)} · ${r.reviewCount} reviews</div>
        </div>
        <div class="card-arrow">→</div>
      </a>`;
  }).join('');
}

async function init() {
  const { restaurants, reviews } = await loadData();
  renderSummary(restaurants, reviews);
  renderMonthlyChart(reviews);
  renderCategoryChart(restaurants);
  renderRanking(restaurants, reviews);
}

init();
