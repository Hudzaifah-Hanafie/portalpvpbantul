<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_material_discussions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_material_id')->index();
            $table->uuid('user_id');
            $table->uuid('parent_id')->nullable(); // For Replies
            $table->text('message');
            $table->integer('likes_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            // $table->foreign('course_material_id')->references('id')->on('course_materials')->cascadeOnDelete();
        });

        Schema::table('course_material_discussions', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('course_material_discussions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_material_discussions');
    }
};
