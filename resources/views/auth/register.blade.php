@extends('layouts.app')
@section('title', __('新規会員登録 — FoodLog'))

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">✨ {{ __('新規会員登録') }}</div>
        <h1>{{ __('新規会員登録') }}</h1>
        <p>{{ __('必要事項を入力してアカウントを作成してください（会員IDはシステムが自動採番します）。') }}</p>
      </div>

      @if($errors->any())
        <div class="alert-error">
          @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="/foodlog/register">
        @csrf
        <div class="form-group">
          <label for="name">{{ __('お名前') }}</label>
          <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="100" required autofocus />
        </div>
        <div class="form-group">
          <label for="name_kana">{{ __('お名前（カナ）') }}</label>
          <input type="text" id="name_kana" name="name_kana" value="{{ old('name_kana') }}" maxlength="100" required />
        </div>
        <div class="form-group">
          <label for="nickname">{{ __('ニックネーム（画面表示名）') }}</label>
          <input type="text" id="nickname" name="nickname" value="{{ old('nickname') }}" maxlength="50" required />
        </div>
        <div class="form-group">
          <label for="email">{{ __('メールアドレス') }}</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" required />
        </div>
        <div class="form-group">
          <label for="password">{{ __('パスワード（8文字以上）') }}</label>
          <input type="password" id="password" name="password" minlength="8" maxlength="100" required />
        </div>
        <div class="form-group">
          <label for="password_confirmation">{{ __('パスワード（確認）') }}</label>
          <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" maxlength="100" required />
        </div>
        <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;margin-top:0.4rem;">
          {{ __('会員登録する') }}
        </button>
      </form>

      <div class="auth-footer" style="display:flex;flex-direction:column;gap:0.5rem;align-items:center;">
        <a href="/foodlog/login" class="back-link">{{ __('すでにアカウントをお持ちの方はこちら（ログイン）') }}</a>
        <a href="/foodlog/" class="back-link">{{ __('← ホーム画面に戻る') }}</a>
      </div>
    </div>
  </main>
@endsection
