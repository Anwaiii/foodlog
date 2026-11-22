const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

const RESTAURANTS_PER_PAGE = 10;
let allRestaurants = [];
let homeCurrentPage = 1;
const homeFilters = {
  query: '',
  category: 'all',
  area: 'all',
  price: 'all',
  minRating: 0,
  sort: 'default',
  favOnly: false,
};

function notify(msg, type = 'info') {
  if (typeof window.showToast === 'function') {
    window.showToast(msg, type);
  }
}

if (typeof window.t !== 'function') {
  window.t = k => k;
}

function isEn() {
  return window.LOCALE === 'en';
}

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
  if (totalBadge) totalBadge.textContent = isEn() ? `${totalReviews} Reviews` : `${totalReviews}件`;

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

  const allBtn = el('button', `filter-chip ${homeFilters.category === 'all' ? 'active' : ''}`.trim(), t('すべて'));
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

function buildAreaChips(restaurants) {
  const container = document.getElementById('area-chips');
  if (!container) return;
  container.replaceChildren();

  const allBtn = el('button', `filter-chip ${homeFilters.area === 'all' ? 'active' : ''}`.trim(), t('すべて'));
  allBtn.type = 'button';
  allBtn.dataset.area = 'all';
  container.appendChild(allBtn);

  const areas = new Set();
  restaurants.forEach(r => { if (r.area) areas.add(r.area); });
  [...areas].sort((a, b) => a.localeCompare(b, 'ja')).forEach(area => {
    const btn = el('button', `filter-chip ${homeFilters.area === area ? 'active' : ''}`.trim(), area);
    btn.type = 'button';
    btn.dataset.area = area;
    container.appendChild(btn);
  });
}

