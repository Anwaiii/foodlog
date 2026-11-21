<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE `restaurants` SET `category` = LEFT(`category`, 30) WHERE CHAR_LENGTH(`category`) > 30');
        DB::statement('ALTER TABLE `restaurants` MODIFY `category` VARCHAR(30) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `restaurants` MODIFY `category` VARCHAR(200) NULL');
    }
};
