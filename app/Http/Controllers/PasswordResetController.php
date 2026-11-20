<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email'    => '正しいメールアドレス形式で入力してください。',
        ]);

        $email = trim($request->input('email'));

        $member = Member::where('email', $email)
            ->where('delete_flg', false)
            ->first();

        if (!$member) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'このメールアドレスは登録されていません。']);
        }

        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $member->email],
            [
                'token'      => Hash::make($plainToken),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = $request->getSchemeAndHttpHost()
            . '/foodlog/reset-password/' . $plainToken
            . '?email=' . urlencode($member->email);

        try {
            Mail::send('emails.password-reset', [
                'member'   => $member,
                'resetUrl' => $resetUrl,
            ], function ($message) use ($member) {
                $message->to($member->email, $member->name)
                        ->subject('【FoodLog】パスワード再設定のご案内');
            });
        } catch (Throwable $e) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'メールの送信に失敗しました。.env の Gmail SMTP 設定を確認してください。（' . $e->getMessage() . '）']);
        }

        return back()->with('status', 'パスワード再設定用のメールを送信しました。メール内のリンクから60分以内に再設定を行ってください。');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required'     => 'メールアドレスを入力してください。',
            'email.email'        => '正しいメールアドレス形式で入力してください。',
            'password.required'  => '新しいパスワードを入力してください。',
            'password.min'       => '新しいパスワードは8文字以上で入力してください。',
            'password.confirmed' => '新しいパスワード（確認）が一致しません。',
        ]);

        $email = trim($request->input('email'));
        $token = $request->input('token');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (
            !$record ||
            !Carbon::parse($record->created_at)->addMinutes(60)->isFuture() ||
            !Hash::check($token, $record->token)
        ) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => '再設定リンクが無効か、有効期限（60分）が切れています。もう一度メール送信からやり直してください。']);
        }

        $member = Member::where('email', $email)
            ->where('delete_flg', false)
            ->first();

        if (!$member) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => '対象の会員が見つかりません。']);
        }

        $member->update([
            'password' => $request->input('password'),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect('/foodlog/login')
            ->with('status', 'パスワードを再設定しました。新しいパスワードでログインしてください。');
    }
}
