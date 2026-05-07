<?php

namespace App\Enums;

enum Permission: string
{
    case Viewer = 'viewer';
    case Tracker = 'tracker';
}
