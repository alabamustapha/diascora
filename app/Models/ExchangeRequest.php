<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ExchangeRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'is_anonymous',
        'from_currency',
        'to_currency',
        'from_amount',
        'to_amount',
        'official_rate_at_posting',
        'offered_rate',
        'payment_method_sending',
        'payment_method_receiving',
        'notes',
        'status',
        'accepted_interest_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'from_amount' => 'decimal:2',
            'to_amount' => 'decimal:2',
            'official_rate_at_posting' => 'decimal:6',
            'offered_rate' => 'decimal:6',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(ExchangeInterest::class);
    }

    public function acceptedInterest(): BelongsTo
    {
        return $this->belongsTo(ExchangeInterest::class, 'accepted_interest_id');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where('status', 'open');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
