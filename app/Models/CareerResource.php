<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerResource extends Model
{
    protected $fillable = ['title', 'type', 'content', 'file_url', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
