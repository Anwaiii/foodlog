@extends('layouts.app')
@section('title', $nickname . ' のレビュー履歴 — FoodLog')

@section('content')
  <section class="stats-hero">
    <div class="hero-tag">{{ $isAdmin ? '👑 Admin' : '👤 Member' }} Reviews</div>
    <h1>{{ $nickname }} <span class="grad">のレビュー履歴</span></h1>
    <div class="hero-stats" style="margin-top:1.5rem;">
      <div>
        <div class="stat-val">{{ $totalReviews }}</div>
        <div class="stat-label">Total Reviews</div>
      </div>
      <div>
        <div class="stat-val">{{ $restaurantCount }}</div>
        <div class="stat-label">Restaurants</div>
      </div>
      <div>
        <div class="stat-val">{{ $avgRating !== null ? '★ ' . number_format($avgRating, 1) : '—' }}</div>
        <div class="stat-label">Avg Rating</div>
      </div>
    </div>
    <div style="margin-top:1.4rem;">
      <a href="/foodlog/" class="back-btn">← ホームへ戻る</a>
    </div>
  </section>

  <div class="detail-body">
    <section class="reviews-panel">
      <div class="reviews-section-header">
        <div class="reviews-title-group">
          <h2 style="margin-bottom:0;">📖 投稿レビュー一覧</h2>
          <span class="badge" id="user-review-badge">
            @if($totalReviews > 0 && $avgRating !== null)
              ★ {{ number_format($avgRating, 1) }}&nbsp;&nbsp;&nbsp;&nbsp;{{ $totalReviews }} Reviews
            @else
              {{ $totalReviews }} Reviews
            @endif
          </span>
        </div>
      </div>
      <div id="user-reviews-list"></div>
    </section>
  </div>
@endsection

@section('modals')
  <!-- Photo Lightbox Modal -->
  <div class="modal-overlay" id="photo-lightbox-modal">
    <div class="photo-lightbox-box">
      <button type="button" class="photo-lightbox-close" id="photo-lightbox-close" aria-label="閉じる">✕</button>
      <img id="photo-lightbox-img" class="photo-lightbox-img" src="" alt="Enlarged review photo" />
      <p id="photo-lightbox-caption" class="photo-lightbox-caption"></p>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="/foodlog/assets/js/pagination.js?v=20261122a"></script>
  <script>
    (function () {
      const reviews = @json($reviewsPayload);
      const PER_PAGE = 10;
      let currentPage = 1;

      function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
      }

      function appendTextWithBreaks(container, text) {
        String(text || '').split(/\r?\n/).forEach((line, idx) => {
          if (idx > 0) container.appendChild(document.createElement('br'));
          container.appendChild(document.createTextNode(line));
        });
      }

      function openLightbox(src, caption) {
        const modal = document.getElementById('photo-lightbox-modal');
        const img = document.getElementById('photo-lightbox-img');
        const cap = document.getElementById('photo-lightbox-caption');
        if (!modal || !img) return;
        img.src = src;
        if (cap) { cap.textContent = caption || ''; cap.style.display = caption ? '' : 'none'; }
        modal.classList.add('open');
      }

      function closeLightbox() {
        const modal = document.getElementById('photo-lightbox-modal');
        const img = document.getElementById('photo-lightbox-img');
        if (modal) modal.classList.remove('open');
        if (img) img.src = '';
      }

      document.getElementById('photo-lightbox-close')?.addEventListener('click', closeLightbox);
      document.getElementById('photo-lightbox-modal')?.addEventListener('click', e => {
        if (e.target.id === 'photo-lightbox-modal') closeLightbox();
      });
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLightbox();
      });

      function render() {
        const list = document.getElementById('user-reviews-list');
        list.replaceChildren();

        if (!reviews.length) {
          const empty = el('div', 'reviews-empty');
          empty.append(el('div', 'empty-icon', '📋'), el('p', '', 'まだ投稿されたレビューはありません。'));
          list.appendChild(empty);
          return;
        }

        const totalPages = Math.max(1, Math.ceil(reviews.length / PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * PER_PAGE;
        const pageItems = reviews.slice(start, start + PER_PAGE);

        const columns = el('div', 'reviews-columns');
        const colLeft = el('div', 'reviews-col');
        const colRight = el('div', 'reviews-col');
        columns.append(colLeft, colRight);
        list.appendChild(columns);

        pageItems.forEach((rv, idx) => {
          const item = el('div', 'review-item');

          const header = el('div', 'review-item-header');
          const leftMeta = el('div');
          leftMeta.style.cssText = 'display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;';

          const formatted = new Date(rv.date + 'T00:00:00').toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric'
          });
          leftMeta.appendChild(el('div', 'review-date', `📅 ${formatted}`));

          const restLink = el('a', 'review-restaurant-link', `🏪 ${rv.restaurant_name} →`);
          restLink.href = `/foodlog/restaurant.php?id=${encodeURIComponent(rv.restaurant_id)}`;
          leftMeta.appendChild(restLink);

          header.appendChild(leftMeta);
          item.appendChild(header);

          if (rv.rating) {
            const starsEl = el('div', '', '⭐'.repeat(Number(rv.rating)));
            starsEl.style.cssText = 'font-size:0.9rem;margin-bottom:0.5rem;';
            item.appendChild(starsEl);
          }

          if (rv.image) {
            const img = el('img', 'review-photo');
            img.src = `/foodlog/${rv.image}`;
            img.alt = 'Review photo';
            img.title = 'クリックして拡大';
            img.addEventListener('click', () => {
              openLightbox(img.src, rv.order_details ? `🍽️ ${rv.order_details}` : '');
            });
            item.appendChild(img);
          }

          const orderEl = el('div', 'review-order');
          orderEl.appendChild(document.createTextNode('🍽️ '));
          appendTextWithBreaks(orderEl, rv.order_details);
          item.appendChild(orderEl);

          const impEl = el('div', 'review-impression');
          appendTextWithBreaks(impEl, rv.impression);
          item.appendChild(impEl);

          (idx % 2 === 0 ? colLeft : colRight).appendChild(item);
        });

        if (typeof window.renderPagination === 'function') {
          window.renderPagination(list, {
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
