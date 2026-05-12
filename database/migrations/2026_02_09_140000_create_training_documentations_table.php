<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_documentations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('training_schedule_id')->constrained('training_schedules')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('link_url');
            $table->date('documented_at')->nullable();
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_documentations');
    }
};
