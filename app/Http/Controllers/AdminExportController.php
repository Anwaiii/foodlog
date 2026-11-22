<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, '管理者権限が必要です。');
        }
    }

    public function restaurantsCsv(): StreamedResponse
    {
        $this->ensureAdmin();

        $filename = 'foodlog_restaurants_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            // Excel 文字化け防止の UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', '店名', 'カテゴリ', 'エリア', '予算帯', '住所', '紹介文', 'レビュー数', '平均評価', '登録日時']);

            Restaurant::query()
                ->withCount('reviews')
                ->withAvg('reviews as avg_rating', 'rating')
                ->orderBy('id')
                ->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->id,
                            $r->name,
                            $r->category,
                            $r->area,
                            $r->price_range,
                            $r->address,
                            $r->description,
                            $r->reviews_count,
                            $r->avg_rating ? round((float) $r->avg_rating, 1) : '',
                            $r->created_at,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function reviewsCsv(): StreamedResponse
    {
        $this->ensureAdmin();

        $filename = 'foodlog_reviews_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', '訪問日', '店舗名', '投稿者ニックネーム', '会員コード', '評価', '注文メニュー', '感想', '役に立った数', '登録日時']);

            Review::with(['restaurant:id,name', 'member:id,nickname,member_code'])
                ->withCount('likes')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $rv) {
                        fputcsv($out, [
                            $rv->id,
                            $rv->date,
                            $rv->restaurant?->name ?? '',
                            $rv->member?->nickname ?? '',
                            $rv->member?->member_code ?? '',
                            $rv->rating,
                            $rv->order_details,
                            $rv->impression,
                            $rv->likes_count,
                            $rv->created_at,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
