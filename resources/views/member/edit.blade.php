@extends('layouts.app')
@section('title', __('会員情報の編集 — FoodLog'))

@section('content')
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="hero-tag">✏️ {{ __('会員情報の編集') }}</div>
        <h1>{{ __('会員情報の編集') }}</h1>
        <p>{{ $member->nickname }}（{{ $member->member_code }}）</p>
      </div>

      @if($errors->any())
        <div class="alert-error">
          @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="/foodlog/member/edit" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
          <label>{{ __('プロフィールアイコン') }}</label>
          <div class="avatar-edit-row">
            <div class="avatar-preview-wrap" id="avatar-preview-wrap">
              @if($member->avatar)
                <img src="/foodlog/{{ $member->avatar }}" alt="{{ $member->nickname }}" class="avatar-img avatar-lg" id="avatar-preview-img" />
              @else
                <span class="avatar-initial avatar-lg" id="avatar-preview-initial" style="background:{{ $member->avatarColor() }};">{{ $member->avatarInitial() }}</span>
                <img src="" alt="" class="avatar-img avatar-lg" id="avatar-preview-img" style="display:none;" />
              @endif
            </div>
            <div class="avatar-edit-controls">
              <label for="avatar-input" class="admin-action-btn" style="display:inline-block;cursor:pointer;margin-bottom:0.4rem;">
                📷 {{ __('アイコン画像を選択') }}
              </label>
              <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display:none;" />
              <div style="font-size:0.76rem;color:var(--muted);">{{ __('JPG / PNG / WebP（自動で正方形リサイズ）') }}</div>
              @if($member->avatar)
                <label class="remove-avatar-check">
                  <input type="checkbox" name="remove_avatar" value="1" />
                  <span>{{ __('アイコンを削除して初期表示に戻す') }}</span>
                </label>
              @endif
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="name">{{ __('お名前') }}</label>
          <input type="text" id="name" name="name" value="{{ old('name', $member->name) }}" maxlength="100" required />
        </div>

        <div class="form-group">
          <label for="name_kana">{{ __('お名前（カナ）') }}</label>
          <input type="text" id="name_kana" name="name_kana" value="{{ old('name_kana', $member->name_kana) }}" maxlength="100" required />
        </div>

        <div class="form-group">
          <label for="nickname">{{ __('ニックネーム（画面表示名）') }}</label>
          <input type="text" id="nickname" name="nickname" value="{{ old('nickname', $member->nickname) }}" maxlength="50" required />
          <div style="font-size:0.75rem;color:var(--muted);margin-top:0.3rem;">{{ __('※変更すると過去のレビュー表示名も自動で更新されます。') }}</div>
        </div>

        <div class="form-group">
          <label for="email">{{ __('メールアドレス') }}</label>
          <input type="email" id="email" name="email" value="{{ old('email', $member->email) }}" maxlength="255" required />
        </div>

        <div class="modal-actions">
          <a href="/foodlog/member" class="btn-cancel" style="text-decoration:none;text-align:center;">{{ __('戻る') }}</a>
          <button type="submit" class="btn-submit">{{ __('変更を保存') }}</button>
        </div>
      </form>
    </div>
  </main>
@endsection

@section('scripts')
  <script>
    (function () {
      const input = document.getElementById('avatar-input');
      const img = document.getElementById('avatar-preview-img');
      const initial = document.getElementById('avatar-preview-initial');
      if (input && img) {
        input.addEventListener('change', function () {
          const file = input.files && input.files[0];
          if (!file) return;
          const reader = new FileReader();
          reader.onload = function (e) {
            img.src = e.target.result;
            img.style.display = 'inline-block';
            if (initial) initial.style.display = 'none';
          };
          reader.readAsDataURL(file);
        });
      }
    })();
  </script>
@endsection
