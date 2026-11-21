@extends('layouts.app', ['navActive' => 'favorites'])
@section('title', 'お気に入り一覧 — FoodLog')

@section('content')
  <section class="stats-hero">
    <div class="hero-tag">❤️ お気に入り</div>
    <h1>お気に入り <span class="grad">レストラン一覧</span></h1>
    <p>保存したお気に入りレストランをいつでも見返せます。</p>
  </section>

  <main class="section">
    <div class="section-header">
      <div class="section-title">お気に入り店舗 <span id="favorites-count"></span></div>
      <a href="/foodlog/" class="back-btn">← ホームへ戻る</a>
    </div>
    <div class="restaurant-grid" id="favorites-grid"></div>
    <div id="favorites-pagination"></div>
  </main>
@endsection

@section('scripts')
  <script src="/foodlog/assets/js/pagination.js?v=20261122b"></script>
  <script>
    (function () {
      const GRADIENTS = [
        'linear-gradient(135deg, #1a0533 0%, #3d1a6e 100%)',
        'linear-gradient(135deg, #1a0a00 0%, #7a3200 100%)',
        'linear-gradient(135deg, #00171a 0%, #006070 100%)',
        'linear-gradient(135deg, #0d1a00 0%, #3a5e00 100%)',
      ];
      const PER_PAGE = 10;
      let items = @json($favoritesPayload);
      let currentPage = 1;

      function csrfHeaders() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        return { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' };
      }

      function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
      }

      function render() {
        const grid = document.getElementById('favorites-grid');
        const pg = document.getElementById('favorites-pagination');
        const countEl = document.getElementById('favorites-count');
        grid.replaceChildren();
        if (pg) pg.replaceChildren();
        if (countEl) countEl.textContent = `(${items.length}件)`;

        if (!items.length) {
          const empty = el('div', 'empty-state');
          const p = el('p', '', 'まだお気に入り登録されたレストランがありません。');
          const backLink = el('a', 'add-btn', 'ホームでレストランを探す');
          backLink.href = '/foodlog/';
          backLink.style.cssText = 'display:inline-flex;margin-top:1rem;text-decoration:none;';
          empty.append(el('div', 'empty-icon', '🤍'), p, backLink);
          grid.appendChild(empty);
          return;
        }

        const totalPages = Math.max(1, Math.ceil(items.length / PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * PER_PAGE;
        const pageItems = items.slice(start, start + PER_PAGE);

        pageItems.forEach((r, idx) => {
          const count = parseInt(r.review_count) || 0;
          const avgText = r.avg_rating > 0 ? `★ ${Number(r.avg_rating).toFixed(1)}` : '評価なし';

          const card = el('a', 'restaurant-card');
          card.href = `/foodlog/restaurant.php?id=${encodeURIComponent(r.id)}`;

          const favBtn = el('button', 'card-fav-btn favorited', '❤️');
          favBtn.type = 'button';
          favBtn.title = 'お気に入り解除';
          favBtn.setAttribute('aria-label', 'お気に入り解除');
          favBtn.addEventListener('click', async e => {
            e.preventDefault();
            e.stopPropagation();
            favBtn.disabled = true;
            const res = await fetch('/foodlog/api/favorites.php', {
              method: 'POST',
              headers: csrfHeaders(),
              body: JSON.stringify({ restaurant_id: r.id }),
            });
            favBtn.disabled = false;
            if (!res.ok) return;
            const data = await res.json();
            if (!data.favorited) {
              items = items.filter(x => String(x.id) !== String(r.id));
              render();
            }
          });
          card.appendChild(favBtn);

          const placeholder = el('div', 'card-image-placeholder', '🍽️');
          placeholder.style.background = GRADIENTS[idx % GRADIENTS.length];
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

          const body = el('div', 'card-body');
          body.append(
            el('div', 'card-category', r.category || ''),
            el('div', 'card-name', r.name || ''),
            el('div', 'card-desc', r.description || ''),
            el('div', 'card-address', r.address ? `📍 ${r.address}` : '')
          );

          const footer = el('div', 'card-footer');
          const reviewCountEl = el('div', 'review-count');
          reviewCountEl.append(el('strong', '', String(count)), document.createTextNode(`件のレビュー  ·  ${avgText}`));
          footer.append(reviewCountEl, el('div', 'card-arrow', '→'));
          body.appendChild(footer);

          card.appendChild(body);
          grid.appendChild(card);
        });

        if (pg && typeof window.renderPagination === 'function') {
          window.renderPagination(pg, {
            currentPage,
            totalPages,
            onPageChange: p => {
              currentPage = p;
              render();
            },
          });
        }
      }

      render();
    })();
  </script>
@endsection
