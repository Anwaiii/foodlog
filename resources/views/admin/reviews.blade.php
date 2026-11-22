@extends('layouts.app')
@section('title', __('レビュー監視（管理者） — FoodLog'))

@section('content')
  <section class="stats-hero">
    <div class="hero-tag">📝 {{ __('管理者') }}</div>
    <h1>{{ __('レビュー監視') }} <span class="grad">{{ __('一覧') }}</span></h1>
    <p>{{ __('全店舗の投稿レビューを横断確認し、不適切な投稿の削除や CSV エクスポートを行えます。') }}</p>
    <div class="hero-stats" style="margin-top:1.5rem;">
      <div>
        <div class="stat-val">{{ $totalAll }}</div>
        <div class="stat-label">{{ __('総レビュー数') }}</div>
      </div>
      <div>
        <div class="stat-val">{{ $reviews->total() }}</div>
        <div class="stat-label">{{ __('表示該当数') }}</div>
      </div>
    </div>
  </section>

  <main class="section" style="max-width:1380px;">
    @if(session('status'))
      <div class="alert-success">{{ session('status') }}</div>
    @endif

    <div class="section-header" style="flex-wrap:wrap;gap:0.75rem;">
      <div class="section-title">{{ __('投稿レビュー一覧') }}（{{ $reviews->total() }}）</div>
      <div style="display:flex;gap:0.6rem;flex-wrap:wrap;align-items:center;">
        <a href="/foodlog/admin/export/restaurants.csv" class="admin-action-btn" style="text-decoration:none;">📥 {{ __('店舗CSV') }}</a>
        <a href="/foodlog/admin/export/reviews.csv" class="admin-action-btn" style="text-decoration:none;">📥 {{ __('レビューCSV') }}</a>
        <a href="/foodlog/admin/members" class="admin-action-btn" style="text-decoration:none;">🛠️ {{ __('会員管理') }}</a>
        <a href="/foodlog/" class="back-btn">{{ __('← ホームへ戻る') }}</a>
      </div>
    </div>

    <form method="GET" action="/foodlog/admin/reviews" class="admin-filter-form">
      <input type="search" name="q" value="{{ $keyword }}" class="review-search-input" style="min-width:260px;flex:1;" placeholder="{{ __('🔍 店名・投稿者・メニュー・感想を検索...') }}" />
      <select name="rating" class="review-control">
        <option value="all" {{ $rating === 'all' ? 'selected' : '' }}>{{ __('★ すべて') }}</option>
        <option value="5" {{ $rating === '5' ? 'selected' : '' }}>★★★★★ (5)</option>
        <option value="4" {{ $rating === '4' ? 'selected' : '' }}>★★★★ (4)</option>
        <option value="3" {{ $rating === '3' ? 'selected' : '' }}>★★★ (3)</option>
        <option value="2" {{ $rating === '2' ? 'selected' : '' }}>★★ (2)</option>
        <option value="1" {{ $rating === '1' ? 'selected' : '' }}>★ (1)</option>
      </select>
      <button type="submit" class="add-btn" style="padding:0.55rem 1.15rem;font-size:0.85rem;">{{ __('絞り込み') }}</button>
      @if($keyword !== '' || $rating !== 'all')
        <a href="/foodlog/admin/reviews" class="admin-action-btn" style="text-decoration:none;">{{ __('条件をリセット') }}</a>
      @endif
    </form>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>{{ __('訪問日') }}</th>
            <th>{{ __('店名') }}</th>
            <th>{{ __('投稿者') }}</th>
            <th>{{ __('評価') }}</th>
            <th>{{ __('注文メニュー / 感想') }}</th>
            <th>👍</th>
            <th>{{ __('操作') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reviews as $rv)
            <tr>
              <td>{{ $rv->date }}</td>
              <td>
                @if($rv->restaurant)
                  <a href="/foodlog/restaurant.php?id={{ $rv->restaurant->id }}" class="review-restaurant-link">{{ $rv->restaurant->name }}</a>
                @else
                  <span style="color:var(--muted);">—</span>
                @endif
              </td>
              <td>
                @if($rv->member)
                  <a href="/foodlog/users/{{ $rv->member->id }}" class="review-author review-author-link">
                    @if($rv->member->avatar)
                      <img src="/foodlog/{{ $rv->member->avatar }}" alt="" class="avatar-img avatar-xs" />
                    @else
                      <span class="avatar-initial avatar-xs" style="background:{{ $rv->member->avatarColor() }};">{{ $rv->member->avatarInitial() }}</span>
                    @endif
                    <span>{{ $rv->member->nickname }}</span>
                  </a>
                @else
                  <span style="color:var(--muted);">—</span>
                @endif
              </td>
              <td>{{ $rv->rating ? str_repeat('⭐', (int) $rv->rating) : '—' }}</td>
              <td style="white-space:normal;max-width:420px;line-height:1.45;">
                <div style="font-weight:700;margin-bottom:0.2rem;">🍽️ {{ $rv->order_details }}</div>
                <div style="color:var(--muted);font-size:0.82rem;">{{ $rv->impression }}</div>
              </td>
              <td>{{ $rv->likes_count }}</td>
              <td>
                <form method="POST" action="/foodlog/admin/reviews/{{ $rv->id }}/delete" style="margin:0;">
                  @csrf
                  <button type="submit" class="admin-action-btn danger"
                          onclick="return confirm('{{ __('このレビューを削除しますか？') }}');">
                    🗑 {{ __('削除') }}
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align:center;padding:2rem;color:var(--muted);">{{ __('条件に一致するレビューがありません。') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($reviews->lastPage() > 1)
      <div class="pagination" style="margin-top:1.5rem;">
        @if($reviews->onFirstPage())
          <button type="button" class="pagination-btn" disabled>{{ __('← 前へ') }}</button>
        @else
          <a href="{{ $reviews->previousPageUrl() }}" class="pagination-btn" style="text-decoration:none;">{{ __('← 前へ') }}</a>
        @endif
        <span class="pagination-total" style="padding:0 0.75rem;">{{ $reviews->currentPage() }} / {{ $reviews->lastPage() }}</span>
        @if($reviews->hasMorePages())
          <a href="{{ $reviews->nextPageUrl() }}" class="pagination-btn" style="text-decoration:none;">{{ __('次へ →') }}</a>
        @else
          <button type="button" class="pagination-btn" disabled>{{ __('次へ →') }}</button>
        @endif
      </div>
    @endif
  </main>
@endsection
