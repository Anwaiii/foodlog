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
const reviewFilters = {
  sort: 'date_desc',
  rating: 'all',
  photoOnly: false,
  myOnly: false,
  keyword: ''
};

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

function getAverageRating(reviews) {
  const rated = reviews.filter(rv => Number(rv.rating) > 0);
  if (!rated.length) return null;
  const sum = rated.reduce((acc, rv) => acc + Number(rv.rating), 0);
  return (sum / rated.length).toFixed(1);
}

function hasActiveReviewFilters() {
  return reviewFilters.rating !== 'all' ||
    reviewFilters.photoOnly ||
    reviewFilters.myOnly ||
    reviewFilters.keyword.trim() !== '';
}

function resetReviewFilters() {
  reviewFilters.sort = 'date_desc';
  reviewFilters.rating = 'all';
  reviewFilters.photoOnly = false;
  reviewFilters.myOnly = false;
  reviewFilters.keyword = '';

  const sortEl = document.getElementById('review-sort');
  const ratingEl = document.getElementById('review-rating-filter');
  const photoBtn = document.getElementById('review-photo-only');
  const myBtn = document.getElementById('review-my-only');
  const kwEl = document.getElementById('review-keyword');
  if (sortEl) sortEl.value = 'date_desc';
  if (ratingEl) ratingEl.value = 'all';
  if (photoBtn) { photoBtn.classList.remove('active'); photoBtn.setAttribute('aria-pressed', 'false'); }
  if (myBtn) { myBtn.classList.remove('active'); myBtn.setAttribute('aria-pressed', 'false'); }
  if (kwEl) kwEl.value = '';

  currentPage = 1;
  renderReviews();
}

function getFilteredAndSortedReviews() {
  const loggedIn = Boolean(window.AUTH && window.AUTH.loggedIn);
  const userId   = window.AUTH ? window.AUTH.userId : null;
  const kw       = reviewFilters.keyword.trim().toLowerCase();

  const filtered = allReviews.filter(rv => {
    const rating = Number(rv.rating || 0);
    if (reviewFilters.rating === '5' && rating !== 5) return false;
    if (reviewFilters.rating === '4' && rating < 4) return false;
    if (reviewFilters.rating === '3' && rating < 3) return false;
    if (reviewFilters.rating === '2' && (rating === 0 || rating > 2)) return false;

    if (reviewFilters.photoOnly && !rv.image) return false;

    if (reviewFilters.myOnly) {
      if (!loggedIn || userId === null || rv.member_id === null || Number(rv.member_id) !== Number(userId)) {
        return false;
      }
    }

    if (kw) {
      const hay = `${rv.order_details || ''} ${rv.impression || ''} ${rv.member_nickname || ''}`.toLowerCase();
      if (!hay.includes(kw)) return false;
    }

    return true;
  });

  filtered.sort((a, b) => {
    const dateA = a.date || '';
    const dateB = b.date || '';
    const rateA = Number(a.rating || 0);
    const rateB = Number(b.rating || 0);
    const idA   = Number(a.id || 0);
    const idB   = Number(b.id || 0);

    switch (reviewFilters.sort) {
      case 'date_asc':
        return dateA.localeCompare(dateB) || (idA - idB);
      case 'rating_desc':
        return (rateB - rateA) || dateB.localeCompare(dateA) || (idB - idA);
      case 'rating_asc':
        return (rateA - rateB) || dateB.localeCompare(dateA) || (idB - idA);
      case 'date_desc':
      default:
        return dateB.localeCompare(dateA) || (idB - idA);
    }
  });

  return filtered;
}

