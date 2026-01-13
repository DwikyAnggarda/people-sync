<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // FK ke employees (BIGINT)
            $table->foreignId('employee_id')
                    ->constrained()
                    ->restrictOnDelete();

            $table->date('date');
            $table->timestamp('clock_in_at');
            $table->timestamp('clock_out_at')->nullable();

            $table->string('photo_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('source')->default('mobile');

            $table->timestamps();
        });

        // 1 employee hanya boleh 1 attendance per hari
        DB::statement("
            CREATE UNIQUE INDEX attendances_employee_date_unique
            ON attendances(employee_id, date)
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS attendances_employee_date_unique');
        Schema::dropIfExists('attendances');
    }
};

