const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

let allRestaurants = [];
let allReviewsMap = {}; // { restaurantId: [reviews] }
const homeFilters = {
  query: '',
  category: 'all',
  minRating: 0,
  sort: 'default',
};

function csrfHeaders() {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  return { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' };
}

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (text !== undefined && text !== null) node.textContent = text;
  return node;
}

async function loadRestaurants() {
  const res = await fetch('/foodlog/api/restaurants.php');
  allRestaurants = await res.json();

  await Promise.all(allRestaurants.map(async r => {
    try {
      const rv = await fetch(`/foodlog/api/reviews.php?restaurant_id=${encodeURIComponent(r.id)}`);
      allReviewsMap[r.id] = await rv.json();
    } catch {
      allReviewsMap[r.id] = [];
    }
  }));

  return allRestaurants;
}

function updateHeroStats(restaurants) {
  let totalReviews = 0;
  restaurants.forEach(r => { totalReviews += parseInt(r.review_count) || 0; });

  const statRest = document.getElementById('stat-restaurants');
  const statRev = document.getElementById('stat-reviews');
  const totalBadge = document.getElementById('total-reviews-badge');
  if (statRest) statRest.textContent = restaurants.length;
  if (statRev) statRev.textContent = totalReviews;
  if (totalBadge) totalBadge.textContent = totalReviews + ' Reviews';

  const dates = restaurants.map(r => r.latest_date).filter(Boolean).sort().reverse();
  const statLatest = document.getElementById('stat-latest');
  if (statLatest) {
    if (dates.length) {
      const d = new Date(dates[0] + 'T00:00:00');
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      statLatest.textContent = `${y}/${m}/${day}`;
    } else {
      statLatest.textContent = '—';
    }
  }
}

function buildCategoryChips(restaurants) {
  const container = document.getElementById('category-chips');
  if (!container) return;
  container.replaceChildren();

  const allBtn = el('button', `filter-chip ${homeFilters.category === 'all' ? 'active' : ''}`.trim(), 'All');
  allBtn.type = 'button';
  allBtn.dataset.category = 'all';
  container.appendChild(allBtn);

  const cats = new Set();
  restaurants.forEach(r => { if (r.category) cats.add(r.category); });
  [...cats].sort().forEach(cat => {
    const btn = el('button', `filter-chip ${homeFilters.category === cat ? 'active' : ''}`.trim(), cat);
    btn.type = 'button';
    btn.dataset.category = cat;
    container.appendChild(btn);
  });
}

function getFilteredRestaurants() {
  let results = allRestaurants.map(r => {
    const reviews = allReviewsMap[r.id] || [];
    const rated = reviews.filter(rv => Number(rv.rating) > 0);
    const avgRating = rated.length > 0
      ? rated.reduce((s, rv) => s + Number(rv.rating), 0) / rated.length
      : (Number(r.avg_rating) || 0);
    const reviewCount = parseInt(r.review_count) || reviews.length || 0;
    return { ...r, reviews, avgRating, reviewCount };
  });

  if (homeFilters.query) {
    const q = homeFilters.query.toLowerCase();
    results = results.filter(r => {
      const nameMatch = (r.name || '').toLowerCase().includes(q);
      const catMatch = (r.category || '').toLowerCase().includes(q);
      const descMatch = (r.description || '').toLowerCase().includes(q);
      const addrMatch = (r.address || '').toLowerCase().includes(q);
      const reviewMatch = r.reviews.some(rv =>
        (rv.order_details || '').toLowerCase().includes(q) ||
        (rv.impression || '').toLowerCase().includes(q)
      );
      return nameMatch || catMatch || descMatch || addrMatch || reviewMatch;
    });
  }

  if (homeFilters.category !== 'all') {
    results = results.filter(r => r.category === homeFilters.category);
  }

  if (homeFilters.minRating > 0) {
    results = results.filter(r => r.avgRating >= homeFilters.minRating);
  }

  switch (homeFilters.sort) {
    case 'reviews':
      results.sort((a, b) => b.reviewCount - a.reviewCount);
      break;
    case 'rating':
      results.sort((a, b) => b.avgRating - a.avgRating);
      break;
    case 'newest':
      results.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    case 'default':
    default:
      break;
  }

  return results;
}

