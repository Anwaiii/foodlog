<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        DB::unprepared(file_get_contents($sqlPath));
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
