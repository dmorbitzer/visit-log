<?php

namespace App\Policies;

use App\Models\TrackingDay;
use App\Models\User;

class TrackingDayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TrackingDay $trackingDay): bool
    {
        return $user->canView($trackingDay->event);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TrackingDay $trackingDay): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TrackingDay $trackingDay): bool
    {
        return $user->isAdmin();
    }

    public function updateNotes(User $user, TrackingDay $trackingDay): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canTrack($trackingDay->event);
    }
}
