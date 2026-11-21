@extends('layouts.app')
@section('title', 'パスワード変更 — FoodLog')

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">🔒 Change Password</div>
        <h1>パスワード変更</h1>
        <p>{{ $member->nickname }}（{{ $member->member_id }}）のパスワードを変更します。</p>
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

      <form method="POST" action="/foodlog/member/password">
        @csrf
        <div class="form-group">
          <label for="current_password">現在のパスワード</label>
          <input type="password" id="current_password" name="current_password" required />
        </div>
        <div class="form-group">
          <label for="password">新しいパスワード（8文字以上）</label>
          <input type="password" id="password" name="password" minlength="8" required />
        </div>
        <div class="form-group">
          <label for="password_confirmation">新しいパスワード（確認）</label>
          <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" required />
        </div>
        <div class="modal-actions">
          <a href="/foodlog/member" class="btn-cancel" style="text-decoration:none;text-align:center;">戻る</a>
          <button type="submit" class="btn-submit">パスワードを変更する</button>
        </div>
      </form>
    </div>
  </main>
@endsection
