<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('manager');
            $table->unsignedInteger('building_quota')->default(1);
            $table->boolean('is_active')->default(true);
        });

        $firstUserId = DB::table('users')->min('id');
        if ($firstUserId !== null) {
            DB::table('users')
                ->where('id', $firstUserId)
                ->update(['role' => 'superuser', 'building_quota' => 0]);
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'building_quota', 'is_active']);
        });
    }
};
