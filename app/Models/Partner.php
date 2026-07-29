<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    protected $fillable = ['name', 'logo', 'website'];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
