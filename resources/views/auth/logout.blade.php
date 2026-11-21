@extends('layouts.app')
@section('title', 'ログアウト完了 — FoodLog')

@section('content')
  <main class="auth-container">
    <div class="auth-card" style="text-align:center;">
      <div style="font-size:3rem;margin-bottom:1rem;">👋</div>
      <h1 style="font-size:1.6rem;font-weight:800;margin-bottom:0.6rem;">ログアウトしました</h1>
      <p style="color:var(--muted);font-size:0.95rem;margin-bottom:2rem;">
        ご利用ありがとうございました。またのご利用をお待ちしております。
      </p>
      <div style="display:flex;gap:0.75rem;flex-wrap:wrap;justify-content:center;">
        <a href="/foodlog/" class="btn-cancel" style="text-decoration:none;text-align:center;">ホームへ戻る</a>
        <a href="/foodlog/login" class="btn-submit" style="text-decoration:none;text-align:center;">再ログイン</a>
      </div>
    </div>
  </main>
@endsection
