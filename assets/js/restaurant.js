const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

const params = new URLSearchParams(window.location.search);
const restaurantId = params.get('id');
if (!restaurantId) window.location.href = '/foodlog/';

const today = new Date().toISOString().split('T')[0];
document.getElementById('rv-date').value = today;

async function loadRestaurant() {
  const res = await fetch('/foodlog/api/restaurants.php');
  const list = await res.json();
  const r = list.find(x => String(x.id) === String(restaurantId));
  if (!r) { document.getElementById('detail-name').textContent = 'Not found'; return; }

  document.title = r.name + ' — FoodLog';
  document.getElementById('detail-category').textContent = r.category || '';
  document.getElementById('detail-name').textContent = r.name;
  document.getElementById('detail-desc').textContent = r.description || '';

  const idx = list.indexOf(r);
  if (r.image) {
    const img = document.getElementById('banner-img');
    img.src = `/foodlog/${r.image}`; img.alt = r.name; img.style.display = 'block';
    img.onerror = () => { img.style.display = 'none'; showPlaceholder(idx, r.emoji); };
  } else {
    showPlaceholder(idx, r.emoji);
  }
}

function showPlaceholder(idx, emoji) {
  const el = document.getElementById('banner-placeholder');
  el.style.background = GRADIENTS[idx % GRADIENTS.length];
  el.style.display = 'flex';
  el.textContent = emoji || '🍽️';
}

async function loadReviews() {
  const res = await fetch(`/foodlog/api/reviews.php?restaurant_id=${restaurantId}`);
  const reviews = await res.json();
  renderReviews(reviews);
}

function renderReviews(reviews) {
  const list = document.getElementById('reviews-list');
  document.getElementById('review-count-badge').textContent = reviews.length + ' Reviews';

  if (!reviews.length) {
    list.innerHTML = `<div class="reviews-empty"><div class="empty-icon">📋</div><p>No orders logged yet.<br/>Use the form to add your first review!</p></div>`;
    return;
  }

  list.innerHTML = '';
  reviews.forEach(rv => {
    const stars = rv.rating ? '⭐'.repeat(Number(rv.rating)) : '';
    const formatted = new Date(rv.date + 'T00:00:00').toLocaleDateString('en-US', {
      year: 'numeric', month: 'long', day: 'numeric'
    });
    const item = document.createElement('div');
    item.className = 'review-item';
    item.innerHTML = `
      <div class="review-item-header">
        <div class="review-date">📅 ${formatted}</div>
        <button class="review-delete" data-id="${rv.id}" title="Delete">🗑</button>
      </div>
      ${stars ? `<div style="font-size:0.9rem;margin-bottom:0.5rem;">${stars}</div>` : ''}
      <div class="review-order">🛒 <span>${rv.order_details}</span></div>
      <div class="review-impression" style="margin-top:0.6rem;">${rv.impression}</div>`;
    list.appendChild(item);
  });

  list.querySelectorAll('.review-delete').forEach(btn => {
    btn.addEventListener('click', () => deleteReview(btn.dataset.id));
  });
}

async function deleteReview(id) {
  await fetch(`/foodlog/api/reviews.php?id=${id}`, { method: 'DELETE' });
  loadReviews();
}

document.getElementById('review-form').addEventListener('submit', async e => {
  e.preventDefault();
  const date       = document.getElementById('rv-date').value;
  const order      = document.getElementById('rv-order').value.trim();
  const impression = document.getElementById('rv-impression').value.trim();
  const rating     = document.getElementById('rv-rating').value;
  if (!date || !order || !impression) return;

  await fetch('/foodlog/api/reviews.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ restaurant_id: restaurantId, date, order_details: order, impression, rating }),
  });

  document.getElementById('rv-order').value = '';
  document.getElementById('rv-impression').value = '';
  document.getElementById('rv-rating').value = '';
  document.getElementById('rv-date').value = today;
  loadReviews();
});

loadRestaurant();
loadReviews();
