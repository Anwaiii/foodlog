// ─── 統計ダッシュボード ──────────────────────────────────────────────────────

const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

function t(k) {
  return typeof window.t === 'function' ? window.t(k) : k;
}

function isEn() {
  return window.LOCALE === 'en';
}

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (text !== undefined && text !== null) node.textContent = text;
  return node;
}

async function loadData() {
  const resR = await fetch('/foodlog/api/restaurants.php');
  const restaurants = await resR.json();

  const allReviews = [];
  await Promise.all(restaurants.map(async r => {
    const rv = await fetch(`/foodlog/api/reviews.php?restaurant_id=${encodeURIComponent(r.id)}`);
    const reviews = await rv.json();
    allReviews.push(...reviews.map(review => ({ ...review, restaurant: r })));
  }));

  return { restaurants, reviews: allReviews };
}

function renderSummary(restaurants, reviews) {
  document.getElementById('s-restaurants').textContent = restaurants.length;
  document.getElementById('s-reviews').textContent = reviews.length;
  const totalBadge = document.getElementById('total-reviews-badge');
  if (totalBadge) totalBadge.textContent = isEn() ? `${reviews.length} Reviews` : `${reviews.length}件`;

  const rated = reviews.filter(r => Number(r.rating) > 0);
  if (rated.length > 0) {
    const avg = rated.reduce((sum, r) => sum + Number(r.rating), 0) / rated.length;
    document.getElementById('s-avg-rating').textContent = avg.toFixed(1);
  }

  if (reviews.length > 0) {
    const dates = reviews.map(r => r.date).sort().reverse();
    const d = dates[0];
    document.getElementById('s-latest').textContent = d.replace(/-/g, '/');
  }
}

function renderMonthlyChart(reviews) {
  const container = document.getElementById('monthly-chart');
  container.replaceChildren();

  const months = [];
  const now = new Date();
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    const label = isEn()
      ? d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
      : `${d.getFullYear()}年${d.getMonth() + 1}月`;
    months.push({ key, label, count: 0 });
  }

  reviews.forEach(rv => {
    const key = (rv.date || '').substring(0, 7);
    const m = months.find(x => x.key === key);
    if (m) m.count++;
  });

  const max = Math.max(...months.map(m => m.count), 1);

  months.forEach(m => {
    const row = el('div', 'bar-row');
    const track = el('div', 'bar-track');
    const fill = el('div', 'bar-fill');
    fill.style.width = `${(m.count / max) * 100}%`;
    track.appendChild(fill);
    row.append(
      el('div', 'bar-label', m.label),
      track,
      el('div', 'bar-value', String(m.count))
    );
    container.appendChild(row);
  });
}

function renderCategoryChart(restaurants) {
  const container = document.getElementById('category-chart');
  container.replaceChildren();

  const cats = {};
  restaurants.forEach(r => {
    const cat = r.category || t('その他');
    cats[cat] = (cats[cat] || 0) + 1;
  });

  const sorted = Object.entries(cats).sort((a, b) => b[1] - a[1]);
  const max = Math.max(...sorted.map(x => x[1]), 1);
  const colors = ['#ff6b35', '#7c3aed', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444'];

  sorted.forEach(([cat, count], i) => {
    const row = el('div', 'bar-row');
    const track = el('div', 'bar-track');
    const fill = el('div', 'bar-fill');
    fill.style.width = `${(count / max) * 100}%`;
    fill.style.background = colors[i % colors.length];
    track.appendChild(fill);
    row.append(
      el('div', 'bar-label', cat),
      track,
      el('div', 'bar-value', String(count))
    );
    container.appendChild(row);
  });
}

function renderRanking(restaurants, reviews) {
  const container = document.getElementById('ranking-list');
  container.replaceChildren();

  const stats = restaurants.map(r => {
    const rvs = reviews.filter(rv => String(rv.restaurant_id) === String(r.id));
    const rated = rvs.filter(rv => Number(rv.rating) > 0);
    const avg = rated.length > 0
      ? rated.reduce((s, rv) => s + Number(rv.rating), 0) / rated.length
      : 0;
    return { ...r, avg, ratedCount: rated.length, reviewCount: rvs.length };
  });

  const top = stats
    .filter(r => r.ratedCount > 0)
    .sort((a, b) => (b.avg - a.avg) || (b.reviewCount - a.reviewCount))
    .slice(0, 5);

  if (top.length === 0) {
    const empty = el('div', 'reviews-empty');
    empty.append(el('div', 'empty-icon', '📋'), el('p', '', t('まだレビューがありません。')));
    container.appendChild(empty);
    return;
  }

  top.forEach((r, i) => {
    const link = el('a', 'ranking-item');
    link.href = `/foodlog/restaurant.php?id=${encodeURIComponent(r.id)}`;

    let thumb;
    if (r.image) {
      thumb = el('img', 'ranking-thumb');
      thumb.src = `/foodlog/${r.image}`;
      thumb.alt = r.name || '';
    } else {
      thumb = el('div', 'ranking-thumb-placeholder', '🍽️');
      thumb.style.background = GRADIENTS[i % GRADIENTS.length];
    }

    const info = el('div', 'ranking-info');
    const reviewLabel = isEn()
      ? `${r.reviewCount} ${r.reviewCount === 1 ? 'review' : 'reviews'}`
      : `${r.reviewCount}件のレビュー`;
    info.append(
      el('div', 'ranking-name', r.name || ''),
      el('div', 'ranking-meta', `★ ${r.avg.toFixed(1)}\u00A0\u00A0\u00A0${reviewLabel}`)
    );

    link.append(
      el('div', 'ranking-rank', `#${i + 1}`),
      thumb,
      info,
      el('div', 'card-arrow', '→')
    );
    container.appendChild(link);
  });
}

async function init() {
  const { restaurants, reviews } = await loadData();
  renderSummary(restaurants, reviews);
  renderMonthlyChart(reviews);
  renderCategoryChart(restaurants);
  renderRanking(restaurants, reviews);
}

init();
