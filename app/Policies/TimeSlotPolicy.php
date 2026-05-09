<?php

namespace App\Policies;

use App\Models\TimeSlot;
use App\Models\User;

class TimeSlotPolicy
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
    public function view(User $user, TimeSlot $timeSlot): bool
    {
        return $user->canView($timeSlot->trackingDay->event);
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
    public function update(User $user, TimeSlot $timeSlot): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimeSlot $timeSlot): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the models tracking data.
     */
    public function track(User $user, TimeSlot $timeSlot): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canTrack($timeSlot->trackingDay->event);
    }
}
