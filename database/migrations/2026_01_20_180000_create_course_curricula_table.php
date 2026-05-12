<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_curricula', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_class_id')->nullable()->index();
            $table->string('title');
            $table->string('skkni_reference')->nullable();
            $table->string('matrix_reference')->nullable();
            $table->text('notes')->nullable();
            $table->enum('method', ['luring', 'daring', 'blended'])->default('luring');
            $table->unsignedInteger('total_jp_theory')->default(0);
            $table->unsignedInteger('total_jp_practice')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_curricula');
    }
};
