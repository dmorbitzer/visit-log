<?php

namespace App\Models;

use Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tracking_day_id', 'slot_start', 'slot_end', 'visitor_count'])]
class TimeSlot extends Model
{
    /** @use HasFactory<TimeSlotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'slot_start' => 'datetime:H:i',
            'slot_end' => 'datetime:H:i',
            'visitor_count' => 'integer',
        ];
    }

    public function trackingDay(): BelongsTo
    {
        return $this->belongsTo(TrackingDay::class);
    }
}
