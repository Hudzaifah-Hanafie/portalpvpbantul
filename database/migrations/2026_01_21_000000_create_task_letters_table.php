<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_class_id')->constrained('course_classes')->cascadeOnDelete();
            $table->string('letter_number')->nullable();
            $table->text('legal_basis')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_link')->nullable();
            $table->json('instructors')->nullable();
            $table->json('committees')->nullable();
            $table->json('participant_statuses')->nullable();
            $table->string('status')->default('draft');
            $table->string('signed_city')->nullable();
            $table->date('signed_at')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_position')->nullable();
            $table->string('signatory_nip')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_letters');
    }
};
