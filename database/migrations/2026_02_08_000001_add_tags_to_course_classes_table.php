<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('badge');
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
