<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapists', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->string('clinic_id')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapists');
    }
};
