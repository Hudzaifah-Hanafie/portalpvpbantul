<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_assignments', function (Blueprint $table) {
            $table->string('quiz_scope', 20)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('course_assignments', function (Blueprint $table) {
            $table->dropColumn('quiz_scope');
        });
    }
};
