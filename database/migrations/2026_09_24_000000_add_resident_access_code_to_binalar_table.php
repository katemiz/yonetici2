<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('binalar', function (Blueprint $table) {
            $table->string('resident_access_code')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('binalar', function (Blueprint $table) {
            $table->dropColumn('resident_access_code');
        });
    }
};
