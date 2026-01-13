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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('employee_id');
            $table->timestampTz('clock_in');
            $table->timestampTz('clock_out')->nullable();
            $table->timestampTz('clock_in_client_ts')->nullable();
            $table->timestampTz('clock_out_client_ts')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('location', 255)->nullable();
            $table->decimal('location_accuracy', 8, 3)->nullable();
            $table->string('device_id', 255)->nullable();
            $table->string('photo_url', 1024)->nullable();
            $table->string('source', 50)->nullable();
            $table->string('sync_status', 50)->default('pending');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->index(['employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
