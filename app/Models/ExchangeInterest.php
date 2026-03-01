<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeInterest extends Model
{
    /** @use HasFactory<\Database\Factories\ExchangeInterestFactory> */
    use HasFactory;

    protected $fillable = [
        'exchange_request_id',
        'user_id',
        'comment',
        'payment_method_sending',
        'payment_method_receiving',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function exchangeRequest(): BelongsTo
    {
        return $this->belongsTo(ExchangeRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
