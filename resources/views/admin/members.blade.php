@extends('layouts.app')
@section('title', '会員管理（管理者） — FoodLog')

@section('content')
  <section class="stats-hero">
    <div class="hero-tag">🛠️ Admin Dashboard</div>
    <h1>会員管理 <span class="grad">一覧</span></h1>
    <p>登録会員のステータス確認およびアカウント有効／停止の切り替えができます。</p>
    <div class="hero-stats" style="margin-top:1.5rem;">
      <div>
        <div class="stat-val">{{ $totalCount }}</div>
        <div class="stat-label">総会員数</div>
      </div>
      <div>
        <div class="stat-val">{{ $activeCount }}</div>
        <div class="stat-label">有効会員</div>
      </div>
      <div>
        <div class="stat-val">{{ $disabledCount }}</div>
        <div class="stat-label">停止中</div>
      </div>
    </div>
  </section>

  <main class="section" style="max-width:1280px;">
    @if(session('status'))
      <div class="alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
      <div class="alert-error">
        @foreach($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif

    <div class="section-header">
      <div class="section-title">登録会員一覧（{{ $totalCount }}名）</div>
      <a href="/foodlog/" class="back-btn">← ホームへ戻る</a>
    </div>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>会員ID</th>
            <th>ニックネーム</th>
            <th>名前（カナ）</th>
            <th>メールアドレス</th>
            <th>種別</th>
            <th>レビュー数</th>
            <th>状態</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($members as $m)
            <tr class="{{ $m->delete_flg ? 'is-disabled' : '' }}">
              <td><code>{{ $m->member_id }}</code></td>
              <td>
                @if(!$m->delete_flg)
                  <a href="/foodlog/users/{{ $m->id }}" class="review-author review-author-link">
                    {{ $m->isAdmin() ? '👑' : '👤' }} {{ $m->nickname }}
                  </a>
                @else
                  <span>{{ $m->nickname }}</span>
                @endif
              </td>
              <td>{{ $m->name }}<br><span style="font-size:0.75rem;color:var(--muted);">{{ $m->name_kana }}</span></td>
              <td>{{ $m->email }}</td>
              <td>
                @if($m->isAdmin())
                  <span class="role-badge admin">👑 管理者</span>
                @else
                  <span class="role-badge general">一般会員</span>
                @endif
              </td>
              <td>{{ $m->reviews_count }}件</td>
              <td>
                @if($m->delete_flg)
                  <span class="status-badge disabled">停止中</span>
                @else
                  <span class="status-badge active">有効</span>
                @endif
              </td>
              <td>
                @if($m->id === Auth::id())
                  <span style="font-size:0.78rem;color:var(--muted);">ログイン中</span>
                @else
                  <form method="POST" action="/foodlog/admin/members/{{ $m->id }}/toggle-status" style="margin:0;">
                    @csrf
                    <button type="submit" class="admin-action-btn {{ $m->delete_flg ? '' : 'danger' }}"
                            onclick="return confirm('会員「{{ $m->nickname }}」を{{ $m->delete_flg ? '有効化' : '停止' }}しますか？');">
                      {{ $m->delete_flg ? '✅ 有効化' : '⛔ 停止' }}
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </main>
@endsection
