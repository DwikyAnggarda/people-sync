<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * PostgreSQL / Neon must not run this migration in a transaction
     */
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // FK ke users (UUID)
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // business fields
            $table->string('employee_number');
            $table->string('name');
            $table->string('email')->nullable();

            // FK ke departments (BIGINT)
            $table->foreignId('department_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('status')->default('active');
            $table->date('joined_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // partial unique index (PostgreSQL)
        DB::statement("
            CREATE UNIQUE INDEX employees_number_unique
            ON employees(employee_number)
            WHERE deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS employees_number_unique');
        Schema::dropIfExists('employees');
    }
};