function getFilteredRestaurants() {
  let results = allRestaurants.map(r => {
    const avgRating = Number(r.avg_rating) || 0;
    const reviewCount = parseInt(r.review_count) || 0;
    return { ...r, avgRating, reviewCount };
  });

  if (homeFilters.query) {
    const q = homeFilters.query.toLowerCase();
    results = results.filter(r => {
      const hay = [
        r.name || '',
        r.category || '',
        r.area || '',
        r.price_range || '',
        r.description || '',
        r.address || '',
        r.review_search_text || '',
      ].join(' ').toLowerCase();
      return hay.includes(q);
    });
  }

  if (homeFilters.category !== 'all') {
    results = results.filter(r => r.category === homeFilters.category);
  }

  if (homeFilters.area !== 'all') {
    results = results.filter(r => r.area === homeFilters.area);
  }

  if (homeFilters.price !== 'all') {
    results = results.filter(r => r.price_range === homeFilters.price);
  }

  if (homeFilters.minRating > 0) {
    results = results.filter(r => r.avgRating >= homeFilters.minRating);
  }

  if (homeFilters.favOnly) {
    results = results.filter(r => Boolean(r.is_favorited));
  }

  switch (homeFilters.sort) {
    case 'reviews':
      results.sort((a, b) => b.reviewCount - a.reviewCount);
      break;
    case 'rating':
      results.sort((a, b) => (b.avgRating - a.avgRating) || (b.reviewCount - a.reviewCount));
      break;
    case 'favorites':
      results.sort((a, b) => ((Number(b.favorites_count) || 0) - (Number(a.favorites_count) || 0)) || (b.avgRating - a.avgRating));
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
  homeFilters.area = 'all';
  homeFilters.price = 'all';
  homeFilters.minRating = 0;
  homeFilters.sort = 'default';
  homeFilters.favOnly = false;
  homeCurrentPage = 1;

  const searchInput = document.getElementById('search-input');
  if (searchInput) searchInput.value = '';
  document.querySelectorAll('#category-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.category === 'all'));
  document.querySelectorAll('#area-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.area === 'all'));
  document.querySelectorAll('#price-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.price === 'all'));
  document.querySelectorAll('#rating-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.rating === '0'));
  document.querySelectorAll('#sort-chips .filter-chip').forEach(b => b.classList.toggle('active', b.dataset.sort === 'default'));
  const favChip = document.getElementById('fav-only-chip');
  if (favChip) { favChip.classList.remove('active'); favChip.setAttribute('aria-pressed', 'false'); }

  renderFilteredGrid();
}

function renderFilteredGrid() {
  const restaurants = getFilteredRestaurants();
  renderGrid(restaurants);
}

function renderGrid(restaurants) {
  const grid = document.getElementById('restaurant-grid');
  const pgContainer = document.getElementById('home-pagination');
  grid.replaceChildren();
  if (pgContainer) pgContainer.replaceChildren();

  const countEl = document.getElementById('restaurant-count');
  const isFiltered = homeFilters.query || homeFilters.category !== 'all' || homeFilters.area !== 'all' || homeFilters.price !== 'all' || homeFilters.minRating > 0 || homeFilters.favOnly;
  if (countEl) {
    if (isEn()) {
      countEl.textContent = isFiltered
        ? `(${restaurants.length} / ${allRestaurants.length})`
        : `(${restaurants.length})`;
    } else {
      countEl.textContent = isFiltered
        ? `(${restaurants.length} / ${allRestaurants.length}件)`
        : `(${restaurants.length}件)`;
    }
  }

  if (!allRestaurants.length) {
    const empty = el('div', 'empty-state');
    empty.append(el('div', 'empty-icon', '🍽️'), el('p', '', t('まだレストランが登録されていません。')));
    grid.appendChild(empty);
    return;
  }

  if (!restaurants.length) {
    const empty = el('div', 'empty-state');
    const p = el('p');
    p.append(document.createTextNode(t('条件に一致するレストランがありません。')), el('br'), document.createTextNode(t('別のキーワードや条件をお試しください。')));
    const resetBtn = el('button', 'filter-chip', t('条件をリセット'));
    resetBtn.type = 'button';
    resetBtn.style.marginTop = '0.9rem';
    resetBtn.addEventListener('click', resetHomeFilters);
    empty.append(el('div', 'empty-icon', '🔍'), p, resetBtn);
    grid.appendChild(empty);
    return;
  }

  const totalPages = Math.max(1, Math.ceil(restaurants.length / RESTAURANTS_PER_PAGE));
  if (homeCurrentPage > totalPages) homeCurrentPage = totalPages;
  if (homeCurrentPage < 1) homeCurrentPage = 1;
  const start = (homeCurrentPage - 1) * RESTAURANTS_PER_PAGE;
  const pageRestaurants = restaurants.slice(start, start + RESTAURANTS_PER_PAGE);

  const loggedIn = Boolean(window.AUTH && window.AUTH.loggedIn);
  const isAdmin = Boolean(window.AUTH && window.AUTH.isAdmin);

  pageRestaurants.forEach((r, idx) => {
    const count = parseInt(r.reviewCount ?? r.review_count) || 0;
    const avgText = r.avgRating > 0 ? `★ ${r.avgRating.toFixed(1)}` : t('評価なし');

    const card = el('a', 'restaurant-card');
    card.href = `/foodlog/restaurant.php?id=${encodeURIComponent(r.id)}`;

    const favBtn = el('button', `card-fav-btn ${r.is_favorited ? 'favorited' : ''}`.trim(), r.is_favorited ? '❤️' : '🤍');
    favBtn.type = 'button';
    favBtn.title = loggedIn ? (r.is_favorited ? t('お気に入り解除') : t('お気に入り登録')) : t('ログインしてお気に入り登録');
    favBtn.setAttribute('aria-label', favBtn.title);
    favBtn.addEventListener('click', async e => {
      e.preventDefault();
      e.stopPropagation();
      if (!loggedIn) {
        if (confirm(t('お気に入り登録にはログインが必要です。ログイン画面へ移動しますか？'))) {
          location.href = '/foodlog/login';
        }
        return;
      }
      favBtn.disabled = true;
      const res = await fetch('/foodlog/api/favorites.php', {
        method: 'POST',
        headers: { ...csrfHeaders(), 'Content-Type': 'application/json' },
        body: JSON.stringify({ restaurant_id: r.id }),
      });
      favBtn.disabled = false;
      if (!res.ok) return;
      const data = await res.json();
      const target = allRestaurants.find(x => String(x.id) === String(r.id));
      if (target) {
        target.is_favorited = data.favorited;
      }
      notify(data.favorited ? t('お気に入りに追加しました。') : t('お気に入りから解除しました。'), data.favorited ? 'success' : 'info');
      renderFilteredGrid();
    });
    card.appendChild(favBtn);

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
      delBtn.title = t('削除');
      delBtn.addEventListener('click', async e => {
        e.preventDefault();
        e.stopPropagation();
        const delMsg = isEn()
          ? `Delete "${r.name}"?\nAll reviews for this restaurant will also be deleted.`
          : `「${r.name}」を削除しますか？\nすべてのレビューも削除されます。`;
        if (!confirm(delMsg)) return;
        delBtn.textContent = '…';
        delBtn.disabled = true;
        const res = await fetch(`/foodlog/api/restaurants.php?id=${encodeURIComponent(r.id)}`, {
          method: 'DELETE',
          headers: csrfHeaders(),
        });
        if (!res.ok) {
          notify(t('削除権限がありません。'), 'error');
          delBtn.textContent = '🗑';
          delBtn.disabled = false;
          return;
        }
        notify(t('レストランを削除しました。'), 'info');
        init();
      });
      card.appendChild(delBtn);
    }

    const body = el('div', 'card-body');
    const nameEl = el('div', 'card-name', r.name || '');
    nameEl.title = r.name || '';
    const descEl = el('div', 'card-desc', r.description || '');
    descEl.title = r.description || '';
    body.append(
      el('div', 'card-category', r.category || ''),
      nameEl
    );

    if (r.area || r.price_range) {
      const tags = el('div', 'card-meta-tags');
      if (r.area) tags.appendChild(el('span', 'meta-tag', `📍 ${r.area}`));
      if (r.price_range) tags.appendChild(el('span', 'meta-tag', `💴 ${r.price_range}`));
      body.appendChild(tags);
    }

    body.appendChild(descEl);

    const addr = el('div', 'card-address', r.address ? `🗺️ ${r.address}` : '');
    if (r.address) addr.title = r.address;
    body.appendChild(addr);

    const footer = el('div', 'card-footer');
    const reviewCountEl = el('div', 'review-count');
    const strong = el('strong', '', String(count));
    const reviewUnit = isEn()
      ? ` ${count === 1 ? 'review' : 'reviews'}  ·  ${avgText}`
      : `件のレビュー  ·  ${avgText}`;
    reviewCountEl.append(strong, document.createTextNode(reviewUnit));
    footer.append(reviewCountEl, el('div', 'card-arrow', '→'));
    body.appendChild(footer);

    card.appendChild(body);
    grid.appendChild(card);
  });

  if (pgContainer && typeof window.renderPagination === 'function') {
    window.renderPagination(pgContainer, {
      currentPage: homeCurrentPage,
      totalPages,
      onPageChange: p => {
        homeCurrentPage = p;
        renderFilteredGrid();
      },
    });
  }
}

// ─── 検索・フィルターイベント登録 ──────────────────────────────────────────────
function setupHomeFilters() {
  const searchInput = document.getElementById('search-input');
  if (searchInput && !searchInput.dataset.bound) {
    searchInput.addEventListener('input', e => {
      homeFilters.query = e.target.value.trim();
      homeCurrentPage = 1;
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
      homeCurrentPage = 1;
      renderFilteredGrid();
    });
    catChips.dataset.bound = '1';
  }

  const areaChips = document.getElementById('area-chips');
  if (areaChips && !areaChips.dataset.bound) {
    areaChips.addEventListener('click', e => {
      const btn = e.target.closest('.filter-chip');
      if (!btn) return;
      areaChips.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      homeFilters.area = btn.dataset.area;
      homeCurrentPage = 1;
      renderFilteredGrid();
    });
    areaChips.dataset.bound = '1';
  }

  const priceChips = document.getElementById('price-chips');
  if (priceChips && !priceChips.dataset.bound) {
    priceChips.addEventListener('click', e => {
      const btn = e.target.closest('.filter-chip');
      if (!btn) return;
      priceChips.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      homeFilters.price = btn.dataset.price;
      homeCurrentPage = 1;
      renderFilteredGrid();
    });
    priceChips.dataset.bound = '1';
  }

  const ratingChips = document.getElementById('rating-chips');
  if (ratingChips && !ratingChips.dataset.bound) {
    ratingChips.addEventListener('click', e => {
      const btn = e.target.closest('.filter-chip');
      if (!btn) return;
      ratingChips.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      homeFilters.minRating = Number(btn.dataset.rating);
      homeCurrentPage = 1;
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
      homeCurrentPage = 1;
      renderFilteredGrid();
    });
    sortChips.dataset.bound = '1';
  }

  const favChip = document.getElementById('fav-only-chip');
  if (favChip && !favChip.dataset.bound) {
    favChip.addEventListener('click', () => {
      homeFilters.favOnly = !homeFilters.favOnly;
      favChip.classList.toggle('active', homeFilters.favOnly);
      favChip.setAttribute('aria-pressed', String(homeFilters.favOnly));
      homeCurrentPage = 1;
      renderFilteredGrid();
    });
    favChip.dataset.bound = '1';
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
      notify(t('追加権限がありません。'), 'error');
      return;
    }
    await res.json();
    closeModal();
    notify(t('レストランを登録しました。'), 'success');
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
  buildAreaChips(restaurants);
  renderFilteredGrid();
}

init();
