// ─── 検索・フィルター ────────────────────────────────────────────────────────

const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

let allRestaurants = [];
let allReviews = {};  // { restaurantId: [reviews] }
let currentFilters = {
  query: '',
  category: 'all',
  minRating: 0,
  sort: 'reviews',
};

// ─── データ読み込み ──────────────────────────────────────────────────────────
async function loadData() {
  const res = await fetch('/foodlog/api/restaurants.php');
  allRestaurants = await res.json();

  // 全レストランのレビューを取得
  for (const r of allRestaurants) {
    const rv = await fetch(`/foodlog/api/reviews.php?restaurant_id=${r.id}`);
    allReviews[r.id] = await rv.json();
  }

  // 総レビュー数
  const totalReviews = Object.values(allReviews).reduce((s, arr) => s + arr.length, 0);
  document.getElementById('total-reviews-badge').textContent = totalReviews + ' Reviews';

  // カテゴリチップを動的生成
  buildCategoryChips();

  // 初期表示
  applyFilters();
}

// ─── カテゴリチップ動的生成 ──────────────────────────────────────────────────
function buildCategoryChips() {
  const cats = new Set();
  allRestaurants.forEach(r => { if (r.category) cats.add(r.category); });

  const container = document.getElementById('category-chips');
  const sortedCats = [...cats].sort();
  sortedCats.forEach(cat => {
    const btn = document.createElement('button');
    btn.className = 'filter-chip';
    btn.dataset.category = cat;
    btn.textContent = cat;
    container.appendChild(btn);
  });
}

// ─── フィルタ適用 ────────────────────────────────────────────────────────────
function applyFilters() {
  let results = [...allRestaurants];

  // レストランごとの統計を計算
  results = results.map(r => {
    const reviews = allReviews[r.id] || [];
    const rated = reviews.filter(rv => rv.rating);
    const avgRating = rated.length > 0
      ? rated.reduce((s, rv) => s + Number(rv.rating), 0) / rated.length
      : 0;
    return { ...r, reviews, avgRating, reviewCount: reviews.length };
  });

  // キーワード検索
  if (currentFilters.query) {
    const q = currentFilters.query.toLowerCase();
    results = results.filter(r => {
      const nameMatch = r.name.toLowerCase().includes(q);
      const catMatch = (r.category || '').toLowerCase().includes(q);
      const descMatch = (r.description || '').toLowerCase().includes(q);
      const addressMatch = (r.address || '').toLowerCase().includes(q);
      const reviewMatch = r.reviews.some(rv =>
        rv.order_details.toLowerCase().includes(q) ||
        rv.impression.toLowerCase().includes(q)
      );
      return nameMatch || catMatch || descMatch || addressMatch || reviewMatch;
    });
  }

  // カテゴリフィルタ
  if (currentFilters.category !== 'all') {
    results = results.filter(r => r.category === currentFilters.category);
  }

  // 評価フィルタ
  if (currentFilters.minRating > 0) {
    results = results.filter(r => r.avgRating >= currentFilters.minRating);
  }

  // 並び替え
  switch (currentFilters.sort) {
    case 'reviews':
      results.sort((a, b) => b.reviewCount - a.reviewCount);
      break;
    case 'rating':
      results.sort((a, b) => b.avgRating - a.avgRating);
      break;
    case 'newest':
      results.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
  }

  renderResults(results);
}

// ─── 結果描画 ────────────────────────────────────────────────────────────────
function renderResults(results) {
  const grid = document.getElementById('search-results');
  document.getElementById('result-count').textContent = `(${results.length})`;

  if (results.length === 0) {
    grid.innerHTML = `<div class="empty-state"><div class="empty-icon">🔍</div><p>No restaurants found.<br/>Try a different search or filter.</p></div>`;
    return;
  }

  grid.innerHTML = '';
  results.forEach((r, i) => {
    const card = document.createElement('a');
    card.className = 'restaurant-card';
    card.href = `/foodlog/restaurant.php?id=${r.id}`;

    const imgSrc = r.image ? `/foodlog/${r.image}` : null;
    const gradient = GRADIENTS[i % GRADIENTS.length];
    const imageHtml = imgSrc
      ? `<img class="card-image" src="${imgSrc}" alt="${r.name}" loading="lazy"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" />
         <div class="card-image-placeholder" style="display:none;background:${gradient}">🍽️</div>`
      : `<div class="card-image-placeholder" style="background:${gradient}">🍽️</div>`;

    const stars = r.avgRating > 0 ? '⭐'.repeat(Math.round(r.avgRating)) + ` ${r.avgRating.toFixed(1)}` : '';

    card.innerHTML = `${imageHtml}
      <div class="card-body">
        <div class="card-category">${r.category || ''}</div>
        <div class="card-name">${r.name}</div>
        <div class="card-desc">${r.description || ''}</div>
        ${r.address ? `<div style="font-size:0.85rem;color:var(--muted);margin-top:0.4rem;">📍 ${r.address}</div>` : ''}
        ${stars ? `<div style="font-size:0.82rem;margin-top:0.5rem;">${stars}</div>` : ''}
        <div class="card-footer">
          <div class="review-count"><strong>${r.reviewCount}</strong> ${r.reviewCount === 1 ? 'review' : 'reviews'}</div>
          <div class="card-arrow">→</div>
        </div>
      </div>`;
    grid.appendChild(card);
  });
}

// ─── イベントバインド ────────────────────────────────────────────────────────
document.getElementById('search-input').addEventListener('input', e => {
  currentFilters.query = e.target.value.trim();
  applyFilters();
});

document.addEventListener('click', e => {
  const chip = e.target.closest('.filter-chip');
  if (!chip) return;

  // どのグループか判定
  const group = chip.closest('.filter-chips');
  if (!group) return;

  // 同一グループ内のactiveを解除
  group.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
  chip.classList.add('active');

  if (chip.dataset.category !== undefined) {
    currentFilters.category = chip.dataset.category;
  } else if (chip.dataset.rating !== undefined) {
    currentFilters.minRating = Number(chip.dataset.rating);
  } else if (chip.dataset.sort !== undefined) {
    currentFilters.sort = chip.dataset.sort;
  }

  applyFilters();
});

// ─── 初期化 ──────────────────────────────────────────────────────────────────
loadData();
