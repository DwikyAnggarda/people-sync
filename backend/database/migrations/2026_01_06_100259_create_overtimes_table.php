<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();

            // FK ke employees (BIGINT)
            $table->foreignId('employee_id')
                    ->constrained()
                    ->restrictOnDelete();

            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

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
        Schema::dropIfExists('overtimes');
    }
};

