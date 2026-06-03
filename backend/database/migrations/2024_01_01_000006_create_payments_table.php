<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('appointment_id');
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('thb');
            $table->string('method')->nullable(); // credit_card, transfer, promptpay
            $table->string('status')->default('pending'); // pending, succeeded, failed
            $table->string('provider')->default('stripe'); // stripe, dev
            $table->string('stripe_payment_intent_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
