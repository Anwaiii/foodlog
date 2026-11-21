@extends('layouts.app')
@section('title', __('パスワード再設定メール送信 — FoodLog'))

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">✉️ {{ __('パスワード再設定') }}</div>
        <h1>{{ __('パスワードをお忘れですか？') }}</h1>
        <p>{{ __('ご登録のメールアドレスを入力してください。パスワード再設定用のリンクをメールでお送りします。') }}</p>
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

      <form method="POST" action="/foodlog/forgot-password">
        @csrf
        <div class="form-group">
          <label for="email">{{ __('登録メールアドレス') }}</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus />
        </div>
        <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;margin-top:0.5rem;">
          {{ __('再設定メールを送信する') }}
        </button>
      </form>

      <div class="auth-footer">
        <a href="/foodlog/login" class="back-link">{{ __('← ログイン画面に戻る') }}</a>
      </div>
    </div>
  </main>
@endsection
