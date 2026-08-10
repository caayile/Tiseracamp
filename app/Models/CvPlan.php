<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvPlan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'tagline',
        'price',
        'reviews',
        'days',
        'badge',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'reviews' => 'integer',
            'days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function toPlanArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'tagline' => $this->tagline ?? '',
            'price' => (int) $this->price,
            'reviews' => $this->reviews === null ? null : (int) $this->reviews,
            'days' => (int) $this->days,
            'badge' => $this->badge,
            'features' => $this->features ?? [],
        ];
    }
}
