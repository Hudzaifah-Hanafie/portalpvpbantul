<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_attendance')->default(80);
            $table->unsignedSmallInteger('min_score')->default(70);
            $table->boolean('require_final_project')->default(true);
            $table->boolean('require_final_exam')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropColumn(['min_attendance', 'min_score', 'require_final_project', 'require_final_exam']);
        });
    }
};
