<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->unsignedBigInteger('bina_id')->nullable()->after('user_id');
            $table->string('door_no')->nullable()->after('bina_id');
            $table->boolean('is_evsahibi')->default(false);
            $table->string('phone', 16)->nullable();
            $table->float('payratio', 12, 4)->nullable();
            $table->string('giris_tarihi')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_sakin')->default(false);
            $table->boolean('is_manager')->default(false);
            $table->boolean('is_superuser')->default(false);
        });

        DB::table('users')
            ->where('role', 'manager')
            ->update(['is_manager' => true]);

        DB::table('users')
            ->where('role', 'superuser')
            ->update(['is_superuser' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::dropIfExists('sakinler');
    }

    public function down()
    {
        Schema::create('sakinler', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class);
            $table->foreignIdFor(\App\Models\Bina::class);
            $table->string('name');
            $table->string('lastname');
            $table->string('door_no');
            $table->boolean('is_evsahibi')->default(true);
            $table->string('phone', 16)->nullable();
            $table->float('payratio', 12, 4)->default(100);
            $table->string('giris_tarihi')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'bina_id',
                'door_no',
                'is_evsahibi',
                'phone',
                'payratio',
                'giris_tarihi',
                'remarks',
                'is_sakin',
                'is_manager',
                'is_superuser',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('manager');
        });

        return 0;
    }
};
