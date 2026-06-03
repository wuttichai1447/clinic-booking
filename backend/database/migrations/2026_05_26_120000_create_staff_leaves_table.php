<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('therapist_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('staff_leaves');
    }
};
