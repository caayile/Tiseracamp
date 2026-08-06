<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_code',
        'plan_name',
        'amount',
        'reviews_limit',
        'reviews_used',
        'invoice_code',
        'proof_path',
        'status',
        'admin_note',
        'starts_at',
        'ends_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'reviews_limit' => 'integer',
            'reviews_used' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function (Builder $q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->reviews_limit !== null && $this->reviews_used >= $this->reviews_limit) {
            return false;
        }

        return true;
    }

    public function remainingReviews(): ?int
    {
        if ($this->reviews_limit === null) {
            return null;
        }

        return max(0, $this->reviews_limit - $this->reviews_used);
    }

    public function formattedAmount(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    public function activate(): void
    {
        $days = (int) (config('cv_plans.'.$this->plan_code.'.days') ?? 30);

        $this->forceFill([
            'status' => 'active',
            'paid_at' => $this->paid_at ?? now(),
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
            'reviews_used' => $this->reviews_used ?? 0,
        ])->save();
    }

    public function consumeReview(): void
    {
        if ($this->reviews_limit === null) {
            $this->increment('reviews_used');

            return;
        }

        if ($this->reviews_used < $this->reviews_limit) {
            $this->increment('reviews_used');
        }
    }
}
