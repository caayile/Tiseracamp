<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'body', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeLabel(): string
    {
        return $this->created_at?->format('H:i') ?? '';
    }

    public function dateLabel(): string
    {
        $date = $this->created_at;
        if (! $date) {
            return '';
        }

        if ($date->isToday()) {
            return 'Hari ini';
        }

        if ($date->isYesterday()) {
            return 'Kemarin';
        }

        return $date->translatedFormat('d M Y');
    }
}
