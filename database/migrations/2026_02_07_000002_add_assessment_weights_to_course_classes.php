<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->unsignedSmallInteger('weight_theory')->default(30)->after('min_score');
            $table->unsignedSmallInteger('weight_practice')->default(60)->after('weight_theory');
            $table->unsignedSmallInteger('weight_attitude')->default(10)->after('weight_practice');
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropColumn(['weight_theory', 'weight_practice', 'weight_attitude']);
        });
    }
};
