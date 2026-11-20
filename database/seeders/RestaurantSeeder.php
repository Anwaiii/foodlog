<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'id'          => 1,
                'name'        => 'Spicy Ramen Hub',
                'category'    => 'Japanese',
                'description' => 'Rich tonkotsu broth, handmade noodles & premium toppings',
                'address'     => '東京都渋谷区神宮前1-2-3',
                'image'       => 'assets/images/ramen.png',
            ],
            [
                'id'          => 3,
                'name'        => 'Sakura Sushi',
                'category'    => 'Japanese',
                'description' => 'Premium nigiri, maki rolls & omakase platters',
                'address'     => '東京都港区六本木3-4-5',
                'image'       => 'assets/images/sushi.png',
            ],
            [
                'id'          => 4,
                'name'        => 'Napoli Pizza',
                'category'    => 'Italian',
                'description' => 'Wood-fired Neapolitan pizza with San Marzano tomatoes',
                'address'     => '東京都目黒区自由が丘2-1-1',
                'image'       => 'assets/images/pizza.png',
            ],
            [
                'id'          => 5,
                'name'        => 'Bangkok Curry House',
                'category'    => 'Thai',
                'description' => 'Authentic Thai green & red curries with jasmine rice',
                'address'     => '東京都新宿区新宿3-10-1',
                'image'       => 'assets/images/curry.png',
            ],
            [
                'id'          => 6,
                'name'        => 'Seoul BBQ Garden',
                'category'    => 'Korean',
                'description' => 'Premium wagyu bulgogi, samgyeopsal & banchan sides',
                'address'     => '東京都新宿区百人町1-5-2',
                'image'       => 'assets/images/korean_bbq.png',
            ],
            [
                'id'          => 15,
                'name'        => '[TEST] Photo 11081154 Edited',
                'category'    => 'TestEdited',
                'description' => 'Edited description T-023',
                'address'     => '7-8-9 Umeda, Kita-ku, Osaka',
                'image'       => 'assets/images/burger.png',
            ],
        ];

        foreach ($items as $item) {
            Restaurant::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
