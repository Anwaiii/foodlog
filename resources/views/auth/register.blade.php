@extends('layouts.app')
@section('title', '新規会員登録 — FoodLog')

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">✨ Sign Up</div>
        <h1>新規会員登録</h1>
        <p>必要事項を入力してアカウントを作成してください（登録後すぐにご利用いただけます）。</p>
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
          <label for="member_id">会員ID（半角英数字・3〜50文字）</label>
          <input type="text" id="member_id" name="member_id" value="{{ old('member_id') }}"
                 placeholder="e.g. tanaka_01" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_-]+" required autofocus />
        </div>
        <div class="form-group">
          <label for="name">お名前</label>
          <input type="text" id="name" name="name" value="{{ old('name') }}"
                 placeholder="e.g. 田中 太郎" maxlength="100" required />
        </div>
        <div class="form-group">
          <label for="name_kana">お名前（カナ）</label>
          <input type="text" id="name_kana" name="name_kana" value="{{ old('name_kana') }}"
                 placeholder="e.g. タナカ タロウ" maxlength="100" required />
        </div>
        <div class="form-group">
          <label for="nickname">ニックネーム（画面表示名）</label>
          <input type="text" id="nickname" name="nickname" value="{{ old('nickname') }}"
                 placeholder="e.g. Taro" maxlength="50" required />
        </div>
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}"
                 placeholder="e.g. taro@example.com" maxlength="255" required />
        </div>
        <div class="form-group">
          <label for="password">パスワード（8文字以上）</label>
          <input type="password" id="password" name="password" minlength="8" maxlength="100"
                 placeholder="8文字以上で入力" required />
        </div>
        <div class="form-group">
          <label for="password_confirmation">パスワード（確認）</label>
          <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" maxlength="100"
                 placeholder="もう一度入力" required />
        </div>
        <button type="submit" class="btn-submit" style="width:100%;padding:0.85rem;margin-top:0.4rem;">
          会員登録する
        </button>
      </form>

      <div class="auth-footer" style="display:flex;flex-direction:column;gap:0.5rem;align-items:center;">
        <a href="/foodlog/login" class="back-link">すでにアカウントをお持ちの方はこちら（ログイン）</a>
        <a href="/foodlog/" class="back-link">← ホーム画面に戻る</a>
      </div>
    </div>
  </main>
@endsection
