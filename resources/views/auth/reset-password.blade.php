@extends('layouts.app')
@section('title', '新しいパスワードの設定 — FoodLog')

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">🔒 New Password</div>
        <h1>パスワード再設定</h1>
        <p>新しいパスワード（8文字以上）を入力してください。</p>
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
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required />
        </div>
        <div class="form-group">
          <label for="password">新しいパスワード（8文字以上）</label>
          <input type="password" id="password" name="password" minlength="8" placeholder="8文字以上で入力" required autofocus />
        </div>
        <div class="form-group">
          <label for="password_confirmation">新しいパスワード（確認）</label>
          <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" placeholder="もう一度入力" required />
        </div>
        <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;margin-top:0.5rem;">
          パスワードを再設定する
        </button>
      </form>

      <div class="auth-footer">
        <a href="/foodlog/login" class="back-link">← ログイン画面に戻る</a>
      </div>
    </div>
  </main>
@endsection
