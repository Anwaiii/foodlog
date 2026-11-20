<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
</head>
<body style="font-family:sans-serif;background:#f5f5f7;padding:24px;color:#222;">
  <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:12px;padding:28px;border:1px solid #e5e5ea;">
    <h2 style="margin-top:0;color:#ff6b35;">🍜 FoodLog パスワード再設定</h2>
    <p>{{ $member->name }}（{{ $member->nickname }}）様</p>
    <p>FoodLog のパスワード再設定リクエストを受け付けました。<br/>
       以下のボタン（またはリンク）をクリックして、新しいパスワードを設定してください。</p>
    <p style="margin:24px 0;text-align:center;">
      <a href="{{ $resetUrl }}"
         style="display:inline-block;background:#ff6b35;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 24px;border-radius:8px;">
        パスワードを再設定する
      </a>
    </p>
    <p style="font-size:12px;color:#666;word-break:break-all;">
      URL: <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
    </p>
    <hr style="border:none;border-top:1px solid #eee;margin:20px 0;" />
    <p style="font-size:12px;color:#888;margin-bottom:0;">
      ※このリンクの有効期限は 60分 です。<br/>
      ※お心当たりがない場合は、このメールを破棄してください。
    </p>
  </div>
</body>
</html>
