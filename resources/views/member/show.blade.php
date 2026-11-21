@extends('layouts.app')
@section('title', __('会員情報の確認 — FoodLog'))

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">👤 {{ __('会員情報の確認') }}</div>
        <h1>{{ __('会員情報の確認') }}</h1>
        <p>{{ __('現在ログイン中の会員情報です。') }}</p>
      </div>

      @if(session('status'))
        <div class="alert-success">{{ session('status') }}</div>
      @endif

      <div class="profile-list">
        <div class="profile-row">
          <span class="profile-label">{{ __('会員ID') }}</span>
          <span class="profile-value">{{ $member->member_id }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">{{ __('名前') }}</span>
          <span class="profile-value">{{ $member->name }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">{{ __('名前（カナ）') }}</span>
          <span class="profile-value">{{ $member->name_kana }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">{{ __('ニックネーム') }}</span>
          <span class="profile-value">{{ $member->nickname }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">{{ __('メールアドレス') }}</span>
          <span class="profile-value">{{ $member->email }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-label">{{ __('会員タイプ') }}</span>
          <span class="profile-value">
            @if($member->isAdmin())
              <span class="role-badge admin">👑 {{ __('管理者') }}</span>
            @else
              <span class="role-badge general">{{ __('一般会員') }}</span>
            @endif
          </span>
        </div>
        <div class="profile-row">
          <span class="profile-label">{{ __('登録日時') }}</span>
          <span class="profile-value">{{ $member->created_at?->format('Y/m/d H:i') }}</span>
        </div>
      </div>

      <div class="modal-actions" style="margin-top:1.8rem;flex-wrap:wrap;">
        <a href="/foodlog/" class="btn-cancel" style="text-decoration:none;text-align:center;">{{ __('ホームへ戻る') }}</a>
        <a href="/foodlog/users/{{ $member->id }}" class="btn-cancel" style="text-decoration:none;text-align:center;">{{ __('📖 自分のレビュー履歴') }}</a>
        <a href="/foodlog/member/password" class="btn-submit" style="text-decoration:none;text-align:center;">{{ __('🔒 パスワード変更') }}</a>
      </div>
    </div>
  </main>
@endsection
