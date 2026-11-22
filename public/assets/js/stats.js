// ─── 統計ダッシュボード（単一SQL集計API版） ─────────────────────────────────

const GRADIENTS = [
  'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
  'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
  'linear-gradient(135deg, #00171a 0%, #006070 100%)',
  'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
];

if (typeof window.t !== 'function') {
  window.t = k => k;
}

function isEn() {
  return window.LOCALE === 'en';
}

function el(tag, className, text) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  if (text !== undefined && text !== null) node.textContent = text;
  return node;
}

async function loadStats() {
  const res = await fetch('/foodlog/api/stats.php');
  if (!res.ok) throw new Error('Failed to load stats');
  return await res.json();
}

function renderSummary(summary) {
  document.getElementById('s-restaurants').textContent = summary.restaurant_count ?? 0;
  document.getElementById('s-reviews').textContent = summary.review_count ?? 0;
  const totalBadge = document.getElementById('total-reviews-badge');
  if (totalBadge) {
    const cnt = summary.review_count ?? 0;
    totalBadge.textContent = isEn() ? `${cnt} Reviews` : `${cnt}件`;
  }

  if (summary.avg_rating !== null && summary.avg_rating !== undefined) {
    document.getElementById('s-avg-rating').textContent = Number(summary.avg_rating).toFixed(1);
  }

  if (summary.latest_date) {
    document.getElementById('s-latest').textContent = String(summary.latest_date).replace(/-/g, '/');
  }
}

function renderMonthlyChart(monthly) {
  const container = document.getElementById('monthly-chart');
  container.replaceChildren();

  const max = Math.max(...monthly.map(m => Number(m.count) || 0), 1);

  monthly.forEach(m => {
    const d = new Date(m.year, (m.month || 1) - 1, 1);
    const label = isEn()
      ? d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
      : `${m.year}年${m.month}月`;
    const row = el('div', 'bar-row');
    const track = el('div', 'bar-track');
    const fill = el('div', 'bar-fill');
    fill.style.width = `${((Number(m.count) || 0) / max) * 100}%`;
    track.appendChild(fill);
    row.append(
      el('div', 'bar-label', label),
      track,
      el('div', 'bar-value', String(m.count ?? 0))
    );
    container.appendChild(row);
  });
}

function renderCategoryChart(categories) {
  const container = document.getElementById('category-chart');
  container.replaceChildren();

  const max = Math.max(...categories.map(x => Number(x.count) || 0), 1);
  const colors = ['#ff6b35', '#7c3aed', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444'];

  categories.forEach((item, i) => {
    const label = item.category === 'Other' ? t('その他') : (item.category || t('その他'));
    const count = Number(item.count) || 0;
    const row = el('div', 'bar-row');
    const track = el('div', 'bar-track');
    const fill = el('div', 'bar-fill');
    fill.style.width = `${(count / max) * 100}%`;
    fill.style.background = colors[i % colors.length];
    track.appendChild(fill);
    row.append(
      el('div', 'bar-label', label),
      track,
      el('div', 'bar-value', String(count))
    );
    container.appendChild(row);
  });
}

function renderRanking(ranking) {
  const container = document.getElementById('ranking-list');
  container.replaceChildren();

  if (!ranking || ranking.length === 0) {
    const empty = el('div', 'reviews-empty');
    empty.append(el('div', 'empty-icon', '📋'), el('p', '', t('まだレビューがありません。')));
    container.appendChild(empty);
    return;
  }

  ranking.forEach((r, i) => {
    const link = el('a', 'ranking-item');
    link.href = `/foodlog/restaurant.php?id=${encodeURIComponent(r.id)}`;

    let thumb;
    if (r.image) {
      thumb = el('img', 'ranking-thumb');
      thumb.src = `/foodlog/${r.image}`;
      thumb.alt = r.name || '';
    } else {
      thumb = el('div', 'ranking-thumb-placeholder', '🍽️');
      thumb.style.background = GRADIENTS[i % GRADIENTS.length];
    }

    const info = el('div', 'ranking-info');
    const count = Number(r.review_count) || 0;
    const reviewLabel = isEn()
      ? `${count} ${count === 1 ? 'review' : 'reviews'}`
      : `${count}件のレビュー`;
    info.append(
      el('div', 'ranking-name', r.name || ''),
      el('div', 'ranking-meta', `★ ${Number(r.avg_rating || 0).toFixed(1)}\u00A0\u00A0\u00A0${reviewLabel}`)
    );

    link.append(
      el('div', 'ranking-rank', `#${i + 1}`),
      thumb,
      info,
      el('div', 'card-arrow', '→')
    );
    container.appendChild(link);
  });
}

async function init() {
  try {
    const data = await loadStats();
    renderSummary(data.summary || {});
    renderMonthlyChart(data.monthly || []);
    renderCategoryChart(data.categories || []);
    renderRanking(data.ranking || []);
  } catch (e) {
    if (typeof window.showToast === 'function') {
      window.showToast(t('統計データの読み込みに失敗しました。'), 'error');
    }
  }
}

init();
