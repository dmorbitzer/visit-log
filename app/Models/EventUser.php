<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['event_id', 'user_id', 'permission'])]
class EventUser extends Pivot
{
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
        ];
    }
}
