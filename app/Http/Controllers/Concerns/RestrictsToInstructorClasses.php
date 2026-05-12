<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CourseClass;
use App\Models\CourseModule;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait RestrictsToInstructorClasses
{
    protected function isInstructorUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['instructor', 'instruktur'])
            && ! $user->hasAnyRole(['admin', 'superadmin']);
    }

    protected function scopeClassesForUser(Builder $query, ?User $user): Builder
    {
        if ($this->isInstructorUser($user)) {
            $query->where('instructor_id', $user->id);
        }

        return $query;
    }

    protected function scopeModulesForUser(Builder $query, ?User $user): Builder
    {
        if ($this->isInstructorUser($user)) {
            $query->whereHas('course', function (Builder $builder) use ($user) {
                $builder->where('instructor_id', $user->id);
            });
        }

        return $query;
    }

    protected function scopedClassOptions(?User $user)
    {
        return $this->scopeClassesForUser(CourseClass::orderBy('title'), $user)
            ->pluck('title', 'id');
    }

    protected function scopedModuleOptions(?User $user)
    {
        return $this->scopeModulesForUser(CourseModule::orderBy('sort_order'), $user)
            ->pluck('title', 'id');
    }

    protected function ensureInstructorOwnsClassId(?User $user, string $classId): void
    {
        if (! $this->isInstructorUser($user)) {
            return;
        }

        $exists = CourseClass::where('id', $classId)
            ->where('instructor_id', $user->id)
            ->exists();

        abort_unless($exists, 403);
    }

    protected function ensureInstructorOwnsClass(?User $user, CourseClass $class): void
    {
        if (! $this->isInstructorUser($user)) {
            return;
        }

        abort_unless($class->instructor_id === $user->id, 403);
    }

    protected function getRoutePrefix(): string
    {
        return request()->routeIs('instructor.*') || request()->routeIs('*.lms.*')
            ? 'instructor.lms.'
            : 'admin.';
    }
}
