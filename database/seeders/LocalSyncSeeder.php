<?php

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocalSyncSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = database_path('seeders/local_snapshot.sql');
        if (!file_exists($sqlPath)) {
            $this->command?->error('local_snapshot.sql not found.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 前回のエラーでテーブル定義が消えたり型が変わっていた場合に備えて正規スキーマで再作成
        Schema::dropIfExists('review_likes');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('restaurants');
        Schema::dropIfExists('members');

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code', 50)->unique();
            $table->string('name', 100);
            $table->string('name_kana', 100);
            $table->string('password', 255);
            $table->string('nickname', 50);
            $table->string('avatar', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->tinyInteger('member_type')->default(2);
            $table->boolean('delete_flg')->default(0);
            $table->timestamps();
        });

        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('category', 30)->nullable();
            $table->string('area', 50)->nullable();
            $table->string('price_range', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('address', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('emoji', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->date('date');
            $table->text('order_details');
            $table->text('impression');
            $table->tinyInteger('rating')->nullable();
            $table->string('image', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['member_id', 'restaurant_id'], 'favorites_member_restaurant_unique');
        });

        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['member_id', 'review_id'], 'review_likes_member_review_unique');
        });

        DB::unprepared(file_get_contents($sqlPath));
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
