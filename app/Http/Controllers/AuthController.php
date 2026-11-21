<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/foodlog/');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ], [
            'login_id.required' => '会員IDまたはメールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
        ]);

        $loginId = trim($credentials['login_id']);

        $member = Member::where('delete_flg', false)
            ->where(function ($query) use ($loginId) {
                $query->where('member_id', $loginId)
                      ->orWhere('email', $loginId);
            })
            ->first();

        if (!$member || !Hash::check($credentials['password'], $member->password)) {
            return back()
                ->withInput($request->only('login_id'))
                ->withErrors(['login_id' => '会員ID・メールアドレス、またはパスワードが正しくありません。']);
        }

        Auth::login($member);
        $request->session()->regenerate();

        return redirect()->intended('/foodlog/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/foodlog/logout');
    }

    public function showLogout(): View
    {
        return view('auth.logout');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/foodlog/');
        }
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:members,member_id'],
            'name'      => ['required', 'string', 'max:100'],
            'name_kana' => ['required', 'string', 'max:100'],
            'nickname'  => ['required', 'string', 'max:50'],
            'email'     => ['required', 'email', 'max:255', 'unique:members,email'],
            'password'  => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ], [
            'member_id.required' => '会員IDを入力してください。',
            'member_id.min'      => '会員IDは3文字以上で入力してください。',
            'member_id.max'      => '会員IDは50文字以内で入力してください。',
            'member_id.regex'    => '会員IDは半角英数字・ハイフン・アンダースコアのみ使用できます。',
            'member_id.unique'   => 'この会員IDはすでに使用されています。',
            'name.required'      => 'お名前を入力してください。',
            'name_kana.required' => 'お名前（カナ）を入力してください。',
            'nickname.required'  => 'ニックネームを入力してください。',
            'email.required'     => 'メールアドレスを入力してください。',
            'email.email'        => '正しいメールアドレス形式で入力してください。',
            'email.unique'       => 'このメールアドレスはすでに登録されています。',
            'password.required'  => 'パスワードを入力してください。',
            'password.min'       => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワード（確認）が一致しません。',
        ]);

        $member = Member::create([
            'member_id'   => trim($validated['member_id']),
            'name'        => trim($validated['name']),
            'name_kana'   => trim($validated['name_kana']),
            'nickname'    => trim($validated['nickname']),
            'email'       => trim($validated['email']),
            'password'    => $validated['password'],
            'member_type' => Member::TYPE_GENERAL,
            'delete_flg'  => false,
        ]);

        Auth::login($member);
        $request->session()->regenerate();

        return redirect('/foodlog/');
    }
}
