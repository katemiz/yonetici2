<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE kayitlar MODIFY spending_category ENUM('Isınma', 'Su', 'Elektrik', 'Temizlik', 'Diğer') NULL");

        DB::table('kayitlar')
            ->where('spending_category', 'Isunma')
            ->update(['spending_category' => 'Isınma']);
    }

    public function down(): void
    {
        DB::table('kayitlar')
            ->where('spending_category', 'Isınma')
            ->update(['spending_category' => 'Isunma']);

        DB::statement("ALTER TABLE kayitlar MODIFY spending_category ENUM('Isunma', 'Su', 'Elektrik', 'Temizlik', 'Diğer') NULL");
    }
};
