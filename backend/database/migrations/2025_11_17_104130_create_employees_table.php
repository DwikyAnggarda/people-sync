<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('employee_number', 50);
            $table->string('first_name', 150);
            $table->string('last_name', 150)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->uuid('department_id')->nullable();
            $table->date('hired_at')->nullable();
            $table->string('status', 50)->default('active');
            $table->decimal('current_salary', 18, 2)->nullable();
            $table->uuid('latest_payroll_id')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
            $table->index('department_id');
            $table->index('latest_payroll_id');
        });

        DB::statement('CREATE UNIQUE INDEX employees_employee_number_not_deleted ON employees (employee_number) WHERE deleted_at IS NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropIndex(['employee_id']);
        });

        DB::statement('DROP INDEX IF EXISTS employees_employee_number_not_deleted');
        Schema::dropIfExists('employees');
    }
};
