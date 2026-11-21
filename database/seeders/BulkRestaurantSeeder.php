<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulkRestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $memberIds = DB::table('members')
            ->where('member_type', 2)
            ->where('delete_flg', 0)
            ->pluck('id')
            ->all();

        if (empty($memberIds)) {
            $memberIds = [null];
        }

        $restaurants = [
            ['Shibuya Smash Burger', 'Burger', 'Juicy double smash burgers, crispy fries & craft shakes', '東京都渋谷区宇田川町21-6', 'assets/images/burger.png', 5],
            ['Ginza Tempura Kuroda', 'Japanese', 'Light, crispy seasonal Edomae tempura bowls & soba', '東京都中央区銀座6-8-4', 'assets/images/sushi.png', 5],
            ['Trattoria Milano', 'Italian', 'Handmade carbonara, truffle risotto & wood-fired focaccia', '東京都港区六本木4-9-2', 'assets/images/pizza.png', 4],
            ['Sichuan Mala Kitchen', 'Chinese', 'Authentic numbing mapo tofu, dan dan noodles & chili wontons', '東京都新宿区歌舞伎町1-14-3', 'assets/images/ramen.png', 4],
            ['Spice Route Mumbai', 'Indian', 'Rich butter chicken, garlic naan & aromatic mutton biryani', '東京都世田谷区北沢2-11-8', 'assets/images/curry.png', 5],
            ['Gangnam Fried Chicken', 'Korean', 'Double-fried yangnyeom & honey butter crispy chicken', '東京都新宿区大久保1-16-9', 'assets/images/korean_bbq.png', 4],
            ['Chiang Mai Noodle Bar', 'Thai', 'Creamy khao soi curry noodles, pad thai & mango sticky rice', '東京都目黒区自由が丘1-8-22', 'assets/images/curry.png', 4],
            ['Harajuku Pancake Lab', 'Cafe', 'Fluffy souffle pancakes, matcha latte & seasonal parfaits', '東京都渋谷区神宮前4-26-5', null, 5],
            ['Bistro Le Petit Paris', 'French', 'Classic beef bourguignon, duck confit & fresh baguettes', '東京都新宿区神楽坂3-6-1', null, 5],
            ['Tacos El Sol', 'Mexican', 'Al pastor street tacos, fresh guacamole & loaded quesadillas', '東京都渋谷区恵比寿西1-13-7', null, 4],
            ['Asakusa Tonkatsu Maru', 'Japanese', 'Thick-cut Kurobuta pork loin katsu with homemade sauce', '東京都台東区浅草1-25-12', 'assets/images/ramen.png', 5],
            ['Yokohama Iekei Ramen', 'Japanese', 'Rich soy-pork bone broth with spinach, nori & thick noodles', '東京都豊島区南池袋2-19-4', 'assets/images/ramen.png', 4],
            ['Osteria Roma', 'Italian', 'Cacio e pepe, prosciutto pizza & homemade tiramisu', '東京都目黒区中目黒1-10-18', 'assets/images/pizza.png', 4],
            ['Canton Dim Sum House', 'Chinese', 'Steamed har gow, xiao long bao & roast duck rice', '東京都千代田区有楽町2-5-1', null, 4],
            ['Busan Seafood Grill', 'Korean', 'Spicy haemul pajeon, sundubu jjigae & grilled mackerel', '東京都新宿区百人町2-3-15', 'assets/images/korean_bbq.png', 3],
            ['Brooklyn Craft Burger', 'Burger', 'Smoky BBQ bacon burger, onion rings & coleslaw', '東京都港区南青山5-7-21', 'assets/images/burger.png', 4],
            ['Kyoto Matcha Roastery', 'Cafe', 'Stone-ground Uji matcha sweets, hojicha & cold brew coffee', '東京都文京区本郷3-18-9', null, 5],
            ['Phuket Street Kitchen', 'Thai', 'Gapao rice with crispy fried egg & spicy tom yum soup', '東京都港区赤坂3-12-10', 'assets/images/curry.png', 4],
            ['Delhi Tandoor Express', 'Indian', 'Charcoal-grilled tandoori chicken, palak paneer & cheese naan', '東京都杉並区高円寺南4-24-7', 'assets/images/curry.png', 3],
            ['Tsukiji Donburi Stand', 'Japanese', 'Fresh uni, ikura & fatty tuna kaisendon bowls', '東京都中央区築地4-10-14', 'assets/images/sushi.png', 5],
            ['Pizzeria Vesuvio', 'Italian', 'Diavola spicy salami pizza, burrata salad & arancini', '東京都渋谷区代官山町17-6', 'assets/images/pizza.png', 4],
            ['Beijing Dumpling Club', 'Chinese', 'Pan-fried gyoza, black vinegar sweet & sour pork', '東京都台東区上野4-7-8', null, 3],
            ['Guadalajara Burrito Co', 'Mexican', 'Slow-cooked carnitas burritos, salsa verde & churros', '東京都港区麻布十番2-8-12', null, 4],
            ['Nakameguro Bistro & Wine', 'French', 'Roast chicken, truffle fries & Basque burnt cheesecake', '東京都目黒区青葉台1-22-3', null, 5],
        ];

        $sampleOrders = [
            'Chef Special Set',
            'Signature Combo + Drink',
            'Lunch Box Deluxe',
            'House Favorite Plate',
            'Large Portion + Side Salad',
            'Spicy Special + Rice',
        ];

        $sampleImpressions = [
            '配送が早くてアツアツの状態で届きました！味付けもバランス良く、またリピートしたいです。',
            'ボリューム満点でコスパ最高。梱包も丁寧で汁漏れもありませんでした。',
            '香りがとても良く、お店で食べるのと変わらないクオリティでした！',
            '少し濃いめの味付けですがご飯が進みます。次回は別のメニューも試したいです。',
            '見た目が綺麗で味も大満足でした。週末のランチにぴったりです。',
            '普通に美味しいですが、もう少し量があるとさらに嬉しいです。',
        ];

        $now = now();
        $insertedRestaurants = 0;
        $insertedReviews = 0;

        foreach ($restaurants as $idx => $r) {
            [$name, $category, $desc, $address, $image, $targetRating] = $r;

            $exists = DB::table('restaurants')->where('name', $name)->exists();
            if ($exists) {
                continue;
            }

            $createdAt = $now->copy()->subDays(30 - $idx)->format('Y-m-d H:i:s');
            $restaurantId = DB::table('restaurants')->insertGetId([
                'name'        => $name,
                'category'    => $category,
                'description' => $desc,
                'address'     => $address,
                'image'       => $image,
                'emoji'       => null,
                'created_at'  => $createdAt,
            ]);
            $insertedRestaurants++;

            $reviewCount = 3 + ($idx % 4); // 3〜6 reviews each
            for ($j = 0; $j < $reviewCount; $j++) {
                $rating = max(1, min(5, $targetRating - ($j % 2 === 1 && $targetRating > 3 ? 1 : 0)));
                $date = $now->copy()->subDays(($idx * 2 + $j * 3) % 45)->format('Y-m-d');
                DB::table('reviews')->insert([
                    'restaurant_id' => $restaurantId,
                    'member_id'     => $memberIds[($idx + $j) % count($memberIds)],
                    'date'          => $date,
                    'order_details' => $sampleOrders[($idx + $j) % count($sampleOrders)],
                    'impression'    => $sampleImpressions[($idx + $j) % count($sampleImpressions)],
                    'rating'        => $rating,
                    'image'         => null,
                    'created_at'    => $now->copy()->subDays(($idx + $j) % 20)->format('Y-m-d H:i:s'),
                ]);
                $insertedReviews++;
            }
        }

        $this->command?->info("BulkRestaurantSeeder: added {$insertedRestaurants} restaurants and {$insertedReviews} reviews.");
    }
}
