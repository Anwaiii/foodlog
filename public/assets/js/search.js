// ─── 検索・フィルター ────────────────────────────────────────────────────────

const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (text !== undefined && text !== null) node.textContent = text;
  return node;
}

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

  for (const r of allRestaurants) {
    const rv = await fetch(`/foodlog/api/reviews.php?restaurant_id=${encodeURIComponent(r.id)}`);
    allReviews[r.id] = await rv.json();
  }

  const totalReviews = Object.values(allReviews).reduce((s, arr) => s + arr.length, 0);
  const totalBadge = document.getElementById('total-reviews-badge');
  if (totalBadge) totalBadge.textContent = totalReviews + ' Reviews';

  buildCategoryChips();
  applyFilters();
}

// ─── カテゴリチップ動的生成 ──────────────────────────────────────────────────
function buildCategoryChips() {
  const cats = new Set();
  allRestaurants.forEach(r => { if (r.category) cats.add(r.category); });

  const container = document.getElementById('category-chips');
  const sortedCats = [...cats].sort();
  sortedCats.forEach(cat => {
    const btn = el('button', 'filter-chip', cat);
    btn.dataset.category = cat;
    container.appendChild(btn);
  });
}

// ─── フィルタ適用 ────────────────────────────────────────────────────────────
function applyFilters() {
  let results = [...allRestaurants];

  results = results.map(r => {
    const reviews = allReviews[r.id] || [];
    const rated = reviews.filter(rv => rv.rating);
    const avgRating = rated.length > 0
      ? rated.reduce((s, rv) => s + Number(rv.rating), 0) / rated.length
      : 0;
    return { ...r, reviews, avgRating, reviewCount: reviews.length };
  });

  if (currentFilters.query) {
    const q = currentFilters.query.toLowerCase();
    results = results.filter(r => {
      const nameMatch = (r.name || '').toLowerCase().includes(q);
      const catMatch = (r.category || '').toLowerCase().includes(q);
      const descMatch = (r.description || '').toLowerCase().includes(q);
      const addressMatch = (r.address || '').toLowerCase().includes(q);
      const reviewMatch = r.reviews.some(rv =>
        (rv.order_details || '').toLowerCase().includes(q) ||
        (rv.impression || '').toLowerCase().includes(q)
      );
      return nameMatch || catMatch || descMatch || addressMatch || reviewMatch;
    });
  }

  if (currentFilters.category !== 'all') {
    results = results.filter(r => r.category === currentFilters.category);
  }

  if (currentFilters.minRating > 0) {
    results = results.filter(r => r.avgRating >= currentFilters.minRating);
  }

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

  grid.replaceChildren();

  if (results.length === 0) {
    const empty = el('div', 'empty-state');
    const p = el('p');
    p.append(document.createTextNode('No restaurants found.'), el('br'), document.createTextNode('Try a different search or filter.'));
    empty.append(el('div', 'empty-icon', '🔍'), p);
    grid.appendChild(empty);
    return;
  }

  results.forEach((r, i) => {
    const card = el('a', 'restaurant-card');
    card.href = `/foodlog/restaurant.php?id=${encodeURIComponent(r.id)}`;

    const gradient = GRADIENTS[i % GRADIENTS.length];
    const placeholder = el('div', 'card-image-placeholder', '🍽️');
    placeholder.style.background = gradient;

    if (r.image) {
      const img = el('img', 'card-image');
      img.src = `/foodlog/${r.image}`;
      img.alt = r.name || '';
      img.loading = 'lazy';
      placeholder.style.display = 'none';
      img.addEventListener('error', () => {
        img.style.display = 'none';
        placeholder.style.display = 'flex';
      });
      card.append(img, placeholder);
    } else {
      card.appendChild(placeholder);
    }

    const body = el('div', 'card-body');
    body.append(
      el('div', 'card-category', r.category || ''),
      el('div', 'card-name', r.name || ''),
      el('div', 'card-desc', r.description || '')
    );

    if (r.address) {
      const addr = el('div', '', `📍 ${r.address}`);
      addr.style.cssText = 'font-size:0.85rem;color:var(--muted);margin-top:0.4rem;';
      body.appendChild(addr);
    }

    if (r.avgRating > 0) {
      const starsText = '⭐'.repeat(Math.round(r.avgRating)) + ` ${r.avgRating.toFixed(1)}`;
      const starsEl = el('div', '', starsText);
      starsEl.style.cssText = 'font-size:0.82rem;margin-top:0.5rem;';
      body.appendChild(starsEl);
    }

    const footer = el('div', 'card-footer');
    const reviewCountEl = el('div', 'review-count');
    reviewCountEl.append(
      el('strong', '', String(r.reviewCount)),
      document.createTextNode(r.reviewCount === 1 ? ' review' : ' reviews')
    );
    footer.append(reviewCountEl, el('div', 'card-arrow', '→'));
    body.appendChild(footer);

    card.appendChild(body);
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

  const group = chip.closest('.filter-chips');
  if (!group) return;

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
