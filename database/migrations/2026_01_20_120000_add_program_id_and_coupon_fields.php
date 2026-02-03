<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_schedules', function (Blueprint $table) {
            $table->foreignUuid('program_id')
                ->nullable()
                ->after('batch_id')
                ->constrained('programs')
                ->nullOnDelete();
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->string('coupon_code')->nullable()->unique()->after('certificate_url');
            $table->timestamp('coupon_issued_at')->nullable()->after('coupon_code');
            $table->string('coupon_issue_mode', 20)->nullable()->after('coupon_issued_at');
            $table->foreignUuid('coupon_issued_by')
                ->nullable()
                ->after('coupon_issue_mode')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('training_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_id');
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_issued_by');
            $table->dropColumn(['coupon_code', 'coupon_issued_at', 'coupon_issue_mode']);
        });
    }
};
