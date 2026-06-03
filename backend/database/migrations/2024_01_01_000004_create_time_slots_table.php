<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('therapist_id');
            $table->string('time');
            $table->boolean('available')->default(true);
            $table->date('slot_date')->nullable();
            $table->timestamps();

            $table->foreign('therapist_id')->references('id')->on('therapists')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
