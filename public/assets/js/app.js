const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

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
  return res.json();
}

function renderGrid(restaurants) {
  const grid = document.getElementById('restaurant-grid');
  grid.replaceChildren();

  if (!restaurants.length) {
    const empty = el('div', 'empty-state');
    empty.append(el('div', 'empty-icon', '🍽️'), el('p', '', 'No restaurants yet. Add one!'));
    grid.appendChild(empty);
    return;
  }

  let totalReviews = 0;
  const isAdmin = Boolean(window.AUTH && window.AUTH.isAdmin);

  restaurants.forEach((r, idx) => {
    const count = parseInt(r.review_count) || 0;
    totalReviews += count;

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
    reviewCountEl.append(strong, document.createTextNode(count === 1 ? ' review' : ' reviews'));
    footer.append(reviewCountEl, el('div', 'card-arrow', '→'));
    body.appendChild(footer);

    card.appendChild(body);
    grid.appendChild(card);
  });

  document.getElementById('stat-restaurants').textContent = restaurants.length;
  document.getElementById('stat-reviews').textContent = totalReviews;
  const totalBadge = document.getElementById('total-reviews-badge');
  if (totalBadge) totalBadge.textContent = totalReviews + ' Reviews';
  document.getElementById('restaurant-count').textContent = '(' + restaurants.length + ')';

  // 最新注文日を表示
  const dates = restaurants.map(r => r.latest_date).filter(Boolean).sort().reverse();
  if (dates.length) {
    const d = new Date(dates[0] + 'T00:00:00');
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    document.getElementById('stat-latest').textContent = `${y}/${m}/${day}`;
  } else {
    document.getElementById('stat-latest').textContent = '—';
  }
}

// ─── Upload area setup ────────────────────────────────────────────────────────
function setupUploadArea(areaId, placeholderId, previewId, inputId) {
  const area = document.getElementById(areaId);
  if (!area) return;
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
  input.addEventListener('input', update);
  update();
}

async function init() {
  setupUploadArea('add-upload-area', 'add-upload-placeholder', 'add-image-preview', 'r-image');

  setupCharCounter('r-name', 'r-name-count');
  setupCharCounter('r-category', 'r-category-count');
  setupCharCounter('r-desc', 'r-desc-count');
  setupCharCounter('r-address', 'r-address-count');

  const restaurants = await loadRestaurants();
  renderGrid(restaurants);
}

init();
