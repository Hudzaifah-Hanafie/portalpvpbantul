<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decision_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('letter_number')->nullable();
            $table->string('batch_id')->nullable();
            $table->unsignedInteger('period_year')->nullable();
            $table->json('schedule_ids')->nullable();
            $table->text('subject')->nullable();
            $table->json('considerations')->nullable();
            $table->json('legal_basis')->nullable();
            $table->json('decisions')->nullable();
            $table->string('location_name')->nullable();
            $table->string('status')->default('draft');
            $table->string('signed_city')->nullable();
            $table->date('signed_at')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_position')->nullable();
            $table->string('signatory_nip')->nullable();
            $table->string('approval_left_name')->nullable();
            $table->string('approval_left_position')->nullable();
            $table->string('approval_left_nip')->nullable();
            $table->string('approval_right_name')->nullable();
            $table->string('approval_right_position')->nullable();
            $table->string('approval_right_nip')->nullable();
            $table->json('instructor_team')->nullable();
            $table->json('recruitment_team')->nullable();
            $table->json('management_team')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_letters');
    }
};
