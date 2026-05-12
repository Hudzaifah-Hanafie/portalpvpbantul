<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->decimal('pre_test_score', 5, 2)->nullable()->after('final_score');
            $table->decimal('post_test_score', 5, 2)->nullable()->after('pre_test_score');
            $table->decimal('practice_score', 5, 2)->nullable()->after('post_test_score');
            $table->decimal('attitude_score', 5, 2)->nullable()->after('practice_score');
            $table->decimal('attendance_rate', 5, 2)->nullable()->after('attitude_score');
            $table->decimal('final_grade', 5, 2)->nullable()->after('attendance_rate');
            $table->string('competency_status', 20)->nullable()->after('final_grade');
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'pre_test_score',
                'post_test_score',
                'practice_score',
                'attitude_score',
                'attendance_rate',
                'final_grade',
                'competency_status',
            ]);
        });
    }
};
