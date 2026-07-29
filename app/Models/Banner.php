<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['title', 'subtitle', 'cta_text', 'cta_link', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
