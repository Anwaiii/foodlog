<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('members')
            && Schema::hasColumn('members', 'member_id')
            && !Schema::hasColumn('members', 'member_code')) {
            DB::statement('ALTER TABLE members CHANGE COLUMN member_id member_code VARCHAR(50) NOT NULL');

            $hasOldIndex = !empty(DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_member_id_unique'"));
            $hasNewIndex = !empty(DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_member_code_unique'"));
            if ($hasOldIndex && !$hasNewIndex) {
                DB::statement('ALTER TABLE members DROP INDEX members_member_id_unique, ADD UNIQUE INDEX members_member_code_unique (member_code)');
            } elseif (!$hasNewIndex) {
                DB::statement('ALTER TABLE members ADD UNIQUE INDEX members_member_code_unique (member_code)');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members')
            && Schema::hasColumn('members', 'member_code')
            && !Schema::hasColumn('members', 'member_id')) {
            DB::statement('ALTER TABLE members CHANGE COLUMN member_code member_id VARCHAR(50) NOT NULL');

            $hasNewIndex = !empty(DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_member_code_unique'"));
            $hasOldIndex = !empty(DB::select("SHOW INDEX FROM members WHERE Key_name = 'members_member_id_unique'"));
            if ($hasNewIndex && !$hasOldIndex) {
                DB::statement('ALTER TABLE members DROP INDEX members_member_code_unique, ADD UNIQUE INDEX members_member_id_unique (member_id)');
            }
        }
    }
};
