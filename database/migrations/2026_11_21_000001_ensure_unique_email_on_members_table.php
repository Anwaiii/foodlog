<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members')) {
            $indexes = DB::select("SHOW INDEX FROM members WHERE Column_name = 'email' AND Non_unique = 0");
            if (empty($indexes)) {
                Schema::table('members', function (Blueprint $table) {
                    $table->unique('email', 'members_email_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members')) {
            $indexes = DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_email_unique'");
            if (!empty($indexes)) {
                Schema::table('members', function (Blueprint $table) {
                    $table->dropUnique('members_email_unique');
                });
            }
        }
    }
};
