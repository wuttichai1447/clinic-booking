<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PostgreSQL: clinic/therapist IDs are strings, not UUIDs — recreate tables if FK types were wrong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('clinic_holidays');
        Schema::create('clinic_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('clinic_id')->nullable();
            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
            $table->date('date');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->unique(['clinic_id', 'date']);
        });

        if (Schema::hasTable('staff_leaves')) {
            Schema::dropIfExists('staff_leaves');
        }

        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('therapist_id');
            $table->foreign('therapist_id')->references('id')->on('therapists')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('leave_type', 32)->default('annual');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['therapist_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_holidays');
        Schema::dropIfExists('staff_leaves');
    }
};
