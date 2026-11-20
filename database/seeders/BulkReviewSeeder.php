<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Restaurant;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BulkReviewSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::find(15);
        if (!$restaurant) {
            $this->command?->error('Restaurant ID 15 not found.');
            return;
        }

        $hashedPassword = Hash::make('password123');

        $orders = [
            "特製濃厚ラーメン\n半熟味玉トッピング",
            "黒豚餃子（6個）\nチャーハンセット",
            "ピリ辛担々麺\n小ライス",
            "チーズカレーライス\nラッシー",
            "マルゲリータピザ（Mサイズ）\nシーザーサラダ",
            "牛カルビ焼肉弁当\nわかめスープ",
            "サーモンアボカド丼\nお味噌汁",
            "唐揚げ定食（大盛り）",
            "海老天うどん\nいなり寿司2個",
            "デミグラスハンバーグ\nライス・サラダ付き",
        ];

        $impressions = [
            'スープが熱々でとても美味しかったです。配達も予定より早くて大満足です！',
            'ボリュームたっぷりでコスパ最高でした。またリピートします。',
            '味は良かったですが、少しだけ冷めていたのが惜しかったです。',
            '梱包がとても丁寧で汁漏れも一切ありませんでした。香りも最高で、一口目から最後まで美味しくいただけました。家族にも好評だったので次回は別のメニューも試してみたいです。',
            'ちょうど良い辛さでご飯が進みました！量もちょうど良かったです。',
            'サクサク感が残っていて最高でした。',
            '安定の美味しさです。忙しい日のランチにいつも助かっています。',
            '具材が大きくて食べ応えがありました。ソースとの相性も抜群です。',
            '少し味が濃いめに感じましたが、おつまみとしてはちょうど良かったです。',
            '期待以上のクオリティでした。次は友達にもおすすめしようと思います。',
        ];

        $lastNames  = ['佐藤', '鈴木', '高橋', '田中', '伊藤', '渡辺', '山本', '中村', '小林', '加藤'];
        $lastKanas  = ['サトウ', 'スズキ', 'タカハシ', 'タナカ', 'イトウ', 'ワタナベ', 'ヤマモト', 'ナカムラ', 'コバヤシ', 'カトウ'];
        $firstNames = ['翔太', '美咲', '健太', '陽菜', '大輔', 'さくら', '拓海', '結衣', '蓮', '葵'];
        $firstKanas = ['ショウタ', 'ミサキ', 'ケンタ', 'ヒナ', 'ダイスケ', 'サクラ', 'タクミ', 'ユイ', 'レン', 'アオイ'];

        $today = Carbon::today();

        for ($i = 1; $i <= 50; $i++) {
            $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $memberIdStr = "demo{$num}";
            $lnIdx = ($i - 1) % count($lastNames);
            $fnIdx = intdiv($i - 1, count($lastNames)) % count($firstNames);

            $member = Member::updateOrCreate(
                ['member_id' => $memberIdStr],
                [
                    'name'        => $lastNames[$lnIdx] . ' ' . $firstNames[$fnIdx],
                    'name_kana'   => $lastKanas[$lnIdx] . ' ' . $firstKanas[$fnIdx],
                    'password'    => $hashedPassword,
                    'nickname'    => "Member_{$num}",
                    'email'       => "demo{$num}@foodlog.local",
                    'member_type' => Member::TYPE_GENERAL,
                    'delete_flg'  => false,
                ]
            );

            Review::create([
                'restaurant_id' => $restaurant->id,
                'member_id'     => $member->id,
                'date'          => $today->copy()->subDays($i)->format('Y-m-d'),
                'order_details' => $orders[($i - 1) % count($orders)] . " (#{$num})",
                'impression'    => $impressions[($i - 1) % count($impressions)],
                'rating'        => (($i - 1) % 5) + 1,
                'image'         => null,
            ]);
        }
    }
}
