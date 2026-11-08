const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

// ─── URLパラメータ ────────────────────────────────────────────────────────────
const params = new URLSearchParams(window.location.search);
const restaurantId = params.get('id');
if (!restaurantId) window.location.href = '/foodlog/';

const today = new Date().toISOString().split('T')[0];
document.getElementById('rv-date').value = today;

// 現在のレストランデータをキャッシュ
let currentRestaurant = null;
let restaurantList = [];

// ページネーション用
const REVIEWS_PER_PAGE = 5;
let allReviews = [];
let currentPage = 1;

// ─── レストラン情報の読み込み ─────────────────────────────────────────────────
async function loadRestaurant() {
  const res = await fetch('/foodlog/api/restaurants.php');
  restaurantList = await res.json();
  const r = restaurantList.find(x => String(x.id) === String(restaurantId));
  if (!r) { document.getElementById('detail-name').textContent = 'Not found'; return; }
  currentRestaurant = r;
  renderRestaurantInfo(r);
}

function renderRestaurantInfo(r) {
  document.title = r.name + ' — FoodLog';
  document.getElementById('detail-category').textContent = r.category || '';
  document.getElementById('detail-name').textContent = r.name;
  document.getElementById('detail-desc').textContent = r.description || '';
  const detailAddress = document.getElementById('detail-address');
  if (r.address) {
    detailAddress.textContent = r.address;
    detailAddress.style.display = 'block';
  } else {
    detailAddress.style.display = 'none';
  }

  const idx = restaurantList.indexOf(r);
  const bannerImg = document.getElementById('banner-img');
  const placeholder = document.getElementById('banner-placeholder');

  if (r.image) {
    bannerImg.src = `/foodlog/${r.image}`;
    bannerImg.alt = r.name;
    bannerImg.style.display = 'block';
    placeholder.style.display = 'none';
    bannerImg.onerror = () => {
      bannerImg.style.display = 'none';
      showPlaceholder(idx);
    };
  } else {
    bannerImg.style.display = 'none';
    showPlaceholder(idx);
  }
}

function showPlaceholder(idx) {
  const el = document.getElementById('banner-placeholder');
  el.style.background = GRADIENTS[idx % GRADIENTS.length];
  el.style.display = 'flex';
  el.textContent = '🍽️';
}

// ─── レビューの読み込み・描画 ─────────────────────────────────────────────────
async function loadReviews() {
  const res = await fetch(`/foodlog/api/reviews.php?restaurant_id=${restaurantId}`);
  allReviews = await res.json();
  currentPage = 1;
  renderReviews();
}

function renderReviews() {
  const list = document.getElementById('reviews-list');
  const total = allReviews.length;
  const totalPages = Math.max(1, Math.ceil(total / REVIEWS_PER_PAGE));
  const start = (currentPage - 1) * REVIEWS_PER_PAGE;
  const pageReviews = allReviews.slice(start, start + REVIEWS_PER_PAGE);

  document.getElementById('review-count-badge').textContent = total + ' Reviews';

  if (!total) {
    list.innerHTML = `<div class="reviews-empty"><div class="empty-icon">📋</div><p>No orders logged yet.<br/>Use the form to add your first review!</p></div>`;
    return;
  }

  list.innerHTML = '';
  pageReviews.forEach(rv => {
    const stars = rv.rating ? '⭐'.repeat(Number(rv.rating)) : '';
    const formatted = new Date(rv.date + 'T00:00:00').toLocaleDateString('en-US', {
      year: 'numeric', month: 'long', day: 'numeric'
    });
    // HTMLエスケープ
    const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    // 改行を<br>に変換
    const nl2br = s => esc(s).replace(/\n/g, '<br>');

    const item = document.createElement('div');
    item.className = 'review-item';
    item.innerHTML = `
      <div class="review-item-header">
        <div class="review-date">📅 ${formatted}</div>
        <div style="display:flex;gap:6px;">
          <button class="review-edit" data-id="${rv.id}"
            data-date="${rv.date}"
            data-order="${esc(rv.order_details)}"
            data-impression="${esc(rv.impression)}"
            data-rating="${rv.rating || ''}"
            data-image="${rv.image || ''}"
            title="Edit">✏️</button>
          <button class="review-delete" data-id="${rv.id}" title="Delete">🗑</button>
        </div>
      </div>
      ${stars ? `<div style="font-size:0.9rem;margin-bottom:0.5rem;">${stars}</div>` : ''}
      ${rv.image ? `<img src="/foodlog/${rv.image}" alt="Review photo" style="max-width:100%;border-radius:8px;margin-bottom:0.5rem;display:block;" />` : ''}
      <div class="review-order">${nl2br(rv.order_details)}</div>
      <div class="review-impression">${nl2br(rv.impression)}</div>`;
    list.appendChild(item);
  });

  list.querySelectorAll('.review-delete').forEach(btn => {
    btn.addEventListener('click', () => deleteReview(btn.dataset.id));
  });
  list.querySelectorAll('.review-edit').forEach(btn => {
    btn.addEventListener('click', () => openEditReviewModal(btn.dataset));
  });

  // ページネーションUI
  if (totalPages > 1) {
    const nav = document.createElement('div');
    nav.className = 'pagination';
    nav.innerHTML = `
      <button class="pagination-btn" id="pg-prev" ${currentPage === 1 ? 'disabled' : ''}>← Prev</button>
      <span class="pagination-info">${currentPage} / ${totalPages}</span>
      <button class="pagination-btn" id="pg-next" ${currentPage === totalPages ? 'disabled' : ''}>Next →</button>`;
    list.appendChild(nav);
    document.getElementById('pg-prev').addEventListener('click', () => { currentPage--; renderReviews(); });
    document.getElementById('pg-next').addEventListener('click', () => { currentPage++; renderReviews(); });
  }
}

