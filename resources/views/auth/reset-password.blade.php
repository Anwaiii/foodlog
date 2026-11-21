@extends('layouts.app')
@section('title', __('新しいパスワードの設定 — FoodLog'))

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">🔒 {{ __('パスワード再設定') }}</div>
        <h1>{{ __('パスワード再設定') }}</h1>
        <p>{{ __('新しいパスワード（8文字以上）を入力してください。') }}</p>
      </div>

      @if($errors->any())
        <div class="alert-error">
          @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="/foodlog/reset-password">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}" />
        <div class="form-group">
          <label for="email">{{ __('メールアドレス') }}</label>
          <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required />
        </div>
        <div class="form-group">
          <label for="password">{{ __('新しいパスワード（8文字以上）') }}</label>
          <input type="password" id="password" name="password" minlength="8" required autofocus />
        </div>
        <div class="form-group">
          <label for="password_confirmation">{{ __('新しいパスワード（確認）') }}</label>
          <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" required />
        </div>
        <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;margin-top:0.5rem;">
          {{ __('パスワードを再設定する') }}
        </button>
      </form>

      <div class="auth-footer">
        <a href="/foodlog/login" class="back-link">{{ __('← ログイン画面に戻る') }}</a>
      </div>
    </div>
  </main>
@endsection
