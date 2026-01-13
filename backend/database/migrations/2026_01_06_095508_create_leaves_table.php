<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            // FK ke employees (BIGINT)
            $table->foreignId('employee_id')
                    ->constrained()
                    ->restrictOnDelete();

            // leave attributes
            $table->string('type'); // annual, sick, permission, unpaid
            $table->date('start_date');
            $table->date('end_date');

            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('reason')->nullable();

            // FK ke users (UUID) sebagai approver
            $table->uuid('approved_by')->nullable();
            $table->foreign('approved_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
