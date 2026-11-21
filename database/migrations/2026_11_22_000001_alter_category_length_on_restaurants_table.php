<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `restaurants` MODIFY `category` VARCHAR(200) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `restaurants` MODIFY `category` VARCHAR(100) NULL');
    }
};
