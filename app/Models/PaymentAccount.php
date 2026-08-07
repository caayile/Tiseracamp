<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    protected $fillable = [
        'bank_name',
        'account_number',
        'account_holder',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Rekening aktif yang ditampilkan di checkout.
     */
    public static function current(): self
    {
        $account = static::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if ($account) {
            return $account;
        }

        return static::query()->firstOrCreate(
            ['is_active' => true],
            [
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'PT Tiga Serangkai',
            ]
        );
    }
}
