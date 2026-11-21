<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function show(): View
    {
        return view('member.show', [
            'member' => Auth::user(),
        ]);
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
            'isAdmin'         => $target->isAdmin(),
            'totalReviews'    => $reviews->count(),
            'restaurantCount' => $restaurantCount,
            'avgRating'       => $avgRating,
            'reviewsPayload'  => $reviewsPayload,
        ]);
    }
}