function resetHomeFilters() {
  homeFilters.query = '';
  homeFilters.category = 'all';
  homeFilters.minRating = 0;
  homeFilters.sort = 'default';

  const searchInput = document.getElementById('search-input');
  if (searchInput) searchInput.value = '';
  document.querySelectorAll('#category-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.category === 'all'));
  document.querySelectorAll('#rating-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.rating === '0'));
  document.querySelectorAll('#sort-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.sort === 'default'));

  renderFilteredGrid();
}

function renderFilteredGrid() {
  const restaurants = getFilteredRestaurants();
  renderGrid(restaurants);
}

function renderGrid(restaurants) {
  const grid = document.getElementById('restaurant-grid');
  grid.replaceChildren();

  const countEl = document.getElementById('restaurant-count');
  const isFiltered = homeFilters.query || homeFilters.category !== 'all' || homeFilters.minRating > 0;
  if (countEl) {
    countEl.textContent = isFiltered
      ? `(${restaurants.length} / ${allRestaurants.length})`
      : `(${restaurants.length})`;
  }

  if (!allRestaurants.length) {
    const empty = el('div', 'empty-state');
    empty.append(el('div', 'empty-icon', '🍽️'), el('p', '', 'No restaurants yet. Add one!'));
    grid.appendChild(empty);
    return;
  }

  if (!restaurants.length) {
    const empty = el('div', 'empty-state');
    const p = el('p');
    p.append(document.createTextNode('No restaurants found.'), el('br'), document.createTextNode('Try a different search or filter.'));
    const resetBtn = el('button', 'filter-chip', 'Reset Filters');
    resetBtn.type = 'button';
    resetBtn.style.marginTop = '0.9rem';
    resetBtn.addEventListener('click', resetHomeFilters);
    empty.append(el('div', 'empty-icon', '🔍'), p, resetBtn);
    grid.appendChild(empty);
    return;
  }

  const isAdmin = Boolean(window.AUTH && window.AUTH.isAdmin);

  restaurants.forEach((r, idx) => {
    const count = parseInt(r.reviewCount ?? r.review_count) || 0;
    const avgText = r.avgRating > 0 ? `★ ${r.avgRating.toFixed(1)}` : 'No rating';

    const card = el('a', 'restaurant-card');
    card.href = `/foodlog/restaurant.php?id=${encodeURIComponent(r.id)}`;

    const gradient = GRADIENTS[idx % GRADIENTS.length];
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

    if (isAdmin) {
      const delBtn = el('button', 'card-delete-btn', '🗑');
      delBtn.type = 'button';
      delBtn.title = 'Delete';
      delBtn.addEventListener('click', async e => {
        e.preventDefault();
        e.stopPropagation();
        if (!confirm(`「${r.name}」を削除しますか？\nすべてのレビューも削除されます。`)) return;
        delBtn.textContent = '…';
        delBtn.disabled = true;
        const res = await fetch(`/foodlog/api/restaurants.php?id=${encodeURIComponent(r.id)}`, {
          method: 'DELETE',
          headers: csrfHeaders(),
        });
        if (!res.ok) {
          alert('削除権限がありません。');
          delBtn.textContent = '🗑';
          delBtn.disabled = false;
          return;
        }
        init();
      });
      card.appendChild(delBtn);
    }

    const body = el('div', 'card-body');
    body.append(
      el('div', 'card-category', r.category || ''),
      el('div', 'card-name', r.name || ''),
      el('div', 'card-desc', r.description || '')
    );

    if (r.address) {
      const addr = el('div', 'card-address', `📍 ${r.address}`);
      addr.style.cssText = 'font-size:0.85rem;color:var(--muted);margin-top:0.4rem;';
      body.appendChild(addr);
    }

    const footer = el('div', 'card-footer');
    const reviewCountEl = el('div', 'review-count');
    const strong = el('strong', '', String(count));
    reviewCountEl.append(strong, document.createTextNode((count === 1 ? ' review' : ' reviews') + `  ·  ${avgText}`));
    footer.append(reviewCountEl, el('div', 'card-arrow', '→'));
    body.appendChild(footer);

    card.appendChild(body);
    grid.appendChild(card);
  });
}

// ─── 検索・フィルターイベント登録 ──────────────────────────────────────────────
function setupHomeFilters() {
  const searchInput = document.getElementById('search-input');
  if (searchInput && !searchInput.dataset.bound) {
    searchInput.addEventListener('input', e => {
      homeFilters.query = e.target.value.trim();
      renderFilteredGrid();
    });
    searchInput.dataset.bound = '1';
  }

  const catChips = document.getElementById('category-chips');
  if (catChips && !catChips.dataset.bound) {
    catChips.addEventListener('click', e => {
      const btn = e.target.closest('.filter-chip');
      if (!btn) return;
      catChips.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      homeFilters.category = btn.dataset.category;
      renderFilteredGrid();
    });
    catChips.dataset.bound = '1';
  }

  const ratingChips = document.getElementById('rating-chips');
  if (ratingChips && !ratingChips.dataset.bound) {
    ratingChips.addEventListener('click', e => {
      const btn = e.target.closest('.filter-chip');
      if (!btn) return;
      ratingChips.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      homeFilters.minRating = Number(btn.dataset.rating);
      renderFilteredGrid();
    });
    ratingChips.dataset.bound = '1';
  }

  const sortChips = document.getElementById('sort-chips');
  if (sortChips && !sortChips.dataset.bound) {
    sortChips.addEventListener('click', e => {
      const btn = e.target.closest('.filter-chip');
      if (!btn) return;
      sortChips.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      homeFilters.sort = btn.dataset.sort;
      renderFilteredGrid();
    });
    sortChips.dataset.bound = '1';
  }
}

// ─── Upload area setup ────────────────────────────────────────────────────────
function setupUploadArea(areaId, placeholderId, previewId, inputId) {
  const area = document.getElementById(areaId);
  if (!area || area.dataset.bound) return;
  area.dataset.bound = '1';
  const placeholder = document.getElementById(placeholderId);
  const preview = document.getElementById(previewId);
  const input = document.getElementById(inputId);

  area.addEventListener('click', () => input.click());
  area.addEventListener('dragover', e => { e.preventDefault(); area.style.borderColor = 'var(--accent)'; });
  area.addEventListener('dragleave', () => { area.style.borderColor = ''; });
  area.addEventListener('drop', e => {
    e.preventDefault(); area.style.borderColor = '';
    const file = e.dataTransfer.files[0];
    if (file) showPreview(file, placeholder, preview);
    const dt = new DataTransfer(); dt.items.add(file);
    input.files = dt.files;
  });
  input.addEventListener('change', () => {
    if (input.files[0]) showPreview(input.files[0], placeholder, preview);
  });
}

function showPreview(file, placeholder, preview) {
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);
}

// ─── Modal ────────────────────────────────────────────────────────────────────
const modal = document.getElementById('add-modal');
const addBtn = document.getElementById('add-restaurant-btn');
const cancelBtn = document.getElementById('cancel-modal-btn');
const addForm = document.getElementById('add-restaurant-form');

if (modal && addBtn) {
  addBtn.addEventListener('click', () => {
    modal.classList.add('open');
    document.getElementById('r-name').focus();
  });
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
}

function closeModal() {
  if (!modal) return;
  modal.classList.remove('open');
  addForm.reset();
  document.getElementById('add-image-preview').style.display = 'none';
  document.getElementById('add-upload-placeholder').style.display = 'flex';
}

if (addForm) {
  addForm.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('/foodlog/api/restaurants.php', {
      method: 'POST',
      headers: csrfHeaders(),
      body: formData,
    });
    if (!res.ok) {
      alert('追加権限がありません。');
      return;
    }
    await res.json();
    closeModal();
    init();
  });
}

// ─── 文字数カウンター汎用 ─────────────────────────────────────────────────────
function setupCharCounter(inputId, counterId) {
  const input = document.getElementById(inputId);
  const counter = document.getElementById(counterId);
  if (!input || !counter) return;
  const max = parseInt(input.getAttribute('maxlength')) || 999;
  const update = () => {
    const len = input.value.length;
    counter.textContent = `${len} / ${max}`;
    const ratio = len / max;
    counter.className = 'char-count' +
      (ratio >= 1 ? ' at-limit' : ratio >= 0.8 ? ' near-limit' : '');
  };
  if (!input.dataset.counterBound) {
    input.addEventListener('input', update);
    input.dataset.counterBound = '1';
  }
  update();
}

async function init() {
  setupUploadArea('add-upload-area', 'add-upload-placeholder', 'add-image-preview', 'r-image');

  setupCharCounter('r-name', 'r-name-count');
  setupCharCounter('r-category', 'r-category-count');
  setupCharCounter('r-desc', 'r-desc-count');
  setupCharCounter('r-address', 'r-address-count');

  setupHomeFilters();

  const restaurants = await loadRestaurants();
  updateHeroStats(restaurants);
  buildCategoryChips(restaurants);
  renderFilteredGrid();
}

init();
