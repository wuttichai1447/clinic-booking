<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('amount')->constrained()->nullOnDelete();
            $table->unsignedInteger('subtotal')->default(0)->after('promotion_id');
            $table->unsignedInteger('discount_amount')->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn(['subtotal', 'discount_amount']);
        });
    }
};
