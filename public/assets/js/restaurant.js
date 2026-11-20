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

function appendTextWithBreaks(parent, text) {
  const lines = String(text || '').split('\n');
  lines.forEach((line, i) => {
    if (i > 0) parent.appendChild(document.createElement('br'));
    parent.appendChild(document.createTextNode(line));
  });
}

// ─── URLパラメータ ────────────────────────────────────────────────────────────
const params = new URLSearchParams(window.location.search);
const restaurantId = params.get('id');
if (!restaurantId) window.location.href = '/foodlog/';

const today = new Date().toISOString().split('T')[0];
const rvDateInput = document.getElementById('rv-date');
if (rvDateInput) rvDateInput.value = today;

let currentRestaurant = null;
let restaurantList = [];

const REVIEWS_PER_PAGE = 10;
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
  const elPlaceholder = document.getElementById('banner-placeholder');
  elPlaceholder.style.background = GRADIENTS[idx % GRADIENTS.length];
  elPlaceholder.style.display = 'flex';
  elPlaceholder.textContent = '🍽️';
}

// ─── レビューの読み込み・描画 ─────────────────────────────────────────────────
async function loadReviews() {
  const res = await fetch(`/foodlog/api/reviews.php?restaurant_id=${encodeURIComponent(restaurantId)}`);
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

  const badge = document.getElementById('review-count-badge');
  if (badge) badge.textContent = total + ' Reviews';

  list.replaceChildren();

  if (!total) {
    const empty = el('div', 'reviews-empty');
    empty.append(el('div', 'empty-icon', '📋'), el('p', '', 'No reviews yet.'));
    list.appendChild(empty);
    return;
  }

  const loggedIn = Boolean(window.AUTH && window.AUTH.loggedIn);
  const isAdmin  = Boolean(window.AUTH && window.AUTH.isAdmin);
  const userId   = window.AUTH ? window.AUTH.userId : null;

  const columns = el('div', 'reviews-columns');
  const colLeft = el('div', 'reviews-col');
  const colRight = el('div', 'reviews-col');
  columns.append(colLeft, colRight);
  list.appendChild(columns);

  pageReviews.forEach((rv, idx) => {
    const stars = rv.rating ? '⭐'.repeat(Number(rv.rating)) : '';
    const formatted = new Date(rv.date + 'T00:00:00').toLocaleDateString('en-US', {
      year: 'numeric', month: 'long', day: 'numeric'
    });

    const isOwner = loggedIn && userId !== null && rv.member_id !== null && Number(rv.member_id) === Number(userId);
    const canEdit = isOwner;
    const canDelete = isOwner || isAdmin;

    const item = el('div', 'review-item');

    const header = el('div', 'review-item-header');
    const leftMeta = el('div');
    leftMeta.style.cssText = 'display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;';
    leftMeta.appendChild(el('div', 'review-date', `📅 ${formatted}`));
    if (rv.member_nickname) {
      leftMeta.appendChild(el('span', 'review-author', `👤 ${rv.member_nickname}`));
    }

    const actions = el('div');
    actions.style.cssText = 'display:flex;gap:6px;';
    if (canEdit) {
      const editBtn = el('button', 'review-edit', '✏️');
      editBtn.type = 'button';
      editBtn.title = 'Edit';
      editBtn.dataset.id = rv.id;
      editBtn.dataset.date = rv.date;
      editBtn.dataset.order = rv.order_details || '';
      editBtn.dataset.impression = rv.impression || '';
      editBtn.dataset.rating = rv.rating || '';
      editBtn.dataset.image = rv.image || '';
      editBtn.addEventListener('click', () => openEditReviewModal(editBtn.dataset));
      actions.appendChild(editBtn);
    }
    if (canDelete) {
      const delBtn = el('button', 'review-delete', '🗑');
      delBtn.type = 'button';
      delBtn.title = 'Delete';
      delBtn.dataset.id = rv.id;
      delBtn.addEventListener('click', () => deleteReview(delBtn.dataset.id));
      actions.appendChild(delBtn);
    }

    header.append(leftMeta, actions);
    item.appendChild(header);

    if (stars) {
      const starsEl = el('div', '', stars);
      starsEl.style.cssText = 'font-size:0.9rem;margin-bottom:0.5rem;';
      item.appendChild(starsEl);
    }

    if (rv.image) {
      const img = el('img', 'review-photo');
      img.src = `/foodlog/${rv.image}`;
      img.alt = 'Review photo';
      item.appendChild(img);
    }

    const orderEl = el('div', 'review-order');
    appendTextWithBreaks(orderEl, rv.order_details);
    item.appendChild(orderEl);

    const impEl = el('div', 'review-impression');
    appendTextWithBreaks(impEl, rv.impression);
    item.appendChild(impEl);

    (idx % 2 === 0 ? colLeft : colRight).appendChild(item);
  });

  // ページネーションUI
  if (totalPages > 1) {
    const nav = el('div', 'pagination');

    const prevBtn = el('button', 'pagination-btn', '← Prev');
    prevBtn.id = 'pg-prev';
    prevBtn.type = 'button';
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener('click', () => { currentPage--; renderReviews(); });

    const numbersWrap = el('div', 'pagination-numbers');
    const appendPageNum = p => {
      const b = el('button', `pagination-num ${currentPage === p ? 'active' : ''}`.trim(), String(p));
      b.type = 'button';
      b.dataset.page = String(p);
      b.addEventListener('click', () => {
        currentPage = p;
        renderReviews();
      });
      numbersWrap.appendChild(b);
    };

    appendPageNum(1);
    const windowSize = 9;
    let pStart = Math.max(2, currentPage - 4);
    let pEnd = Math.min(totalPages, pStart + windowSize - 1);
    if (pEnd - pStart + 1 < windowSize) {
      pStart = Math.max(2, pEnd - windowSize + 1);
    }
    if (pStart > 2) numbersWrap.appendChild(el('span', 'pagination-ellipsis', '…'));
    for (let p = pStart; p <= pEnd; p++) appendPageNum(p);
    if (pEnd < totalPages) numbersWrap.appendChild(el('span', 'pagination-ellipsis', '…'));

    const nextBtn = el('button', 'pagination-btn', 'Next →');
    nextBtn.id = 'pg-next';
    nextBtn.type = 'button';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener('click', () => { currentPage++; renderReviews(); });

    const jumpWrap = el('div', 'pagination-jump');
    const jumpInput = el('input', 'pagination-input');
    jumpInput.type = 'text';
    jumpInput.inputMode = 'numeric';
    jumpInput.id = 'pg-jump-input';
    jumpInput.placeholder = `1-${totalPages}`;
    jumpInput.setAttribute('aria-label', 'ページ番号');
    jumpInput.title = `1〜${totalPages}の数字を入力してEnter`;

    jumpInput.addEventListener('input', () => {
      let val = jumpInput.value
        .replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
        .replace(/[^0-9]/g, '')
        .replace(/^0+/, '');
      if (val !== '') {
        const num = parseInt(val, 10);
        if (num > totalPages) val = String(totalPages);
      }
      jumpInput.value = val;
    });
    jumpInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const num = parseInt(jumpInput.value, 10);
        if (num >= 1 && num <= totalPages) {
          currentPage = num;
          renderReviews();
        }
      }
    });

    jumpWrap.append(jumpInput, el('span', 'pagination-total', `/ ${totalPages}`));
    nav.append(prevBtn, numbersWrap, nextBtn, jumpWrap);
    list.appendChild(nav);
  }
}

async function deleteReview(id) {
  const res = await fetch(`/foodlog/api/reviews.php?id=${encodeURIComponent(id)}`, {
    method: 'DELETE',
    headers: csrfHeaders(),
  });
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
      headers: csrfHeaders(),
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
    const res = await fetch(`/foodlog/api/restaurants.php?id=${encodeURIComponent(restaurantId)}`, {
      method: 'DELETE',
      headers: csrfHeaders(),
    });
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
    const res = await fetch(`/foodlog/api/restaurants.php?id=${encodeURIComponent(restaurantId)}`, {
      method: 'POST',
      headers: csrfHeaders(),
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
    const res = await fetch(`/foodlog/api/reviews.php?id=${encodeURIComponent(id)}`, {
      method: 'POST',
      headers: csrfHeaders(),
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
