<?php

namespace App\Models;

use Database\Factories\TrackingDayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['event_id', 'date', 'notes'])]
class TrackingDay extends Model
{
    /** @use HasFactory<TrackingDayFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class)->orderBy('slot_start');
    }
}
