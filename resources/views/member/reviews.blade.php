@extends('layouts.app')
@section('title', $nickname . ' のレビュー履歴 — FoodLog')

@section('content')
  <section class="stats-hero">
    <div class="hero-tag">{{ $isAdmin ? '👑 管理者' : '👤 会員' }} レビュー履歴</div>
    <h1>{{ $nickname }} <span class="grad">のレビュー履歴</span></h1>
    <div class="hero-stats" style="margin-top:1.5rem;">
      <div>
        <div class="stat-val">{{ $totalReviews }}</div>
        <div class="stat-label">総レビュー数</div>
      </div>
      <div>
        <div class="stat-val">{{ $restaurantCount }}</div>
        <div class="stat-label">訪問店舗数</div>
      </div>
      <div>
        <div class="stat-val">{{ $avgRating !== null ? '★ ' . number_format($avgRating, 1) : '—' }}</div>
        <div class="stat-label">平均評価</div>
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
              ★ {{ number_format($avgRating, 1) }}&nbsp;&nbsp;&nbsp;&nbsp;{{ $totalReviews }}件
            @else
              {{ $totalReviews }}件
            @endif
          </span>
        </div>
        <div class="reviews-toolbar">
          <select id="user-review-sort" class="review-control" aria-label="並び替え">
            <option value="date_desc">📅 新しい順</option>
            <option value="date_asc">📅 古い順</option>
            <option value="rating_desc">⭐ 星が高い順</option>
            <option value="rating_asc">⭐ 星が低い順</option>
          </select>
          <input type="search" id="user-review-keyword" class="review-search-input" placeholder="🔍 店舗・感想を検索..." aria-label="キーワード検索" />
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
      <img id="photo-lightbox-img" class="photo-lightbox-img" src="" alt="拡大レビュー写真" />
      <p id="photo-lightbox-caption" class="photo-lightbox-caption"></p>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="/foodlog/assets/js/pagination.js?v=20261122b"></script>
  <script>
    (function () {
      const allReviews = @json($reviewsPayload);
      const PER_PAGE = 10;
      let currentPage = 1;
      let currentSort = 'date_desc';
      let currentKeyword = '';

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

      document.getElementById('user-review-sort')?.addEventListener('change', e => {
        currentSort = e.target.value;
        currentPage = 1;
        render();
      });
      document.getElementById('user-review-keyword')?.addEventListener('input', e => {
        currentKeyword = e.target.value.trim().toLowerCase();
        currentPage = 1;
        render();
      });

      function getFiltered() {
        const filtered = allReviews.filter(rv => {
          if (!currentKeyword) return true;
          const hay = `${rv.restaurant_name || ''} ${rv.order_details || ''} ${rv.impression || ''}`.toLowerCase();
          return hay.includes(currentKeyword);
        });
        filtered.sort((a, b) => {
          const dA = a.date || '', dB = b.date || '';
          const rA = Number(a.rating || 0), rB = Number(b.rating || 0);
          switch (currentSort) {
            case 'date_asc': return dA.localeCompare(dB) || (a.id - b.id);
            case 'rating_desc': return (rB - rA) || dB.localeCompare(dA) || (b.id - a.id);
            case 'rating_asc': return (rA - rB) || dB.localeCompare(dA) || (b.id - a.id);
            default: return dB.localeCompare(dA) || (b.id - a.id);
          }
        });
        return filtered;
      }

      function render() {
        const list = document.getElementById('user-reviews-list');
        const reviews = getFiltered();
        const badge = document.getElementById('user-review-badge');
        const rated = allReviews.filter(r => Number(r.rating) > 0);
        const avg = rated.length ? (rated.reduce((s, r) => s + Number(r.rating), 0) / rated.length).toFixed(1) : null;
        if (badge) {
          if (!allReviews.length) {
            badge.textContent = '0件';
          } else if (currentKeyword) {
            badge.textContent = (avg ? `★ ${avg}\u00A0\u00A0\u00A0\u00A0` : '') + `${reviews.length} / ${allReviews.length}件`;
          } else {
            badge.textContent = (avg ? `★ ${avg}\u00A0\u00A0\u00A0\u00A0` : '') + `${allReviews.length}件`;
          }
        }

        list.replaceChildren();

        if (!allReviews.length) {
          const empty = el('div', 'reviews-empty');
          empty.append(el('div', 'empty-icon', '📋'), el('p', '', 'まだ投稿されたレビューはありません。'));
          list.appendChild(empty);
          return;
        }

        if (!reviews.length) {
          const empty = el('div', 'reviews-empty');
          empty.append(el('div', 'empty-icon', '🔍'), el('p', '', '条件に一致するレビューがありません。'));
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

          const d = new Date(rv.date + 'T00:00:00');
          const formatted = `${d.getFullYear()}/${String(d.getMonth()+1).padStart(2,'0')}/${String(d.getDate()).padStart(2,'0')}`;
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
            img.alt = 'レビュー写真';
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
