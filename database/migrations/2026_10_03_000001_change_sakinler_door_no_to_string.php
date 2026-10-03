<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE sakinler MODIFY door_no VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        $hasNonNumericDoorNumber = DB::table('sakinler')
            ->whereRaw("door_no REGEXP '[^0-9]'")
            ->exists();

        if ($hasNonNumericDoorNumber) {
            throw new RuntimeException('Cannot convert alphanumeric door numbers back to integers.');
        }

        DB::statement('ALTER TABLE sakinler MODIFY door_no INT NOT NULL');
    }
};