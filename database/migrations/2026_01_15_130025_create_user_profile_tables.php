<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('sso_user_id')->nullable()->index();
            $table->string('sso_profile_id')->nullable()->unique();
            $table->string('identity_number')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('domicile_address')->nullable();
            $table->string('domicile_region_id')->nullable();
            $table->string('domicile_region_type')->nullable();
            $table->string('domicile_province_id')->nullable();
            $table->string('domicile_city_id')->nullable();
            $table->string('domicile_sub_district_id')->nullable();
            $table->string('domicile_village_id')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('birth_date')->nullable();
            $table->text('about')->nullable();
            $table->string('picture_uri')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->nullable();
            $table->json('domicile_region_payload')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_educations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sso_id')->nullable();
            $table->string('school_name')->nullable();
            $table->text('school_address')->nullable();
            $table->string('graduate_name')->nullable();
            $table->string('study_field_name')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('finish_year')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'sso_id']);
        });

        Schema::create('user_experiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sso_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->unsignedTinyInteger('start_month')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedTinyInteger('finish_month')->nullable();
            $table->unsignedSmallInteger('finish_year')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'sso_id']);
        });

        Schema::create('user_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sso_id')->nullable();
            $table->string('institution_name')->nullable();
            $table->string('program_name')->nullable();
            $table->unsignedTinyInteger('issued_month')->nullable();
            $table->unsignedSmallInteger('issued_year')->nullable();
            $table->unsignedTinyInteger('expire_month')->nullable();
            $table->unsignedSmallInteger('expire_year')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'sso_id']);
        });

        Schema::create('user_trainings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sso_id')->nullable();
            $table->string('training_center_name')->nullable();
            $table->string('vocational_name')->nullable();
            $table->string('sub_vocational_name')->nullable();
            $table->string('training_program_name')->nullable();
            $table->unsignedTinyInteger('start_month')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedTinyInteger('finish_month')->nullable();
            $table->unsignedSmallInteger('finish_year')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'sso_id']);
        });

        Schema::create('user_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sso_id')->nullable();
            $table->string('name')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'sso_id']);
        });

        Schema::create('user_languages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sso_id')->nullable();
            $table->string('language_name')->nullable();
            $table->string('proficiency_name')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'sso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_languages');
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('user_trainings');
        Schema::dropIfExists('user_certifications');
        Schema::dropIfExists('user_experiences');
        Schema::dropIfExists('user_educations');
        Schema::dropIfExists('user_profiles');
    }
};
