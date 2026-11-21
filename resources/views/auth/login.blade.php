@extends('layouts.app')
@section('title', __('ログイン — FoodLog'))

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">🔑 {{ __('ログイン') }}</div>
        <h1>{{ __('FoodLog にログイン') }}</h1>
        <p>{{ __('会員ID または メールアドレスとパスワードを入力してください。') }}</p>
      </div>

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

      <form method="POST" action="/foodlog/login">
        @csrf
        <div class="form-group">
          <label for="login_id">{{ __('会員ID または メールアドレス') }}</label>
          <input type="text" id="login_id" name="login_id" value="{{ old('login_id') }}"
                 placeholder="e.g. user01 / user01@foodlog.local" required autofocus />
        </div>
        <div class="form-group">
          <label for="password">{{ __('パスワード') }}</label>
          <input type="password" id="password" name="password" required />
        </div>
        <div style="text-align:right;margin-top:-0.4rem;margin-bottom:1rem;">
          <a href="/foodlog/forgot-password" class="back-link">{{ __('パスワードをお忘れですか？') }}</a>
        </div>
        <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;margin-top:0.2rem;">
          {{ __('ログイン') }}
        </button>
      </form>

      <div class="auth-footer" style="display:flex;flex-direction:column;gap:0.5rem;align-items:center;">
        <a href="/foodlog/register" class="back-link">{{ __('✨ アカウントをお持ちでない方はこちら（新規会員登録）') }}</a>
        <a href="/foodlog/" class="back-link">{{ __('← ホーム画面に戻る') }}</a>
      </div>
    </div>
  </main>
@endsection
