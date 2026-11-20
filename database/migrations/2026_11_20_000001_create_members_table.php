<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->id();
                $table->string('member_id', 50)->unique();
                $table->string('name', 100);
                $table->string('name_kana', 100);
                $table->string('password', 255);
                $table->string('nickname', 50);
                $table->string('email', 255)->unique();
                $table->tinyInteger('member_type')->default(2)->comment('1:管理者, 2:一般会員');
                $table->boolean('delete_flg')->default(0)->comment('0:有効, 1:削除');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
