<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'thumbnail', 'body', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
