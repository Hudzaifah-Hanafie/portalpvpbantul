<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_curriculum_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_curriculum_id')->index();
            $table->string('unit_code')->nullable();
            $table->string('unit_title');
            $table->text('elements')->nullable();
            $table->text('kuk')->nullable();
            $table->text('materials')->nullable();
            $table->unsignedInteger('jp_theory')->default(0);
            $table->unsignedInteger('jp_practice')->default(0);
            $table->enum('method', ['luring', 'daring', 'blended'])->default('luring');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_curriculum_units');
    }
};
