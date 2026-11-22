<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('restaurants')) {
            Schema::table('restaurants', function (Blueprint $table) {
                if (!Schema::hasColumn('restaurants', 'area')) {
                    $table->string('area', 50)->nullable()->after('category');
                }
                if (!Schema::hasColumn('restaurants', 'price_range')) {
                    $table->string('price_range', 30)->nullable()->after('area');
                }
            });

            // 既存店舗の住所・カテゴリからエリアと予算帯を自動補完（NULL のもののみ）
            $areaKeywords = [
                '渋谷', '新宿', '六本木', '恵比寿', '銀座', '表参道', '原宿',
                '中目黒', '代官山', '池袋', '秋葉原', '上野', '浅草', '下北沢',
                '吉祥寺', '自由が丘', '赤坂', '麻布', '神楽坂', '品川', '目黒',
                '丸の内', '日本橋', '神田', '新橋', '大久保', '新大久保', '高田馬場',
                '港区', '中央区', '千代田区', '世田谷区', '豊島区', '台東区', '文京区',
            ];

            $cheapCategories = ['Cafe', 'Burger', 'Indian', 'Mexican'];
            $highCategories  = ['French', 'Italian'];

            $rows = DB::table('restaurants')->select('id', 'category', 'address', 'area', 'price_range')->get();
            foreach ($rows as $row) {
                $updates = [];

                if (empty($row->area)) {
                    $detected = '東京';
                    $addr = (string) ($row->address ?? '');
                    foreach ($areaKeywords as $kw) {
                        if ($addr !== '' && mb_strpos($addr, $kw) !== false) {
                            $detected = rtrim($kw, '区');
                            break;
                        }
                    }
                    $updates['area'] = $detected;
                }

                if (empty($row->price_range)) {
                    $cat = (string) ($row->category ?? '');
                    if (in_array($cat, $highCategories, true) && ((int) $row->id % 2 === 0)) {
                        $updates['price_range'] = '¥3,000〜';
                    } elseif (in_array($cat, $cheapCategories, true)) {
                        $updates['price_range'] = ((int) $row->id % 3 === 0) ? '¥1,000〜¥3,000' : '〜¥1,000';
                    } elseif ($cat === 'Japanese' && ((int) $row->id % 3 === 0)) {
                        $updates['price_range'] = '¥3,000〜';
                    } else {
                        $updates['price_range'] = ((int) $row->id % 4 === 0) ? '〜¥1,000' : '¥1,000〜¥3,000';
                    }
                }

                if (!empty($updates)) {
                    DB::table('restaurants')->where('id', $row->id)->update($updates);
                }
            }
        }

        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (!Schema::hasColumn('members', 'avatar')) {
                    $table->string('avatar', 255)->nullable()->after('nickname');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('restaurants')) {
            Schema::table('restaurants', function (Blueprint $table) {
                if (Schema::hasColumn('restaurants', 'price_range')) {
                    $table->dropColumn('price_range');
                }
                if (Schema::hasColumn('restaurants', 'area')) {
                    $table->dropColumn('area');
                }
            });
        }

        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (Schema::hasColumn('members', 'avatar')) {
                    $table->dropColumn('avatar');
                }
            });
        }
    }
};
