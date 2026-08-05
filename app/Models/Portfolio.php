<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'project_url', 'image_url', 'portfolio_file_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
