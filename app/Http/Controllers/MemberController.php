<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Review;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function show(): View
    {
        return view('member.show', [
            'member' => Auth::user(),
        ]);
    }

    public function editProfile(): View
    {
        return view('member.edit', [
            'member' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var Member $member */
        $member = Auth::user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'name_kana'     => ['required', 'string', 'max:100'],
            'nickname'      => ['required', 'string', 'max:50'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member->id)],
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'name.required'      => 'お名前を入力してください。',
            'name_kana.required' => 'お名前（カナ）を入力してください。',
            'nickname.required'  => 'ニックネームを入力してください。',
            'email.required'     => 'メールアドレスを入力してください。',
            'email.email'        => '正しいメールアドレス形式で入力してください。',
            'email.unique'       => 'このメールアドレスはすでに使用されています。',
            'avatar.image'       => 'アイコンには画像ファイルを選択してください。',
            'avatar.max'         => 'アイコン画像は5MB以内でアップロードしてください。',
        ]);

        $updateData = [
            'name'      => trim($validated['name']),
            'name_kana' => trim($validated['name_kana']),
            'nickname'  => trim($validated['nickname']),
            'email'     => trim($validated['email']),
        ];

        if ($request->boolean('remove_avatar')) {
            $updateData['avatar'] = null;
        } elseif ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatarDir = public_path('assets/images/avatars/');
            $updateData['avatar'] = ImageOptimizer::saveOptimized(
                $request->file('avatar'),
                $avatarDir,
                'assets/images/avatars',
                'avatar_' . $member->id,
                320,
                true,
                84
            );
        }

        $member->update($updateData);

        return redirect('/foodlog/member')->with('status', __('会員情報を更新しました。'));
    }

    public function editPassword(): View
    {
        return view('member.password', [
            'member' => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => '現在のパスワードを入力してください。',
            'password.required'         => '新しいパスワードを入力してください。',
            'password.min'              => '新しいパスワードは8文字以上で入力してください。',
            'password.confirmed'        => '新しいパスワード（確認）が一致しません。',
        ]);

        $member = Auth::user();

        if (!Hash::check($request->input('current_password'), $member->password)) {
            return back()->withErrors([
                'current_password' => '現在のパスワードが正しくありません。',
            ]);
        }

        $member->update([
            'password' => $request->input('password'),
        ]);

        return back()->with('status', 'パスワードを変更しました。');
    }

    public function publicProfile(int $id): View
    {
        $target = Member::where('id', $id)
            ->where('delete_flg', false)
            ->firstOrFail();

        $reviews = Review::with('restaurant')
            ->where('member_id', $target->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $rated = $reviews->filter(fn ($rv) => (int) ($rv->rating ?? 0) > 0);
        $avgRating = $rated->count() > 0
            ? round($rated->avg('rating'), 1)
            : null;
        $restaurantCount = $reviews->pluck('restaurant_id')->unique()->count();

        $reviewsPayload = $reviews->map(function ($rv) {
            return [
                'id'              => $rv->id,
                'restaurant_id'   => $rv->restaurant_id,
                'restaurant_name' => $rv->restaurant?->name ?? 'Unknown Restaurant',
                'date'            => $rv->date,
                'order_details'   => $rv->order_details,
                'impression'      => $rv->impression,
                'rating'          => $rv->rating,
                'image'           => $rv->image,
            ];
        })->values();

        return view('member.reviews', [
            'nickname'        => $target->nickname,
            'avatar'          => $target->avatar,
            'avatarInitial'   => $target->avatarInitial(),
            'avatarColor'     => $target->avatarColor(),
            'isAdmin'         => $target->isAdmin(),
            'totalReviews'    => $reviews->count(),
            'restaurantCount' => $restaurantCount,
            'avgRating'       => $avgRating,
            'reviewsPayload'  => $reviewsPayload,
        ]);
    }
}
