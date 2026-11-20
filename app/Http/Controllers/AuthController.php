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
}
