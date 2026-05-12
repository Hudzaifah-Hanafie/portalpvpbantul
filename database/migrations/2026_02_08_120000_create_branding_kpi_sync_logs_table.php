<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branding_kpi_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('platform', 64);
            $table->string('status', 32);
            $table->string('indicator_key', 32)->nullable();
            $table->string('metric', 64)->nullable();
            $table->decimal('metric_value', 14, 2)->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branding_kpi_sync_logs');
    }
};
