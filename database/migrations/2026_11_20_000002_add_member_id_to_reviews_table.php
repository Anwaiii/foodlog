<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews') && !Schema::hasColumn('reviews', 'member_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreignId('member_id')
                    ->nullable()
                    ->after('restaurant_id')
                    ->constrained('members')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'member_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['member_id']);
                $table->dropColumn('member_id');
            });
        }
    }
};
