<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('clinic_id');
            $table->string('service_id');
            $table->string('therapist_id');
            $table->date('date');
            $table->string('time_slot_id');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, awaiting_payment, confirmed, cancelled
            $table->unsignedInteger('amount');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('partner_reference')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('therapist_id')->references('id')->on('therapists');
            $table->foreign('time_slot_id')->references('id')->on('time_slots');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
