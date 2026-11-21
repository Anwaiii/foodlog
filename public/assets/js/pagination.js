// ─── 共通ページネーションUI（SCR-01 TOP / SCR-02 詳細 共通） ──────────────────
(function (global) {
  function pgEl(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined && text !== null) node.textContent = text;
    return node;
  }

  /**
   * 共通ページネーションを描画する
   * @param {HTMLElement} container 描画先要素
   * @param {{ currentPage: number, totalPages: number, onPageChange: (page: number) => void }} opts
   */
  function renderPagination(container, opts) {
    if (!container) return;
    const { currentPage, totalPages, onPageChange } = opts;
    if (totalPages <= 1 || typeof onPageChange !== 'function') return;

    const nav = pgEl('div', 'pagination');

    const prevBtn = pgEl('button', 'pagination-btn', '← Prev');
    prevBtn.id = 'pg-prev';
    prevBtn.type = 'button';
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener('click', () => onPageChange(currentPage - 1));

    const numbersWrap = pgEl('div', 'pagination-numbers');
    const appendPageNum = p => {
      const b = pgEl('button', `pagination-num ${currentPage === p ? 'active' : ''}`.trim(), String(p));
      b.type = 'button';
      b.dataset.page = String(p);
      b.addEventListener('click', () => onPageChange(p));
      numbersWrap.appendChild(b);
    };

    appendPageNum(1);
    const windowSize = 9;
    let pStart = Math.max(2, currentPage - 4);
    let pEnd = Math.min(totalPages, pStart + windowSize - 1);
    if (pEnd - pStart + 1 < windowSize) {
      pStart = Math.max(2, pEnd - windowSize + 1);
    }
    if (pStart > 2) numbersWrap.appendChild(pgEl('span', 'pagination-ellipsis', '…'));
    for (let p = pStart; p <= pEnd; p++) appendPageNum(p);
    if (pEnd < totalPages) numbersWrap.appendChild(pgEl('span', 'pagination-ellipsis', '…'));

    const nextBtn = pgEl('button', 'pagination-btn', 'Next →');
    nextBtn.id = 'pg-next';
    nextBtn.type = 'button';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener('click', () => onPageChange(currentPage + 1));

    const jumpWrap = pgEl('div', 'pagination-jump');
    const jumpInput = pgEl('input', 'pagination-input');
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
          onPageChange(num);
        }
      }
    });

    jumpWrap.append(jumpInput, pgEl('span', 'pagination-total', `/ ${totalPages}`));
    nav.append(prevBtn, numbersWrap, nextBtn, jumpWrap);
    container.appendChild(nav);
  }

  global.renderPagination = renderPagination;
})(window);
