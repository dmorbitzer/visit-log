<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'type',
    'status',
    'recurrence_weekdays',
    'start_date',
    'end_date',
    'tracking_start',
    'tracking_end',
    'slot_interval_minutes',
    'type' => EventType::class,
    'status' => EventStatus::class,
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'recurrence_weekdays' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'tracking_start' => 'datetime:H:i',
            'tracking_end' => 'datetime:H:i',
        ];
    }

    public function trackingDays(): HasMany
    {
        return $this->hasMany(TrackingDay::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas(
            'users',
            fn ($q) => $q->where('user_id', $user->id)
        );
    }
}
