<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('pdpa_accepted_at')->nullable()->after('role');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('slot_locked_until')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            $table->string('cancel_reason')->nullable()->after('cancelled_at');
            $table->string('refund_status')->nullable()->after('cancel_reason');
            $table->unsignedInteger('refund_amount')->nullable()->after('refund_status');
            $table->date('rescheduled_from_date')->nullable();
            $table->string('rescheduled_from_time_slot_id')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('payment_reference');
        });

        Schema::create('clinic_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('clinic_id')->nullable();
            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
            $table->date('date');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->unique(['clinic_id', 'date']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 64);
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 36)->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });

            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });

            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('clinic_holidays');
        Schema::table('payments', fn (Blueprint $t) => $t->dropColumn('proof_path'));
        Schema::table('appointments', function (Blueprint $t) {
            $t->dropColumn([
                'slot_locked_until', 'cancelled_at', 'cancel_reason',
                'refund_status', 'refund_amount',
                'rescheduled_from_date', 'rescheduled_from_time_slot_id',
            ]);
        });
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('pdpa_accepted_at'));
    }
};
