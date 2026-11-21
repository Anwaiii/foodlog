<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminMemberController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, '管理者権限が必要です。');
        }
    }

    public function index(): View
    {
        $this->ensureAdmin();

        $members = Member::withCount('reviews')
            ->orderBy('member_type', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.members', [
            'members'       => $members,
            'totalCount'    => $members->count(),
            'activeCount'   => $members->where('delete_flg', false)->count(),
            'disabledCount' => $members->where('delete_flg', true)->count(),
        ]);
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $this->ensureAdmin();

        $target = Member::findOrFail($id);

        if ($target->id === Auth::id()) {
            return back()->withErrors([
                'status' => '自分自身のアカウントは停止できません。',
            ]);
        }

        $target->delete_flg = !$target->delete_flg;
        $target->save();

        $label = $target->delete_flg ? '停止' : '有効化';

        return back()->with('status', "会員「{$target->nickname}（{$target->member_id}）」を{$label}しました。");
    }
}
