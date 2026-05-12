<?php

namespace App\Models;

use App\Traits\HasUuid;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\CourseEnrollment;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasUuid;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nik',
        'email',
        'siap_kerja_id',
        'sso_payload',
        'password',
        'email_verified_at',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'sso_payload',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'sso_payload' => 'array',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function permissions(): Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })->exists();
    }

    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync(array_filter($roleIds));
    }

    public function generateApiToken(string $name = 'api'): string
    {
        return $this->createToken($name)->plainTextToken;
    }

    public function revokeApiToken(): void
    {
        $this->tokens()->delete();
    }

    public function forumTopics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(UserEducation::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(UserExperience::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(UserCertification::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(UserTraining::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(UserSkill::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(UserLanguage::class);
    }

    public function kejuruanModules(): HasMany
    {
        return $this->hasMany(KejuruanModule::class, 'owner_id');
    }

    public function trainingDocumentations(): HasMany
    {
        return $this->hasMany(TrainingDocumentation::class, 'owner_id');
    }

    public function adminNotifications(): HasMany
    {
        return $this->hasMany(AdminNotification::class);
    }

    public function gamificationPoints(): HasMany
    {
        return $this->hasMany(GamificationPoint::class);
    }

    public function materialProgress(): HasMany
    {
        return $this->hasMany(CourseMaterialProgress::class);
    }
}
