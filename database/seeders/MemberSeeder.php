<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'member_code' => 'admin01',
                'name'        => '管理者 太郎',
                'name_kana'   => 'カンリシャ タロウ',
                'password'    => Hash::make('password123'),
                'nickname'    => 'Admin',
                'email'       => 'admin@foodlog.local',
                'member_type' => Member::TYPE_ADMIN,
                'delete_flg'  => false,
            ],
            [
                'member_code' => 'user01',
                'name'        => '山田 花子',
                'name_kana'   => 'ヤマダ ハナコ',
                'password'    => Hash::make('password123'),
                'nickname'    => 'Hanako',
                'email'       => 'user01@foodlog.local',
                'member_type' => Member::TYPE_GENERAL,
                'delete_flg'  => false,
            ],
            [
                'member_code' => 'user02',
                'name'        => '鈴木 一郎',
                'name_kana'   => 'スズキ イチロウ',
                'password'    => Hash::make('password123'),
                'nickname'    => 'Ichiro',
                'email'       => 'user02@foodlog.local',
                'member_type' => Member::TYPE_GENERAL,
                'delete_flg'  => false,
            ],
        ];

        foreach ($members as $data) {
            Member::updateOrCreate(
                ['member_code' => $data['member_code']],
                $data
            );
        }
    }
}
