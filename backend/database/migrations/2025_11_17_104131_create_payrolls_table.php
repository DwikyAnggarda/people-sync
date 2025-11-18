<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->unsignedInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->decimal('gross_amount', 18, 2)->default(0);
            $table->decimal('net_amount', 18, 2)->default(0);
            $table->string('status', 50)->default('draft');
            $table->jsonb('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('generated_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->unique(['employee_id', 'period_year', 'period_month']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('latest_payroll_id')
                ->references('id')
                ->on('payrolls')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['latest_payroll_id']);
        });

        Schema::dropIfExists('payrolls');
    }
};
