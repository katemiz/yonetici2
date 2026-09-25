<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kayitlar', function (Blueprint $table) {
            $table->enum('spending_category', [
                'Isınma',
                'Su',
                'Elektrik',
                'Temizlik',
                'Diğer',
            ])->nullable()->after('tur');
        });
    }

    public function down(): void
    {
        Schema::table('kayitlar', function (Blueprint $table) {
            $table->dropColumn('spending_category');
        });
    }
};