async function deleteReview(id) {
  await fetch(`/foodlog/api/reviews.php?id=${id}`, { method: 'DELETE' });
  loadReviews();
}

// ─── レビュー投稿フォーム ─────────────────────────────────────────────────────
document.getElementById('review-form').addEventListener('submit', async e => {
  e.preventDefault();
  const date       = document.getElementById('rv-date').value;
  const order      = document.getElementById('rv-order').value.trim();
  const impression = document.getElementById('rv-impression').value.trim();
  if (!date || !order || !impression) return;

  const btn = e.target.querySelector('button[type="submit"]');
  btn.textContent = '保存中…'; btn.disabled = true;

  const formData = new FormData(e.target);
  formData.append('restaurant_id', restaurantId);

  await fetch('/foodlog/api/reviews.php', {
    method: 'POST',
    body: formData,
  });

  document.getElementById('review-form').reset();
  document.getElementById('rv-date').value = today;
  document.getElementById('rv-image-preview').style.display = 'none';
  document.getElementById('rv-upload-placeholder').style.display = 'flex';
  btn.textContent = 'Save Review'; btn.disabled = false;

  loadReviews();
  if (window.innerWidth < 700) document.getElementById('reviews-list').scrollIntoView({ behavior: 'smooth' });
});

// ─── 画像アップロードエリア共通処理 ──────────────────────────────────────────
function setupUploadArea(areaId, placeholderId, previewId, inputId) {
  const area        = document.getElementById(areaId);
  const placeholder = document.getElementById(placeholderId);
  const preview     = document.getElementById(previewId);
  const input       = document.getElementById(inputId);

  area.addEventListener('click', () => input.click());
  area.addEventListener('dragover', e => { e.preventDefault(); area.style.borderColor = 'var(--accent)'; });
  area.addEventListener('dragleave', () => { area.style.borderColor = ''; });
  area.addEventListener('drop', e => {
    e.preventDefault(); area.style.borderColor = '';
    const file = e.dataTransfer.files[0];
    if (file) showImagePreview(file, placeholder, preview);
    const dt = new DataTransfer(); dt.items.add(file);
    input.files = dt.files;
  });
  input.addEventListener('change', () => {
    if (input.files[0]) showImagePreview(input.files[0], placeholder, preview);
  });
}

function showImagePreview(file, placeholder, preview) {
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);
}

// ─── レストラン削除（詳細ページ）────────────────────────────────────────────
document.getElementById('delete-restaurant-btn').addEventListener('click', async () => {
  const name = currentRestaurant ? currentRestaurant.name : 'このレストラン';
  if (!confirm(`「${name}」を削除しますか？\nすべてのレビューも削除されます。`)) return;
  const btn = document.getElementById('delete-restaurant-btn');
  btn.textContent = '削除中…'; btn.disabled = true;
  await fetch(`/foodlog/api/restaurants.php?id=${restaurantId}`, { method: 'DELETE' });
  window.location.href = '/foodlog/';
});

// ─── 編集モーダル（レストラン）────────────────────────────────────────────────
const editModal = document.getElementById('edit-modal');

document.getElementById('edit-info-btn').addEventListener('click', () => {
  if (!currentRestaurant) return;

  // 現在の値をフォームにセット
  document.getElementById('edit-name').value     = currentRestaurant.name || '';
  document.getElementById('edit-category').value = currentRestaurant.category || '';
  document.getElementById('edit-desc').value     = currentRestaurant.description || '';
  document.getElementById('edit-address').value  = currentRestaurant.address || '';

  // 現在の画像をプレビューに表示
  const preview     = document.getElementById('edit-image-preview');
  const placeholder = document.getElementById('edit-upload-placeholder');
  if (currentRestaurant.image) {
    preview.src = `/foodlog/${currentRestaurant.image}`;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
  } else {
    preview.style.display = 'none';
    placeholder.style.display = 'flex';
  }

  editModal.classList.add('open');
  document.getElementById('edit-name').focus();
});

