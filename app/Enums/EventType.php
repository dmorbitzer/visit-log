<?php

namespace App\Enums;

enum EventType: string
{
    case Recurring = 'recurring';
    case DateRange = 'date_range';
    case CustomDays = 'custom_days';
}
