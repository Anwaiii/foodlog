const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

async function loadRestaurants() {
  const res = await fetch('/foodlog/api/restaurants.php');
  return res.json();
}

function renderGrid(restaurants) {
  const grid = document.getElementById('restaurant-grid');
  grid.innerHTML = '';
  if (!restaurants.length) {
    grid.innerHTML = `<div class="empty-state"><div class="empty-icon">🍽️</div><p>No restaurants yet. Add one!</p></div>`;
    return;
  }
  let totalReviews = 0;
  restaurants.forEach((r, idx) => {
    const count = parseInt(r.review_count) || 0;
    totalReviews += count;
    const card = document.createElement('a');
    card.className = 'restaurant-card';
    card.href = `/foodlog/restaurant.php?id=${r.id}`;
    const gradient = GRADIENTS[idx % GRADIENTS.length];
    const imgSrc = r.image ? `/foodlog/${r.image}` : null;
    let imageHtml = imgSrc
      ? `<img class="card-image" src="${imgSrc}" alt="${r.name}" loading="lazy"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" />
         <div class="card-image-placeholder" style="display:none;background:${gradient}">🍽️</div>`
      : `<div class="card-image-placeholder" style="background:${gradient}">🍽️</div>`;
    card.innerHTML = `${imageHtml}
      <button class="card-delete-btn" data-id="${r.id}" data-name="${r.name.replace(/"/g,'&quot;')}" title="Delete">🗑</button>
      <div class="card-body">
        <div class="card-category">${r.category || ''}</div>
        <div class="card-name">${r.name}</div>
        <div class="card-desc">${r.description || ''}</div>
        <div class="card-footer">
          <div class="review-count"><strong>${count}</strong> ${count === 1 ? 'review' : 'reviews'}</div>
          <div class="card-arrow">→</div>
        </div>
      </div>`;
    grid.appendChild(card);
  });

  // 削除ボタン（カードクリックを伝播させない）
  grid.querySelectorAll('.card-delete-btn').forEach(btn => {
    btn.addEventListener('click', async e => {
      e.preventDefault();
      e.stopPropagation();
      const name = btn.dataset.name;
      if (!confirm(`「${name}」を削除しますか？\nすべてのレビューも削除されます。`)) return;
      btn.textContent = '…'; btn.disabled = true;
      await fetch(`/foodlog/api/restaurants.php?id=${btn.dataset.id}`, { method: 'DELETE' });
      init();
    });
  });

  document.getElementById('stat-restaurants').textContent = restaurants.length;
  document.getElementById('stat-reviews').textContent = totalReviews;
  document.getElementById('total-reviews-badge').textContent = totalReviews + ' Reviews';
  document.getElementById('restaurant-count').textContent = '(' + restaurants.length + ')';
}

// ─── Upload area setup ────────────────────────────────────────────────────────
function setupUploadArea(areaId, placeholderId, previewId, inputId) {
  const area = document.getElementById(areaId);
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
document.getElementById('add-restaurant-btn').addEventListener('click', () => {
  modal.classList.add('open');
  document.getElementById('r-name').focus();
});
document.getElementById('cancel-modal-btn').addEventListener('click', closeModal);
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

function closeModal() {
  modal.classList.remove('open');
  document.getElementById('add-restaurant-form').reset();
  document.getElementById('add-image-preview').style.display = 'none';
  document.getElementById('add-upload-placeholder').style.display = 'flex';
}

document.getElementById('add-restaurant-form').addEventListener('submit', async e => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const res = await fetch('/foodlog/api/restaurants.php', { method: 'POST', body: formData });
  await res.json();
  closeModal();
  init();
});

async function init() {
  setupUploadArea('add-upload-area', 'add-upload-placeholder', 'add-image-preview', 'r-image');
  const restaurants = await loadRestaurants();
  renderGrid(restaurants);
}

init();
