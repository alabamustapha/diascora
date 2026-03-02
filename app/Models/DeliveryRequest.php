<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DeliveryRequest extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'destination_country',
        'weight_kg',
        'payment_amount',
        'payment_currency',
        'payment_method',
        'description',
        'item_image_path',
        'status',
        'accepted_offer_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(DeliveryOffer::class);
    }

    public function acceptedOffer(): BelongsTo
    {
        return $this->belongsTo(DeliveryOffer::class, 'accepted_offer_id');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where('status', 'open');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function imageUrl(): ?string
    {
        if (! $this->item_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->item_image_path);
    }
}