function renderReviews() {
  const list = document.getElementById('reviews-list');
  const allTotal = allReviews.length;
  const filtered = getFilteredAndSortedReviews();
  const total = filtered.length;
  const totalPages = Math.max(1, Math.ceil(total / REVIEWS_PER_PAGE));
  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const start = (currentPage - 1) * REVIEWS_PER_PAGE;
  const pageReviews = filtered.slice(start, start + REVIEWS_PER_PAGE);

  const avg = getAverageRating(allReviews);
  const filteredActive = hasActiveReviewFilters();
  const badge = document.getElementById('review-count-badge');
  if (badge) {
    if (!allTotal) {
      badge.textContent = '0 Reviews';
    } else if (filteredActive) {
      badge.textContent = (avg ? `★ ${avg}\u00A0\u00A0\u00A0\u00A0` : '') + `${total} / ${allTotal} Reviews`;
    } else {
      badge.textContent = (avg ? `★ ${avg}\u00A0\u00A0\u00A0\u00A0` : '') + `${allTotal} Reviews`;
    }
  }

  const resetBtn = document.getElementById('review-filter-reset');
  if (resetBtn) {
    resetBtn.style.display = (filteredActive || reviewFilters.sort !== 'date_desc') ? '' : 'none';
  }

  list.replaceChildren();

  if (!allTotal) {
    const empty = el('div', 'reviews-empty');
    empty.append(el('div', 'empty-icon', '📋'), el('p', '', 'No reviews yet.'));
    list.appendChild(empty);
    return;
  }

  if (!total) {
    const empty = el('div', 'reviews-empty');
    empty.append(el('div', 'empty-icon', '🔍'), el('p', '', '条件に一致するレビューがありません。'));
    const clearBtn = el('button', 'review-toggle-btn', '条件をリセット');
    clearBtn.type = 'button';
    clearBtn.style.marginTop = '0.75rem';
    clearBtn.addEventListener('click', resetReviewFilters);
    empty.appendChild(clearBtn);
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
      if (rv.member_id) {
        const authorLink = el('a', 'review-author review-author-link', `👤 ${rv.member_nickname}`);
        authorLink.href = `/foodlog/users/${encodeURIComponent(rv.member_id)}`;
        authorLink.title = `${rv.member_nickname} のレビュー履歴を見る`;
        leftMeta.appendChild(authorLink);
      } else {
        leftMeta.appendChild(el('span', 'review-author', `👤 ${rv.member_nickname}`));
      }
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
      img.title = 'クリックして拡大';
      img.addEventListener('click', () => {
        const caption = rv.order_details ? `🍽️ ${rv.order_details}` : '';
        openPhotoLightbox(img.src, caption);
      });
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

  // 共通ページネーションUI
  if (typeof window.renderPagination === 'function') {
    window.renderPagination(list, {
      currentPage,
      totalPages,
      onPageChange: p => {
        currentPage = p;
        renderReviews();
      },
    });
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
      if (res.status === 401 || res.status === 403) {
        alert('更新権限がありません。');
      } else if (res.status === 422) {
        const errData = await res.json().catch(() => ({}));
        alert('入力エラー: ' + (errData.message || '入力内容を確認してください。'));
      } else {
        alert(`更新に失敗しました（HTTP ${res.status}）。`);
      }
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
      if (res.status === 401 || res.status === 403) {
        alert('レビューの更新権限がありません。');
      } else if (res.status === 422) {
        const errData = await res.json().catch(() => ({}));
        alert('入力エラー: ' + (errData.message || '入力内容を確認してください。'));
      } else {
        alert(`レビューの更新に失敗しました（HTTP ${res.status}）。`);
      }
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

function setupReviewControls() {
  const sortEl = document.getElementById('review-sort');
  const ratingEl = document.getElementById('review-rating-filter');
  const photoBtn = document.getElementById('review-photo-only');
  const myBtn = document.getElementById('review-my-only');
  const kwEl = document.getElementById('review-keyword');
  const resetBtn = document.getElementById('review-filter-reset');

  if (sortEl) {
    sortEl.addEventListener('change', () => {
      reviewFilters.sort = sortEl.value;
      currentPage = 1;
      renderReviews();
    });
  }
  if (ratingEl) {
    ratingEl.addEventListener('change', () => {
      reviewFilters.rating = ratingEl.value;
      currentPage = 1;
      renderReviews();
    });
  }
  if (photoBtn) {
    photoBtn.addEventListener('click', () => {
      reviewFilters.photoOnly = !reviewFilters.photoOnly;
      photoBtn.classList.toggle('active', reviewFilters.photoOnly);
      photoBtn.setAttribute('aria-pressed', String(reviewFilters.photoOnly));
      currentPage = 1;
      renderReviews();
    });
  }
  if (myBtn) {
    myBtn.addEventListener('click', () => {
      reviewFilters.myOnly = !reviewFilters.myOnly;
      myBtn.classList.toggle('active', reviewFilters.myOnly);
      myBtn.setAttribute('aria-pressed', String(reviewFilters.myOnly));
      currentPage = 1;
      renderReviews();
    });
  }
  if (kwEl) {
    kwEl.addEventListener('input', () => {
      reviewFilters.keyword = kwEl.value;
      currentPage = 1;
      renderReviews();
    });
  }
  if (resetBtn) {
    resetBtn.addEventListener('click', resetReviewFilters);
  }
}

function openPhotoLightbox(src, caption = '') {
  const modal = document.getElementById('photo-lightbox-modal');
  const img = document.getElementById('photo-lightbox-img');
  const cap = document.getElementById('photo-lightbox-caption');
  if (!modal || !img) return;
  img.src = src;
  if (cap) {
    cap.textContent = caption;
    cap.style.display = caption ? '' : 'none';
  }
  modal.classList.add('open');
}

function closePhotoLightbox() {
  const modal = document.getElementById('photo-lightbox-modal');
  const img = document.getElementById('photo-lightbox-img');
  if (modal) modal.classList.remove('open');
  if (img) img.src = '';
}

function setupPhotoLightbox() {
  const modal = document.getElementById('photo-lightbox-modal');
  const closeBtn = document.getElementById('photo-lightbox-close');
  if (closeBtn) closeBtn.addEventListener('click', closePhotoLightbox);
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closePhotoLightbox();
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal && modal.classList.contains('open')) {
      closePhotoLightbox();
    }
  });
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

  setupReviewControls();
  setupPhotoLightbox();

  loadRestaurant();
  loadReviews();
}

init();
