<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'google_id', 'password', 'role', 'avatar', 'phone',
    'university', 'major', 'semester', 'education_level', 'bio',
    'expertise', 'status', 'rating', 'otp_code', 'otp_expires_at', 'email_verified_at',
    'is_tsu', 'tsu_status', 'ktm_path', 'tsu_verified_at', 'screening_completed_at',
])]
#[Hidden(['password', 'remember_token', 'otp_code'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expertise' => 'array',
            'otp_expires_at' => 'datetime',
            'rating' => 'float',
            'is_tsu' => 'boolean',
            'tsu_verified_at' => 'datetime',
            'screening_completed_at' => 'datetime',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function internshipApplications(): HasMany
    {
        return $this->hasMany(InternshipApplication::class);
    }

    public function logbookEntries(): HasMany
    {
        return $this->hasMany(LogbookEntry::class);
    }

    public function mentoredPrograms(): HasMany
    {
        return $this->hasMany(Program::class, 'mentor_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class)->withPivot('earned_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cvSubscriptions(): HasMany
    {
        return $this->hasMany(CvSubscription::class);
    }

    public function activeCvSubscription(): ?CvSubscription
    {
        static $memo = [];
        $userId = $this->id;
        if (array_key_exists($userId, $memo)) {
            return $memo[$userId];
        }

        $candidates = $this->cvSubscriptions()
            ->active()
            ->latest('paid_at')
            ->limit(5)
            ->get();

        $usable = $candidates->first(fn (CvSubscription $subscription) => $subscription->isUsable());

        return $memo[$userId] = $usable;
    }

    public function hasActiveCvSubscription(): bool
    {
        return $this->activeCvSubscription() !== null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTsuStudent(): bool
    {
        return $this->is_tsu === true && $this->tsu_verified_at !== null;
    }

    public function isTsuPending(): bool
    {
        return $this->is_tsu === true && $this->tsu_verified_at === null;
    }

    public function isTsuActiveStudent(): bool
    {
        return $this->isTsuStudent() && $this->tsu_status === 'active';
    }

    public function tsuStatusLabel(): string
    {
        return match ($this->tsu_status) {
            'active' => 'Mahasiswa Aktif',
            'fresh_graduate' => 'Fresh Graduate',
            default => 'Mahasiswa TSU',
        };
    }

    public function hasCompletedScreening(): bool
    {
        return $this->screening_completed_at !== null;
    }

    public function needsScreening(): bool
    {
        return $this->isStudent() && ! $this->hasCompletedScreening();
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'mentor' => 'mentor.dashboard',
            default => 'dashboard',
        };
    }

    public function postAuthRoute(): string
    {
        return $this->needsScreening() ? 'screening.show' : $this->dashboardRoute();
    }
}
