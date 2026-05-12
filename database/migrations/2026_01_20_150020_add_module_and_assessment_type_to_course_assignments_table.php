<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_assignments', function (Blueprint $table) {
            $table->uuid('course_module_id')->nullable()->index();
            $table->string('assessment_type')->default('regular')->index();
        });
    }

    public function down(): void
    {
        Schema::table('course_assignments', function (Blueprint $table) {
            $table->dropColumn(['course_module_id', 'assessment_type']);
        });
    }
};
