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
        $hashedPassword = Hash::make('password123');

        $lastNames  = ['佐藤', '鈴木', '高橋', '田中', '伊藤', '渡辺', '山本', '中村', '小林', '加藤'];
        $lastKanas  = ['サトウ', 'スズキ', 'タカハシ', 'タナカ', 'イトウ', 'ワタナベ', 'ヤマモト', 'ナカムラ', 'コバヤシ', 'カトウ'];
        $firstNames = ['翔太', '美咲', '健太', '陽菜', '大輔', 'さくら', '拓海', '結衣', '蓮', '葵'];
        $firstKanas = ['ショウタ', 'ミサキ', 'ケンタ', 'ヒナ', 'ダイスケ', 'サクラ', 'タクミ', 'ユイ', 'レン', 'アオイ'];

        // 使うデモ一般会員は10名以内（demo01〜demo10）に統一
        $members = [];
        for ($i = 1; $i <= 10; $i++) {
            $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $idx = $i - 1;

            $members[] = Member::updateOrCreate(
                ['member_code' => "demo{$num}"],
                [
                    'name'        => $lastNames[$idx] . ' ' . $firstNames[$idx],
                    'name_kana'   => $lastKanas[$idx] . ' ' . $firstKanas[$idx],
                    'password'    => $hashedPassword,
                    'nickname'    => "Member_{$num}",
                    'email'       => "demo{$num}@foodlog.local",
                    'member_type' => Member::TYPE_GENERAL,
                    'delete_flg'  => false,
                ]
            );
        }

        // demo11以降の余分な会員がいれば、既存レビューをdemo01〜demo10に振り替えた上で削除
        $extraMembers = Member::where('member_code', 'like', 'demo%')
            ->whereNotIn('member_code', collect($members)->pluck('member_code'))
            ->get();

        if ($extraMembers->isNotEmpty()) {
            foreach ($extraMembers as $emIdx => $em) {
                $fallbackMember = $members[$emIdx % count($members)];
                Review::where('member_id', $em->id)->update(['member_id' => $fallbackMember->id]);
                $em->delete();
            }
        }

        $ordersByCategory = [
            'Japanese' => [
                "特製濃厚豚骨ラーメン\n半熟味玉トッピング",
                "黒豚餃子（6個）\n半チャーハンセット",
                "特上握り寿司10貫盛り\nあおさの味噌汁",
                "サーモン＆いくら親子丼\n茶碗蒸し",
                "炙りチャーシュー麺（大盛）\n海苔トッピング",
                "本マグロ三昧セット\n海老天ロール",
            ],
            'Italian' => [
                "マルゲリータピザ（Lサイズ）\nシーザーサラダ",
                "クワトロフォルマッジ（ハチミツ付）\nミネストローネ",
                "生ハムとルッコラのピッツァ\nガーリックトースト",
                "ペペロンチーノ＆マルゲリータセット",
                "トリュフ香るビスマルクピザ\nカプレーゼ",
            ],
            'Thai' => [
                "グリーンカレー＆ジャスミンライス\n生春巻き2本",
                "本格パッタイ（海老入り）\nトムヤムクンスープ",
                "ガパオライス目玉焼き乗せ\nマンゴーラッシー",
                "マッサマンカレー（チキン）\nヤムウンセン",
                "レッドカレー＆チーズナンセット",
            ],
            'Korean' => [
                "厚切りサムギョプサル弁当\nサンチュ＆キムチセット",
                "特選和牛プルコギ重\nわかめスープ",
                "チーズタッカルビ＆キンパセット",
                "海鮮スンドゥブチゲ（辛口）\nライス",
                "ヤンニョムチキン（8ピース）\n海鮮チヂミ",
            ],
            'default' => [
                "特製濃厚ラーメン\n半熟味玉トッピング",
                "黒豚餃子（6個）\nチャーハンセット",
                "チーズカレーライス\nラッシー",
                "マルゲリータピザ（Mサイズ）\nシーザーサラダ",
                "牛カルビ焼肉弁当\nわかめスープ",
                "サーモンアボカド丼\nお味噌汁",
                "唐揚げ定食（大盛り）",
                "デミグラスハンバーグ\nライス・サラダ付き",
            ],
        ];

        $impressions = [
            '熱々の状態で届いてとても美味しかったです！配達も予定より早くて大満足です。',
            'ボリュームたっぷりでコスパ最高でした。またリピートします！',
            '味付けが絶妙で、最後まで飽きずに食べられました。',
            '梱包がとても丁寧で崩れも一切ありませんでした。香りも最高で、家族にも好評だったので次回は別のメニューも試してみたいです。',
            'ちょうど良い味付けでご飯が進みました！量もちょうど良かったです。',
            'お店で食べるのと変わらないクオリティで感動しました。',
            '安定の美味しさです。忙しい日のランチやディナーにいつも助かっています。',
            '具材が大きくて食べ応えがありました。ソースやスパイスとの相性も抜群です。',
            '少し味が濃いめに感じましたが、おつまみや夕食としては最高でした。',
            '期待以上の美味しさでした！次は友達にもおすすめしようと思います。',
        ];

        // 各レストランについて、レビュー数が20個以下なら 30〜60個 になるよう追加（ID 15が20以下の場合は150個）
        $targetCounts = [36, 44, 52, 38, 58, 46];
        $today = Carbon::today();
        $restaurants = Restaurant::orderBy('id')->get();

        foreach ($restaurants as $rIdx => $restaurant) {
            $currentCount = $restaurant->reviews()->count();
            if ($currentCount > 20) {
                continue;
            }

            $targetTotal = ($restaurant->id === 15)
                ? 150
                : $targetCounts[$rIdx % count($targetCounts)];

            $toCreate = max(0, $targetTotal - $currentCount);
            $catKey = isset($ordersByCategory[$restaurant->category]) ? $restaurant->category : 'default';
            $menuList = $ordersByCategory[$catKey];

            for ($i = 1; $i <= $toCreate; $i++) {
                $member = $members[($i - 1) % count($members)];
                $seq = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

                Review::create([
                    'restaurant_id' => $restaurant->id,
                    'member_id'     => $member->id,
                    'date'          => $today->copy()->subDays(($i * 2 + $rIdx) % 120)->format('Y-m-d'),
                    'order_details' => $menuList[($i - 1) % count($menuList)] . " (#{$seq})",
                    'impression'    => $impressions[($i + $rIdx) % count($impressions)],
                    'rating'        => (($i + $rIdx) % 5) + 1,
                    'image'         => null,
                ]);
            }
        }
    }
}
