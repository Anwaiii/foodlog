<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('favorites')) {
            Schema::create('favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
                $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['member_id', 'restaurant_id'], 'favorites_member_restaurant_unique');
            });
        }

        if (!Schema::hasTable('review_likes')) {
            Schema::create('review_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
                $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['member_id', 'review_id'], 'review_likes_member_review_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('review_likes');
        Schema::dropIfExists('favorites');
    }
};