document.getElementById('cancel-edit-btn').addEventListener('click', closeEditModal);
editModal.addEventListener('click', e => { if (e.target === editModal) closeEditModal(); });

function closeEditModal() {
  editModal.classList.remove('open');
  document.getElementById('edit-restaurant-form').reset();
  document.getElementById('edit-image-preview').style.display = 'none';
  document.getElementById('edit-upload-placeholder').style.display = 'flex';
}

document.getElementById('edit-restaurant-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  btn.textContent = '保存中…'; btn.disabled = true;

  const formData = new FormData(e.target);
  const res = await fetch(`/foodlog/api/restaurants.php?id=${restaurantId}`, {
    method: 'POST',
    body: formData,
  });
  const updated = await res.json();

  if (updated && updated.id) {
    currentRestaurant = updated;
    // restaurantListも更新
    const idx = restaurantList.findIndex(r => String(r.id) === String(restaurantId));
    if (idx !== -1) restaurantList[idx] = updated;
    renderRestaurantInfo(updated);
  }

  btn.textContent = 'Save Changes'; btn.disabled = false;
  closeEditModal();
});

// ─── レビュー編集モーダル ─────────────────────────────────────────────────────
const editReviewModal = document.getElementById('edit-review-modal');

function openEditReviewModal(data) {
  document.getElementById('edit-review-id').value         = data.id;
  document.getElementById('edit-rv-date').value           = data.date;
  document.getElementById('edit-rv-order').value          = data.order;
  document.getElementById('edit-rv-impression').value     = data.impression;
  document.getElementById('edit-rv-rating').value         = data.rating || '';

  const preview = document.getElementById('edit-rv-image-preview');
  const placeholder = document.getElementById('edit-rv-upload-placeholder');
  if (data.image) {
    preview.src = `/foodlog/${data.image}`;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
  } else {
    preview.style.display = 'none';
    placeholder.style.display = 'flex';
  }

  editReviewModal.classList.add('open');
  document.getElementById('edit-rv-order').focus();
}

function closeEditReviewModal() {
  editReviewModal.classList.remove('open');
  document.getElementById('edit-review-form').reset();
}

document.getElementById('cancel-edit-review-btn').addEventListener('click', closeEditReviewModal);
editReviewModal.addEventListener('click', e => { if (e.target === editReviewModal) closeEditReviewModal(); });

document.getElementById('edit-review-form').addEventListener('submit', async e => {
  e.preventDefault();
  const id         = document.getElementById('edit-review-id').value;
  const date       = document.getElementById('edit-rv-date').value;
  const order      = document.getElementById('edit-rv-order').value.trim();
  const impression = document.getElementById('edit-rv-impression').value.trim();
  if (!date || !order || !impression) return;

  const btn = e.target.querySelector('button[type="submit"]');
  btn.textContent = '保存中…'; btn.disabled = true;

  const formData = new FormData(e.target);
  await fetch(`/foodlog/api/reviews.php?id=${id}`, {
    method: 'POST',
    body: formData,
  });

  btn.textContent = 'Save Changes'; btn.disabled = false;
  closeEditReviewModal();
  loadReviews();
});

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
  update(); // 初期表示
}

// ─── 未来日を選べないようにする ──────────────────────────────────────────────
function setupDateMax(inputId) {
  const input = document.getElementById(inputId);
  if (input) input.setAttribute('max', today);
}

// ─── 初期化 ──────────────────────────────────────────────────────────────────
function init() {
  setupUploadArea('edit-upload-area', 'edit-upload-placeholder', 'edit-image-preview', 'edit-image');
  setupUploadArea('rv-upload-area', 'rv-upload-placeholder', 'rv-image-preview', 'rv-image');
  setupUploadArea('edit-rv-upload-area', 'edit-rv-upload-placeholder', 'edit-rv-image-preview', 'edit-rv-image');

  // 未来日禁止
  setupDateMax('rv-date');
  setupDateMax('edit-rv-date');

  // Log an Order フォーム
  setupCharCounter('rv-order', 'order-count');
  setupCharCounter('rv-impression', 'impression-count');

  // Edit Restaurant Info モーダル
  setupCharCounter('edit-name', 'edit-name-count');
  setupCharCounter('edit-category', 'edit-category-count');
  setupCharCounter('edit-desc', 'edit-desc-count');
  setupCharCounter('edit-address', 'edit-address-count');

  // Edit Review モーダル
  setupCharCounter('edit-rv-order', 'edit-order-count');
  setupCharCounter('edit-rv-impression', 'edit-impression-count');

  loadRestaurant();
  loadReviews();
}

init();
