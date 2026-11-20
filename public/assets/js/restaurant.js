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
const rvDateInput = document.getElementById('rv-date');
if (rvDateInput) rvDateInput.value = today;

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
    list.innerHTML = `<div class="reviews-empty"><div class="empty-icon">📋</div><p>No reviews yet.</p></div>`;
    return;
  }

  const loggedIn = Boolean(window.AUTH && window.AUTH.loggedIn);
  const isAdmin  = Boolean(window.AUTH && window.AUTH.isAdmin);
  const userId   = window.AUTH ? window.AUTH.userId : null;

  list.innerHTML = '';
  pageReviews.forEach(rv => {
    const stars = rv.rating ? '⭐'.repeat(Number(rv.rating)) : '';
    const formatted = new Date(rv.date + 'T00:00:00').toLocaleDateString('en-US', {
      year: 'numeric', month: 'long', day: 'numeric'
    });
    const esc = s => String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const nl2br = s => esc(s).replace(/\n/g, '<br>');

    const isOwner = loggedIn && userId !== null && rv.member_id !== null && Number(rv.member_id) === Number(userId);
    const canEdit = isOwner;
    const canDelete = isOwner || isAdmin;

    const editBtnHtml = canEdit ? `
      <button class="review-edit" data-id="${rv.id}"
        data-date="${rv.date}"
        data-order="${esc(rv.order_details)}"
        data-impression="${esc(rv.impression)}"
        data-rating="${rv.rating || ''}"
        data-image="${rv.image || ''}"
        title="Edit">✏️</button>` : '';
    const deleteBtnHtml = canDelete ? `
      <button class="review-delete" data-id="${rv.id}" title="Delete">🗑</button>` : '';

    const authorHtml = rv.member_nickname
      ? `<span class="review-author">👤 ${esc(rv.member_nickname)}</span>`
      : '';

    const item = document.createElement('div');
    item.className = 'review-item';
    item.innerHTML = `
      <div class="review-item-header">
        <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
          <div class="review-date">📅 ${formatted}</div>
          ${authorHtml}
        </div>
        <div style="display:flex;gap:6px;">
          ${editBtnHtml}
          ${deleteBtnHtml}
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
  const res = await fetch(`/foodlog/api/reviews.php?id=${id}`, { method: 'DELETE' });
  if (!res.ok) {
    alert('削除権限がありません。');
    return;
  }
  loadReviews();
}

// ─── レビュー投稿モーダル ─────────────────────────────────────────────────────
const addReviewModal = document.getElementById('add-review-modal');
const addReviewBtn   = document.getElementById('add-review-btn');
const cancelAddReviewBtn = document.getElementById('cancel-add-review-btn');
const reviewForm     = document.getElementById('review-form');

function closeAddReviewModal() {
  if (!addReviewModal) return;
  addReviewModal.classList.remove('open');
  reviewForm.reset();
  if (rvDateInput) rvDateInput.value = today;
  const preview = document.getElementById('rv-image-preview');
  const placeholder = document.getElementById('rv-upload-placeholder');
  if (preview) preview.style.display = 'none';
  if (placeholder) placeholder.style.display = 'flex';
  setupCharCounter('rv-order', 'order-count');
  setupCharCounter('rv-impression', 'impression-count');
}

if (addReviewModal && addReviewBtn) {
  addReviewBtn.addEventListener('click', () => {
    if (rvDateInput && !rvDateInput.value) rvDateInput.value = today;
    addReviewModal.classList.add('open');
    document.getElementById('rv-order').focus();
  });
  if (cancelAddReviewBtn) cancelAddReviewBtn.addEventListener('click', closeAddReviewModal);
  addReviewModal.addEventListener('click', e => { if (e.target === addReviewModal) closeAddReviewModal(); });
}

if (reviewForm) {
  reviewForm.addEventListener('submit', async e => {
    e.preventDefault();
    const date       = document.getElementById('rv-date').value;
    const order      = document.getElementById('rv-order').value.trim();
    const impression = document.getElementById('rv-impression').value.trim();
    if (!date || !order || !impression) return;

    const btn = e.target.querySelector('button[type="submit"]');
    btn.textContent = '保存中…'; btn.disabled = true;

    const formData = new FormData(e.target);
    formData.append('restaurant_id', restaurantId);

    const res = await fetch('/foodlog/api/reviews.php', {
      method: 'POST',
      body: formData,
    });

    btn.textContent = 'Save Review'; btn.disabled = false;

    if (!res.ok) {
      alert('レビューの保存に失敗しました。ログイン状態を確認してください。');
      return;
    }

    closeAddReviewModal();
    loadReviews();
  });
}

// ─── 画像アップロードエリア共通処理 ──────────────────────────────────────────
function setupUploadArea(areaId, placeholderId, previewId, inputId) {
  const area = document.getElementById(areaId);
  if (!area) return;
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
const deleteRestaurantBtn = document.getElementById('delete-restaurant-btn');
if (deleteRestaurantBtn) {
  deleteRestaurantBtn.addEventListener('click', async () => {
    const name = currentRestaurant ? currentRestaurant.name : 'このレストラン';
    if (!confirm(`「${name}」を削除しますか？\nすべてのレビューも削除されます。`)) return;
    deleteRestaurantBtn.textContent = '削除中…'; deleteRestaurantBtn.disabled = true;
    const res = await fetch(`/foodlog/api/restaurants.php?id=${restaurantId}`, { method: 'DELETE' });
    if (!res.ok) {
      alert('削除権限がありません。');
      deleteRestaurantBtn.textContent = '🗑 Delete'; deleteRestaurantBtn.disabled = false;
      return;
    }
    window.location.href = '/foodlog/';
  });
}

// ─── 編集モーダル（レストラン）────────────────────────────────────────────────
const editModal   = document.getElementById('edit-modal');
const editInfoBtn = document.getElementById('edit-info-btn');
const cancelEditBtn = document.getElementById('cancel-edit-btn');
const editRestaurantForm = document.getElementById('edit-restaurant-form');

if (editModal && editInfoBtn) {
  editInfoBtn.addEventListener('click', () => {
    if (!currentRestaurant) return;

    document.getElementById('edit-name').value     = currentRestaurant.name || '';
    document.getElementById('edit-category').value = currentRestaurant.category || '';
    document.getElementById('edit-desc').value     = currentRestaurant.description || '';
    document.getElementById('edit-address').value  = currentRestaurant.address || '';

    setupCharCounter('edit-name', 'edit-name-count');
    setupCharCounter('edit-category', 'edit-category-count');
    setupCharCounter('edit-desc', 'edit-desc-count');
    setupCharCounter('edit-address', 'edit-address-count');

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

  if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);
  editModal.addEventListener('click', e => { if (e.target === editModal) closeEditModal(); });
}

function closeEditModal() {
  if (!editModal) return;
  editModal.classList.remove('open');
  editRestaurantForm.reset();
  document.getElementById('edit-image-preview').style.display = 'none';
  document.getElementById('edit-upload-placeholder').style.display = 'flex';
}

if (editRestaurantForm) {
  editRestaurantForm.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.textContent = '保存中…'; btn.disabled = true;

    const formData = new FormData(e.target);
    const res = await fetch(`/foodlog/api/restaurants.php?id=${restaurantId}`, {
      method: 'POST',
      body: formData,
    });

    btn.textContent = 'Save Changes'; btn.disabled = false;

    if (!res.ok) {
      alert('更新権限がありません。');
      return;
    }

    const updated = await res.json();
    if (updated && updated.id) {
      currentRestaurant = updated;
      const idx = restaurantList.findIndex(r => String(r.id) === String(restaurantId));
      if (idx !== -1) restaurantList[idx] = updated;
      renderRestaurantInfo(updated);
    }
    closeEditModal();
  });
}

// ─── レビュー編集モーダル ─────────────────────────────────────────────────────
const editReviewModal = document.getElementById('edit-review-modal');
const cancelEditReviewBtn = document.getElementById('cancel-edit-review-btn');
const editReviewForm = document.getElementById('edit-review-form');

function openEditReviewModal(data) {
  if (!editReviewModal) return;
  document.getElementById('edit-review-id').value         = data.id;
  document.getElementById('edit-rv-date').value           = data.date;
  document.getElementById('edit-rv-order').value          = data.order;
  document.getElementById('edit-rv-impression').value     = data.impression;
  document.getElementById('edit-rv-rating').value         = data.rating || '';

  setupCharCounter('edit-rv-order', 'edit-order-count');
  setupCharCounter('edit-rv-impression', 'edit-impression-count');

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
  if (!editReviewModal) return;
  editReviewModal.classList.remove('open');
  editReviewForm.reset();
}

if (editReviewModal) {
  if (cancelEditReviewBtn) cancelEditReviewBtn.addEventListener('click', closeEditReviewModal);
  editReviewModal.addEventListener('click', e => { if (e.target === editReviewModal) closeEditReviewModal(); });
}

if (editReviewForm) {
  editReviewForm.addEventListener('submit', async e => {
    e.preventDefault();
    const id         = document.getElementById('edit-review-id').value;
    const date       = document.getElementById('edit-rv-date').value;
    const order      = document.getElementById('edit-rv-order').value.trim();
    const impression = document.getElementById('edit-rv-impression').value.trim();
    if (!date || !order || !impression) return;

    const btn = e.target.querySelector('button[type="submit"]');
    btn.textContent = '保存中…'; btn.disabled = true;

    const formData = new FormData(e.target);
    const res = await fetch(`/foodlog/api/reviews.php?id=${id}`, {
      method: 'POST',
      body: formData,
    });

    btn.textContent = 'Save Changes'; btn.disabled = false;

    if (!res.ok) {
      alert('レビューの更新権限がありません。');
      return;
    }

    closeEditReviewModal();
    loadReviews();
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

  setupDateMax('rv-date');
  setupDateMax('edit-rv-date');

  setupCharCounter('rv-order', 'order-count');
  setupCharCounter('rv-impression', 'impression-count');

  setupCharCounter('edit-name', 'edit-name-count');
  setupCharCounter('edit-category', 'edit-category-count');
  setupCharCounter('edit-desc', 'edit-desc-count');
  setupCharCounter('edit-address', 'edit-address-count');

  setupCharCounter('edit-rv-order', 'edit-order-count');
  setupCharCounter('edit-rv-impression', 'edit-impression-count');

  loadRestaurant();
  loadReviews();
}

init();
