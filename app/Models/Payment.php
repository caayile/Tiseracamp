<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'program_id', 'enrollment_id', 'amount', 'invoice_code',
        'proof_path', 'status', 'admin_note', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function formattedAmount(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    /**
     * Activate student enrollment so they can enter the class after payment is approved.
     */
    public function grantClassAccess(): Enrollment
    {
        $this->loadMissing('program.batches');

        $enrollment = Enrollment::firstOrNew([
            'user_id' => $this->user_id,
            'program_id' => $this->program_id,
        ]);

        if (! $enrollment->exists) {
            $enrollment->progress = 0;
            $enrollment->enrolled_at = now();
            $enrollment->batch_id = $this->program?->batches
                ?->firstWhere('status', 'active')
                ?->id
                ?? $this->program?->batches?->first()?->id;
        }

        $enrollment->status = 'active';
        $enrollment->enrolled_at ??= now();
        $enrollment->save();

        if ($this->enrollment_id !== $enrollment->id) {
            $this->forceFill(['enrollment_id' => $enrollment->id])->save();
        }

        return $enrollment;
    }
}
